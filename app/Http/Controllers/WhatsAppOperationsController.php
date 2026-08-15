<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class WhatsAppOperationsController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
        $this->middleware(function ($request, $next) {
            if (!auth()->user()?->is_admin) {
                abort(403, 'Admin access required.');
            }
            return $next($request);
        });
    }

    // =========================================================================
    // SEND MESSAGES
    // =========================================================================

    public function sendMessages()
    {
        $contacts = \App\Models\Contact::orderBy('name')->get();
        $groups = \App\Models\WhatsAppGroup::orderBy('name')->get();
        $templates = \App\Models\MessageTemplate::orderBy('name')->get();

        return view('whatsapp.messages.send', compact('contacts', 'groups', 'templates'));
    }

    public function sendMessagesPost(Request $request)
    {
        $validated = $request->validate([
            'message_type' => 'required|in:text,image,document,video,audio',
            'recipient_type' => 'required|in:contact,group,phone',
            'phone' => 'nullable|string|required_if:recipient_type,phone',
            'contact_id' => 'nullable|exists:contacts,id|required_if:recipient_type,contact',
            'group_id' => 'nullable|exists:whatsapp_groups,id|required_if:recipient_type,group',
            'text' => 'nullable|string|required_if:message_type,text',
            'media_url' => 'nullable|url|required_if:message_type,image,document,video,audio',
            'caption' => 'nullable|string',
            'file_name' => 'nullable|string|required_if:message_type,document',
        ]);

        try {
            $results = [];
            
            if ($validated['recipient_type'] === 'phone') {
                $result = $this->sendToPhone($validated);
                $results[] = ['phone' => $validated['phone'], ...$result];
            } elseif ($validated['recipient_type'] === 'contact') {
                $contact = \App\Models\Contact::find($validated['contact_id']);
                $result = $this->sendToPhone(array_merge($validated, ['phone' => $contact->phone]));
                $results[] = ['contact' => $contact->name, 'phone' => $contact->phone, ...$result];
            } elseif ($validated['recipient_type'] === 'group') {
                $group = \App\Models\WhatsAppGroup::find($validated['group_id']);
                $result = $this->sendToGroup($validated, $group->group_id);
                $results[] = ['group' => $group->name, 'group_id' => $group->group_id, ...$result];
            }

            return response()->json(['success' => true, 'results' => $results]);
        } catch (\Exception $e) {
            Log::error('WhatsApp send message error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendBulkMessages(Request $request)
    {
        $validated = $request->validate([
            'message_type' => 'required|in:text,image,document,video,audio',
            'recipient_type' => 'required|in:contacts,groups,custom',
            'contact_ids' => 'nullable|array|required_if:recipient_type,contacts',
            'contact_ids.*' => 'exists:contacts,id',
            'group_ids' => 'nullable|array|required_if:recipient_type,groups',
            'group_ids.*' => 'exists:whatsapp_groups,id',
            'custom_phones' => 'nullable|string|required_if:recipient_type,custom',
            'text' => 'nullable|string|required_if:message_type,text',
            'media_url' => 'nullable|url|required_if:message_type,image,document,video,audio',
            'caption' => 'nullable|string',
            'file_name' => 'nullable|string|required_if:message_type,document',
        ]);

        $results = [];

        try {
            if ($validated['recipient_type'] === 'contacts') {
                foreach ($validated['contact_ids'] as $contactId) {
                    $contact = \App\Models\Contact::find($contactId);
                    $result = $this->sendToPhone(array_merge($validated, ['phone' => $contact->phone]));
                    $results[] = ['contact' => $contact->name, 'phone' => $contact->phone, ...$result];
                }
            } elseif ($validated['recipient_type'] === 'groups') {
                foreach ($validated['group_ids'] as $groupId) {
                    $group = \App\Models\WhatsAppGroup::find($groupId);
                    $result = $this->sendToGroup($validated, $group->group_id);
                    $results[] = ['group' => $group->name, 'group_id' => $group->group_id, ...$result];
                }
            } elseif ($validated['recipient_type'] === 'custom') {
                $phones = array_filter(array_map('trim', explode("\n", $validated['custom_phones'])));
                foreach ($phones as $phone) {
                    $result = $this->sendToPhone(array_merge($validated, ['phone' => $phone]));
                    $results[] = ['phone' => $phone, ...$result];
                }
            }

            return response()->json(['success' => true, 'results' => $results]);
        } catch (\Exception $e) {
            Log::error('WhatsApp bulk send error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    protected function sendToPhone(array $data): array
    {
        switch ($data['message_type']) {
            case 'text':
                return $this->whatsapp->sendText($data['phone'], $data['text']);
            case 'image':
                return $this->whatsapp->sendImage($data['phone'], $data['media_url'], $data['caption'] ?? '');
            case 'document':
                return $this->whatsapp->sendDocument($data['phone'], $data['media_url'], $data['file_name'], $data['caption'] ?? '');
            case 'video':
            case 'audio':
                $payload = [
                    'to' => $data['phone'],
                    $data['message_type'] . 'Url' => $data['media_url'],
                ];
                if (!empty($data['caption'])) {
                    $payload['text'] = $data['caption'];
                }
                return $this->whatsapp->sendWasenderRequest($payload);
            default:
                return ['success' => false, 'message' => 'Invalid message type'];
        }
    }

    protected function sendToGroup(array $data, string $groupId): array
    {
        $payload = ['to' => $groupId];
        
        switch ($data['message_type']) {
            case 'text':
                $payload['text'] = $data['text'];
                break;
            case 'image':
                $payload['imageUrl'] = $data['media_url'];
                if (!empty($data['caption'])) {
                    $payload['text'] = $data['caption'];
                }
                break;
            case 'document':
                $payload['documentUrl'] = $data['media_url'];
                if (!empty($data['caption'])) {
                    $payload['text'] = $data['caption'];
                }
                break;
            case 'video':
            case 'audio':
                $payload[$data['message_type'] . 'Url'] = $data['media_url'];
                if (!empty($data['caption'])) {
                    $payload['text'] = $data['caption'];
                }
                break;
        }

        return $this->whatsapp->sendWasenderRequest($payload);
    }

    // =========================================================================
    // MANAGE CONTACTS
    // =========================================================================

    public function contacts()
    {
        $contacts = \App\Models\Contact::withCount('groups')->orderBy('name')->paginate(20);
        return view('whatsapp.contacts.index', compact('contacts'));
    }

    public function createContact()
    {
        $groups = \App\Models\WhatsAppGroup::orderBy('name')->get();
        return view('whatsapp.contacts.create', compact('groups'));
    }

    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:contacts,phone',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:whatsapp_groups,id',
        ]);

        $contact = \App\Models\Contact::create($validated);
        
        if (!empty($validated['groups'])) {
            $contact->groups()->attach($validated['groups']);
        }

        return redirect()->route('whatsapp.contacts.index')->with('success', 'Contact created successfully!');
    }

    public function editContact($id)
    {
        $contact = \App\Models\Contact::with('groups')->findOrFail($id);
        $groups = \App\Models\WhatsAppGroup::orderBy('name')->get();
        return view('whatsapp.contacts.edit', compact('contact', 'groups'));
    }

    public function updateContact(Request $request, $id)
    {
        $contact = \App\Models\Contact::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', Rule::unique('contacts')->ignore($contact->id)],
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:whatsapp_groups,id',
        ]);

        $contact->update($validated);
        $contact->groups()->sync($validated['groups'] ?? []);

        return redirect()->route('whatsapp.contacts.index')->with('success', 'Contact updated successfully!');
    }

    public function destroyContact($id)
    {
        $contact = \App\Models\Contact::findOrFail($id);
        $contact->delete();
        return response()->json(['success' => true, 'message' => 'Contact deleted successfully!']);
    }

    public function importContacts(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return redirect()->back()->with('error', 'Could not read the uploaded file.');
        }

        $header = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header ?: ['name', 'phone'], array_pad($row, count($header ?: ['name', 'phone']), null));

            if (empty($data['phone'])) {
                $skipped++;
                continue;
            }

            $exists = \App\Models\Contact::where('phone', $data['phone'])->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            \App\Models\Contact::create([
                'name' => $data['name'] ?? $data['phone'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'company' => $data['company'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $imported++;
        }

        fclose($handle);

        return redirect()->back()->with('success', "Imported {$imported} contacts, skipped {$skipped}.");
    }

    public function exportContacts()
    {
        $contacts = \App\Models\Contact::all();

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['Name', 'Phone', 'Email', 'Company', 'Notes']);

        foreach ($contacts as $contact) {
            fputcsv($csv, [$contact->name, $contact->phone, $contact->email, $contact->company, $contact->notes]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="whatsapp-contacts-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // =========================================================================
    // MANAGE GROUPS
    // =========================================================================

    public function groups()
    {
        $groups = \App\Models\WhatsAppGroup::withCount('contacts')->orderBy('name')->paginate(20);
        return view('whatsapp.groups.index', compact('groups'));
    }

    public function createGroup()
    {
        $contacts = \App\Models\Contact::orderBy('name')->get();
        return view('whatsapp.groups.create', compact('contacts'));
    }

    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'group_id' => 'nullable|string|max:100|unique:whatsapp_groups,group_id',
            'description' => 'nullable|string',
            'contacts' => 'nullable|array',
            'contacts.*' => 'exists:contacts,id',
        ]);

        $group = \App\Models\WhatsAppGroup::create($validated);
        
        if (!empty($validated['contacts'])) {
            $group->contacts()->attach($validated['contacts']);
        }

        return redirect()->route('whatsapp.groups.index')->with('success', 'Group created successfully!');
    }

    public function editGroup($id)
    {
        $group = \App\Models\WhatsAppGroup::with('contacts')->findOrFail($id);
        $contacts = \App\Models\Contact::orderBy('name')->get();
        return view('whatsapp.groups.edit', compact('group', 'contacts'));
    }

    public function updateGroup(Request $request, $id)
    {
        $group = \App\Models\WhatsAppGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'group_id' => ['nullable', 'string', 'max:100', Rule::unique('whatsapp_groups')->ignore($group->id)],
            'description' => 'nullable|string',
            'contacts' => 'nullable|array',
            'contacts.*' => 'exists:contacts,id',
        ]);

        $group->update($validated);
        $group->contacts()->sync($validated['contacts'] ?? []);

        return redirect()->route('whatsapp.groups.index')->with('success', 'Group updated successfully!');
    }

    public function destroyGroup($id)
    {
        $group = \App\Models\WhatsAppGroup::findOrFail($id);
        $group->delete();
        return response()->json(['success' => true, 'message' => 'Group deleted successfully!']);
    }

    public function addGroupMembers(Request $request, $id)
    {
        $group = \App\Models\WhatsAppGroup::findOrFail($id);
        
        $request->validate([
            'contacts' => 'required|array',
            'contacts.*' => 'exists:contacts,id',
        ]);

        $group->contacts()->attach($request->contacts);
        
        return response()->json(['success' => true, 'message' => 'Members added successfully!']);
    }

    public function removeGroupMember(Request $request, $id)
    {
        $group = \App\Models\WhatsAppGroup::findOrFail($id);
        
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
        ]);

        $group->contacts()->detach($request->contact_id);
        
        return response()->json(['success' => true, 'message' => 'Member removed successfully!']);
    }

    // =========================================================================
    // MANAGE SESSIONS
    // =========================================================================

    public function sessions()
    {
        $sessions = $this->whatsapp->getSessions();
        $sessionInfo = $this->whatsapp->getSessionInfo();
        
        return view('whatsapp.sessions.index', compact('sessions', 'sessionInfo'));
    }

    public function createSession()
    {
        // This would integrate with Wasender API to create a new session
        return response()->json(['success' => true, 'message' => 'Session creation initiated. Check Wasender dashboard.']);
    }

    public function connectSession($id)
    {
        // Connect session logic
        return response()->json(['success' => true, 'message' => 'Session connect initiated.']);
    }

    public function disconnectSession($id)
    {
        // Disconnect session logic
        return response()->json(['success' => true, 'message' => 'Session disconnected.']);
    }

    public function restartSession($id)
    {
        // Restart session logic
        return response()->json(['success' => true, 'message' => 'Session restarted.']);
    }

    public function destroySession($id)
    {
        // Delete session logic
        return response()->json(['success' => true, 'message' => 'Session deleted.']);
    }

    public function getSessionQr($id)
    {
        // Get QR code for session
        return response()->json(['success' => true, 'qr_code' => 'base64_qr_data']);
    }

    public function getSessionStatus($id)
    {
        // Get session status
        return response()->json(['success' => true, 'status' => 'connected']);
    }

    // =========================================================================
    // MANAGE WEBHOOKS
    // =========================================================================

    public function webhooks()
    {
        $webhooks = \App\Models\WhatsAppWebhook::orderBy('created_at', 'desc')->paginate(20);
        return view('whatsapp.webhooks.index', compact('webhooks'));
    }

    public function createWebhook()
    {
        return view('whatsapp.webhooks.create');
    }

    public function generateWebhookUrl()
    {
        $token = \Illuminate\Support\Str::random(40);

        return response()->json([
            'success' => true,
            'token' => $token,
            'url' => route('whatsapp.webhook.receive', ['token' => $token]),
        ]);
    }

    public function generateWebhookSecret()
    {
        return response()->json([
            'success' => true,
            'secret' => 'whsec_' . \Illuminate\Support\Str::random(32),
        ]);
    }

    public function storeWebhook(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'events.*' => 'in:message_received,message_sent,message_delivered,message_read,session_status,contact_update,group_update',
            'secret' => 'nullable|string|max:255',
            'token' => 'nullable|string|max:64',
            'is_active' => 'boolean',
        ]);

        $validated['token'] = $validated['token'] ?? \Illuminate\Support\Str::random(40);
        $validated['secret'] = $validated['secret'] ?? 'whsec_' . \Illuminate\Support\Str::random(32);

        $webhook = \App\Models\WhatsAppWebhook::create($validated);

        return redirect()->route('whatsapp.webhooks.index')->with('success', 'Webhook created successfully!');
    }

    public function editWebhook($id)
    {
        $webhook = \App\Models\WhatsAppWebhook::findOrFail($id);
        return view('whatsapp.webhooks.edit', compact('webhook'));
    }

    public function updateWebhook(Request $request, $id)
    {
        $webhook = \App\Models\WhatsAppWebhook::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'events.*' => 'in:message_received,message_sent,message_delivered,message_read,session_status,contact_update,group_update',
            'secret' => 'nullable|string|max:255',
            'token' => 'nullable|string|max:64',
            'is_active' => 'boolean',
        ]);

        $validated['token'] = $validated['token'] ?? $webhook->token ?? \Illuminate\Support\Str::random(40);
        $validated['secret'] = $validated['secret'] ?? $webhook->secret ?? 'whsec_' . \Illuminate\Support\Str::random(32);

        $webhook->update($validated);

        return redirect()->route('whatsapp.webhooks.index')->with('success', 'Webhook updated successfully!');
    }

    public function destroyWebhook($id)
    {
        $webhook = \App\Models\WhatsAppWebhook::findOrFail($id);
        $webhook->delete();
        return response()->json(['success' => true, 'message' => 'Webhook deleted successfully!']);
    }

    public function testWebhook($id)
    {
        $webhook = \App\Models\WhatsAppWebhook::findOrFail($id);
        
        // Send test payload
        $testPayload = [
            'event' => 'test',
            'timestamp' => now()->toISOString(),
            'data' => [
                'message' => 'This is a test webhook from FEEDTAN',
            ],
        ];

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Webhook-Secret' => $webhook->secret,
            ])->post($webhook->url, $testPayload);

            return response()->json([
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Test webhook sent successfully!' : 'Webhook test failed: ' . $response->body(),
                'status_code' => $response->status(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook test failed: ' . $e->getMessage(),
            ]);
        }
    }

    // =========================================================================
    // MEDIA & FILES
    // =========================================================================

    public function media()
    {
        $mediaFiles = \App\Models\WhatsAppMedia::orderBy('created_at', 'desc')->paginate(20);
        return view('whatsapp.media.index', compact('mediaFiles'));
    }

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
            'type' => 'required|in:image,document,video,audio',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Upload to Wasender
        $result = $this->whatsapp->uploadFile($file);

        if ($result['success']) {
            $media = \App\Models\WhatsAppMedia::create([
                'name' => $originalName,
                'type' => $request->type,
                'mime_type' => $mimeType,
                'size' => $size,
                'url' => $result['data']['url'] ?? $result['data']['fileUrl'] ?? null,
                'wasender_id' => $result['data']['id'] ?? null,
            ]);

            return response()->json(['success' => true, 'data' => $media]);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 500);
    }

    public function destroyMedia($id)
    {
        $media = \App\Models\WhatsAppMedia::findOrFail($id);
        $media->delete();
        return response()->json(['success' => true, 'message' => 'Media deleted successfully!']);
    }

    public function downloadMedia($id)
    {
        $media = \App\Models\WhatsAppMedia::findOrFail($id);

        if (!$media->url) {
            return redirect()->back()->with('error', 'File URL not found.');
        }

        $response = \Illuminate\Support\Facades\Http::timeout(60)->get($media->url);

        if (!$response->successful()) {
            return redirect()->back()->with('error', 'Could not fetch the file from the remote server.');
        }

        return response($response->body(), 200, [
            'Content-Type' => $media->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $media->name . '"',
        ]);
    }
}