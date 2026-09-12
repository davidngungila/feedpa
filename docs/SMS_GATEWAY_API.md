# SMS Gateway & Accounting Integration — API Documentation for Flutter App

Base URL: `https://pay.feedtancmg.org`

All device APIs are under `https://pay.feedtancmg.org/api/v1`. Web UI is at `https://pay.feedtancmg.org/sms-gateway/*` (auth required).

---

## 1. Overview

```
M-Pesa/Airtel/Mixx/HaloPesa → SIM → 📱 Flutter Gateway → HTTPS → Laravel → MySQL → 🌐 Web (Tabora)
```

Flutter gateway responsibilities:
- Listen `android.provider.Telephony.SMS_RECEIVED` via Kotlin BroadcastReceiver
- Persist SMS locally BEFORE network (SQLite/Drift)
- Queue states: RECEIVED → PENDING → PROCESSING → SENT / FAILED → RETRY → SENT
- Auto retry on internet restore, handle phone restart, battery optimization
- Report heartbeat (battery, network, pending queue)
- Managed activation via 6-digit code (prevents unknown devices)
- Per-device revocable token (Bearer)

---

## 2. Authentication

### DeviceAuthMiddleware `app/Http/Middleware/DeviceAuthMiddleware.php:1`

Every gateway has its own token. Revoking MOSHI-01 does not affect MOSHI-02.

**How to send token:**
```
Authorization: Bearer <64-char hex token plain>
```
or
```
X-Device-Token: <token>
X-Device-ID: MOSHI-01   (optional, validated if present)
```

If token invalid / expired / device not ACTIVE → `401`.

---

## 3. Device Activation Flow

### 3.1 Admin creates device (web)

`POST /sms-gateway/devices` (auth: web user, is_admin recommended)
```json
{
  "device_code": "MOSHI-01",
  "name": "Moshi Main Phone",
  "location_id": 1,
  "phone_number": "25574xxxxxxx",
  "sim_operator": "Vodacom"
}
```
Response: `302` redirect with flash `Activation code: 849215`. Retrieve via `GET /sms-gateway/devices/{id}`.

Server creates:
```php
SmsDevice::create([... 'status'=>'PENDING']);
generateActivationCode() → 6 digits, ttl 60 min, unique
```

### 3.2 Flutter: Activate Device

**POST** `/api/v1/auth/device/activate`  — PUBLIC (no device token yet)

Headers: `Content-Type: application/json`, `Accept: application/json`

Body:
```json
{
  "activation_code": "849215",
  "device_info": {
    "android_version": "14",
    "app_version": "1.0.4"
  }
}
```

cURL:
```bash
curl -X POST https://pay.feedtancmg.org/api/v1/auth/device/activate \
  -H "Content-Type: application/json" \
  -d '{"activation_code":"849215","device_info":{"android_version":"14","app_version":"1.0.4"}}'
```

Success `200`:
```json
{
  "success": true,
  "message": "Device activated successfully.",
  "data": {
    "device_id": "MOSHI-01",
    "device_db_id": 1,
    "device_name": "Moshi Main Phone",
    "location": "Moshi",
    "status": "ACTIVE",
    "token": "a1b2c...60 chars (STORE SECURELY)",
    "token_hint": "xyz1"
  }
}
```

Errors:
- `404` Invalid code
- `422` Expired
- `403` SUSPENDED/REVOKED

**Flutter storage:** Persist `device_id` (MOSHI-01) + `token` securely (flutter_secure_storage). All subsequent calls use `Authorization: Bearer <token>`.

### 3.3 Alternative login (re-activate)

**POST** `/api/v1/auth/device/login`
```json
{
  "device_code": "MOSHI-01",
  "activation_code": "NEWCODE"
}
```

---

## 4. Authenticated Gateway Endpoints

### 4.1 GET /api/v1/device/config

Fetch remote safe config (no hidden remote-control).

Headers: `Authorization: Bearer <token>`

Response `200`:
```json
{
  "success": true,
  "data": {
    "device_id": "MOSHI-01",
    "status": "ACTIVE",
    "config": {
      "auto_sync": true,
      "retry_interval_seconds": 30,
      "heartbeat_interval_seconds": 60,
      "max_queue": 10000,
      "notifications": true,
      "batch_size": 50
    },
    "server_time": "2026-09-12T19:43:12+03:00"
  }
}
```

Poll every ~5 min or on resume.

### 4.2 POST /api/v1/device/heartbeat

Every 60s (configurable). Server uses `last_heartbeat_at` to show 🟢 ONLINE (threshold 180s).

