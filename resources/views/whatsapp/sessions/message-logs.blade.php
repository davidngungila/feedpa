@extends('layouts.app')

@section('title', 'Message Logs - Session ' . $id)

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-scroll text-primary-500"></i> Message Logs
            </h2>
            <p class="text-xs text-primary-500 mt-1">
                Session {{ $session['name'] ?? ('#' . $id) }}
                @if(!empty($session['phone_number'])) ({{ $session['phone_number'] }}) @endif
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('whatsapp.sessions.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
                <i class="fas fa-arrow-left mr-1"></i> Back to Sessions
            </a>
        </div>
    </div>

    @if(!$personalTokenConfigured)
        <div class="card p-4 border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
            <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle mr-1"></i> The WhatsApp Personal Access Token is not configured.
                <a href="{{ route('settings.whatsapp') }}" class="underline">Configure it in WhatsApp settings</a> to load message logs.
            </p>
        </div>
    @endif

    @if($error)
        <div class="card p-4 border-l-4 border-l-red-500 bg-red-50/60 dark:bg-red-900/10">
            <p class="text-xs font-bold text-red-700 dark:text-red-300">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ $error }}
                <span class="font-normal mt-0.5 block text-red-600 dark:text-red-400">Message logging must be enabled for the session in Wasender settings.</span>
            </p>
        </div>
    @endif

    <!-- Logs Table -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-primary-100 dark:border-dark-border">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-list"></i> Messages sent via API
                @if($paginator)
                    <span class="ml-auto text-[10px] font-bold text-primary-400">{{ $paginator->total() }} total</span>
                @endif
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">To</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Content</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Sent At</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($items as $log)
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
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 font-mono">{{ $log['id'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 font-mono break-all">{{ $log['to'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 max-w-md line-clamp-2">{{ $text ?: '—' }}</p>
                                @if(!empty($log['failed_reason']))
                                    <p class="text-[10px] text-red-600 dark:text-red-400 mt-0.5">Reason: {{ $log['failed_reason'] }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ \Illuminate\Support\Str::limit($log['created_at'] ?? '—', 19) }}</p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
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
                            <td colspan="6" class="px-6 py-16 text-center">
                                <i class="fas fa-scroll text-4xl text-primary-300 mb-3 block"></i>
                                <p class="text-sm font-bold text-primary-500">No message logs found</p>
                                <p class="text-xs text-primary-400 mt-1">Enable message logging for the session in Wasender settings to capture logs.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($paginator && $paginator->hasPages())
            <div class="p-6 border-t border-primary-100 dark:border-dark-border">
                {{ $paginator->links() }}
            </div>
        @endif
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
