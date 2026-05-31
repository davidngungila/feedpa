@extends('layouts.app')

@section('title', 'Verify Payout OTP')

@section('content')
<div class="space-y-6 animate-fade-in max-w-2xl mx-auto">
    <div class="flex flex-col gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-lock text-primary-500"></i>
                Verify Payout
            </h2>
            <p class="text-xs text-primary-500 mt-1">Please enter the OTP sent to your phone to proceed with payout</p>
        </div>
    </div>

    @if(session('error'))
        <div class="card p-4 border-l-4 border-l-red-500 bg-red-50/60 dark:bg-red-900/10">
            <p class="text-xs font-bold text-red-700 dark:text-red-300">
                <i class="fas fa-circle-exclamation me-1"></i> {{ session('error') }}
            </p>
        </div>
    @endif

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-circle-check me-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    <!-- Payout Details Card -->
    <div class="card p-6">
        <h3 class="text-xs font-black uppercase tracking-wider text-primary-500 mb-4 flex items-center gap-2">
            <i class="fas fa-receipt"></i> Payout Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                <p class="text-[10px] font-bold text-primary-500 uppercase">Order Reference</p>
                <p class="text-sm font-semibold text-primary-900 dark:text-white font-mono">{{ $payout->order_reference }}</p>
            </div>
            <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                <p class="text-[10px] font-bold text-primary-500 uppercase">Amount</p>
                <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $payout->currency }} {{ number_format($payout->amount, 2) }}</p>
            </div>
            <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                <p class="text-[10px] font-bold text-primary-500 uppercase">Recipient</p>
                <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $payout->recipient_name }}</p>
            </div>
            <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                <p class="text-[10px] font-bold text-primary-500 uppercase">Type</p>
                <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $payout->payout_type === 'MOBILE_MONEY' ? 'Mobile Money' : 'Bank Transfer' }}</p>
            </div>
            @if($payout->payout_type === 'MOBILE_MONEY')
                <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                    <p class="text-[10px] font-bold text-primary-500 uppercase">Phone</p>
                    <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $payout->recipient_phone }}</p>
                </div>
            @else
                <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                    <p class="text-[10px] font-bold text-primary-500 uppercase">Bank</p>
                    <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $payout->bank_name }}</p>
                </div>
                <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                    <p class="text-[10px] font-bold text-primary-500 uppercase">Account</p>
                    <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $payout->bank_account_number }}</p>
                </div>
            @endif
            @if($payout->description)
                <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl md:col-span-2">
                    <p class="text-[10px] font-bold text-primary-500 uppercase">Description</p>
                    <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $payout->description }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- OTP Form -->
    <form action="{{ route('payouts.verify', $payout->order_reference) }}" method="POST" class="card p-6">
        @csrf
        <div class="mb-4">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">
                Enter OTP
            </label>
            <input type="text" name="otp" maxlength="6" placeholder="000000" required
                   class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-4 py-3 text-2xl font-mono text-center tracking-[0.5em] font-black text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-black transition-all">
                <i class="fas fa-check-circle me-1"></i> Verify Payout
            </button>
            <a href="{{ route('payouts.resend-otp', $payout->order_reference) }}"
               class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-dark-border dark:hover:bg-dark-700 text-xs font-bold text-gray-700 dark:text-gray-200 transition-all text-center">
                <i class="fas fa-redo me-1"></i> Resend OTP
            </a>
        </div>
    </form>

</div>
@endsection