Body:
```json
{
  "battery": 82,
  "network": "4G",
  "pending_sms": 0,
  "app_version": "1.0.4",
  "android_version": "14",
  "signal": "Good"
}
```

cURL:
```bash
curl -X POST https://pay.feedtancmg.org/api/v1/device/heartbeat \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"battery":82,"network":"4G","pending_sms":1,"app_version":"1.0.4"}'
```

Response `200`:
```json
{
  "success": true,
  "message": "Heartbeat recorded.",
  "data": {
    "device_id": "MOSHI-01",
    "server_time": "2026-09-12T19:43:13+03:00",
    "pending_sms_server": 0
  }
}
```

### 4.3 GET /api/v1/device/status

Same auth. Returns current device snapshot + `is_online`.

---

## 5. SMS Synchronization (Core)

### 5.1 POST /api/v1/sms/batch  ← PRIMARY

Send 1..100 SMS in one request. **Idempotent** via `hash`.

Server hash = `SHA256(device_code|sender|timestamp_Y-m-d H:i:s|body)`

Duplicate → `DUPLICATE` (no new transaction, safe to retry).

Body:
```json
{
  "messages": [
    {
      "device_message_id": "local-uuid-1",
      "uuid": "optional-client-uuid",
      "sender": "MPesa",
      "body": "You have received TZS 150,000 from 074XXXXXXX. Transaction ID MPX827362. New balance TZS 1,200,000",
      "timestamp": "2026-09-12 19:43:12"
    },
    {
      "device_message_id": "local-uuid-2",
      "sender": "AirtelMoney",
      "body": "You have received TZS 75,000 from 0681234567 TxnId AIR123456",
      "timestamp": "2026-09-12T19:43:13+03:00"
    }
  ]
}
```

Fields:
- `sender` required: provider name or phone (`MPesa`, `TigoPesa`, `068...`)
- `body` required: full SMS text (max 2000)
- `timestamp` required: when SMS was received on device (ISO8601 or `Y-m-d H:i:s`). Server normalizes to Carbon.
- `device_message_id` / `uuid`: local unique id for tracing. If `uuid` omitted server generates one.

cURL:
```bash
curl -X POST https://pay.feedtancmg.org/api/v1/sms/batch \
  -H "Authorization: Bearer <token>" \
  -H "X-Device-ID: MOSHI-01" \
  -H "Content-Type: application/json" \
  -d '{
    "messages": [{
      "device_message_id": "sms_001",
      "sender": "MPesa",
      "body": "You have received TZS 150,000 from 0742123456. Transaction ID MPX827362.",
      "timestamp": "2026-09-12 19:43:12"
    }]
  }'
```

Success `200`:
```json
{
  "success": true,
  "message": "Batch processed: 1 created, 0 duplicates.",
  "data": {
    "created": 1,
    "duplicates": 0,
    "failed": 0,
    "results": [
      {
        "index": 0,
        "device_message_id": "sms_001",
        "status": "SENT",
        "server_id": 101,
        "uuid": "550e8400-e29b-41d4-a716-446655440001",
        "hash": "abc...64hex",
        "provider": "MPESA",
        "parsed": {
          "amount": 150000,
          "reference": "MPX827362",
          "type": "PAYMENT"
        }
      }
    ]
  }
}
```

Duplicate entry:
```json
{
  "index": 1,
  "device_message_id": "sms_002",
  "status": "DUPLICATE",
  "server_id": 99,
  "hash": "samehash..."
}
```

**Flutter logic:**
```dart
// Pseudocode
for (var sms in localQueue.where(status==PENDING).take(50)) {
  final batch = queue.map((s) => {
    'device_message_id': s.id,
    'sender': s.sender,
    'body': s.body,
    'timestamp': s.timestamp.toIso8601String(),
  }).toList();
  final res = await dio.post('/api/v1/sms/batch', data:{'messages':batch}, options: Options(headers:{'Authorization':'Bearer $token'}));
  for (var r in res.data['data']['results']) {
    if (r['status']=='SENT') localDb.markSent(r['device_message_id'], serverId:r['server_id']);
    if (r['status']=='DUPLICATE') localDb.markSent(r['device_message_id'], serverId:r['server_id']); // also done
  }
}
// On failure keep PENDING → WorkManager retry every retry_interval_seconds
```

### 5.2 POST /api/v1/sms  (single)

Same as batch but for one SMS:

```json
{
  "sender": "MPesa",
  "body": "You have received TZS 50,000...",
  "timestamp": "2026-09-12 19:43:12",
  "device_message_id": "local-1"
}
```
Internally calls batch.

