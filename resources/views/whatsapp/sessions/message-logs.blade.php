@extends('layouts.app')

@section('title', 'Message Logs - Session ' . $id)

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in" x-data="logDrawer(@js($items))" x-init="window.__logDrawer = this" @keydown.escape.window="closeDrawer()">
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
                        <tr @click="onRowClick($event, @js($log))" class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer">
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
                                    @if(!empty($log['id']))
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
                                    <button type="button" @click.stop="openLogFromRow(@js($log))" class="view-message-btn px-3 py-1.5 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-300 text-[10px] font-bold hover:bg-primary-100 transition-all"
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

    <!-- Right Drawer -->
    <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] flex justify-end overflow-hidden" style="display:none;">
        <div @click="closeDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[520px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-5 pt-0 pb-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/60 dark:bg-dark-900/60">
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-primary-900 dark:text-white flex items-center gap-2"><i class="fas fa-scroll text-primary-500"></i> Message Details</h3>
                    <p class="text-[11px] text-primary-500 truncate" x-text="selected ? '# ' + (selected.id ?? '') + ' → ' + (selected.to ?? '') : ''"></p>
                </div>
                <button @click="closeDrawer()" class="w-8 h-8 rounded-lg bg-white dark:bg-dark-800 border border-primary-100 dark:border-dark-border flex items-center justify-center hover:bg-primary-50"><i class="fas fa-times text-primary-600"></i></button>
            </div>

            <div x-show="!drawerLoading" class="flex-1 min-h-0 overflow-y-auto" x-cloak>
                <div class="space-y-5 p-5">
                    <!-- Status -->
                    <div class="p-4 rounded-xl" :class="statusClass">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[10px] font-bold tracking-widest text-primary-500 uppercase">Status</p>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold" :class="statusClass"><span x-text="selectedStatus"></span></span>
                        </div>
                        <template x-if="selected?.failed_reason">
                            <p class="text-[11px] font-bold mt-2 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> <span x-text="selected.failed_reason"></span></p>
                        </template>
                    </div>

                    <!-- Details -->
                    <div class="p-4 rounded-xl bg-primary-50 border border-primary-100 dark:bg-dark-800 dark:border-dark-border">
                        <p class="text-[10px] font-bold tracking-widest text-primary-500 mb-2">MESSAGE</p>
                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between gap-2 border-b border-primary-100 dark:border-dark-border pb-2"><span class="text-primary-500 shrink-0">Message ID</span><span class="font-mono font-bold break-all text-right" x-text="selected?.id ?? '—'"></span></div>
                            <div class="flex justify-between gap-2 border-b border-primary-100 dark:border-dark-border pb-2"><span class="text-primary-500 shrink-0">To</span><span class="font-mono font-bold break-all text-right" x-text="selected?.to ?? '—'"></span></div>
                            <div class="flex justify-between gap-2 border-b border-primary-100 dark:border-dark-border pb-2"><span class="text-primary-500 shrink-0">Content</span><span class="font-bold break-all text-right" x-text="selectedText"></span></div>
                            <div class="flex justify-between gap-2 border-b border-primary-100 dark:border-dark-border pb-2"><span class="text-primary-500 shrink-0">Sent At</span><span class="font-bold break-all text-right" x-text="(selected?.created_at ?? '—').toString().substring(0,19)"></span></div>
                            <div class="flex justify-between gap-2"><span class="text-primary-500 shrink-0">Updated At</span><span class="font-bold break-all text-right" x-text="selected?.updated_at ? (String(selected.updated_at).substring(0,19)) : '—'"></span></div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <template x-if="selected?.id">
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <template x-if="selectedStatus.toLowerCase() === 'failed'">
                                <button type="button" class="resend-message-btn flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-white text-xs font-bold" :data-msg-id="selected.id"><i class="fas fa-redo"></i> Resend</button>
                            </template>
                            <button type="button" class="edit-message-btn flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold" :data-msg-id="selected.id"><i class="fas fa-edit"></i> Edit</button>
                            <button type="button" class="info-message-btn flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold" :data-msg-id="selected.id"><i class="fas fa-server"></i> Info</button>
                            <button type="button" class="delete-message-btn flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg bg-red-600 hover:bg-red-500 text-white text-xs font-bold" :data-msg-id="selected.id"><i class="fas fa-trash-alt"></i> Delete</button>
                            <button @click="closeDrawer()" class="col-span-2 px-3 py-2 rounded-lg bg-gray-900 dark:bg-white dark:text-gray-900 text-white text-xs font-bold"><i class="fas fa-times mr-1"></i> Close</button>
                        </div>
                    </template>
                    <template x-if="!selected?.id">
                        <button @click="closeDrawer()" class="w-full px-3 py-2 rounded-lg bg-gray-900 dark:bg-white dark:text-gray-900 text-white text-xs font-bold"><i class="fas fa-times mr-1"></i> Close</button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection

