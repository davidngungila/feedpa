@extends('layouts.app')

@section('title', 'Manage WhatsApp Sessions')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 animate-fade-in">
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
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
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
                                    @if($status === 'connected' || $status === 'online' || $status === 'active')
                                        <button type="button" class="session-action p-2 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 hover:bg-yellow-600 hover:text-white transition-all" title="Restart" data-url="{{ route('whatsapp.sessions.restart', $sessionId) }}" data-method="POST">
                                            <i class="fas fa-sync text-xs"></i>
                                        </button>
                                        <button type="button" class="session-action p-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 hover:bg-gray-600 hover:text-white transition-all" title="Disconnect" data-url="{{ route('whatsapp.sessions.disconnect', $sessionId) }}" data-method="POST">
                                            <i class="fas fa-unlink text-xs"></i>
                                        </button>
                                    @else
                                        <button type="button" class="session-action p-2 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 hover:bg-green-600 hover:text-white transition-all" title="Connect" data-url="{{ route('whatsapp.sessions.connect', $sessionId) }}" data-method="POST">
                                            <i class="fas fa-plug text-xs"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="session-action p-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-600 hover:text-white transition-all" title="Delete" data-url="{{ route('whatsapp.sessions.destroy', $sessionId) }}" data-method="DELETE">
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
</div>
@endsection

@push('scripts')
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
                createBtn.disabled = true;
                createBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Creating...';

                fetch('{{ route('whatsapp.sessions.create') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
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