### 5.3 GET /api/v1/sms/{id}/status

Check server status of a previously synced SMS (scoped to your device).

Response `200`:
```json
{
  "success": true,
  "data": {
    "id": 101,
    "uuid": "...",
    "sender": "MPesa",
    "body": "...",
    "sync_status": "SENT",
    "processing_status": "PROCESSED",
    "reconciliation_status": "UNRECONCILED",
    "sms_transaction": { "amount": "150000.00", "reference": "MPX827362", "provider_code": "MPESA" },
    "reconciliation": { "status": "UNRECONCILED" }
  }
}
```

---

## 6. Server-Side Processing Details

1. **Validate device token** → `DeviceAuthMiddleware`
2. **For each message:**
   - Parse timestamp
   - Compute dedup `hash` → if exists skip
   - `SmsParserService::parse()` → provider detection + amount/reference/type/balance
   - Create `sms_messages` (sync_status=SENT, processing_status=PROCESSED)
   - Create `sms_transactions` (amount, reference, counterparty, type)
   - `attemptAutoReconcile()` → try match `transactions.order_reference` or `payouts.order_reference` or amount+phone window; create `sms_reconciliations` (MATCHED or UNRECONCILED)
3. **Update** `sms_devices.last_sync_at`, `last_sms_at`
4. **Log** `sms_sync_logs` batch counts

### Providers Supported

Seed in `sms_providers` table:
- `MPESA` (sender_ids: MPesa,M-Pesa, sender detection: mpesa)
- `AIRTEL` (AirtelMoney)
- `MIXX` / `YAS` (TigoPesa,Mixx,Yas)
- `HALOPESA` (HaloPesa,Halotel)

Detection via `SmsProvider::detectProvider(sender, body)` — checks `sender_ids` CSV and `detection_keywords` JSON.

### Transaction Types

`PAYMENT`, `DEPOSIT`, `WITHDRAWAL`, `TRANSFER`, `REVERSAL`, `BALANCE`, `FAILED`, `OTHER` (via `SmsParserService::detectType`)

### Reconciliation Statuses

`UNRECONCILED`, `MATCHED`, `RECONCILED`, `DUPLICATE`, `REVERSED`, `FAILED` (web accountant can manually `RECONCILED` via `POST /sms-gateway/reconciliation/{id}/match`)

---

## 7. Reliability Requirements for Flutter

1. **Save locally first:** `BroadcastReceiver → Drift/SQLite insert (PENDING) → enqueue WorkManager`
2. **Never lose on offline:** if `POST /sms/batch` fails (no internet/server 5xx) keep PENDING; WorkManager retries with exponential backoff (`retry_interval_seconds` from config, default 30s)
3. **Idempotency:** server deduplicates by hash; client may safely resend same batch after timeout
4. **Phone restart:** register `BOOT_COMPLETED` → restart listener + sync pending queue
5. **Battery optimization:** show UI `Battery Exemption: ⚠️ [Fix Settings]` → intent `ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS` (note: OEMs may still restrict)
6. **Permissions:** on first launch explain why SMS needed; request `RECEIVE_SMS`, `READ_SMS`, `READ_PHONE_STATE` (SIM), `POST_NOTIFICATIONS` (Android 13+)
7. **Heartbeat:** `WorkManager` periodic 60s even if no SMS

Example dashboard values to display (as per spec section 3):
```
Device MOSHI-01 🟢 Gateway Active
SMS Listener 🟢 ON | Auto Sync 🟢 ON | Internet 🟢 ONLINE
SMS Today 127 | Synced 126 | Pending 1 | Failed 0
Last SMS 19:43:12 | Last Sync 19:43:13 | Battery 82% | Network 4G
```

---

## 8. Web UI (for Accountant in Tabora)

Sidebar: **SMS Gateway** → **SMS** (SMS-only menu, per requirement)

- `GET /sms-gateway` — Gateway Dashboard (devices online, SMS today, payments TZS)
- `GET /sms-gateway/sms` — Live SMS Inbox (filters: device, provider, status, date; realtime via future WebSocket)
- `GET /sms-gateway/sms/{id}` — SMS detail + extracted transaction + reconciliation actions
- `GET /sms-gateway/devices` — Devices list, health, activation
- `GET /sms-gateway/devices/create` — Register new device → generates activation code
- `GET /sms-gateway/devices/{device}` — Health page (battery, network, last heartbeat)
- `GET /sms-gateway/reconciliation` — Reconciliation queue (manual match)

Roles: accountant can view/reconcile; admin can manage devices; auditor read-only (enforce via `is_admin` + policies).

