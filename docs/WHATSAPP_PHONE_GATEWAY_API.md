# WhatsApp Phone-Assisted Gateway — API Documentation for Flutter

Base URL: `https://pay.feedtancmg.org`
Web module: `https://pay.feedtancmg.org/whatsapp-app/*` (auth required, called **WhatsApp App** per spec)

> **Critical:** This is NOT WhatsApp Cloud/Business API. The phone's normal WhatsApp app sends the message via Android Intent. The gateway only prepares the share and the user taps **Send**.

---

## 1. Architecture

```
WEB (whatsapp-app) → HTTPS → Laravel → MySQL → polling/WebSocket → Flutter Gateway → Kotlin Intent → WhatsApp → User taps SEND → Recipient
```

Multiple devices: `MOSHI-01`, `TABORA-01`, `ARUSHA-01` — each has `device_code` + `device_token` (Bearer, stored hashed `sha256`).

---

## 2. Device Authentication (reuse SMS Gateway)

Same `sms_devices` table and `DeviceAuthMiddleware` (`device.auth` alias).

Headers for every gateway call:
```
Authorization: Bearer <60-char plain token returned at activation>
X-Device-ID: MOSHI-01   (optional, validated if sent)
```
Or `X-Device-Token`.

If token invalid/expired/revoked or `status != ACTIVE` → `401`.

**Activate device (once per phone):**

`POST /api/v1/auth/device/activate`
```json
{ "activation_code": "482917", "device_info": {"android_version":"14","app_version":"1.0.4"} }
```
→ returns `{ device_id, token }` store in `flutter_secure_storage`.

Same flow as SMS Gateway — admin creates device at `https://pay.feedtancmg.org/sms-gateway/devices/create` → shows 6-digit code → phone enters code.

---

## 3. Web → Create WhatsApp Request

**Web form:** `GET/POST /whatsapp-app/messages/create` → `POST /whatsapp-app/messages`

Form fields ( `multipart/form-data` ):
- `device_id` (int, exists:sms_devices,id) — required, must be `ACTIVE` (OFFLINE allowed but queued)
- `recipient_phone` (string, required) — `+255712345678` normalized to `+255...`
- `message` (string, nullable, max 4000) — text or caption for attachment
- `attachment` (file, nullable, max 25 MB, mimes: pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt,csv,zip) — stored private `storage/app/private/whatsapp_attachments/{uuid}/...`
- `template_id` (optional, exists:whatsapp_templates)

Validation: at least `message` or `attachment` required.

Stored as `whatsapp_messages` with `uuid`, `status=PENDING` (or `QUEUED` if device offline), `requested_at=now()`. No public URL for file.

**Statuses (spec 7):** `PENDING, QUEUED, DELIVERED_TO_DEVICE, OPENING, OPENED, USER_ACTION_REQUIRED, SENT, FAILED, CANCELLED, EXPIRED`

`SENT` is **only** when device confirms user tapped **YES — MARK SENT** — never auto.

---

## 4. Gateway Polling

**Poll (WorkManager, every 30-60s or foreground):**

`GET /api/gateway/whatsapp/commands?limit=10`

Headers: `Authorization: Bearer <token>`

Response `200`:
```json
{
  "success": true,
  "device": "MOSHI-01",
  "count": 2,
  "data": [
    {
      "uuid": "550e8400-e29b-41d4-a716-446655440001",
      "recipient_phone": "+255712345678",
      "message": "Dear John, invoice attached.",
      "has_attachment": true,
      "attachment_name": "Invoice-827.pdf",
      "attachment_mime": "application/pdf",
      "attachment_size": 124800,
      "attachment_url": "https://pay.feedtancmg.org/api/gateway/attachments/550e...001",
      "status": "PENDING",
      "requested_at": "2026-09-13T12:43:00+03:00"
    }
  ]
}
```

**Store locally first** (Drift/SQLite) with `PENDING` before network.

**Notify user:** `New WhatsApp Message — To: John +255... — Document attached — [ OPEN ]` → tap opens request.

---

## 5. Attachment Download (FileProvider)

If `has_attachment` true:

`GET /api/gateway/attachments/{uuid}`

Headers: `Authorization: Bearer <token>` (must own the message via `device_id`)

Response: `200` binary stream `Content-Type: <mime>` `Content-Disposition: attachment; filename="..."` from private `storage/app/private/...`. Authenticated, HTTPS, no public URL. Verify `200`, save to `getTemporaryDirectory()/whatsapp_gateway/{uuid}/file`, verify size/mime, create `content://` URI via `FileProvider` (`android:authorities="com.feedtan.gateway.fileprovider"`).

