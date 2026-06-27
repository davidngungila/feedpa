@extends('layouts.app')

@section('title', 'Payout Status')

@section('content')
@php
    $payout = $payoutData;
    $isSuccessful = in_array($payout['status'] ?? '', ['SUCCESS', 'SETTLED']);
    $isFailed = in_array($payout['status'] ?? '', ['FAILED', 'CANCELLED', 'ERROR']);
    $workflowStage = $payout['workflow_stage'] ?? 'UNKNOWN';
    $displayDescription = $payout['display_description'] ?? $payout['description'] ?? 'N/A';
    
    $statusText = $payout['status'] ?? 'UNKNOWN';
    $statusIcon = 'fa-clock';
    $statusColor = 'badge-yellow';

    if ($isSuccessful) {
        $statusText = 'Confirmed';
        $statusIcon = 'fa-check-circle';
        $statusColor = 'badge-green';
    } elseif ($isFailed) {
        $statusText = 'Failed';
        $statusIcon = 'fa-times-circle';
        $statusColor = 'badge-red';
    }

    $workflowLabels = [
        'INITIATION_OTP' => 'Waiting For Initiation OTP',
        'APPROVAL_PENDING' => 'Waiting For Approval',
        'PAYMENT_AUTHORIZATION_OTP' => 'Waiting For Payment Authorization OTP',
        'PROCESSING' => 'Processing With Provider',
        'COMPLETED' => 'Completed',
        'FAILED' => 'Failed',
        'REJECTED' => 'Rejected',
    ];
    $workflowLabel = $workflowLabels[$workflowStage] ?? str_replace('_', ' ', $workflowStage);
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
                <a href="{{ route('payouts.index') }}" class="text-xs font-bold text-primary-600 hover:underline">
                    <i class="fas fa-arrow-left me-1"></i> Back to History
                </a>
            </div>
        </div>
    @elseif(!$payout)
        <div class="card p-6 text-center">
            <i class="fas fa-search text-4xl text-primary-200 mb-4"></i>
            <h4 class="font-bold text-primary-900 dark:text-white">Payout Not Found</h4>
            <p class="text-xs text-primary-500 mb-4">We couldn't find any payout with reference: {{ $orderReference }}</p>
            <a href="{{ route('payouts.index') }}" class="btn bg-primary-600 text-white px-6 py-2 rounded-lg text-xs font-bold">
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
                                       "Order Reference: " . ($payout['orderReference'] ?? $payout['order_reference'] ?? 'N/A') . "\n" .
                                       "Transaction ID: " . ($payout['id'] ?? $payout['transaction_id'] ?? $payout['clickpesa_payout_id'] ?? 'N/A') . "\n" .
                                       "Amount: " . number_format($payout['amount'] ?? 0, 2) . " " . ($payout['currency'] ?? 'TZS') . "\n" .
                                       "Fee: " . number_format($payout['fee'] ?? 0, 2) . " " . ($payout['currency'] ?? 'TZS') . "\n" .
                                       "Status: " . ($payout['status'] ?? 'UNKNOWN') . "\n" .
                                       "Beneficiary: " . ($payout['beneficiary']['accountName'] ?? $payout['recipient_name'] ?? $payout['beneficiary_account_name'] ?? 'N/A') . "\n" .
                                       "Phone: " . ($payout['beneficiary']['beneficiaryMobileNumber'] ?? $payout['recipient_phone'] ?? $payout['beneficiary_mobile'] ?? 'N/A') . "\n" .
                                       "Channel: " . ($payout['channel'] ?? $payout['payout_type'] ?? 'N/A') . "\n" .
                                       "Description: " . $displayDescription . "\n" .
                                       "Date: " . (isset($payout['createdAt']) ? \Carbon\Carbon::parse($payout['createdAt'])->format('Y-m-d H:i:s') : (isset($payout['created_at']) ? \Carbon\Carbon::parse($payout['created_at'])->format('Y-m-d H:i:s') : 'N/A'));
                        @endphp
                        {!! QrCode::size(100)->margin(1)->encoding('UTF-8')->errorCorrection('H')->generate($qrContent) !!}
                    </div>
                    <div>
                        <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Order Reference</div>
                        <div class="text-xl font-mono font-bold text-primary-900 dark:text-white">{{ $payout['orderReference'] ?? $payout['order_reference'] ?? 'N/A' }}</div>
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
                        {{ $payout['currency'] ?? 'TZS' }} {{ number_format($payout['amount'] ?? 0, 2) }}
                    </div>
                    @if(!empty($payout['fee']))
                    <div class="text-xs text-primary-500 mt-1">
                        Fee: {{ $payout['currency'] ?? 'TZS' }} {{ number_format($payout['fee'] ?? 0, 2) }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Beneficiary Info -->
            <div class="card p-6 space-y-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                    <i class="fas fa-user-circle"></i> Beneficiary Information
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold">Account Name</div>
                        <div class="font-bold text-primary-900 dark:text-white">{{ $payout['beneficiary']['accountName'] ?? $payout['beneficiary_account_name'] ?? $payout['recipient_name'] ?? 'N/A' }}</div>
                    </div>
                    <div class="flex gap-6">
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold">Phone</div>
                            <div class="font-mono text-sm text-primary-800 dark:text-primary-200">{{ $payout['beneficiary']['beneficiaryMobileNumber'] ?? $payout['beneficiary_mobile'] ?? $payout['recipient_phone'] ?? 'N/A' }}</div>
                        </div>
                        @if(isset($payout['beneficiary']['beneficiaryEmail']) || isset($payout['beneficiary_email']))
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold">Email</div>
                            <div class="text-sm text-primary-800 dark:text-primary-200">{{ $payout['beneficiary']['beneficiaryEmail'] ?? $payout['beneficiary_email'] ?? 'N/A' }}</div>
                        </div>
                        @endif
                    </div>
                    @if(isset($payout['beneficiary']['accountNumber']) || isset($payout['beneficiary_account_number']) || isset($payout['bank_account_number']))
                    <div class="pt-2 border-t border-primary-100 dark:border-dark-border">
                        <div class="text-[10px] text-gray-400 uppercase font-bold">Account Number</div>
                        <div class="font-mono text-sm text-primary-800 dark:text-primary-200">{{ $payout['beneficiary']['accountNumber'] ?? $payout['beneficiary_account_number'] ?? $payout['bank_account_number'] ?? 'N/A' }}</div>
                    </div>
                    @endif
                    @if(isset($payout['bic']))
                    <div class="pt-2 border-t border-primary-100 dark:border-dark-border">
                        <div class="text-[10px] text-gray-400 uppercase font-bold">BIC/SWIFT</div>
                        <div class="font-mono text-sm text-primary-800 dark:text-primary-200">{{ $payout['bic'] }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Transaction Info -->
            <div class="card p-6 space-y-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                    <i class="fas fa-receipt"></i> Payout Details
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                        <span class="text-xs text-gray-400">Transaction ID</span>
                        <span class="text-xs font-mono font-bold text-primary-900 dark:text-white">{{ $payout['id'] ?? $payout['transaction_id'] ?? $payout['clickpesa_payout_id'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                        <span class="text-xs text-gray-400">Date & Time</span>
                        <span class="text-xs font-bold text-primary-900 dark:text-white">
                            {{ isset($payout['createdAt']) ? \Carbon\Carbon::parse($payout['createdAt'])->format('M d, Y • H:i:s') : (isset($payout['created_at']) ? \Carbon\Carbon::parse($payout['created_at'])->format('M d, Y • H:i:s') : 'N/A') }}
                        </span>
                    </div>
                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                        <span class="text-xs text-gray-400">Payout Type</span>
                        <span class="text-xs font-bold text-primary-900 dark:text-white uppercase">
                            {{ $payout['payout_type'] ?? $payout['channel'] ?? 'N/A' }}
                        </span>
                    </div>
                    @if(isset($payout['channel']) && isset($payout['channelProvider']))
                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                        <span class="text-xs text-gray-400">Channel Provider</span>
                        <span class="text-xs font-bold text-primary-900 dark:text-white uppercase">
                            {{ $payout['channelProvider'] ?? $payout['channel_provider'] ?? 'N/A' }}
                        </span>
                    </div>
                    @endif
                    @if(isset($payout['transfer_type']) || isset($payout['transferType']))
                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                        <span class="text-xs text-gray-400">Transfer Type</span>
                        <span class="text-xs font-bold text-primary-900 dark:text-white uppercase">
                            {{ $payout['transferType'] ?? $payout['transfer_type'] ?? 'N/A' }}
                        </span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Updated At</span>
                        <span class="text-xs font-bold text-primary-900 dark:text-white">
                            {{ isset($payout['updatedAt']) ? \Carbon\Carbon::parse($payout['updatedAt'])->format('M d, Y • H:i:s') : (isset($payout['updated_at']) ? \Carbon\Carbon::parse($payout['updated_at'])->format('M d, Y • H:i:s') : 'N/A') }}
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
                {{ $displayDescription }}
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                    <i class="fas fa-diagram-project"></i> Workflow
                </h3>
                <span class="badge {{ $workflowStage === 'REJECTED' || $isFailed ? 'badge-red' : ($workflowStage === 'COMPLETED' || $isSuccessful ? 'badge-green' : 'badge-yellow') }}">
                    {{ $workflowLabel }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border">
                    <div class="text-[10px] uppercase font-bold tracking-widest text-primary-500">Initiated By</div>
                    <div class="mt-1 text-sm font-bold text-primary-900 dark:text-white">{{ $payout['initiator_name'] ?? 'N/A' }}</div>
                    <div class="text-xs text-primary-500 mt-1">{{ isset($payout['initiated_at']) && $payout['initiated_at'] ? \Carbon\Carbon::parse($payout['initiated_at'])->format('d M Y, H:i') : 'Pending' }}</div>
                </div>
                <div class="p-4 rounded-xl bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border">
                    <div class="text-[10px] uppercase font-bold tracking-widest text-primary-500">Initiation Verified By</div>
                    <div class="mt-1 text-sm font-bold text-primary-900 dark:text-white">{{ $payout['initiation_verifier_name'] ?? 'Pending' }}</div>
                    <div class="text-xs text-primary-500 mt-1">{{ isset($payout['initiation_verified_at']) && $payout['initiation_verified_at'] ? \Carbon\Carbon::parse($payout['initiation_verified_at'])->format('d M Y, H:i') : 'Waiting' }}</div>
                </div>
                <div class="p-4 rounded-xl bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border">
                    <div class="text-[10px] uppercase font-bold tracking-widest text-primary-500">Approved By</div>
                    <div class="mt-1 text-sm font-bold text-primary-900 dark:text-white">{{ $payout['approver_name'] ?? 'Pending' }}</div>
                    <div class="text-xs text-primary-500 mt-1">{{ isset($payout['approved_at']) && $payout['approved_at'] ? \Carbon\Carbon::parse($payout['approved_at'])->format('d M Y, H:i') : 'Waiting' }}</div>
                </div>
                <div class="p-4 rounded-xl bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border">
                    <div class="text-[10px] uppercase font-bold tracking-widest text-primary-500">Payment Authorized By</div>
                    <div class="mt-1 text-sm font-bold text-primary-900 dark:text-white">{{ $payout['payment_authorizer_name'] ?? 'Pending' }}</div>
                    <div class="text-xs text-primary-500 mt-1">{{ isset($payout['payment_authorized_at']) && $payout['payment_authorized_at'] ? \Carbon\Carbon::parse($payout['payment_authorized_at'])->format('d M Y, H:i') : 'Waiting' }}</div>
                </div>
            </div>

            @if(($payout['workflow_stage'] ?? '') === 'REJECTED')
                <div class="p-4 rounded-xl bg-red-50 border border-red-100 text-red-700">
                    <div class="text-xs font-bold">Rejected By {{ $payout['rejector_name'] ?? 'Unknown Officer' }}</div>
                    <div class="text-sm mt-2">{{ $payout['rejection_reason'] ?? 'No reason provided.' }}</div>
                </div>
            @endif
        </div>

        @if(auth()->check() && auth()->user()->can_create_payouts && !in_array($payout['workflow_stage'] ?? '', ['COMPLETED', 'FAILED', 'REJECTED']) && !$isSuccessful)
            <div class="card p-6">
                <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 mb-3 flex items-center gap-2">
                    <i class="fas fa-ban"></i> Reject Payout
                </h3>
                <form action="{{ route('payouts.reject', $orderReference) }}" method="POST" class="space-y-3">
                    @csrf
                    <textarea name="rejection_reason" rows="3" required maxlength="1000"
                              class="w-full rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-card px-4 py-3 text-sm text-primary-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-400"
                              placeholder="Enter the reason for rejecting this payout..."></textarea>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold shadow-lg shadow-red-900/20 transition-all">
                        <i class="fas fa-ban"></i> Reject Payout
                    </button>
                </form>
            </div>
        @endif

        <!-- Notes Card -->
        <div class="card p-6">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 mb-3 flex items-center gap-2">
                <i class="fas fa-comment-alt"></i> Notes
            </h3>
            
            @if(auth()->check())
                <form action="{{ route('payouts.notes.add', $orderReference) }}" method="POST" class="mb-4">
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
            
            @if(isset($notes) && count($notes) > 0)
                <div class="space-y-3">
                    @foreach($notes as $note)
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @if(auth()->check() && auth()->user()->can_create_payouts && in_array($payout['workflow_stage'] ?? '', ['INITIATION_OTP', 'PAYMENT_AUTHORIZATION_OTP']))
                <a href="{{ route('payouts.verify-otp', $orderReference) }}"
                   class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-lg shadow-purple-900/20 transition-all">
                    <i class="fas fa-shield-alt"></i>
                    {{ ($payout['workflow_stage'] ?? '') === 'PAYMENT_AUTHORIZATION_OTP' ? 'Authorize Payment' : 'Verify OTP' }}
                </a>
            @endif

            @if(auth()->check() && auth()->user()->can_create_payouts && ($payout['workflow_stage'] ?? '') === 'APPROVAL_PENDING')
                <form action="{{ route('payouts.approve', $orderReference) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-900/20 transition-all w-full">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </form>
            @endif

            @if(in_array($payout['status'] ?? '', ['SUCCESS', 'SETTLED']))
                <a href="{{ route('payouts.receipt', $orderReference) }}" target="_blank"
                   class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-900/20 transition-all">
                    <i class="fas fa-download"></i> Receipt
                </a>
            @else
                <form action="{{ route('payouts.refresh', $orderReference) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-900/20 transition-all w-full">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </form>
            @endif
            
            <a href="{{ route('payouts.index') }}"
               class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 transition-all">
                <i class="fas fa-arrow-left"></i> History
            </a>
            
            @if(auth()->check())
                <form action="{{ route('payouts.sync') }}" method="POST" class="contents">
                    @csrf
                    <button type="submit"
                            class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 transition-all">
                        <i class="fas fa-sync"></i> Sync All
                    </button>
                </form>
                <a href="{{ route('payouts.create') }}"
                   class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-900/20 transition-all">
                    <i class="fas fa-plus"></i> New Payout
                </a>
            @endif
        </div>
    @endif
</div>
@endsection