---

## 9. Error Handling

Standard envelope:
```json
{"success": false, "message": "Invalid activation code."}
```

Status codes: `200` success, `401` auth, `403` suspended, `404` not found, `422` validation, `500` server.

Validate all inputs; never expose MySQL directly.

---

## 10. Testing Checklist for Flutter

1. Register MOSHI-01 on web → note code `849215`
2. On fresh phone: `POST /api/v1/auth/device/activate` → store token
3. `POST /api/v1/device/heartbeat` → expect 200, `last_heartbeat_at` updates
4. `GET /api/v1/device/config` → verify config
5. Airplane ON → send test SMS to SIM → verify local DB PENDING
6. Airplane OFF → verify auto batch sync → server inbox shows SMS with parsed amount/reference
7. Resend same batch → expect `DUPLICATE` (0 created)
8. Check web `/sms-gateway/sms` shows provider extracted, reconciliation UNRECONCILED or MATCHED
9. Reboot phone → verify listener restarts and pending queue syncs without manual action
10. Revoke device on web → next heartbeat/batch → `401`

---

## 11. Security Notes

- HTTPS/TLS required
- Tokens are SHA256 hashed at rest (`sms_device_tokens.token`); plain shown only once
- Rate-limit device endpoints (e.g., 60/min per device - add via Laravel throttle if needed)
- Log audit for device create/revoke/activate

---

## 12. File Map

- Migration: `database/migrations/2026_09_12_000001_create_sms_gateway_tables.php`
- Models: `app/Models/Sms*` (Device, Message, Transaction, Reconciliation, Provider, Location, ParsingRule, DeviceToken, Heartbeat)
- Service: `app/Services/SmsParserService.php`
- Middleware: `app/Http/Middleware/DeviceAuthMiddleware.php` alias `device.auth`
- API Controller: `app/Http/Controllers/Api/SmsGatewayController.php`
- Web Controllers: `app/Http/Controllers/Sms*`
- Routes: `routes/api_sms_gateway.php` (mounted in `routes/api.php` → `/api/v1/*`), `routes/web_sms_gateway.php` (mounted in `routes/web.php` → `/sms-gateway/*`)
- Views: `resources/views/sms-gateway/{dashboard,inbox,devices,reconciliation}/*`
- Sidebar: `resources/views/layouts/app.blade.php` → **SMS Gateway** menu (SMS ONLY badge), `resources/views/layouts/navigation.blade.php`

---

## 13. Example Flutter (Dart) Snippets

```dart
// dio setup
final dio = Dio(BaseOptions(baseUrl: 'https://pay.feedtancmg.org', connectTimeout: Duration(seconds: 15)));
dio.interceptors.add(LogInterceptor());

// Activate
Future<void> activate(String code) async {
  final res = await dio.post('/api/v1/auth/device/activate', data: {
    'activation_code': code,
    'device_info': {'android_version': androidVersion, 'app_version': packageInfo.version}
  });
  await secureStorage.write(key:'device_token', value: res.data['data']['token']);
  await secureStorage.write(key:'device_code', value: res.data['data']['device_id']);
}

// Heartbeat periodic
Timer.periodic(Duration(seconds: config.heartbeatInterval), (_) async {
  final token = await secureStorage.read(key:'device_token');
  await dio.post('/api/v1/device/heartbeat', data: {
    'battery': await battery.level,
    'network': await connectivity.result, // 4G/WiFi
    'pending_sms': await localDb.pendingCount(),
    'app_version': packageInfo.version,
  }, options: Options(headers:{'Authorization':'Bearer $token'}));
});

// SMS receiver (Kotlin BroadcastReceiver forwards to Flutter via MethodChannel)
// then batch sync
Future<void> syncPending() async {
  final pendings = await localDb.getPendings(limit: 50);
  if (pendings.isEmpty) return;
  final token = await secureStorage.read(key:'device_token');
  final res = await dio.post('/api/v1/sms/batch',
    data: {'messages': pendings.map((s)=>{'device_message_id':s.id,'sender':s.sender,'body':s.body,'timestamp':s.timestamp.toIso8601String()}).toList()},
    options: Options(headers:{'Authorization':'Bearer $token','X-Device-ID': await secureStorage.read(key:'device_code')}),
  );
  for (var r in res.data['data']['results']) {
    await localDb.updateStatus(r['device_message_id'], r['status']); // SENT or DUPLICATE => done
  }
}
```

---

Ready for `php artisan migrate` and Flutter integration. For Play Store distribution, declare SMS permissions compliance; for internal fleet prefer managed distribution (not public Play).
