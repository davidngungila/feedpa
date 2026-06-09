@extends('layouts.app')

@section('title', 'Disable Two-Factor Authentication')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 animate-fade-in">
    <div class="card p-6 sm:p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
            </div>
            <h2 class="text-xl font-black text-primary-900">Disable Two-Factor Authentication</h2>
            <p class="text-xs text-primary-500 mt-2">
                Are you sure you want to disable two-factor authentication? This will make your account less secure.
            </p>
        </div>

        <form method="POST" action="{{ route('profile.two-factor.disable') }}" class="space-y-4">
            @csrf
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-widest text-primary-500 mb-2">Confirm Your Password</label>
                <input type="password" id="password" name="password" 
                       class="w-full bg-primary-50 border border-primary-100 rounded-xl px-4 py-3 text-sm text-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                       placeholder="Enter your password" required>
                @error('password')
                    <p class="text-xs font-bold text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('profile.index') }}" 
                   class="flex-1 py-3 px-4 rounded-xl bg-white border border-primary-100 text-xs font-bold text-primary-600 hover:bg-primary-50 transition-all">
                    Cancel
                </a>
                <button type="submit" 
                        class="flex-1 py-3 px-4 rounded-xl bg-red-600 hover:bg-red-500 text-xs font-bold text-white transition-all">
                    Disable Two-Factor Auth
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