Log: `Attachment Download Started` → `Attachment Downloaded`.

---

## 6. WhatsApp Intent (Kotlin)

Flutter `MethodChannel("whatsapp_gateway")` → Kotlin:

```kotlin
// Dart
final channel = MethodChannel('whatsapp_gateway');
await channel.invokeMethod('prepareWhatsAppMessage', {
  'phone': '+255712345678', // E.164
  'message': 'Dear John...',
  'attachmentPath': '/data/.../Invoice.pdf' // nullable
});

// Kotlin
private fun prepareWhatsAppMessage(phone: String, message: String?, attachmentPath: String?) {
  val pm = packageManager
  val waPkg = "com.whatsapp" // allow config for com.whatsapp.w4b
  if (pm.getLaunchIntentForPackage(waPkg) == null) {
    result.error("WHATSAPP_NOT_INSTALLED", "WhatsApp is not installed", null); return
  }
  if (attachmentPath != null) {
    val file = File(attachmentPath)
    val uri = FileProvider.getUriForFile(context, "${context.packageName}.fileprovider", file)
    val mime = getMimeType(file) // from extension or probe
    val intent = Intent(Intent.ACTION_SEND).apply {
      `package` = waPkg
      type = mime
      putExtra(Intent.EXTRA_TEXT, message) // caption
      putExtra(Intent.EXTRA_STREAM, uri)
      addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
      // phone: ACTION_SEND does not reliably set recipient; use ACTION_VIEW with wa.me as fallback for text:
      // For text-only, prefer ACTION_VIEW with https://wa.me/<phone>?text=<encoded>
    }
    // Grant permission to WhatsApp
    val resInfo = pm.queryIntentActivities(intent, 0)
    for (ri in resInfo) grantUriPermission(ri.activityInfo.packageName, uri, Intent.FLAG_GRANT_READ_URI_PERMISSION)
    try { startActivity(intent) } catch (e: Exception) { result.error("INTENT_FAILED", e.message, null) }
  } else {
    // Text only: use wa.me deep link
    val encoded = URLEncoder.encode(message ?: "", "UTF-8")
    val uri = Uri.parse("https://wa.me/${phone.replace("+","")}?text=$encoded")
    val intent = Intent(Intent.ACTION_VIEW, uri).apply { `package` = waPkg }
    try { startActivity(intent) } catch(e: Exception) { result.error("INTENT_FAILED", e.message, null) }
  }
  result.success(true)
}
```

Check WhatsApp installed via `packageManager.getPackageInfo`.

Do **not** attempt to press Send — user does.

---

## 7. Status Callbacks (device → server)

All with `Authorization: Bearer <token>`:

```
POST /api/gateway/whatsapp/{uuid}/received
  → DELIVERED_TO_DEVICE, log Received by device

POST /api/gateway/whatsapp/{uuid}/opened
  → OPENED, log WhatsApp Open Requested (after Intent startActivity)

POST /api/gateway/whatsapp/{uuid}/status
  body: { status: "USER_ACTION_REQUIRED"|"FAILED", failure_reason?: "..." }

POST /api/gateway/whatsapp/{uuid}/completed
  → SENT, log User Marked Sent (user tapped YES — MARK SENT)

POST /api/gateway/whatsapp/{uuid}/cancelled
  → USER_ACTION_REQUIRED (user tapped NO — KEEP PENDING)
```

**Do not** mark `SENT` on `OPENED` — distinguish.

Example flow logs:
```
12:43:02 WA-827362 Received by MOSHI-01
12:43:04 Attachment downloaded
12:43:05 WhatsApp launch requested
12:43:19 User returned from WhatsApp
12:43:30 User marked message SENT
```

---

## 8. Web History & Filters

`GET /whatsapp-app/outbox?status=SENT&device_id=1&search=0712...`

Columns: Date | Device | Recipient | Attachment | Status | (full details in drawer)

Filters: Date, Device, Recipient, Status, Created By, Has Attachment

Row click → right drawer (like payments/history) with full details, copy `uuid`/`recipient`, attachment name, status timeline, **Preview WhatsApp** action, **Cancel** if `PENDING/QUEUED`.

Logs at `GET /whatsapp-app/messages/{uuid}/logs` (paginated).

---

## 9. Templates & Variables

`whatsapp_templates` table: `code`, `content` with `{customer_name} {amount} {reference} {date} {invoice_number} {account_number}`

