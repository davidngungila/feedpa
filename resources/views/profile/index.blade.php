@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    <!-- Header Card -->
    <div class="card overflow-hidden">
        <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <!-- Avatar Section -->
                <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center overflow-hidden">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-user text-4xl text-primary-600 dark:text-primary-400"></i>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">User ID</div>
                    <div class="text-xl font-mono font-bold text-primary-900 dark:text-white">#{{ auth()->user()->id }}</div>
                    <div class="mt-2">
                        <span class="px-4 py-1.5 text-xs font-bold rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                            <i class="fas fa-id-card me-2"></i>
                            {{ auth()->user()->position ?? 'Member' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="text-center sm:text-right">
                <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Profile</div>
                <div class="text-3xl font-mono font-black text-primary-600 dark:text-primary-400">
                    {{ auth()->user()->name }}
                </div>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Contact Info -->
        <div class="card p-6 space-y-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-envelope"></i> Contact Information
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                    <span class="text-xs text-gray-400 uppercase font-bold">Email Address</span>
                    <span class="text-xs font-bold text-primary-900 dark:text-white">{{ auth()->user()->email }}</span>
                </div>
                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                    <span class="text-xs text-gray-400 uppercase font-bold">Phone Number</span>
                    <span class="text-xs font-bold text-primary-900 dark:text-white">{{ auth()->user()->phone ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-gray-400 uppercase font-bold">Member Since</span>
                    <span class="text-xs font-bold text-primary-900 dark:text-white">
                        {{ auth()->user()->created_at->format('M d, Y • H:i:s') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Account Info -->
        <div class="card p-6 space-y-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-cog"></i> Account Details
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                    <span class="text-xs text-gray-400">Status</span>
                    <span class="text-xs font-bold text-green-600 dark:text-green-400 uppercase">
                        <i class="fas fa-check-circle me-1"></i> Active
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-gray-400">Last Updated</span>
                    <span class="text-xs font-bold text-primary-900 dark:text-white">
                        {{ auth()->user()->updated_at->format('M d, Y • H:i:s') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Card -->
    <div class="card p-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 mb-3 flex items-center gap-2">
            <i class="fas fa-tools"></i> Quick Actions
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('profile.edit') }}" 
               class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-900/20 transition-all">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
            <a href="{{ route('profile.edit') }}#password" 
               class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 transition-all">
                <i class="fas fa-key"></i> Change Password
            </a>
            <a href="{{ route('dashboard.index') }}" 
               class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 transition-all">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Active Sessions Card -->
    <div class="card p-6" x-data="{ activeSessions: [], loading: true }" x-init="
        async function fetchSessions() {
            try {
                const response = await fetch('{{ route('profile.sessions') }}');
                const data = await response.json();
                activeSessions = data.sessions;
            } catch (e) {
                console.error('Error fetching sessions:', e);
            } finally {
                loading = false;
            }
        }
        await fetchSessions();
        setInterval(fetchSessions, 5000);
    ">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-laptop-code"></i> Active Sessions
            </h3>
            <form method="POST" action="{{ route('profile.sessions.logout-others') }}" x-data="{}" @submit.prevent="
                const form = $event.target;
                fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                    .then(() => {
                        fetchSessions();
                    });
            ">
                @csrf
                <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 transition-all">
                    Logout All Others
                </button>
            </form>
        </div>

        <div x-show="loading" class="py-8 text-center">
            <i class="fas fa-spinner fa-spin text-primary-400 text-2xl"></i>
            <p class="text-xs text-primary-500 mt-2">Loading sessions...</p>
        </div>

        <template x-if="!loading && activeSessions.length === 0">
            <div class="py-8 text-center">
                <i class="fas fa-users-slash text-4xl text-primary-200 dark:text-primary-800"></i>
                <p class="text-xs text-primary-500 mt-2">No active sessions found.</p>
            </div>
        </template>

        <div x-show="!loading && activeSessions.length > 0" class="space-y-3">
            <template x-for="session in activeSessions" :key="session.id">
                <div class="flex items-center justify-between p-4 rounded-xl bg-primary-50/50 dark:bg-primary-900/20 border border-primary-100 dark:border-dark-border">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                            <i class="fas fa-desktop text-primary-600 dark:text-primary-400"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-primary-900 dark:text-white" x-text="session.is_current ? 'Current Session' : 'Session'"></span>
                                <template x-if="session.is_current">
                                    <span class="badge badge-green text-[9px]"><i class="fas fa-check"></i> Active</span>
                                </template>
                            </div>
                            <div class="text-[10px] text-primary-500 mt-0.5">
                                <span x-text="session.ip_address ?? 'Unknown IP'"></span> • 
                                <span x-text="session.user_agent ?? 'Unknown Browser'"></span>
                            </div>
                            <div class="text-[10px] text-primary-400 mt-0.5">
                                Last activity: <span x-text="session.last_activity"></span>
                            </div>
                        </div>
                    </div>
                    <template x-if="!session.is_current">
                        <form method="POST" x-bind:action="`{{ route('profile.sessions.logout', '') }}/${session.session_id}`" 
                              x-data="{}" 
                              @submit.prevent="
                                const form = $event.target;
                                fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                                    .then(() => fetchSessions());
                              ">
                            @csrf
                            <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 transition-all">
                                Logout
                            </button>
                        </form>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection