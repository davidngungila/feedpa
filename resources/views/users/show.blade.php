@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    <!-- Header Card -->
    <div class="card overflow-hidden">
        <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <!-- Avatar Section -->
                <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center overflow-hidden">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-user text-4xl text-primary-600 dark:text-primary-400"></i>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">User ID</div>
                    <div class="text-xl font-mono font-bold text-primary-900 dark:text-white">#{{ $user->id }}</div>
                    <div class="mt-2">
                        <span class="px-4 py-1.5 text-xs font-bold rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                            <i class="fas fa-id-card me-2"></i>
                            {{ $user->position ?? 'Member' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="text-center sm:text-right">
                <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Full Name</div>
                <div class="text-3xl font-mono font-black text-primary-600 dark:text-primary-400">
                    {{ $user->name }}
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
                    <span class="text-xs font-bold text-primary-900 dark:text-white">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-gray-400 uppercase font-bold">Member Since</span>
                    <span class="text-xs font-bold text-primary-900 dark:text-white">
                        {{ $user->created_at->format('M d, Y • H:i:s') }}
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
                        {{ $user->updated_at->format('M d, Y • H:i:s') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Card -->
    <div class="card p-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 mb-3 flex items-center gap-2">
            <i class="fas fa-tools"></i> Actions
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('users.edit', $user->id) }}" 
               class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-900/20 transition-all">
                <i class="fas fa-edit"></i> Edit User
            </a>
            <a href="{{ route('users.index') }}" 
               class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 transition-all">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <form id="deleteForm{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: contents;">
                @csrf
                @method('DELETE')
                <button type="button" onclick="confirmDelete({{ $user->id }})" 
                        class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-red-100 dark:border-red-900 text-red-600 dark:text-red-400 text-xs font-bold hover:bg-red-50 transition-all">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        document.getElementById('deleteForm' + id).submit();
    }
}
</script>
@endsection