Web replaces variables before storing `message` for device (server-side render).

Example:
```
Payment Confirmation
Hello {customer_name}, we confirm TZS {amount}. Ref: {reference}. Thank you.
```

---

## 10. File Security

- `storeAs(WhatsappMessage::attachmentDir(uuid), safeName, 'local')` → `storage/app/private/...` never public
- `GET /api/gateway/attachments/{uuid}` requires `device.auth` + `device_id` matches
- `max:25600` (25 MB), `mimes` whitelist, `getMimeType()` check
- Auto-delete after configurable period (e.g., 7 days via scheduled `Storage::delete`)
- Audit `whatsapp_message_logs`

Do **not** expose `storage` symlink for these.

---

## 11. Minimal Flutter Proof-of-Concept (Phase 1 → text only)

**UI:**
```
WhatsApp Gateway
Status: 🟢 Connected  Device: MOSHI-01  Pending: 2  Today: 37  Last: 12:43 PM
[Check for Messages]  [WhatsApp Queue]
```

**Dart polling (WorkManager + foreground):**
```dart
final dio = Dio(BaseOptions(baseUrl: 'https://pay.feedtancmg.org', headers: {'Authorization':'Bearer $token'}));
Future<void> poll() async {
  final res = await dio.get('/api/gateway/whatsapp/commands');
  for (final item in res.data['data']) {
    await localDb.insert(item); // PENDING
    await dio.post('/api/gateway/whatsapp/${item['uuid']}/received');
    showNotification(item); // New WhatsApp Message — [ OPEN ]
  }
}
```

**Open WhatsApp:**
```dart
await MethodChannel('whatsapp_gateway').invokeMethod('prepareWhatsAppMessage', {
  'phone': item['recipient_phone'],
  'message': item['message'],
  'attachmentPath': await downloadIfNeeded(item), // null for text-only POC
});
await dio.post('/api/gateway/whatsapp/${item['uuid']}/opened');
 // Show: Did you send? [ YES — MARK SENT ] [ NO — KEEP PENDING ]
 // On YES:
await dio.post('/api/gateway/whatsapp/${item['uuid']}/completed');
```

**First make text-only work**, then add `attachmentPath` handling with `FileProvider`.

---

## 12. cURL Examples

```bash
# Poll
curl -H "Authorization: Bearer <device_token>" https://pay.feedtancmg.org/api/gateway/whatsapp/commands

# Received
curl -X POST -H "Authorization: Bearer <token>" https://pay.feedtancmg.org/api/gateway/whatsapp/550e...001/received

# Opened
curl -X POST -H "Authorization: Bearer <token>" https://pay.feedtancmg.org/api/gateway/whatsapp/550e...001/opened

# Completed (user tapped YES)
curl -X POST -H "Authorization: Bearer <token>" https://pay.feedtancmg.org/api/gateway/whatsapp/550e...001/completed

# Attachment download
curl -H "Authorization: Bearer <token>" https://pay.feedtancmg.org/api/gateway/attachments/550e...001 -o Invoice.pdf
```

---

## 13. Security / Permissions (web)

Middleware `auth`, policies:

`whatsapp.view, whatsapp.create, whatsapp.send, whatsapp.attach, whatsapp.cancel, whatsapp.history, whatsapp.manage_devices`

Roles: Super Admin, Administrator, Accountant, Operator, Viewer, Auditor — restrict `create` to Operator+, `cancel` to owner/Admin.

Device revocation: `sms_devices.status=REVOKED` → `device.auth` returns `403`, no commands.

HTTPS only, rate limit gateway polling (e.g., 60/min), queue for logs.

---

## 14. File Provider (Android)

`AndroidManifest.xml`:
```xml
<provider
  android:name="androidx.core.content.FileProvider"
  android:authorities="${applicationId}.fileprovider"
  android:exported="false"
  android:grantUriPermissions="true">
  <meta-data android:name="android.support.FILE_PROVIDER_PATHS" android:resource="@xml/filepaths" />
</provider>
```
`res/xml/filepaths.xml`:
```xml
<paths><cache-path name="whatsapp_gateway" path="whatsapp_gateway/" /><external-cache-path name="external" path="whatsapp_gateway/" /></paths>
```

---

Ready for Phase 1+2 (`php artisan migrate`) and Phase 4 Kotlin `whatsapp_gateway` MethodChannel. For Play Store, declare no SMS/WhatsApp API bypass — user-assisted only, minimal deps.
