@extends('layouts.app')

@section('title', 'Webhook Events')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-bolt text-primary-500"></i> Webhook Events
            </h2>
            <p class="text-xs text-primary-500 mt-1">Events received at <span class="font-mono">{{ url('api/whatsapp/webhook') }}</span></p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('whatsapp.webhooks.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
                <i class="fas fa-arrow-left mr-1"></i> Manage Webhooks
            </a>
            <a href="{{ request()->fullUrlWithQuery([]) }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </a>
        </div>
    </div>

    <!-- Events Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Source</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Payload</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Received At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer webhook-event-row" data-event-id="{{ $log->id }}" data-event-payload='@json($log->toArray())'>
                            <td class="px-6 py-4">
                                <p class="text-xs font-mono text-primary-700 dark:text-primary-300">#{{ $log->id }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                                    <i class="fas fa-bolt mr-1"></i>{{ $log->event }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 font-mono">{{ $log->source }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 max-w-md line-clamp-2 break-all">{{ \Illuminate\Support\Str::limit(json_encode($log->payload), 120) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ $log->created_at }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <i class="fas fa-bolt text-4xl text-primary-300 mb-3 block"></i>
                                <p class="text-sm font-bold text-primary-500">No webhook events received yet</p>
                                <p class="text-xs text-primary-400 mt-1">Events will appear here when Wasender delivers them to {{ url('api/whatsapp/webhook') }}.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="p-6 border-t border-primary-100 dark:border-dark-border">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Event Details Modal -->
<div id="eventModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="card w-full max-w-2xl p-6 max-h-[85vh] overflow-y-auto">
        <div class="flex items-start justify-between mb-4">
            <h3 class="text-sm font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-bolt"></i> Event Details
            </h3>
            <button type="button" id="closeEventModal" class="p-2 rounded-lg bg-gray-100 dark:bg-primary-900/20 text-primary-500 hover:text-red-500 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="eventModalContent" class="space-y-3 text-xs">
            <div class="flex flex-wrap gap-2">
                <span id="eventModalBadge" class="px-2 py-1 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300"></span>
                <span id="eventModalSource" class="px-2 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-primary-500 dark:bg-gray-800"></span>
                <span id="eventModalTime" class="px-2 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-primary-500 dark:bg-gray-800"></span>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Summary</p>
                <div id="eventModalSummary" class="rounded-xl bg-gray-50 dark:bg-primary-900/20 divide-y divide-primary-100 dark:divide-primary-800 overflow-hidden"></div>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Payload (JSON)</p>
                <pre id="eventModalPayload" class="p-3 rounded-xl bg-gray-900 text-green-300 text-[10px] leading-relaxed overflow-x-auto max-h-[50vh]"></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('eventModal');
        const closeBtn = document.getElementById('closeEventModal');
        const badge = document.getElementById('eventModalBadge');
        const sourceEl = document.getElementById('eventModalSource');
        const timeEl = document.getElementById('eventModalTime');
        const payloadEl = document.getElementById('eventModalPayload');
        const summaryEl = document.getElementById('eventModalSummary');

        function humanizeEvent(ev) {
            if (!ev) return 'unknown';
            return ev.split(/[.\-_]/).filter(Boolean).map(function (w) {
                return w.charAt(0).toUpperCase() + w.slice(1);
            }).join(' ');
        }

        function cleanId(v) {
            return String(v).replace(/@[a-z.]+$/i, '');
        }

        function messageText(m) {
            if (!m || typeof m !== 'object') return null;
            if (m.messageBody) return String(m.messageBody);
            const msg = m.message || {};
            if (msg.conversation) return String(msg.conversation);
            if (msg.extendedTextMessage && msg.extendedTextMessage.text) return String(msg.extendedTextMessage.text);
            const media = {
                imageMessage: 'Image message',
                videoMessage: 'Video message',
                audioMessage: 'Audio message',
                stickerMessage: 'Sticker message',
                documentMessage: 'Document message',
                contactMessage: 'Contact card',
                locationMessage: 'Location',
                pollMessage: 'Poll',
                viewOnceMessage: 'View-once message'
            };
            for (const type in media) {
                if (msg[type]) return media[type];
            }
            return null;
        }

        function quotedText(m) {
            const msg = (m && m.message) || {};
            const ci = msg.extendedTextMessage && msg.extendedTextMessage.contextInfo;
            if (ci && ci.quotedMessage) return messageText({ message: ci.quotedMessage });
            if (ci && ci.quotedType) return 'a ' + String(ci.quotedType).toLowerCase() + ' message';
            return null;
        }

        function collectSummary(obj, lines) {
            if (!obj || typeof obj !== 'object') return;
            for (const [k, v] of Object.entries(obj)) {
                if (v === null || v === undefined || v === '') continue;
                if (['sessionId', 'timestamp'].includes(k)) continue;
                if (typeof v === 'object') {
                    if (Array.isArray(v)) {
                        if (v.length && typeof v[0] !== 'object') lines.push({ label: k, value: v.join(', ') });
                    } else {
                        collectSummary(v, lines);
                    }
                } else {
                    lines.push({ label: k, value: String(v) });
                }
            }
        }

        function interpretEvent(payload) {
            const lines = [];
            const event = payload.event || '';
            const data = (payload.payload && payload.payload.data) || payload.data || {};

            function add(label, value) {
                if (value === null || value === undefined || value === '') return;
                if (Array.isArray(value) && !value.length) return;
                const v = Array.isArray(value) ? value.join(', ') : String(value);
                lines.push({ label: label, value: v });
            }

            const pretty = function (v) {
                return v === null || v === undefined ? '' : String(v);
            };

            add('Event', humanizeEvent(event));
            if (payload.payload && payload.payload.sessionId) add('Session', payload.payload.sessionId);

            if (event === 'session.status') {
                const st = data.status || '';
                const meanings = {
                    connected: 'Session is connected and ready to send or receive messages.',
                    connecting: 'Session is starting or reconnecting.',
                    need_scan: 'Session needs a WhatsApp QR code scan to link.',
                    need_passkey: 'Session needs passkey linking approval.',
                    disconnected: 'Session disconnected but may reconnect.',
                    logged_out: 'WhatsApp account was logged out from this device.',
                    expired: 'Session expired and needs to be reconnected.'
                };
                add('Status', st);
                add('What this means', meanings[st] || '');
            }

            const msgEvents = ['messages.received', 'messages.upsert', 'messages-personal.received', 'messages-group.received', 'messages-newsletter.received'];
            if (msgEvents.indexOf(event) !== -1) {
                let msgs = data.messages;
                if (msgs && !Array.isArray(msgs)) msgs = [msgs];
                (msgs || []).forEach(function (m) {
                    const key = m.key || {};
                    const fromMe = key.fromMe;
                    const remote = cleanId(pretty(key.remoteJid || m.remoteJid));
                    const isGroup = (key.remoteJid || m.remoteJid || '').indexOf('@g.us') !== -1;
                    const who = fromMe ? 'Sent to' : 'From';
                    const pushName = m.pushName || '';
                    let whom = cleanId(pretty(key.cleanedParticipantPn || key.participantPn || key.participant || key.cleanedSenderPn || key.senderPn || key.senderLid));
                    if (!whom) whom = remote;
                    add(who, pushName ? (pushName + (whom ? ' (' + whom + ')' : '')) : (whom || '(no number)'));
                    add('Direction', fromMe ? 'Outgoing message' : 'Incoming message');
                    if (isGroup) add('Group', remote);
                    if (m.messageTimestamp) add('Sent at', new Date(m.messageTimestamp * 1000).toLocaleString());
                    const quote = quotedText(m);
                    if (quote) add('Replying to', quote);
                    const body = messageText(m);
                    if (body) add('Message', body);
                    if (key.id || m.id) add('Message ID', key.id || m.id);
                });
            }

            if (event === 'message.sent') {
                const key = data.key || {};
                add('To', cleanId(pretty(key.remoteJid || data.to)));
                add('Message', messageText(data) || '');
                add('Status', data.success === false ? 'Failed to send' : 'Delivered successfully');
                if (key.id) add('Message ID', key.id);
            }

            if (event === 'contacts.upsert' || event === 'contacts.update') {
                (data.contacts || []).forEach(function (c) {
                    const name = c.name || c.notify || c.verifiedName || c.pushName || '';
                    const id = c.id || c.jid || '';
                    add(name ? 'Contact' : 'Contact ID', name ? name + ' (' + cleanId(id) + ')' : cleanId(id));
                });
            }

            if (event === 'chat.upsert' || event === 'chat.update' || event === 'chat.delete') {
                const chat = data.chat || data;
                add('Chat', pretty(chat.name || chat.jid));
                add('Type', chat.isGroup ? 'Group chat' : 'Personal chat');
                add('Unread messages', pretty(chat.unreadCount));
                if (chat.lastMessage) add('Last message', messageText(chat.lastMessage));
            }

            if (event === 'group.upsert' || event === 'group.update') {
                const g = data.group || data;
                add('Group', pretty(g.subject || g.name || g.jid));
                add('Group ID', cleanId(pretty(g.id || g.jid)));
                add('Description', pretty(g.desc || g.description));
                add('Size', pretty(g.size));
            }

            if (event === 'group-participants.update') {
                add('Group', cleanId(pretty(data.jid)));
                const actions = { add: 'Added to group', remove: 'Removed from group', promote: 'Promoted to admin', demote: 'Demoted from admin' };
                add('Action', actions[data.action] || data.action || '');
                add('Participants', (data.participants || []).map(cleanId).join(', '));
            }

            if (event === 'webhook.test') {
                add('Test event', 'Test webhook delivered successfully.');
            }

            if (event === 'qrcode.updated') add('QR code', 'New QR code generated for linking.');
            if (event === 'passkey.updated') add('Passkey', 'Passkey linking status updated.');

            if (event === 'call') {
                add('Call from', cleanId(pretty(data.from || data.id)));
                add('Status', pretty(data.status || data.recording || ''));
            }

            if (event === 'poll.results') {
                add('Poll', pretty(data.id || data.pollId));
                add('Results', pretty(data.selectedOption || data.results || ''));
            }

            if (lines.length <= 1) {
                collectSummary(data, lines);
            }

            return lines;
        }

        function renderSummary(lines, container) {
            container.innerHTML = '';
            if (!lines.length) {
                container.classList.add('hidden');
                return;
            }
            container.classList.remove('hidden');
            lines.forEach(function (line) {
                const row = document.createElement('div');
                row.className = 'grid grid-cols-3 gap-2 px-4 py-2';
                const label = document.createElement('p');
                label.className = 'col-span-1 text-[10px] font-bold uppercase tracking-wider text-primary-400';
                label.textContent = line.label;
                const value = document.createElement('p');
                value.className = 'col-span-2 text-xs text-primary-800 dark:text-primary-100 whitespace-pre-wrap break-words';
                value.textContent = line.value;
                row.appendChild(label);
                row.appendChild(value);
                container.appendChild(row);
            });
        }

        function openEvent(payload) {
            badge.innerHTML = '<i class="fas fa-bolt mr-1"></i>' + humanizeEvent(payload.event);
            sourceEl.innerHTML = '<i class="fas fa-tag mr-1"></i>' + (payload.source || '');
            timeEl.innerHTML = '<i class="far fa-clock mr-1"></i>' + (payload.created_at || '');
            renderSummary(interpretEvent(payload), summaryEl);
            payloadEl.textContent = JSON.stringify(payload.payload, null, 2);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        document.querySelectorAll('.webhook-event-row').forEach(function (row) {
            row.addEventListener('click', function () {
                openEvent(JSON.parse(row.dataset.eventPayload));
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });
</script>
@endpush
