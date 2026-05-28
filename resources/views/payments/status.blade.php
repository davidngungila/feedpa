@extends('layouts.app')

@section('title', 'Payment Status')

@section('content')
@php
    $payment = $paymentData;
    $isSuccessful = in_array($payment['status'] ?? '', ['SUCCESS', 'SETTLED']);
    $isFailed = in_array($payment['status'] ?? '', ['FAILED', 'CANCELLED', 'DECLINED']);
    
    $statusText = $payment['status'] ?? 'UNKNOWN';
    $statusIcon = 'fa-clock';
    $statusColor = 'badge-yellow';

    if ($isSuccessful) {
        $statusText = 'Verified';
        $statusIcon = 'fa-check-circle';
        $statusColor = 'badge-green';
    } elseif ($isFailed) {
        $statusText = 'Failed';
        $statusIcon = 'fa-times-circle';
        $statusColor = 'badge-red';
    }
@endphp

<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    @if(isset($error) && $error)
        <div class="card p-6 border-red-100 bg-red-50 dark:bg-red-900/10">
            <div class="flex items-center gap-4 text-red-600 dark:text-red-400">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
                <div>
                    <h4 class="font-bold">Error</h4>
                    <p class="text-xs">{{ $error }}</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('payments.history') }}" class="text-xs font-bold text-primary-600 hover:underline">
                    <i class="fas fa-arrow-left me-1"></i> Back to History
                </a>
            </div>
        </div>
    @elseif(!$payment)
        <div class="card p-6 text-center">
            <i class="fas fa-search text-4xl text-primary-200 mb-4"></i>
            <h4 class="font-bold text-primary-900 dark:text-white">Transaction Not Found</h4>
            <p class="text-xs text-primary-500 mb-4">We couldn't find any transaction with reference: {{ $orderReference }}</p>
            <a href="{{ route('payments.history') }}" class="btn bg-primary-600 text-white px-6 py-2 rounded-lg text-xs font-bold">
                Back to History
            </a>
        </div>
    @else
        <!-- Status Header Card -->
        <div class="card overflow-hidden">
            <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <!-- QR Code Section -->
                    <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                        {!! QrCode::size(100)->margin(1)->generate(request()->fullUrl()) !!}
                    </div>
                    <div>
                        <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Order Reference</div>
                        <div class="text-xl font-mono font-bold text-primary-900 dark:text-white">{{ $payment['orderReference'] ?? 'N/A' }}</div>
                        <div class="mt-2">
                            <span class="badge {{ $statusColor }} px-4 py-1.5 text-xs">
                                <i class="fas {{ $statusIcon }} me-2"></i>
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="text-center sm:text-right">
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Total Amount</div>
                    <div class="text-3xl font-mono font-black text-primary-600 dark:text-primary-400">
                        {{ $payment['collectedCurrency'] ?? 'TZS' }} {{ number_format($payment['collectedAmount'] ?? $payment['amount'] ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Customer Info -->
            <div class="card p-6 space-y-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                    <i class="fas fa-user-circle"></i> Member Information
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold">Member Name</div>
                        <div class="font-bold text-primary-900 dark:text-white">{{ $payment['customer_name'] ?? $payment['payer_name'] ?? 'Mteja' }}</div>
                    </div>
                    @if(isset($payment['payer_name']) && strtolower($payment['payer_name']) !== strtolower($payment['customer_name'] ?? ''))
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold">Actual Payer</div>
                            <div class="font-semibold text-sm text-primary-700 dark:text-primary-300">{{ $payment['payer_name'] }}</div>
                        </div>
                    @endif
                    <div class="flex gap-6">
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold">Phone</div>
                            <div class="font-mono text-sm text-primary-800 dark:text-primary-200">{{ $payment['phone'] ?? 'N/A' }}</div>
                        </div>
                        @if(isset($payment['email']))
                            <div>
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Email</div>
                                <div class="text-sm text-primary-800 dark:text-primary-200">{{ $payment['email'] }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Transaction Info -->
            <div class="card p-6 space-y-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                    <i class="fas fa-receipt"></i> Transaction Details
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                        <span class="text-xs text-gray-400">Transaction ID</span>
                        <span class="text-xs font-mono font-bold text-primary-900 dark:text-white">{{ $payment['id'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                        <span class="text-xs text-gray-400">Date & Time</span>
                        <span class="text-xs font-bold text-primary-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($payment['createdAt'] ?? 'now')->format('M d, Y • H:i:s') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Payment Method</span>
                        <span class="text-xs font-bold text-primary-900 dark:text-white uppercase">
                            {{ $payment['channel'] ?? $payment['paymentMethod'] ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description Card -->
        <div class="card p-6">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> Purpose / Description
            </h3>
            <div class="p-4 bg-primary-50 dark:bg-dark-900 rounded-xl italic text-sm text-primary-800 dark:text-primary-300 border border-primary-100 dark:border-dark-border">
                {{ (!empty($payment['description']) && $payment['description'] !== 'N/A') ? $payment['description'] : ($payment['message'] ?? 'Malipo ya FEEDTAN') }}
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @if(in_array($payment['status'] ?? '', ['SUCCESS', 'SETTLED']))
                <a href="{{ route('payments.receipt', $payment['orderReference'] ?? '') }}" target="_blank"
                   class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-900/20 transition-all">
                    <i class="fas fa-download"></i> Receipt
                </a>
            @else
                <button onclick="window.location.reload()"
                        class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-900/20 transition-all">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            @endif
            
            <button onclick="alert('Sending SMS...')" class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 transition-all">
                <i class="fas fa-sms"></i> SMS
            </button>
            <button onclick="alert('Sending Email...')" class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 transition-all">
                <i class="fas fa-envelope"></i> Email
            </button>
            <button onclick="const c=prompt('Comment:'); if(c) alert('Saved')" class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 transition-all">
                <i class="fas fa-comment-alt"></i> Note
            </button>
        </div>
    @endif
</div>
@endsection
