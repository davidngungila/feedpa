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
            <div class="flex gap-1 bg-gray-100 dark:bg-primary-900/20 p-1 rounded-xl w-fit">
                <button type="button" data-tab="extracted" class="tab-btn px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all"><i class="fas fa-comment-dots mr-1"></i>Extracted</button>
                <button type="button" data-tab="summary" class="tab-btn px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all"><i class="fas fa-list-ul mr-1"></i>Summary</button>
                <button type="button" data-tab="payload" class="tab-btn px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all"><i class="fas fa-code mr-1"></i>Payload (JSON)</button>
            </div>
            <div id="tab-extracted" class="tab-panel hidden">
                <div id="eventModalExtracted" class="p-3 rounded-xl bg-white dark:bg-primary-900/40 text-primary-800 dark:text-primary-100 text-xs leading-relaxed space-y-2"></div>
            </div>
            <div id="tab-summary" class="tab-panel hidden">
                <div id="eventModalSummary" class="rounded-xl bg-gray-50 dark:bg-primary-900/20 divide-y divide-primary-100 dark:divide-primary-800 overflow-hidden"></div>
            </div>
            <div id="tab-payload" class="tab-panel hidden">
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
        const extractedEl = document.getElementById('eventModalExtracted');

        function humanizeEvent(ev) {
            if (!ev) return 'unknown';
            return ev.split(/[.\-_]/).filter(Boolean).map(function (w) {
                return w.charAt(0).toUpperCase() + w.slice(1);
            }).join(' ');
        }

        function cleanId(v) {
            return String(v).replace(/@[a-z.]+$/i, '');
        }

        function toItems(v) {
            if (v === null || v === undefined) return [];
            return Array.isArray(v) ? v : [v];
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

            if (event === 'chats.upsert' || event === 'chats.update' || event === 'chats.delete') {
                toItems(data).forEach(function (c) {
                    add(event === 'chats.delete' ? 'Chat removed' : event === 'chats.upsert' ? 'New chat' : 'Chat', pretty(c.name || c.jid));
                    add('Type', c.isGroup ? 'Group chat' : 'Personal chat');
                    add('Unread messages', pretty(c.unreadCount));
                    add('Muted', c.mute === undefined ? '' : (c.mute ? 'Yes' : 'No'));
                    if (c.lastMessage) add('Last message', messageText(c.lastMessage));
                });
            }

            if (event === 'groups.upsert' || event === 'groups.update') {
                toItems(data).forEach(function (g) {
                    add(event === 'groups.upsert' ? 'New group' : 'Group', pretty(g.subject || g.name || g.jid));
                    add('Group ID', cleanId(pretty(g.id || g.jid)));
                    add('Description', pretty(g.desc || g.description));
                    add('Size', pretty(g.size));
                });
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

        function extractPlain(payload) {
            const event = payload.event || 'unknown';
            const raw = (payload.payload && payload.payload.data) || payload.data;
            const data = raw === null || raw === undefined ? {} : raw;
            const out = [];

            function num(v) {
                return String(v || '').replace(/@[a-z.]+$/i, '').replace(/^\+/, '');
            }

            const msgEvents = ['messages.received', 'messages.upsert', 'messages-personal.received', 'messages-group.received', 'messages-newsletter.received'];
            if (msgEvents.indexOf(event) !== -1) {
                toItems(data.messages).forEach(function (m) {
                    const key = m.key || {};
                    const body = messageText(m);
                    const text = body ? '"' + body + '"' : '(media)';
                    const inGroup = (key.remoteJid || '').indexOf('@g.us') !== -1;
                    const sender = m.pushName || num(key.cleanedParticipantPn || key.participantPn || key.cleanedSenderPn || key.senderPn) || 'Someone';
                    const quote = quotedText(m) ? ' (replying to: "' + quotedText(m) + '")' : '';
                    if (key.fromMe) {
                        out.push((inGroup ? 'You sent a message in the group: ' : 'You sent: ') + text + quote);
                    } else {
                        out.push((inGroup ? sender + ' sent a message in the group: ' : sender + ': ') + text + quote);
                    }
                });
                if (out.length) return out;
            }

            if (event === 'message.sent') {
                const body = messageText(data);
                if (data.success === false) {
                    out.push('The message failed to send' + (body ? ': ' + body : '.'));
                } else {
                    out.push('Message delivered successfully' + (body ? ': ' + body : '.'));
                }
                return out;
            }

            if (event === 'messages.update') return ['A message status was updated (read, delivered, or failed).'];
            if (event === 'messages.delete') return ['A message was deleted.'];
            if (event === 'messages.reaction') return ['A reaction was added to a message.'];
            if (event === 'message-receipt.update') return ['A message delivery receipt was updated.'];

            if (event === 'session.status') {
                const st = data.status || 'unknown';
                const meanings = {
                    connected: 'The session is connected and ready.',
                    connecting: 'The session is starting or reconnecting.',
                    need_scan: 'The session needs a QR code scan to link.',
                    need_passkey: 'The session needs passkey linking approval.',
                    disconnected: 'The session is disconnected but may reconnect.',
                    logged_out: 'The WhatsApp account was logged out from this device.',
                    expired: 'The session expired and needs to be reconnected.'
                };
                out.push('The session status changed to ' + st + '. ' + (meanings[st] || ''));
                return out;
            }

            if (event === 'qrcode.updated') return ['A new QR code is ready to scan for linking the session.'];
            if (event === 'passkey.updated') return ['The passkey linking status was updated.'];

            if (event === 'contacts.upsert' || event === 'contacts.update') {
                toItems(data.contacts).forEach(function (c) {
                    const name = c.name || c.notify || c.verifiedName || c.pushName || '';
                    const id = num(c.id || c.jid);
                    if (name) out.push((event === 'contacts.upsert' ? 'New contact added: ' : 'Contact updated: ') + name + (id ? ' (' + id + ')' : ''));
                    else out.push(event === 'contacts.upsert' ? 'New contact added.' : 'Contact updated.');
                });
                return out;
            }

            if (event === 'chats.upsert' || event === 'chats.update' || event === 'chats.delete') {
                const action = event === 'chats.upsert' ? 'A new chat was added' : event === 'chats.delete' ? 'A chat was removed' : 'A chat was updated';
                toItems(data).forEach(function (c) {
                    let s = action;
                    if (c.name) s += ' for ' + c.name;
                    if (typeof c.unreadCount !== 'undefined') s += ' (unread: ' + c.unreadCount + ')';
                    s += '.';
                    out.push(s);
                });
                if (!out.length) out.push(action + '.');
                return out;
            }

            if (event === 'groups.upsert' || event === 'groups.update') {
                const action = event === 'groups.upsert' ? 'A new group was added' : 'A group was updated';
                toItems(data).forEach(function (g) {
                    out.push((g.subject || g.name ? action + ': ' + (g.subject || g.name) : action) + '.');
                });
                if (!out.length) out.push(action + '.');
                return out;
            }

            if (event === 'group-participants.update') {
                const actions = { add: 'was added to', remove: 'was removed from', promote: 'was promoted to admin in', demote: 'was demoted from admin in' };
                const action = actions[data.action] || 'was changed in';
                (data.participants || []).forEach(function (p) {
                    out.push(num(p) + ' ' + action + ' the group.');
                });
                if (!out.length) out.push('Group participants were updated.');
                return out;
            }

            if (event === 'call') {
                out.push('Call ' + (data.status || 'received') + ' from ' + (num(data.from || data.id) || 'a contact') + '.');
                return out;
            }

            if (event === 'poll.results') return ['Poll results were updated.'];

            if (event === 'webhook.test') return ['This is a test webhook from the simulator.'];

            return ['Received event: ' + humanizeEvent(event) + '.'];
        }

        function renderExtracted(lines, container) {
            container.innerHTML = '';
            if (!lines.length) {
                container.textContent = 'Nothing readable to extract.';
                return;
            }
            lines.forEach(function (line) {
                const p = document.createElement('p');
                p.className = 'flex gap-2 items-start';
                const bullet = document.createElement('span');
                bullet.className = 'text-primary-400 mt-0.5';
                bullet.innerHTML = '<i class="fas fa-caret-right"></i>';
                const text = document.createElement('span');
                text.className = 'text-xs text-primary-800 dark:text-primary-100 whitespace-pre-wrap break-words';
                text.textContent = line;
                p.appendChild(bullet);
                p.appendChild(text);
                container.appendChild(p);
            });
        }

        function showTab(name) {
            document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.add('hidden'); });
            document.querySelectorAll('.tab-btn').forEach(function (b) {
                const active = b.dataset.tab === name;
                b.classList.toggle('bg-primary-600', active);
                b.classList.toggle('text-white', active);
                b.classList.toggle('bg-transparent', !active);
                b.classList.toggle('text-primary-500', !active);
            });
            const panel = document.getElementById('tab-' + name);
            if (panel) panel.classList.remove('hidden');
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
            renderExtracted(extractPlain(payload), extractedEl);
            renderSummary(interpretEvent(payload), summaryEl);
            payloadEl.textContent = JSON.stringify(payload.payload, null, 2);
            showTab('extracted');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        document.querySelectorAll('.tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                showTab(btn.dataset.tab);
            });
        });

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
