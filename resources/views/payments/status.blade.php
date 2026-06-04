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
                        @php
                            $qrContent = "FEEDTAN DIGITAL PAYMENT SYSTEM\n" .
                                       "Order Reference: " . ($payment['orderReference'] ?? 'N/A') . "\n" .
                                       "Transaction ID: " . ($payment['id'] ?? $payment['transaction_id'] ?? 'N/A') . "\n" .
                                       "Amount: " . number_format($payment['collectedAmount'] ?? $payment['amount'] ?? 0, 2) . " " . ($payment['collectedCurrency'] ?? $payment['currency'] ?? 'TZS') . "\n" .
                                       "Status: " . ($payment['status'] ?? 'UNKNOWN') . "\n" .
                                       "Phone: " . ($payment['paymentPhoneNumber'] ?? $payment['phone'] ?? 'N/A') . "\n" .
                                       "Channel: " . ($payment['channel'] ?? $payment['payment_method'] ?? 'N/A') . "\n" .
                                       "Member: " . ($payment['customer_name'] ?? $payment['customer']['customerName'] ?? $payment['payer_name'] ?? 'N/A') . "\n" .
                                       "Payer: " . ($payment['payer_name'] ?? 'N/A') . "\n" .
                                       "Description: " . ($payment['description'] ?? 'N/A') . "\n" .
                                       "Date: " . (isset($payment['createdAt']) ? \Carbon\Carbon::parse($payment['createdAt'])->format('Y-m-d H:i:s') : 'N/A');
                        @endphp
                        {!! QrCode::size(100)->margin(1)->encoding('UTF-8')->errorCorrection('H')->generate($qrContent) !!}
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
                            {{ $payment['channel'] ?? $payment['paymentMethod'] ?? $payment['payment_method'] ?? 'USSD Push' }}
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
                {{ $payment['description'] ?? ($payment['message'] ?? 'Malipo ya FEEDTAN') }}
            </div>
        </div>

        <!-- Notes Card -->
        <div class="card p-6">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 mb-3 flex items-center gap-2">
                <i class="fas fa-comment-alt"></i> Notes
            </h3>
            
            @if(auth()->check())
                <form action="{{ route('payments.notes.add', $payment['orderReference']) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="flex gap-2">
                        <input type="text" name="content" required maxlength="1000" placeholder="Add a note..."
                               class="flex-1 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-900/20 transition-all">
                            Save
                        </button>
                    </div>
                </form>
            @endif
            
            @if(isset($payment['notes']) && count($payment['notes']) > 0)
                <div class="space-y-3">
                    @foreach($payment['notes'] as $note)
                        <div class="p-3 bg-gray-50 dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    {{ $note->user->name ?? 'Unknown User' }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $note->created_at->format('M d, Y h:i A') }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-800 dark:text-gray-200">{{ $note->content }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 italic">No notes yet.</p>
            @endif
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

            @if(auth()->check())
                @if(($payment['sms_sent'] ?? false) === false)
                    <form action="{{ route('payments.send-sms', $payment['orderReference'] ?? '') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 transition-all">
                            <i class="fas fa-sms"></i> Send SMS
                        </button>
                    </form>
                @else
                    <button disabled class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gray-100 dark:bg-dark-border border border-gray-200 dark:border-gray-700 text-gray-400 text-xs font-bold cursor-not-allowed">
                        <i class="fas fa-check"></i> SMS Sent
                    </button>
                @endif
            @else
                <button onclick="alert('Sending SMS...')" class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 transition-all">
                    <i class="fas fa-sms"></i> SMS
                </button>
            @endif
            
            @if(auth()->check())
                @if(($payment['email_sent'] ?? false) === false)
                    <form action="{{ route('payments.send-email', $payment['orderReference'] ?? '') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 transition-all">
                            <i class="fas fa-envelope"></i> Send Email
                        </button>
                    </form>
                @else
                    <button disabled class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gray-100 dark:bg-dark-border border border-gray-200 dark:border-gray-700 text-gray-400 text-xs font-bold cursor-not-allowed">
                        <i class="fas fa-check"></i> Email Sent
                    </button>
                @endif
            @else
                <button onclick="alert('Sending Email...')" class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 transition-all">
                    <i class="fas fa-envelope"></i> Email
                </button>
            @endif
        </div>

        <!-- SMS & Email Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card p-6 space-y-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                    <i class="fas fa-sms"></i> SMS Status
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        @if($payment['sms_sent'] ?? false)
                            <span class="badge badge-green text-xs">
                                <i class="fas fa-check me-1"></i> Sent
                            </span>
                        @elseif($payment['sms_error'] ?? false)
                            <span class="badge badge-red text-xs">
                                <i class="fas fa-times me-1"></i> Failed
                            </span>
                        @else
                            <span class="badge badge-yellow text-xs">
                                <i class="fas fa-clock me-1"></i> Not Sent
                            </span>
                        @endif
                    </div>
                    @if(($payment['sms_sent_at'] ?? false))
                        <div class="text-xs text-primary-600">
                            Sent at: {{ \Carbon\Carbon::parse($payment['sms_sent_at'])->format('d M, Y H:i:s') }}
                        </div>
                    @endif
                    @if(($payment['sms_error'] ?? false))
                        <div class="text-xs text-red-600 font-bold">
                            Error: {{ $payment['sms_error'] }}
                        </div>
                    @endif
                    @if(($payment['sms_message'] ?? false))
                        <div class="p-3 bg-primary-50 dark:bg-dark-900 rounded-lg border border-primary-100 dark:border-dark-border text-xs text-primary-700 dark:text-primary-300">
                            <strong>Message:</strong> {{ $payment['sms_message'] }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card p-6 space-y-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                    <i class="fas fa-envelope"></i> Email Status
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        @if($payment['email_sent'] ?? false)
                            <span class="badge badge-green text-xs">
                                <i class="fas fa-check me-1"></i> Sent
                            </span>
                        @elseif($payment['email_error'] ?? false)
                            <span class="badge badge-red text-xs">
                                <i class="fas fa-times me-1"></i> Failed
                            </span>
                        @else
                            <span class="badge badge-yellow text-xs">
                                <i class="fas fa-clock me-1"></i> Not Sent
                            </span>
                        @endif
                    </div>
                    @if(($payment['email_sent_at'] ?? false))
                        <div class="text-xs text-primary-600">
                            Sent at: {{ \Carbon\Carbon::parse($payment['email_sent_at'])->format('d M, Y H:i:s') }}
                        </div>
                    @endif
                    @if(($payment['email_error'] ?? false))
                        <div class="text-xs text-red-600 font-bold">
                            Error: {{ $payment['email_error'] }}
                        </div>
                    @endif
                    @if(($payment['email_message'] ?? false))
                        <div class="p-3 bg-primary-50 dark:bg-dark-900 rounded-lg border border-primary-100 dark:border-dark-border">
                            <h4 class="text-[10px] text-primary-500 uppercase font-bold mb-2">Email Content:</h4>
                            <div class="max-h-96 overflow-y-auto text-xs">
                                {!! $payment['email_message'] !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
