@extends('layouts.app')

@section('title', 'Enable Two-Factor Authentication')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 animate-fade-in">
    <div class="card p-6 sm:p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shield-alt text-2xl text-primary-600"></i>
            </div>
            <h2 class="text-xl font-black text-primary-900">Enable Two-Factor Authentication</h2>
            <p class="text-xs text-primary-500 mt-2">
                Scan the QR code with your authenticator app (like Google Authenticator) and enter the code to verify.
            </p>
        </div>

        <div class="flex flex-col items-center gap-4 mb-6">
            <div class="p-4 bg-white rounded-2xl border border-primary-100 shadow-sm">
                {!! $qrCodeSvg !!}
            </div>
            <div class="text-center">
                <p class="text-xs text-primary-500">Or manually enter this code:</p>
                <p class="text-sm font-mono font-bold text-primary-900 mt-1 bg-primary-50 px-3 py-1 rounded-lg">{{ $secret }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.two-factor.enable') }}" class="space-y-4">
            @csrf
            <div>
                <label for="code" class="block text-xs font-bold uppercase tracking-widest text-primary-500 mb-2">Verification Code</label>
                <input type="text" id="code" name="code" 
                       class="w-full bg-primary-50 border border-primary-100 rounded-xl px-4 py-3 text-sm text-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                       placeholder="Enter 6-digit code" 
                       maxlength="6" 
                       inputmode="numeric"
                       pattern="[0-9]*">
                @error('code')
                    <p class="text-xs font-bold text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('profile.index') }}" 
                   class="flex-1 py-3 px-4 rounded-xl bg-white border border-primary-100 text-xs font-bold text-primary-600 hover:bg-primary-50 transition-all">
                    Cancel
                </a>
                <button type="submit" 
                        class="flex-1 py-3 px-4 rounded-xl bg-primary-600 hover:bg-primary-500 text-xs font-bold text-white transition-all">
                    Enable Two-Factor Auth
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
