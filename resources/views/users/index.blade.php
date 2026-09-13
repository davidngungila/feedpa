@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in" x-data="userDrawer()" @keydown.escape.window="closeDrawer()">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-users text-primary-500"></i> Users Management
            </h2>
            <p class="text-xs text-primary-500 mt-1">Manage all system users and their permissions</p>
        </div>
        @if(auth()->user()->is_admin)
        <div class="flex gap-2">
            <a href="{{ route('users.create') }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                <i class="fas fa-plus mr-1"></i> Add New User
            </a>
        </div>
        @endif
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    <!-- Users Table Card -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30 flex items-center justify-between gap-2">
            <p class="text-[11px] font-semibold text-primary-700 dark:text-primary-300 flex items-center gap-1.5"><i class="fas fa-hand-pointer text-primary-500"></i> Click any row to open right drawer — full user & permissions details</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Payout Access</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @foreach($users as $user)
                        <tr @click="openDrawer({{ $user->id }})" class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center overflow-hidden shrink-0">
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-user text-primary-600 dark:text-primary-400"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-primary-900 dark:text-white truncate">{{ $user->name }}</p>
                                        <p class="text-[10px] text-primary-500">Joined: {{ $user->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                                    {{ $user->position ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold {{ $user->is_admin ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300' }}">
                                    {{ $user->is_admin ? 'Admin' : 'User' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold {{ $user->can_create_payouts ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                                    {{ $user->can_create_payouts ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-6 border-t border-primary-100 dark:border-dark-border">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Right Drawer -->
    <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] flex justify-end overflow-hidden" style="display:none;">
        <div @click="closeDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[480px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-5 pt-0 pb-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/60 dark:bg-dark-900/60">
                <div>
                    <h3 class="text-sm font-bold text-primary-900 dark:text-white flex items-center gap-2"><i class="fas fa-user text-primary-600"></i> User Details</h3>
                    <p class="text-[11px] text-primary-500" x-text="selected ? 'ID #' + selected.id + ' • ' + (selected.email ?? '') : ''"></p>
                </div>
                <button @click="closeDrawer()" class="w-8 h-8 rounded-lg bg-white dark:bg-dark-800 border border-primary-100 dark:border-dark-border flex items-center justify-center hover:bg-primary-50"><i class="fas fa-times text-primary-600"></i></button>
            </div>

            <div x-show="loading" class="p-8 text-center">
                <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin mx-auto"></div>
                <p class="text-xs text-primary-500 mt-3">Loading details...</p>
            </div>

            <div x-show="!loading && selected" class="flex-1 min-h-0 overflow-y-auto p-5 space-y-5" style="display:none;" x-cloak>
                <!-- Avatar + identity -->
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center overflow-hidden shrink-0">
                        <template x-if="selected?.avatar">
                            <img :src="selected.avatar" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!selected?.avatar">
                            <i class="fas fa-user text-2xl text-primary-600 dark:text-primary-400"></i>
                        </template>
                    </div>
                    <div class="min-w-0">
                        <p class="text-lg font-black text-primary-900 dark:text-white truncate" x-text="selected?.name ?? ''"></p>
                        <p class="text-[11px] text-primary-500" x-text="selected?.position ?? 'No position'"></p>
                    </div>
                </div>

                <!-- Status badges -->
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="p-3 rounded-xl border" :class="selected?.is_admin ? 'bg-purple-50 border-purple-200' : 'bg-gray-50 border-gray-200'">
                        <p class="text-xs font-bold" :class="selected?.is_admin ? 'text-purple-800' : 'text-gray-600'" x-text="selected?.is_admin ? 'ADMIN' : 'USER'"></p>
                        <p class="text-[10px] text-primary-500">Role</p>
                    </div>
                    <div class="p-3 rounded-xl border" :class="selected?.can_create_payouts ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                        <p class="text-xs font-bold" :class="selected?.can_create_payouts ? 'text-green-800' : 'text-red-800'" x-text="selected?.can_create_payouts ? 'ENABLED' : 'DISABLED'"></p>
                        <p class="text-[10px] text-primary-500">Payouts</p>
                    </div>
                    <div class="p-3 rounded-xl border" :class="selected?.two_factor_enabled ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200'">
                        <p class="text-xs font-bold" :class="selected?.two_factor_enabled ? 'text-green-800' : 'text-gray-500'" x-text="selected?.two_factor_enabled ? 'ON' : 'OFF'"></p>
                        <p class="text-[10px] text-primary-500">2FA</p>
                    </div>
                </div>

                <!-- Contact -->
                <div class="p-4 rounded-xl bg-primary-50 border border-primary-100">
                    <p class="text-[10px] font-bold tracking-widest text-primary-500 mb-2">CONTACT INFORMATION</p>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between gap-2 border-b border-primary-100 pb-2"><span class="text-primary-500 shrink-0">Email</span><span class="text-primary-900 dark:text-white font-bold break-all text-right" x-text="selected?.email ?? '—'"></span></div>
                        <div class="flex justify-between border-b border-primary-100 pb-2"><span class="text-primary-500">Phone</span><span class="font-mono font-bold" x-text="selected?.phone ?? '—'"></span></div>
                        <div class="flex justify-between"><span class="text-primary-500">Member Since</span><span class="font-bold" x-text="selected?.created_at ?? '—'"></span></div>
                    </div>
                </div>

                <!-- Lock warning -->
                <template x-if="selected?.is_locked">
                    <div class="p-4 rounded-xl bg-red-50 border border-red-200">
                        <p class="text-xs font-bold text-red-800 flex items-center gap-2"><i class="fas fa-lock"></i> Account is locked</p>
                    </div>
                </template>

                @if(auth()->user()->is_admin)
                <!-- Actions -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a :href="'{{ route('users.index') }}/' + selected?.id + '/edit'" class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold"><i class="fas fa-edit"></i> Edit</a>
                    <form method="POST" :action="'{{ route('users.index') }}/' + selected?.id + '/reset-password'" onsubmit="return confirm('Are you sure you want to reset this user\'s password?')">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-white text-xs font-bold"><i class="fas fa-key"></i> Reset Pass</button>
                    </form>
                    <form method="POST" :action="'{{ route('users.index') }}/' + selected?.id" onsubmit="return confirm('Are you sure you want to delete this user?')" class="col-span-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg bg-red-600 hover:bg-red-500 text-white text-xs font-bold"><i class="fas fa-trash"></i> Delete User</button>
                    </form>
                    <button @click="closeDrawer()" class="col-span-2 px-3 py-2 rounded-lg bg-gray-900 text-white text-xs font-bold">Close</button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection

@push('scripts')
<script>
function userDrawer(){
    return {
        drawerOpen:false,
        selected:null,
        loading:false,
        openDrawer(id){
            this.drawerOpen=true;
            this.loading=true;
            this.selected=null;
            fetch('{{ url('users') }}/' + id + '/details', {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
                .then(r=>r.json())
                .then(data=>{ this.selected=data; this.loading=false; })
                .catch(()=>{ this.loading=false; alert('Failed to load details'); });
        },
        closeDrawer(){ this.drawerOpen=false; setTimeout(()=>{ this.selected=null; }, 300); }
    }
}
</script>
@endpush