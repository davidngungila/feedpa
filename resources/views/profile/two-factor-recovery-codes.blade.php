@extends('layouts.app')

@section('title', 'Two-Factor Recovery Codes')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 animate-fade-in">
    <div class="card p-6 sm:p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-2xl text-green-600"></i>
            </div>
            <h2 class="text-xl font-black text-primary-900">Two-Factor Authentication Enabled</h2>
            <p class="text-xs text-primary-500 mt-2">
                Store these recovery codes in a safe place. They can be used to recover your account if you lose access to your authenticator device.
            </p>
        </div>

        <div class="bg-primary-50 rounded-2xl p-4 mb-6">
            <h3 class="text-xs font-bold uppercase tracking-widest text-primary-600 mb-3">Recovery Codes</h3>
            <div class="grid grid-cols-2 gap-2">
                @foreach($recoveryCodes as $code)
                    <div class="text-sm font-mono text-primary-900 bg-white px-3 py-2 rounded-lg border border-primary-100">
                        {{ $code }}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button type="button" onclick="window.print()" 
                    class="flex-1 py-3 px-4 rounded-xl bg-white border border-primary-100 text-xs font-bold text-primary-600 hover:bg-primary-50 transition-all">
                <i class="fas fa-print mr-2"></i> Print Recovery Codes
            </button>
            <a href="{{ route('profile.two-factor.recovery-codes.pdf') }}" 
               class="flex-1 py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-xs font-bold text-white text-center transition-all">
                <i class="fas fa-download mr-2"></i> Download PDF
            </a>
            <form method="POST" action="{{ route('profile.two-factor.recovery-codes.regenerate') }}">
                @csrf
                <button type="submit" 
                        class="flex-1 py-3 px-4 rounded-xl bg-primary-600 hover:bg-primary-500 text-xs font-bold text-white transition-all">
                    <i class="fas fa-sync mr-2"></i> Regenerate Codes
                </button>
            </form>
        </div>

        <div class="mt-6 pt-4 border-t border-primary-100 text-center">
            <a href="{{ route('profile.index') }}" 
               class="text-xs font-bold text-primary-600 hover:text-primary-500 transition-all">
                <i class="fas fa-arrow-left mr-1"></i> Back to Profile
            </a>
        </div>
    </div>
</div>
@endsection
