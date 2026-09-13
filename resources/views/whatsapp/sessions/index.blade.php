@extends('layouts.app')

@section('title', 'Manage WhatsApp Sessions')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 animate-fade-in" x-data="sessionDrawer()" @keydown.escape.window="closeDrawer()">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fab fa-whatsapp text-green-500"></i> Manage Sessions
            </h2>
            <p class="text-xs text-primary-500 mt-1">View and manage your WhatsApp API sessions</p>
        </div>
        <div class="flex gap-2">
            <button type="button" id="createSessionBtn" class="px-4 py-2 rounded-xl bg-green-600 hover:bg-green-500 text-white text-xs font-bold transition-all">
                <i class="fas fa-plus mr-1"></i> Create Session
            </button>
            <a href="{{ route('settings.whatsapp') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
                <i class="fas fa-cog mr-1"></i> Session Settings
            </a>
        </div>
    </div>

    <!-- Active Session Card -->
    @if($sessionInfo)
        <div class="card p-6 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                    <i class="fab fa-whatsapp text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-green-800 dark:text-green-200">Session Connected</h4>
                    <p class="text-xs text-green-600 dark:text-green-400">Your WhatsApp session is active and ready to send messages.</p>
                </div>
                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                    <i class="fas fa-circle text-[6px] me-1 animate-pulse"></i> ONLINE
                </span>
            </div>
        </div>
    @else
        <div class="card p-6 border-l-4 border-l-yellow-500 bg-yellow-50/60 dark:bg-yellow-900/10">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center">
                    <i class="fas fa-plug text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-yellow-800 dark:text-yellow-200">No Active Session</h4>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400">Configure your Session API Key in WhatsApp Settings first.</p>
                </div>
                <a href="{{ route('settings.whatsapp') }}" class="px-4 py-2 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-white text-xs font-bold transition-all">
                    Configure
                </a>
            </div>
        </div>
    @endif

    @if(!$personalTokenConfigured)
        <div class="card p-4 border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
            <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle mr-1"></i> The WhatsApp Personal Access Token is not configured.
                <a href="{{ route('settings.whatsapp') }}" class="underline">Configure it in WhatsApp settings</a> to manage sessions from here.
            </p>
        </div>
    @endif

    <!-- Sessions Table -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-primary-100 dark:border-dark-border">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-list"></i> WhatsApp Sessions
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Session</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($sessions as $session)
                        @php
                            $status = strtolower($session['status'] ?? 'unknown');
                            $statusClass = match($status) {
                                'connected', 'online', 'active' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                'disconnected', 'offline', 'expired' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                            };
                        @endphp
                        <tr @click="openDrawer(@js($session))" class="cursor-pointer hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 flex items-center justify-center">
                                        <i class="fab fa-whatsapp text-green-600 dark:text-green-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $session['session'] ?? $session['name'] ?? $session['id'] ?? 'Unknown' }}</p>
                                        <p class="text-[10px] text-primary-500">{{ $session['session_id'] ?? $session['unique_id'] ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold {{ $statusClass }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ $session['phone'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ isset($session['created_at']) ? \Illuminate\Support\Str::limit($session['created_at'], 10) : '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @php $sessionId = $session['id'] ?? $session['session'] ?? ''; @endphp
                                    <a href="{{ route('whatsapp.sessions.message-logs', $sessionId) }}" @click.stop class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Message Logs">
                                        <i class="fas fa-scroll text-xs"></i>
                                    </a>
                                    @if($status === 'connected' || $status === 'online' || $status === 'active')
                                        <button type="button" @click.stop class="session-action p-2 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 hover:bg-yellow-600 hover:text-white transition-all" title="Restart" data-url="{{ route('whatsapp.sessions.restart', $sessionId) }}" data-method="POST">
                                            <i class="fas fa-sync text-xs"></i>
                                        </button>
                                        <button type="button" @click.stop class="session-action p-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 hover:bg-gray-600 hover:text-white transition-all" title="Disconnect" data-url="{{ route('whatsapp.sessions.disconnect', $sessionId) }}" data-method="POST">
                                            <i class="fas fa-unlink text-xs"></i>
                                        </button>
                                    @else
                                        <button type="button" @click.stop class="session-action p-2 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 hover:bg-green-600 hover:text-white transition-all" title="Connect" data-url="{{ route('whatsapp.sessions.connect', $sessionId) }}" data-method="POST">
                                            <i class="fas fa-plug text-xs"></i>
                                        </button>
                                    @endif
                                    <button type="button" @click.stop class="session-action p-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-600 hover:text-white transition-all" title="Delete" data-url="{{ route('whatsapp.sessions.destroy', $sessionId) }}" data-method="DELETE">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <i class="fab fa-whatsapp text-4xl text-primary-300 mb-3 block"></i>
                                <p class="text-sm font-bold text-primary-500">No sessions found</p>
                                <p class="text-xs text-primary-400 mt-1">Sessions are managed via the Wasender dashboard.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Session Details Drawer -->
    <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] flex justify-end overflow-hidden" style="display:none;">
        <div @click="closeDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[480px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="flex items-center justify-between p-5 border-b border-primary-100 dark:border-dark-border shrink-0">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-primary-500">Session Details</p>
                    <h3 class="text-lg font-black text-primary-900 dark:text-white" x-text="sessionName()"></h3>
                </div>
                <button type="button" @click="closeDrawer()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 dark:hover:text-gray-300 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto p-5 space-y-4">
                <div x-show="loading" class="flex items-center justify-center py-16">
                    <i class="fas fa-spinner fa-spin text-2xl text-green-600"></i>
                </div>

                <div x-show="!loading" class="space-y-4">
                    <!-- Status -->
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1.5 rounded-full text-[10px] font-bold" :class="statusClass()">
                            <span x-text="sessionStatus()" class="uppercase"></span>
                        </span>
                        <template x-if="isOnline()">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                </span>
                                ONLINE
                            </span>
                        </template>
                    </div>

                    <!-- Details -->
                    <div class="bg-primary-50/50 dark:bg-primary-900/10 rounded-xl divide-y divide-primary-100 dark:divide-primary-800">
                        <div class="flex items-center justify-between p-4">
                            <p class="text-xs font-bold text-primary-500">Session</p>
                            <p class="text-xs font-bold text-primary-900 dark:text-white text-right" x-text="sessionName()"></p>
                        </div>
                        <div class="flex items-center justify-between p-4">
                            <p class="text-xs font-bold text-primary-500">Session ID</p>
                            <p class="text-xs font-mono text-primary-700 dark:text-primary-300 text-right break-all" x-text="sessionUniqueId()"></p>
                        </div>
                        <div class="flex items-center justify-between p-4">
                            <p class="text-xs font-bold text-primary-500">Phone</p>
                            <p class="text-xs text-primary-900 dark:text-white text-right" x-text="selectedSession.phone || '—'"></p>
                        </div>
                        <div class="flex items-center justify-between p-4">
                            <p class="text-xs font-bold text-primary-500">Created</p>
                            <p class="text-xs text-primary-900 dark:text-white text-right" x-text="selectedSession.created_at || '—'"></p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <template x-if="!isOnline()">
                                <form method="POST" :action="connectUrl">
                                    @csrf
                                    <button type="submit" class="w-full p-3 rounded-xl bg-green-600 hover:bg-green-500 text-white text-xs font-bold transition-all">
                                        <i class="fas fa-plug mr-1"></i> Connect
                                    </button>
                                </form>
                            </template>
                            <template x-if="isOnline()">
                                <form method="POST" :action="disconnectUrl">
                                    @csrf
                                    <button type="submit" class="w-full p-3 rounded-xl bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-700 text-xs font-bold transition-all">
                                        <i class="fas fa-unlink mr-1"></i> Disconnect
                                    </button>
                                </form>
                                <form method="POST" :action="restartUrl">
                                    @csrf
                                    <button type="submit" class="w-full p-3 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-white text-xs font-bold transition-all">
                                        <i class="fas fa-sync mr-1"></i> Restart
                                    </button>
                                </form>
                            </template>
                        </div>

                        <form method="POST" :action="deleteUrl" onsubmit="return confirm('Are you sure you want to delete this session?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full p-3 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold transition-all">
                                <i class="fas fa-trash mr-1"></i> Delete Session
                            </button>
                        </form>

                        <a :href="messageLogsUrl" class="w-full p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-600 hover:text-white text-xs font-bold transition-all inline-flex items-center justify-center gap-2">
                            <i class="fas fa-scroll"></i> View Message Logs
                        </a>

                        <button type="button" @click="closeDrawer()" class="w-full p-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-primary-700 dark:text-primary-300 hover:bg-gray-200 dark:hover:bg-gray-700 text-xs font-bold transition-all">
                            <i class="fas fa-times mr-1"></i> Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@push('scripts')
<script>
    function sessionDrawer() {
        return {
            drawerOpen: false,
            loading: false,
            selectedSession: {},
            sessionId: '',
            connectUrl: '',
            disconnectUrl: '',
            restartUrl: '',
            deleteUrl: '',
            messageLogsUrl: '',
            openDrawer(session) {
                this.selectedSession = session;
                this.sessionId = session.id || session.session || '';
                this.connectUrl = '{{ route("whatsapp.sessions.connect", "__SID__") }}'.replace('__SID__', this.sessionId);
                this.disconnectUrl = '{{ route("whatsapp.sessions.disconnect", "__SID__") }}'.replace('__SID__', this.sessionId);
                this.restartUrl = '{{ route("whatsapp.sessions.restart", "__SID__") }}'.replace('__SID__', this.sessionId);
                this.deleteUrl = '{{ route("whatsapp.sessions.destroy", "__SID__") }}'.replace('__SID__', this.sessionId);
                this.messageLogsUrl = '{{ route("whatsapp.sessions.message-logs", "__SID__") }}'.replace('__SID__', this.sessionId);
                this.drawerOpen = true;
                this.loading = true;
                setTimeout(() => this.loading = false, 600);
            },
            closeDrawer() {
                this.drawerOpen = false;
            },
            sessionStatus() {
                return String(this.selectedSession.status || 'unknown').toLowerCase();
            },
            isOnline() {
                return ['connected', 'online', 'active'].includes(this.sessionStatus());
            },
            statusClass() {
                const s = this.sessionStatus();
                if (['connected', 'online', 'active'].includes(s)) {
                    return 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300';
                }
                if (['disconnected', 'offline', 'expired'].includes(s)) {
                    return 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300';
                }
                return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300';
            },
            sessionName() {
                return this.selectedSession.session || this.selectedSession.name || this.selectedSession.id || 'Unknown';
            },
            sessionUniqueId() {
                return this.selectedSession.session_id || this.selectedSession.unique_id || '—';
            },
        };
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.session-action').forEach(btn => {
            btn.addEventListener('click', function () {
                const url = this.dataset.url;
                const method = this.dataset.method;

                if (method === 'DELETE' && !confirm('Are you sure you want to delete this session?')) return;

                fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message || 'Action completed.');
                    location.reload();
                })
                .catch(error => alert('Error: ' + error.message));
            });
        });

        const createBtn = document.getElementById('createSessionBtn');
        if (createBtn) {
            createBtn.addEventListener('click', function () {
                const name = prompt('Session name (used on Wasender dashboard):', 'feedtan-session');
                if (!name) return;

                createBtn.disabled = true;
                createBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Creating...';

                const formData = new FormData();
                formData.append('name', name);

                fetch('{{ route('whatsapp.sessions.create') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    createBtn.disabled = false;
                    createBtn.innerHTML = '<i class="fas fa-plus mr-1"></i> Create Session';
                    alert(data.message || 'Action completed.');
                    location.reload();
                })
                .catch(error => {
                    createBtn.disabled = false;
                    createBtn.innerHTML = '<i class="fas fa-plus mr-1"></i> Create Session';
                    alert('Error: ' + error.message);
                });
            });
        }
    });
</script>
@endpush