@push('scripts')
<script>
function logDrawer(){
    return {
        drawerOpen:false,
        drawerLoading:true,
        selected:null,
        onRowClick(event, log){
            if (event.target.closest('button')) return;
            this.openLogFromRow(log);
        },
        openLogFromRow(log){
            this.selected = log || null;
            this.drawerLoading = false;
            this.drawerOpen = true;
        },
        openFromInfo(info){
            this.selected = info || { id: '—' };
            this.drawerLoading = false;
            this.drawerOpen = true;
        },
        closeDrawer(){ this.drawerOpen=false; setTimeout(()=>{ this.selected=null; }, 300); },
        get selectedStatus(){ return (this.selected?.status ?? 'unknown').replace(/_/g, ' '); },
        get selectedText(){
            const raw = this.selected?.content ?? '';
            let decoded;
            try { decoded = typeof raw === 'string' ? JSON.parse(raw) : raw; } catch(e){ decoded = null; }
            const walk = (v,d)=>{ if(v==null||d>5) return null; if(typeof v==='string') return v; if(typeof v!=='object') return null; if(Array.isArray(v)){ for(const it of v){ const t=walk(it,d+1); if(t) return t; } return null; } for(const k of ['text','message','body','caption','description','name']){ if(v[k]!==undefined){ const t=walk(v[k],d+1); if(t) return t; } } return null; };
            if(decoded && typeof decoded==='object'){
                const t = walk(decoded,0);
                if(t) return t;
                const m = decoded.imageUrl||decoded.videoUrl||decoded.audioUrl||decoded.documentUrl||decoded.stickerUrl;
                if(m) return 'Media message';
                if(Object.keys(decoded).length) return 'Media message (' + Object.keys(decoded).join(', ') + ')';
            }
            return typeof decoded==='string' ? decoded : String(raw ?? '');
        },
        get statusClass(){
            const s = String(this.selected?.status ?? '').toLowerCase();
            if(s==='sent') return 'bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300';
            if(s==='failed') return 'bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300';
            if(s==='in_progress'||s==='pending') return 'bg-yellow-50 border border-yellow-200 text-yellow-700 dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-300';
            return 'bg-gray-50 border border-gray-200 text-gray-600 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300';
        }
    }
}
</script>
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

        const routes = {
            delete: '{{ route('whatsapp.messages.delete', '__ID__') }}',
            edit: '{{ route('whatsapp.messages.edit', '__ID__') }}',
            resend: '{{ route('whatsapp.messages.resend', '__ID__') }}',
            info: '{{ route('whatsapp.messages.info', '__ID__') }}',
        };
        const csrf = '{{ csrf_token() }}';

        function runFetch(url, options, onSuccess, onFail) {
            return fetch(url, options)
                .then(function (response) {
                    return response.text().then(function (text) {
                        var data = null;
                        try { data = text ? JSON.parse(text) : null; }
                        catch (e) { throw { status: response.status, raw: text }; }
                        return { ok: response.ok, status: response.status, data: data };
                    });
                })
                .then(function (result) {
                    if (!result.ok) { throw { status: result.status, data: result.data }; }
                    if (result.data && result.data.success) {
                        showToast(result.data.message || 'Done.', true);
                        onSuccess && onSuccess(result);
                    } else if (result.data) {
                        showToast(result.data.message || 'Request failed (status ' + result.status + ').', false);
                        onFail && onFail(result);
                    } else {
                        throw { status: result.status, data: result.data };
                    }
                })
                .catch(function (err) {
                    if (err && err.data && err.data.redirect) {
                        showToast(err.data.message || 'Session expired. Redirecting...', false);
                        setTimeout(function () { window.location.href = err.data.redirect; }, 1200);
                    } else if (err && err.data && err.data.message) {
                        showToast(err.data.message, false);
                    } else if (err && typeof err.status !== 'undefined' && err.raw) {
                        showToast('Error ' + err.status + ': server returned a non-JSON response.', false);
                    } else {
                        showToast('Network error. Please try again.', false);
                    }
                    onFail && onFail();
                });
        }

        function withSpinner(btn, promise) {
            btn.disabled = true;
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            if (promise && promise.finally) {
                promise.finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
            }
        }

        document.addEventListener('click', function (event) {
            const deleteBtn = event.target.closest('.delete-message-btn');
            if (deleteBtn) {
                const msgId = deleteBtn.dataset.msgId;
                if (!msgId) return;
                if (!confirm('Delete message ' + msgId + ' for everyone? This usually only works shortly after the message was sent.')) return;
                withSpinner(deleteBtn, runFetch(
                    routes.delete.replace('__ID__', msgId),
                    { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } },
                    function () { const row = deleteBtn.closest('tr'); if (row) row.remove(); }
                ));
                return;
            }

            const editBtn = event.target.closest('.edit-message-btn');
            if (editBtn) {
                const msgId = editBtn.dataset.msgId;
                if (!msgId) return;
                const current = editBtn.dataset.current || '';
                const newText = prompt('Edit message ' + msgId + ':', current);
                if (newText === null) return;
                withSpinner(editBtn, runFetch(
                    routes.edit.replace('__ID__', msgId),
                    {
                        method: 'PUT',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ text: newText }),
                    },
                    function () { editBtn.dataset.current = newText; }
                ));
                return;
            }

            const resendBtn = event.target.closest('.resend-message-btn');
            if (resendBtn) {
                const msgId = resendBtn.dataset.msgId;
                if (!msgId) return;
                withSpinner(resendBtn, runFetch(
                    routes.resend.replace('__ID__', msgId),
                    { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } }
                ));
                return;
            }

            const infoBtn = event.target.closest('.info-message-btn');
            if (infoBtn) {
                const msgId = infoBtn.dataset.msgId;
                if (!msgId) return;
                withSpinner(infoBtn, runFetch(
                    routes.info.replace('__ID__', msgId),
                    { method: 'GET', headers: { 'Accept': 'application/json' } },
                    function (result) {
                        const info = result.data.data || {};
                        if (window.__logDrawer) {
                            let content = info.message || info.msg || info.content || info;
                            if (content && typeof content === 'object') {
                                content = JSON.stringify(content);
                            }
                            window.__logDrawer.openFromInfo({
                                id: info.msgId || info.id || '—',
                                to: info.jid || info.to || '—',
                                status: info.status ?? 'sent',
                                content: content,
                                created_at: info.createdAt || info.created_at || '—',
                                updated_at: info.updatedAt || info.updated_at || null,
                                failed_reason: info.failedReason || info.failed_reason || null,
                            });
                        }
                    }
                ));
            }
        });
    });
</script>
@endpush
