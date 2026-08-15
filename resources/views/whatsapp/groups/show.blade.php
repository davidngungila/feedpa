@extends('layouts.app')

@section('title', $group['name'] . ' - Group Details')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fab fa-whatsapp text-primary-500"></i> {{ $group['name'] }}
            </h2>
            <p class="text-xs text-primary-500 mt-1 font-mono break-all">{{ $group['jid'] }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('whatsapp.groups.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
                <i class="fas fa-arrow-left mr-1"></i> Back to Groups
            </a>
        </div>
    </div>

    <!-- Group Info -->
    @if(!empty($metaError))
        <div class="p-4 rounded-xl border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
            <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle mr-1"></i> Could not load group metadata from the WhatsApp API: {{ $metaError }}
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="card p-6 lg:col-span-1">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center overflow-hidden">
                    @if($group['img_url'])
                        <img src="{{ $group['img_url'] }}" alt="{{ $group['name'] }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-users text-2xl text-primary-600 dark:text-primary-400"></i>
                    @endif
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-primary-900 dark:text-white break-all">{{ $group['name'] }}</h3>
                    <p class="text-[10px] text-primary-500 font-mono break-all">{{ $group['jid'] }}</p>
                </div>
            </div>
            <h4 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
                <i class="fas fa-info-circle"></i> Group Information
            </h4>
            <div class="space-y-3 text-xs">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Description</p>
                    <p class="text-primary-700 dark:text-primary-300">{{ $group['description'] ?: 'No description' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Owner</p>
                    <p class="text-primary-700 dark:text-primary-300 font-mono break-all">{{ $group['owner'] ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Created</p>
                    <p class="text-primary-700 dark:text-primary-300">{{ $group['creation'] ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Members</p>
                    <p class="text-primary-700 dark:text-primary-300">{{ count($group['participants']) }}</p>
                </div>
            </div>
        </div>

        <!-- Participants -->
        <div class="card overflow-hidden lg:col-span-2">
            <div class="p-6 border-b border-primary-100 dark:border-dark-border">
                <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                    <i class="fas fa-users"></i> Participants ({{ count($group['participants']) }})
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-primary-50 dark:bg-primary-900/20">
                        <tr>
                            <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Name / JID</th>
                            <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                        @forelse($group['participants'] as $participant)
                            <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                                <td class="px-6 py-3">
                                    @php
                                        $pName = $participantNames[$participant['pn'] ?? ''] ?? $participant['name'] ?? null;
                                    @endphp
                                    <p class="text-xs font-bold text-primary-900 dark:text-white">{{ $pName ?? 'Unknown' }}</p>
                                    <p class="text-[10px] text-primary-500 font-mono break-all">{{ $participant['jid'] ?? $participant['id'] ?? ($pName ?? '') }}</p>
                                </td>
                                <td class="px-6 py-3">
                                    <p class="text-xs text-primary-700 dark:text-primary-300 font-mono">{{ $participant['pn'] ?? '—' }}</p>
                                </td>
                                <td class="px-6 py-3">
                                    @if(!empty($participant['isSuperAdmin']))
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300">
                                            <i class="fas fa-crown mr-1"></i> Super Admin
                                        </span>
                                    @elseif(!empty($participant['isAdmin']))
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                            <i class="fas fa-shield-alt mr-1"></i> Admin
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-primary-500 dark:bg-gray-800">Member</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center">
                                    <p class="text-xs text-primary-500">No participants synced. Reconnect the session if the list is empty.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Participants -->
    <div class="card p-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
            <i class="fas fa-user-plus"></i> Add Group Participants
        </h3>
        <p class="text-[11px] text-primary-500 mb-4">Add participants by international phone number (E.164, e.g. <span class="font-mono">255784510488</span>). Admin privileges are required in the group.</p>
        <form id="addParticipantsForm" action="{{ route('whatsapp.groups.add-participants', $group['jid']) }}" method="POST">
            @csrf
            <div class="flex flex-col md:flex-row gap-3">
                <input type="text" name="participants" id="participantsInput" placeholder="255712345678, 255712345679" required
                    class="flex-1 w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500">
                <button type="submit" id="addParticipantsBtn" class="px-6 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                    <i class="fas fa-user-plus mr-1"></i> Add Participants
                </button>
            </div>
        </form>
        <div id="addParticipantsResult" class="mt-4 hidden"></div>
    </div>

    <!-- Send Group Message -->
    <div class="card p-6" id="composerCard">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
            <i class="fas fa-paper-plane"></i> Send Group Message
        </h3>
        <p class="text-[11px] text-primary-500 mb-4">Compose any message type for this group. Media must be a publicly accessible URL — use the
            <a href="{{ route('whatsapp.media.index') }}" class="text-primary-600 underline">Media &amp; Files</a> page to upload a file and copy its URL.
            For text messages you can mention members via the checkboxes below.</p>

        <form id="sendGroupMessageForm" action="{{ route('whatsapp.groups.send-message', $group['jid']) }}" method="POST">
            @csrf

            <p class="text-[10px] text-gray-400 uppercase font-bold mb-2">Message Type</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 mb-4">
                @php
                    $composerTypes = [
                        'text'      => ['fa-comment-dots', 'Text'],
                        'image'     => ['fa-image', 'Image'],
                        'video'     => ['fa-video', 'Video'],
                        'document'  => ['fa-file-pdf', 'Document'],
                        'audio'     => ['fa-music', 'Audio'],
                        'sticker'   => ['fa-smile-wink', 'Sticker'],
                        'contact'   => ['fa-id-card', 'Contact Card'],
                        'location'  => ['fa-map-marker-alt', 'Location'],
                        'poll'      => ['fa-poll', 'Poll'],
                        'viewOnce'  => ['fa-eye-slash', 'View Once'],
                        'quoted'    => ['fa-reply', 'Quoted'],
                    ];
                    $composerInputClass = 'w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm text-primary-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500';
                @endphp
                @foreach($composerTypes as $value => [$icon, $label])
                <label class="cursor-pointer">
                    <input type="radio" name="message_type" value="{{ $value }}" class="hidden peer composer-type" {{ $loop->first ? 'checked' : '' }}>
                    <div class="px-3 py-2 rounded-xl border border-primary-100 dark:border-primary-800 text-center text-[10px] font-bold text-primary-700 dark:text-primary-300 peer-checked:bg-green-500 peer-checked:text-white peer-checked:border-green-500 transition-all">
                        <i class="fas {{ $icon }} block text-sm mb-1"></i>
                        {{ $label }}
                    </div>
                </label>
                @endforeach
            </div>

            <!-- Mentions (text & quoted) -->
            <div id="composerMentions" class="mt-2">
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-2">Mention Members (optional)</p>
                @php
                    $mentionables = array_filter($group['participants'] ?? [], function ($p) {
                        return !empty($p['jid']) || !empty($p['pn']);
                    });
                @endphp
                @if(count($mentionables) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 max-h-52 overflow-y-auto p-3 rounded-xl bg-gray-50 dark:bg-primary-900/20">
                        @foreach($mentionables as $p)
                            @php
                                $pn = $p['pn'] ?? preg_replace('/@s\.whatsapp\.net$/', '', $p['jid']);
                                $mentionJid = !empty($p['jid']) ? $p['jid'] : ($pn . '@s.whatsapp.net');
                                $label = $participantNames[$pn] ?? $p['name'] ?? $p['id'] ?? $pn;
                            @endphp
                            <label class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-dark-card border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-primary-400 transition-colors">
                                <input type="checkbox" value="{{ $mentionJid }}" data-phone="{{ $pn }}" class="mention-checkbox rounded text-primary-600 focus:ring-primary-500">
                                <span class="min-w-0">
                                    <span class="block text-xs font-bold text-primary-900 dark:text-white truncate">{{ $label }}</span>
                                    <span class="block text-[10px] text-primary-500 font-mono">{{ '@' . $pn }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="text-[11px] text-primary-500">No participant numbers available to mention.</p>
                @endif
            </div>

            <!-- Panel: text -->
            <div class="composer-panel mt-4" data-panel="text">
                <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Message Text</label>
                <textarea name="text" id="groupMessageText" rows="4" placeholder="Type your message to the group..."
                    class="{{ $composerInputClass }} resize-y"></textarea>
            </div>

            <!-- Panel: media -->
            <div class="composer-panel hidden mt-4 space-y-3" data-panel="media">
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Media URL</label>
                    <input type="url" name="media_url" class="{{ $composerInputClass }}" placeholder="https://example.com/file.jpg">
                </div>
                <div id="composerFileNameField">
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">File Name (optional)</label>
                    <input type="text" name="file_name" class="{{ $composerInputClass }}" placeholder="report.pdf">
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Caption (optional)</label>
                    <input type="text" name="caption" class="{{ $composerInputClass }}" placeholder="Caption for the media">
                </div>
            </div>

            <!-- Panel: contact -->
            <div class="composer-panel hidden mt-4 space-y-3" data-panel="contact">
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Contact Name</label>
                    <input type="text" name="contact_name" class="{{ $composerInputClass }}" placeholder="e.g. Support Team">
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Contact Phone</label>
                    <input type="text" name="contact_phone" class="{{ $composerInputClass }}" placeholder="255712345678">
                </div>
            </div>

            <!-- Panel: location -->
            <div class="composer-panel hidden mt-4 space-y-3" data-panel="location">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <input type="number" step="any" name="latitude" class="{{ $composerInputClass }}" placeholder="Latitude (e.g. -6.7924)">
                    <input type="number" step="any" name="longitude" class="{{ $composerInputClass }}" placeholder="Longitude (e.g. 39.2083)">
                </div>
                <input type="text" name="location_name" class="{{ $composerInputClass }}" placeholder="Location name (optional)">
                <input type="text" name="location_address" class="{{ $composerInputClass }}" placeholder="Address (optional)">
                <input type="text" name="text" class="{{ $composerInputClass }}" placeholder="Caption (optional)">
            </div>

            <!-- Panel: poll -->
            <div class="composer-panel hidden mt-4 space-y-3" data-panel="poll">
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Poll Question</label>
                    <input type="text" name="poll_question" class="{{ $composerInputClass }}" placeholder="What is your favourite option?">
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Options (2 to 12, one per line)</label>
                    <textarea name="poll_options_raw" rows="4" class="{{ $composerInputClass }} resize-y" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                </div>
                <label class="flex items-center gap-2 text-xs font-bold text-primary-700 dark:text-primary-300">
                    <input type="checkbox" name="poll_multi" value="1" class="rounded text-primary-600 focus:ring-primary-500">
                    Allow multiple answers
                </label>
            </div>

            <!-- Panel: viewOnce -->
            <div class="composer-panel hidden mt-4 space-y-3" data-panel="viewOnce">
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Media Type</label>
                    <select name="media_type" class="{{ $composerInputClass }}">
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                        <option value="audio">Audio</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Media URL</label>
                    <input type="url" name="media_url" class="{{ $composerInputClass }}" placeholder="https://example.com/file.jpg">
                </div>
            </div>

            <!-- Panel: quoted -->
            <div class="composer-panel hidden mt-4 space-y-3" data-panel="quoted">
                <div class="p-3 rounded-xl bg-primary-50/60 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800">
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Replying to</p>
                    <p class="text-xs text-primary-700 dark:text-primary-300 font-mono" id="composerReplyToLabel">—</p>
                </div>
                <input type="hidden" name="reply_to" id="composerReplyTo" value="">
                <textarea name="text" rows="3" placeholder="Type your reply..." class="{{ $composerInputClass }} resize-y"></textarea>
            </div>

            <div class="mt-4 flex items-center justify-between gap-3">
                <p class="text-[10px] text-primary-400">Message will be sent to <span class="font-mono">{{ $group['jid'] }}</span></p>
                <button type="submit" id="sendGroupMessageBtn" class="px-6 py-2 rounded-xl bg-green-600 hover:bg-green-500 text-white text-xs font-bold transition-all">
                    <i class="fas fa-paper-plane mr-1"></i> Send Message
                </button>
            </div>
        </form>
        <div id="sendGroupMessageResult" class="mt-4 hidden"></div>
    </div>

    <!-- Message Logs for this group -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-primary-100 dark:border-dark-border flex items-center justify-between">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-scroll"></i> Message Logs for this Group
            </h3>
            @if($personalTokenConfigured && $session)
                <a href="{{ route('whatsapp.sessions.message-logs', $session['id']) }}" class="text-[10px] font-bold text-primary-600 dark:text-primary-300 hover:underline">
                    View all session logs <i class="fas fa-arrow-right ml-1"></i>
                </a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">To</th>
                        <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Content</th>
                        <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Sent At</th>
                        <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($messageLogs as $log)
                        @php
                            $contentRaw = $log['content'] ?? '';
                            $decoded = is_array($contentRaw) ? $contentRaw : json_decode((string) $contentRaw, true);
                            if (is_array($decoded)) {
                                $text = $decoded['text'] ?? trim(implode(', ', array_filter([
                                    $decoded['imageUrl'] ?? null,
                                    $decoded['videoUrl'] ?? null,
                                    $decoded['audioUrl'] ?? null,
                                    $decoded['documentUrl'] ?? null,
                                    $decoded['stickerUrl'] ?? null,
                                ])));
                                if ($text === '' && $decoded) {
                                    $text = 'Media message (' . implode(', ', array_keys($decoded)) . ')';
                                }
                            } else {
                                $text = (string) $contentRaw;
                            }
                            $status = strtolower((string) ($log['status'] ?? 'unknown'));
                            $statusClass = match($status) {
                                'sent' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                'failed' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                'in_progress', 'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                                default => 'bg-gray-100 text-primary-500 dark:bg-gray-800',
                            };
                        @endphp
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="px-6 py-3">
                                <p class="text-xs text-primary-700 dark:text-primary-300 font-mono break-all">{{ $log['to'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-xs text-primary-700 dark:text-primary-300 max-w-md line-clamp-2">{{ $text }}</p>
                                @if($log['failed_reason'])
                                    <p class="text-[10px] text-red-600 dark:text-red-400 mt-0.5">Reason: {{ $log['failed_reason'] }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ \Illuminate\Support\Str::limit($log['created_at'] ?? '—', 19) }}</p>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(!empty($log['id']))
                                        <button type="button" class="reply-message-btn px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-bold hover:bg-blue-100 transition-all"
                                                data-msg-id="{{ $log['id'] }}" data-summary="{{ \Illuminate\Support\Str::limit($text, 60) }}" title="Reply to this message">
                                            <i class="fas fa-reply mr-1"></i> Reply
                                        </button>
                                        @if($status === 'failed')
                                            <button type="button" class="resend-message-btn px-3 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 text-[10px] font-bold hover:bg-amber-100 transition-all"
                                                    data-msg-id="{{ $log['id'] }}" title="Resend this message">
                                                <i class="fas fa-redo mr-1"></i> Resend
                                            </button>
                                        @endif
                                        <button type="button" class="edit-message-btn px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold hover:bg-indigo-100 transition-all"
                                                data-msg-id="{{ $log['id'] }}" data-current="{{ $text }}" title="Edit message text">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </button>
                                        <button type="button" class="info-message-btn px-3 py-1.5 rounded-lg bg-cyan-50 dark:bg-cyan-900/20 text-cyan-600 dark:text-cyan-400 text-[10px] font-bold hover:bg-cyan-100 transition-all"
                                                data-msg-id="{{ $log['id'] }}" title="Fetch live message info">
                                            <i class="fas fa-server mr-1"></i> Info
                                        </button>
                                    @endif
                                    <button type="button" class="view-message-btn px-3 py-1.5 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-300 text-[10px] font-bold hover:bg-primary-100 transition-all"
                                            data-log='@json($log)'>
                                        <i class="fas fa-eye mr-1"></i> Details
                                    </button>
                                    @if(!empty($log['id']))
                                        <button type="button" class="delete-message-btn px-3 py-1.5 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-[10px] font-bold hover:bg-red-100 transition-all"
                                                data-msg-id="{{ $log['id'] }}">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <i class="fas fa-scroll text-3xl text-primary-300 mb-3 block"></i>
                                @if(!$personalTokenConfigured)
                                    <p class="text-sm font-bold text-primary-500">Message logs unavailable</p>
                                    <p class="text-xs text-primary-400 mt-1">Configure the Personal Access Token in WhatsApp settings and enable message logging for the session.</p>
                                @elseif($logsError)
                                    <p class="text-sm font-bold text-primary-500">Could not load message logs</p>
                                    <p class="text-xs text-primary-400 mt-1">{{ $logsError }}</p>
                                @else
                                    <p class="text-sm font-bold text-primary-500">No messages sent to this group</p>
                                    <p class="text-xs text-primary-400 mt-1">Messages sent via the API to this group will appear here.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Message Details Modal -->
<div id="messageModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="card w-full max-w-2xl p-6 max-h-[85vh] overflow-y-auto">
        <div class="flex items-start justify-between mb-4">
            <h3 class="text-sm font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> Message Details
            </h3>
            <button type="button" id="closeMessageModal" class="p-2 rounded-lg bg-gray-100 dark:bg-primary-900/20 text-primary-500 hover:text-red-500 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="flex gap-1 bg-gray-100 dark:bg-primary-900/20 p-1 rounded-xl w-fit mb-3">
            <button type="button" data-tab="content" class="msg-tab-btn px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all"><i class="fas fa-comment-dots mr-1"></i>Message</button>
            <button type="button" data-tab="codes" class="msg-tab-btn px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all"><i class="fas fa-code mr-1"></i>Codes</button>
        </div>
        <div id="msg-tab-content" class="msg-tab-panel">
            <div id="messageModalDetails" class="rounded-xl bg-gray-50 dark:bg-primary-900/20 divide-y divide-primary-100 dark:divide-primary-800 overflow-hidden"></div>
        </div>
        <div id="msg-tab-codes" class="msg-tab-panel hidden">
            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Full Payload (JSON)</p>
            <pre id="messageModalPayload" class="p-3 rounded-xl bg-gray-900 text-green-300 text-[10px] leading-relaxed overflow-x-auto max-h-[50vh]"></pre>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('addParticipantsForm');
        const resultBox = document.getElementById('addParticipantsResult');
        const btn = document.getElementById('addParticipantsBtn');

        if (form && resultBox && btn) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Adding...';

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: new FormData(form),
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    const data = result.data;
                    resultBox.classList.remove('hidden');

                    if (data.success && Array.isArray(data.data)) {
                        const rows = data.data.map(function (item) {
                            const ok = item.status === 200;
                            const cls = ok
                                ? 'border-green-200 dark:border-green-800 bg-green-50/60 dark:bg-green-900/10'
                                : 'border-amber-200 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-900/10';
                            const badgeCls = ok ? 'badge-green' : 'badge-amber';
                            return '<div class="flex items-center justify-between gap-3 p-3 rounded-xl border ' + cls + '">' +
                                '<div class="min-w-0">' +
                                    '<p class="text-xs font-bold text-primary-900 dark:text-white font-mono">' + (item.jid || '') + '</p>' +
                                    '<p class="text-[10px] text-primary-500 mt-0.5">' + (item.message || '') + '</p>' +
                                '</div>' +
                                '<span class="badge ' + badgeCls + ' whitespace-nowrap">' + (ok ? 'Added' : 'Not added') + '</span>' +
                            '</div>';
                        }).join('');

                        resultBox.innerHTML = '<div class="space-y-2">' + rows + '</div>';
                    } else {
                        resultBox.innerHTML = '<div class="p-3 rounded-xl bg-red-50/60 dark:bg-red-900/10 border border-red-200 dark:border-red-800 text-xs font-bold text-red-700 dark:text-red-300">' +
                            '<i class="fas fa-exclamation-circle mr-1"></i>' + (data.message || 'Failed to add participants.') + '</div>';
                    }
                })
                .catch(function () {
                    resultBox.classList.remove('hidden');
                    resultBox.innerHTML = '<div class="p-3 rounded-xl bg-red-50/60 dark:bg-red-900/10 border border-red-200 dark:border-red-800 text-xs font-bold text-red-700 dark:text-red-300">' +
                        '<i class="fas fa-exclamation-circle mr-1"></i> Network error. Please try again.</div>';
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
            });
        }

        const sendForm = document.getElementById('sendGroupMessageForm');
        const sendBtn = document.getElementById('sendGroupMessageBtn');
        const sendResult = document.getElementById('sendGroupMessageResult');

        function updateComposer() {
            const selected = document.querySelector('.composer-type:checked');
            if (!selected) return;
            const type = selected.value;
            document.querySelectorAll('.composer-panel').forEach(function (panel) {
                panel.classList.toggle('hidden', panel.dataset.panel !== type);
            });
            const mentions = document.getElementById('composerMentions');
            if (mentions) mentions.classList.toggle('hidden', type !== 'text' && type !== 'quoted');
            const fileNameField = document.getElementById('composerFileNameField');
            if (fileNameField) fileNameField.classList.toggle('hidden', type !== 'document');
        }

        document.querySelectorAll('.composer-type').forEach(function (radio) {
            radio.addEventListener('change', updateComposer);
        });
        updateComposer();

        function currentTextInput() {
            const type = (document.querySelector('.composer-type:checked') || {}).value;
            if (type === 'text') return document.getElementById('groupMessageText');
            if (type === 'quoted') return document.querySelector('.composer-panel[data-panel="quoted"] textarea[name="text"]');
            return null;
        }

        if (sendForm && sendBtn && sendResult) {
            document.querySelectorAll('.mention-checkbox').forEach(function (cb) {
                cb.addEventListener('change', function () {
                    const input = currentTextInput();
                    if (input && cb.checked && cb.dataset.phone) {
                        const mention = '@' + cb.dataset.phone + ' ';
                        if (input.value.indexOf(mention) === -1) {
                            input.value = (input.value.trimEnd() ? input.value.trimEnd() + ' ' : '') + mention;
                            input.focus();
                        }
                    }
                });
            });

            sendForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const original = sendBtn.innerHTML;
                sendBtn.disabled = true;
                sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sending...';

                const type = (document.querySelector('.composer-type:checked') || {}).value || 'text';
                const data = new FormData(sendForm);
                document.querySelectorAll('.mention-checkbox:checked').forEach(function (cb) {
                    data.append('mentions[]', cb.value);
                });
                const pollRaw = data.get('poll_options_raw');
                if (type === 'poll' && pollRaw) {
                    data.delete('poll_options_raw');
                    pollRaw.split('\n').map(function (o) { return o.trim(); }).filter(Boolean).forEach(function (o) {
                        data.append('poll_options[]', o);
                    });
                }

                fetch(sendForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: data,
                })
                .then(function (response) {
                    return response.json().then(function (json) {
                        return { ok: response.ok, data: json };
                    });
                })
                .then(function (result) {
                    const data = result.data;
                    sendResult.classList.remove('hidden');

                    if (data.success) {
                        const info = data.data || {};
                        sendResult.innerHTML = '<div class="p-3 rounded-xl bg-green-50/60 dark:bg-green-900/10 border border-green-200 dark:border-green-800 text-xs font-bold text-green-700 dark:text-green-300">' +
                            '<i class="fas fa-check-circle mr-1"></i>' + (data.message || 'Message sent to the group.') +
                            (info.msgId ? ' (Message ID: <span class="font-mono">' + info.msgId + '</span>)' : '') +
                            '</div>';
                        sendForm.reset();
                        updateComposer();
                        document.querySelectorAll('.mention-checkbox').forEach(function (cb) { cb.checked = false; });
                    } else {
                        sendResult.innerHTML = '<div class="p-3 rounded-xl bg-red-50/60 dark:bg-red-900/10 border border-red-200 dark:border-red-800 text-xs font-bold text-red-700 dark:text-red-300">' +
                            '<i class="fas fa-exclamation-circle mr-1"></i>' + (data.message || 'Failed to send the message.') + '</div>';
                    }
                })
                .catch(function () {
                    sendResult.classList.remove('hidden');
                    sendResult.innerHTML = '<div class="p-3 rounded-xl bg-red-50/60 dark:bg-red-900/10 border border-red-200 dark:border-red-800 text-xs font-bold text-red-700 dark:text-red-300">' +
                        '<i class="fas fa-exclamation-circle mr-1"></i> Network error. Please try again.</div>';
                })
                .finally(function () {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = original;
                });
            });
        }

        function scrollToComposer() {
            const card = document.getElementById('composerCard');
            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        document.querySelectorAll('.reply-message-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const msgId = btn.dataset.msgId;
                const summary = btn.dataset.summary || '';
                const replyRadio = document.querySelector('.composer-type[value="quoted"]');
                if (replyRadio) {
                    replyRadio.checked = true;
                    updateComposer();
                }
                document.getElementById('composerReplyTo').value = msgId;
                const label = document.getElementById('composerReplyToLabel');
                if (label) label.textContent = '#' + msgId + (summary ? ' — ' + summary : '');
                scrollToComposer();
            });
        });

        function showToast(message, ok) {
            let toast = document.getElementById('whatsappToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'whatsappToast';
                document.body.appendChild(toast);
            }
            toast.textContent = message;
            toast.className = 'fixed top-4 right-4 z-[110] px-4 py-3 rounded-xl text-xs font-bold shadow-xl transition-all ' + (ok ? 'bg-green-600 text-white' : 'bg-red-600 text-white');
            clearTimeout(toast._timer);
            toast._timer = setTimeout(function () { toast.remove(); }, 4000);
        }

        const deleteUrl = '{{ route('whatsapp.messages.delete', '__ID__') }}';
        document.querySelectorAll('.delete-message-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const msgId = btn.dataset.msgId;
                if (!confirm('Delete message ' + msgId + ' for everyone? This usually only works shortly after the message was sent.')) return;
                const original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch(deleteUrl.replace('__ID__', msgId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    if (result.data.success) {
                        showToast(result.data.message || 'Message deleted successfully.', true);
                        const row = btn.closest('tr');
                        if (row) row.remove();
                    } else {
                        showToast(result.data.message || 'Failed to delete the message.', false);
                        btn.disabled = false;
                        btn.innerHTML = original;
                    }
                })
                .catch(function () {
                    showToast('Network error. Please try again.', false);
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
            });
        });

        const editUrl = '{{ route('whatsapp.messages.edit', '__ID__') }}';
        document.querySelectorAll('.edit-message-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const msgId = btn.dataset.msgId;
                const current = btn.dataset.current || '';
                const newText = prompt('Edit message ' + msgId + ':', current);
                if (newText === null) return;
                const original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch(editUrl.replace('__ID__', msgId), {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ text: newText }),
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    if (result.data.success) {
                        showToast(result.data.message || 'Message edited successfully.', true);
                        btn.dataset.current = newText;
                    } else {
                        showToast(result.data.message || 'Failed to edit the message.', false);
                    }
                })
                .catch(function () {
                    showToast('Network error. Please try again.', false);
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
            });
        });

        const resendUrl = '{{ route('whatsapp.messages.resend', '__ID__') }}';
        document.querySelectorAll('.resend-message-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const msgId = btn.dataset.msgId;
                const original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch(resendUrl.replace('__ID__', msgId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    if (result.data.success) {
                        showToast(result.data.message || 'Message resent successfully.', true);
                    } else {
                        showToast(result.data.message || 'Failed to resend the message.', false);
                    }
                })
                .catch(function () {
                    showToast('Network error. Please try again.', false);
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
            });
        });

        const infoUrl = '{{ route('whatsapp.messages.info', '__ID__') }}';
        document.querySelectorAll('.info-message-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const msgId = btn.dataset.msgId;
                const original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch(infoUrl.replace('__ID__', msgId), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    if (result.data.success) {
                        const info = result.data.data || {};
                        const detailsEl = document.getElementById('messageModalDetails');
                        if (detailsEl) {
                            const rows = [];
                            function addRow(label, value) {
                                rows.push('<div class="px-4 py-3 flex items-start gap-4">' +
                                    '<p class="w-32 shrink-0 text-[10px] text-gray-400 uppercase font-bold pt-0.5">' + escapeHtml(label) + '</p>' +
                                    '<p class="text-xs text-primary-900 dark:text-white break-all">' + (value === null || value === undefined || value === '' ? '—' : escapeHtml(value)) + '</p>' +
                                '</div>');
                            }
                            const contentInfo = decodeContent(info.message || info.msg || info.content || info);
                            if (info.msgId) addRow('Message ID', info.msgId);
                            if (info.jid || info.to) addRow('To', info.jid || info.to);
                            if (info.status !== undefined) addRow('Status', info.status);
                            if (contentInfo.text) addRow('Content', contentInfo.text);
                            if (info.createdAt) addRow('Created At', info.createdAt);
                            if (info.updatedAt) addRow('Updated At', info.updatedAt);
                            detailsEl.innerHTML = rows.join('');
                        }
                        document.getElementById('messageModalPayload').textContent = JSON.stringify(result.data.data, null, 2);
                        showMsgTab('content');
                        openMessageModal();
                        showToast(result.data.message || 'Message info fetched.', true);
                    } else {
                        showToast(result.data.message || 'Failed to fetch message info.', false);
                    }
                })
                .catch(function () {
                    showToast('Network error. Please try again.', false);
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
            });
        });

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function extractContentText(v, depth) {
            if (v === null || v === undefined || depth > 5) return null;
            if (typeof v === 'string') return v;
            if (typeof v !== 'object') return null;
            if (Array.isArray(v)) {
                for (const item of v) {
                    const t = extractContentText(item, depth + 1);
                    if (t) return t;
                }
                return null;
            }
            for (const key of ['text', 'message', 'body', 'caption', 'description', 'name']) {
                if (v[key] !== undefined) {
                    const t = extractContentText(v[key], depth + 1);
                    if (t) return t;
                }
            }
            return null;
        }

        function mediaUrlText(obj) {
            if (!obj || typeof obj !== 'object') return '';
            return (obj.imageUrl || obj.videoUrl || obj.audioUrl || obj.documentUrl || obj.stickerUrl) || '';
        }

        function decodeContent(contentRaw) {
            let parsed = contentRaw;
            if (typeof contentRaw === 'string' && contentRaw.trim() !== '') {
                try { parsed = JSON.parse(contentRaw); } catch (e) { parsed = contentRaw; }
            }
            let text = '';
            if (Array.isArray(parsed) || (parsed && typeof parsed === 'object')) {
                text = extractContentText(parsed, 0) || mediaUrlText(parsed);
            } else if (parsed !== null && parsed !== undefined) {
                text = String(parsed);
            }
            return { text: text, decoded: parsed };
        }

        const messageModal = document.getElementById('messageModal');
        const closeMessageModalBtn = document.getElementById('closeMessageModal');

        function openMessageModal() {
            if (!messageModal) return;
            messageModal.classList.remove('hidden');
            messageModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeMessageModal() {
            if (!messageModal) return;
            messageModal.classList.add('hidden');
            messageModal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        if (closeMessageModalBtn) {
            closeMessageModalBtn.addEventListener('click', closeMessageModal);
            messageModal.addEventListener('click', function (e) {
                if (e.target === messageModal) closeMessageModal();
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeMessageModal();
        });

        function showMsgTab(name) {
            document.querySelectorAll('.msg-tab-panel').forEach(function (p) { p.classList.add('hidden'); });
            document.querySelectorAll('.msg-tab-btn').forEach(function (b) {
                const active = b.dataset.tab === name;
                b.classList.toggle('bg-primary-600', active);
                b.classList.toggle('text-white', active);
                b.classList.toggle('bg-transparent', !active);
                b.classList.toggle('text-primary-500', !active);
            });
            const panel = document.getElementById('msg-tab-' + name);
            if (panel) panel.classList.remove('hidden');
        }

        document.querySelectorAll('.msg-tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                showMsgTab(btn.dataset.tab);
            });
        });

        document.querySelectorAll('.view-message-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const log = btn.dataset.log ? JSON.parse(btn.dataset.log) : {};
                const detailsEl = document.getElementById('messageModalDetails');
                if (!detailsEl) return;

                const contentInfo = decodeContent(log.content);

                const rows = [];
                function addRow(label, value) {
                    rows.push('<div class="px-4 py-3 flex items-start gap-4">' +
                        '<p class="w-32 shrink-0 text-[10px] text-gray-400 uppercase font-bold pt-0.5">' + escapeHtml(label) + '</p>' +
                        '<p class="text-xs text-primary-900 dark:text-white break-all">' + (value === null || value === undefined || value === '' ? '—' : escapeHtml(value)) + '</p>' +
                    '</div>');
                }

                addRow('Message ID', log.id);
                addRow('To', log.to);
                addRow('Status', log.status);
                addRow('Content', contentInfo.text);
                if (log.failed_reason) addRow('Failure reason', log.failed_reason);
                addRow('Created At', log.created_at);
                if (log.updated_at) addRow('Updated At', log.updated_at);

                detailsEl.innerHTML = rows.join('');
                document.getElementById('messageModalPayload').textContent = JSON.stringify(log, null, 2);
                showMsgTab('content');
                openMessageModal();
            });
        });
    });
</script>
@endpush
