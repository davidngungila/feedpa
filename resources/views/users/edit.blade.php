@extends('layouts.app')

@section('title', 'Edit User')

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
                <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Edit User</div>
                <div class="text-3xl font-mono font-black text-primary-600 dark:text-primary-400">
                    {{ $user->name }}
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card p-6 sm:p-8 space-y-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
            <i class="fas fa-user-edit"></i> Update Information
        </h3>
        
        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Info -->
                <div class="space-y-4">
                    <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-user"></i> Personal Information
                    </h4>
                    <div>
                        <label for="name" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Full Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                        @error('name')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="position" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Position / Role</label>
                        <input id="position" type="text" name="position" value="{{ old('position', $user->position) }}"
                               class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="e.g. Secretary, Chairman">
                        @error('position')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                                   class="rounded border-primary-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-[10px] font-bold text-primary-700 dark:text-primary-300 uppercase tracking-wider">Is Admin</span>
                        </label>
                    </div>
                </div>

                <!-- Contact & Security -->
                <div class="space-y-4">
                    <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-shield-alt"></i> Account & Security
                    </h4>
                    <div>
                        <label for="email" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                        @error('email')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">New Password (Optional)</label>
                        <input id="password" type="password" name="password"
                               class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                        @error('password')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation"
                               class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
            </div>

            <!-- Avatar Upload -->
            <div class="pt-4 border-t border-primary-100 dark:border-dark-border">
                <label for="avatar" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Profile Photo</label>
                <input id="avatar" type="file" name="avatar" accept="image/*"
                       class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                <p class="mt-1 text-[10px] text-primary-500">Optional. Max size: 2MB.</p>
                @error('avatar')
                    <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 flex flex-wrap gap-3 justify-end">
                <a href="{{ route('users.show', $user->id) }}" 
                   class="px-5 py-2.5 rounded-xl border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-900/20 transition-all">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection