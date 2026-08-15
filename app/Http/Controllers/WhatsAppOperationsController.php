<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\LengthAwarePaginator;

class WhatsAppOperationsController extends Controller implements HasMiddleware
{
    protected WhatsAppService $whatsapp;

    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                if (!auth()->user()?->is_admin) {
                    abort(403, 'Admin access required.');
                }
                return $next($request);
            }),
        ];
    }

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
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
        $result = $this->whatsapp->getContactsRaw();
        $contacts = [];
        $error = null;

        if (($result['success'] ?? false) && is_array($result['data'] ?? null)) {
            if (isset($result['data']['items']) && is_array($result['data']['items'])) {
                $contacts = $result['data']['items'];
            } else {
                $contacts = $result['data'];
            }
        } else {
            $error = $result['message'] ?? 'Could not load contacts from the WhatsApp API.';
        }

        return view('whatsapp.contacts.index', compact('contacts', 'error'));
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
        $apiKeyConfigured = $this->whatsapp->hasSessionApiKey();
        $liveGroups = $apiKeyConfigured ? $this->whatsapp->getGroups() : [];
        $groups = [];

        foreach ($liveGroups as $group) {
            $jid = $group['jid'] ?? $group['id'] ?? '';
            $meta = $jid ? $this->whatsapp->getGroupMetadata($jid) : [];

            $groups[] = [
                'jid' => $jid,
                'name' => $group['name'] ?? $meta['subject'] ?? 'Unknown Group',
                'img_url' => $group['imgUrl'] ?? $this->whatsapp->getGroupPicture($jid),
                'description' => $meta['desc'] ?? null,
                'owner' => $meta['owner'] ?? null,
                'creation' => isset($meta['creation']) ? date('Y-m-d H:i', (int) $meta['creation']) : null,
                'participants_count' => count($meta['participants'] ?? []),
                'participants' => $meta['participants'] ?? [],
            ];
        }

        return view('whatsapp.groups.index', compact('groups', 'apiKeyConfigured'));
    }

    public function groupDetails($jid)
    {
        $metaResult = $this->whatsapp->getGroupMetadataRaw($jid);
        $meta = ($metaResult['success'] ?? false) && is_array($metaResult['data'] ?? null) ? $metaResult['data'] : [];
        $participants = $this->whatsapp->getGroupParticipants($jid);
        $metaError = empty($meta) ? ($metaResult['message'] ?? 'Could not load group metadata from the WhatsApp API.') : null;

        if (empty($meta) && empty($participants)) {
            return redirect()->route('whatsapp.groups.index')
                ->with('error', 'Could not load group details from the WhatsApp API.');
        }

        $listName = null;
        if (empty($meta['subject'])) {
            foreach ($this->whatsapp->getGroups() as $g) {
                if (($g['id'] ?? '') === $jid || ($g['jid'] ?? '') === $jid) {
                    $listName = $g['name'] ?? null;
                    break;
                }
            }
        }

        $creation = null;
        if (!empty($meta['creation'])) {
            $creation = ctype_digit((string) $meta['creation'])
                ? date('Y-m-d H:i', (int) $meta['creation'])
                : (string) $meta['creation'];
        }

        $group = [
            'jid' => $jid,
            'name' => $meta['subject'] ?? $listName ?? 'Unknown Group',
            'img_url' => $this->whatsapp->getGroupPicture($jid),
            'description' => $meta['desc'] ?? $meta['description'] ?? null,
            'owner' => $meta['ownerPn'] ?? $meta['ownerJid'] ?? $meta['owner'] ?? null,
            'creation' => $creation,
            'participants' => $participants,
        ];

        $personalTokenConfigured = $this->whatsapp->hasPersonalAccessToken();
        $session = null;
        $messageLogs = [];
        $logsError = null;

        if ($personalTokenConfigured) {
            $sessions = $this->whatsapp->getSessions();
            $session = $sessions[0] ?? null;

            if ($session && !empty($session['id'])) {
                $logsResult = $this->whatsapp->getMessageLogs((int) $session['id'], 1, 100);

                if (($logsResult['success'] ?? false) && is_array($logsResult['data']['data'] ?? null)) {
                    foreach ($logsResult['data']['data'] as $log) {
                        if (stripos((string) ($log['to'] ?? ''), $jid) !== false) {
                            $messageLogs[] = $log;
                        }
                    }
                } else {
                    $logsError = $logsResult['message'] ?? 'Could not load message logs.';
                }
            }
        }

        return view('whatsapp.groups.show', compact('group', 'personalTokenConfigured', 'session', 'messageLogs', 'logsError', 'metaError'));
    }

    public function addGroupParticipants(Request $request, $jid)
    {
        $validated = $request->validate([
            'participants' => 'required|string',
        ]);

        $participants = array_values(array_filter(array_map(
            'trim',
            preg_split('/[\s,;]+/', $validated['participants']) ?: []
        )));

        if (empty($participants)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter at least one phone number.',
            ], 422);
        }

        $result = $this->whatsapp->addGroupParticipants($jid, $participants);

        $items = [];
        foreach (($result['data'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }

            $status = (string) ($item['status'] ?? '');
            $phone = $item['content']['attrs']['phone_number'] ?? $item['jid'] ?? '';
            $phone = preg_replace('/@s\.whatsapp\.net$/', '', $phone);

            $message = match ($status) {
                '200' => 'Added',
                '403' => 'Not authorized - the connected WhatsApp number needs admin rights in the group',
                '409' => 'Already a member of this group',
                default => $item['content']['attrs']['error'] ?? $item['message'] ?? 'Failed (code ' . $status . ')',
            };

            $items[] = [
                'status' => (int) $status,
                'jid'    => $phone,
                'message'=> $message,
            ];
        }

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Failed to add participants.',
            'data'    => $items,
        ], ($result['success'] ?? false) ? 200 : 400);
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
        $personalTokenConfigured = $this->whatsapp->hasPersonalAccessToken();

        return view('whatsapp.sessions.index', compact('sessions', 'sessionInfo', 'personalTokenConfigured'));
    }

    public function messageLogs(Request $request, $id)
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 20);
        $session = $this->whatsapp->getSessionDetails((int) $id);
        $personalTokenConfigured = $this->whatsapp->hasPersonalAccessToken();

        $result = $this->whatsapp->getMessageLogs((int) $id, $page, $perPage);

        $items = [];
        $paginator = null;
        $error = null;

        if (($result['success'] ?? false) && is_array($result['data'] ?? null)) {
            $items = $result['data']['data'] ?? [];

            $paginator = new LengthAwarePaginator(
                $items,
                (int) ($result['data']['total'] ?? 0),
                (int) ($result['data']['per_page'] ?? $perPage),
                (int) ($result['data']['current_page'] ?? $page),
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            );
        } else {
            $error = $result['message'] ?? 'Failed to fetch message logs.';
        }

        return view('whatsapp.sessions.message-logs', compact('id', 'session', 'items', 'paginator', 'error', 'personalTokenConfigured'));
    }

    public function createSession(Request $request)
    {
        $result = $this->whatsapp->createSession($request->only(['name', 'phone_number']));

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Session creation failed.',
            'data'    => $result['data'] ?? null,
        ], ($result['success'] ?? false) ? 200 : 400);
    }

    public function connectSession($id)
    {
        $result = $this->whatsapp->connectSession((int) $id);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Session connect failed.',
            'data'    => $result['data'] ?? null,
        ], ($result['success'] ?? false) ? 200 : 400);
    }

    public function disconnectSession($id)
    {
        $result = $this->whatsapp->disconnectSession((int) $id);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Session disconnect failed.',
            'data'    => $result['data'] ?? null,
        ], ($result['success'] ?? false) ? 200 : 400);
    }

    public function restartSession($id)
    {
        $result = $this->whatsapp->restartSession((int) $id);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Session restart failed.',
            'data'    => $result['data'] ?? null,
        ], ($result['success'] ?? false) ? 200 : 400);
    }

    public function destroySession($id)
    {
        $result = $this->whatsapp->deleteSession((int) $id);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Session delete failed.',
            'data'    => $result['data'] ?? null,
        ], ($result['success'] ?? false) ? 200 : 400);
    }

    public function getSessionQr($id)
    {
        $result = $this->whatsapp->getSessionQr((int) $id);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Failed to fetch QR code.',
            'data'    => $result['data'] ?? null,
        ], ($result['success'] ?? false) ? 200 : 400);
    }

    public function getSessionStatus($id)
    {
        $result = $this->whatsapp->getSessionStatus((int) $id);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Failed to fetch session status.',
            'data'    => $result['data'] ?? null,
        ], ($result['success'] ?? false) ? 200 : 400);
    }

    // =========================================================================
    // MANAGE WEBHOOKS
    // =========================================================================

    public function webhooks()
    {
        $personalTokenConfigured = $this->whatsapp->hasPersonalAccessToken();
        $sessions = $personalTokenConfigured ? $this->whatsapp->getSessions() : [];
        $webhooks = [];

        foreach ($sessions as $session) {
            $details = $this->whatsapp->getSessionDetails((int) ($session['id'] ?? 0));

            $webhooks[] = array_merge($session, [
                'webhook_url'      => $details['webhook_url'] ?? null,
                'webhook_enabled'  => (bool) ($details['webhook_enabled'] ?? false),
                'webhook_events'   => $details['webhook_events'] ?? [],
                'webhook_secret'   => $details['webhook_secret'] ?? null,
                'api_key'          => $details['api_key'] ?? null,
            ]);
        }

        return view('whatsapp.webhooks.index', compact('webhooks', 'personalTokenConfigured'));
    }

    public function createWebhook()
    {
        $sessions = $this->whatsapp->getSessions();
        $events = $this->webhookEvents();
        $canonicalUrl = rtrim(config('app.url', url('/')), '/') . '/api/whatsapp/webhook';
        $personalTokenConfigured = $this->whatsapp->hasPersonalAccessToken();

        return view('whatsapp.webhooks.create', compact('sessions', 'events', 'canonicalUrl', 'personalTokenConfigured'));
    }

    protected function webhookEvents(): array
    {
        return [
            'messages.received',
            'messages-group.received',
            'messages-newsletter.received',
            'messages-personal.received',
            'call',
            'message.sent',
            'session.status',
            'qrcode.updated',
            'passkey.updated',
            'messages.upsert',
            'messages.update',
            'messages.delete',
            'message-receipt.update',
            'messages.reaction',
            'chats.upsert',
            'chats.update',
            'chats.delete',
            'groups.upsert',
            'groups.update',
            'group-participants.update',
            'contacts.upsert',
            'contacts.update',
            'poll.results',
        ];
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
            'session_id' => 'nullable|integer|required_without:session_id_manual',
            'session_id_manual' => 'nullable|integer|required_without:session_id',
            'webhook_url' => 'required|url|max:500',
            'webhook_enabled' => 'nullable|boolean',
            'events' => 'nullable|array',
            'events.*' => 'string',
            'webhook_secret' => 'nullable|string|max:255',
        ]);

        $sessionId = (int) ($validated['session_id'] ?? $validated['session_id_manual']);

        $payload = [
            'webhook_url' => $validated['webhook_url'],
            'webhook_enabled' => (bool) ($validated['webhook_enabled'] ?? false),
            'webhook_events' => $validated['events'] ?? [],
        ];

        if (!empty($validated['webhook_secret'])) {
            $payload['webhook_secret'] = $validated['webhook_secret'];
        }

        $result = $this->whatsapp->updateSession($sessionId, $payload);

        if (($result['success'] ?? false)) {
            return redirect()->route('whatsapp.webhooks.index')
                ->with('success', 'Webhook configured on WhatsApp session successfully!');
        }

        return back()->with('error', 'Failed to configure webhook: ' . ($result['message'] ?? 'Unknown error'))
            ->withInput();
    }

    public function editWebhook($id)
    {
        $session = $this->whatsapp->getSessionDetails((int) $id);
        $sessions = $this->whatsapp->getSessions();
        $events = $this->webhookEvents();
        $canonicalUrl = rtrim(config('app.url', url('/')), '/') . '/api/whatsapp/webhook';

        return view('whatsapp.webhooks.edit', compact('session', 'sessions', 'events', 'canonicalUrl'));
    }

    public function updateWebhook(Request $request, $id)
    {
        $validated = $request->validate([
            'webhook_url' => 'required|url|max:500',
            'webhook_enabled' => 'nullable|boolean',
            'events' => 'nullable|array',
            'events.*' => 'string',
            'webhook_secret' => 'nullable|string|max:255',
        ]);

        $payload = [
            'webhook_url' => $validated['webhook_url'],
            'webhook_enabled' => (bool) ($validated['webhook_enabled'] ?? false),
            'webhook_events' => $validated['events'] ?? [],
        ];

        if (!empty($validated['webhook_secret'])) {
            $payload['webhook_secret'] = $validated['webhook_secret'];
        }

        $result = $this->whatsapp->updateSession((int) $id, $payload);

        if (($result['success'] ?? false)) {
            return redirect()->route('whatsapp.webhooks.index')
                ->with('success', 'Webhook configuration updated successfully!');
        }

        return back()->with('error', 'Failed to update webhook: ' . ($result['message'] ?? 'Unknown error'))
            ->withInput();
    }

    public function destroyWebhook($id)
    {
        $result = $this->whatsapp->updateSession((int) $id, [
            'webhook_enabled' => false,
        ]);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => ($result['success'] ?? false)
                ? 'Webhook disabled on session successfully.'
                : ($result['message'] ?? 'Failed to disable webhook.'),
        ], ($result['success'] ?? false) ? 200 : 400);
    }

    public function testWebhook($id)
    {
        $session = $this->whatsapp->getSessionDetails((int) $id);

        $webhookUrl = $session['webhook_url'] ?? null;

        if (!$webhookUrl) {
            return response()->json([
                'success' => false,
                'message' => 'No webhook URL configured for this session.',
            ], 400);
        }

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
                'X-Webhook-Signature' => $session['webhook_secret'] ?? '',
            ])->post($webhookUrl, $testPayload);

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