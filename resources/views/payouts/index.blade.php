@extends('layouts.app')

@section('title', 'Payout History')

@section('content')
<div class="space-y-6" x-data="payoutHistoryDetails()" @keydown.escape.window="closeDetails()">
    <!-- Status Tabs -->
    <div class="card p-1">
        <div class="flex gap-1">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'SUCCESS', 'page' => 1]) }}"
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ ($activeStatus ?? request('status', 'SUCCESS')) !== 'FAILED' && ($activeStatus ?? request('status', 'SUCCESS')) !== 'PENDING' ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/20' : 'text-primary-600 hover:bg-primary-50 dark:text-primary-300 dark:hover:bg-primary-900/20' }}">
                <i class="fas fa-check-circle"></i>
                SUCCESS
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($activeStatus ?? request('status', 'SUCCESS')) !== 'FAILED' && ($activeStatus ?? request('status', 'SUCCESS')) !== 'PENDING' ? 'bg-white/20' : 'bg-primary-100 dark:bg-primary-900/40' }}">
                    {{ number_format($successCount ?? 0) }}
                </span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'PENDING', 'page' => 1]) }}"
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ ($activeStatus ?? request('status')) === 'PENDING' ? 'bg-amber-600 text-white shadow-lg shadow-amber-900/20' : 'text-amber-600 hover:bg-amber-50 dark:text-amber-300 dark:hover:bg-amber-900/20' }}">
                <i class="fas fa-clock"></i>
                PENDING
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($activeStatus ?? request('status')) === 'PENDING' ? 'bg-white/20' : 'bg-amber-100 dark:bg-amber-900/40' }}">
                    {{ number_format($pendingCount ?? 0) }}
                </span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'FAILED', 'page' => 1]) }}"
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ ($activeStatus ?? request('status')) === 'FAILED' ? 'bg-red-600 text-white shadow-lg shadow-red-900/20' : 'text-red-600 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-900/20' }}">
                <i class="fas fa-times-circle"></i>
                FAILED
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($activeStatus ?? request('status')) === 'FAILED' ? 'bg-white/20' : 'bg-red-100 dark:bg-red-900/40' }}">
                    {{ number_format($failedCount ?? 0) }}
                </span>
            </a>
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

    <!-- Filters Card -->
    <div x-data="{ showFilters: false }" class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-sm text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-filter text-primary-500"></i> Advanced Filters
            </h3>
            <button @click="showFilters = !showFilters" class="text-xs text-primary-600 font-bold hover:underline">
                <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
            </button>
        </div>
        
        <form x-show="showFilters" x-transition method="GET" action="{{ route('payouts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="filterForm">
            <input type="hidden" name="status" value="{{ $activeStatus ?? request('status', 'SUCCESS') }}">
            <div class="sm:col-span-2 lg:col-span-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Search</label>
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Reference, name, phone...">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Start Date</label>
                <input type="date" name="start_date" id="startDate" value="{{ request('start_date') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">End Date</label>
                <input type="date" name="end_date" id="endDate" value="{{ request('end_date') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-500 text-white py-2 rounded-lg text-xs font-bold transition-all">
                    Apply Filter
                </button>
                <a href="{{ route('payouts.index', ['status' => $activeStatus ?? 'SUCCESS']) }}" class="px-3 py-2 bg-gray-100 dark:bg-dark-border rounded-lg text-xs text-gray-600 dark:text-gray-300 hover:bg-gray-200 transition-all">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Export Card -->
    <div x-data="{ showExport: false }" class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-sm text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-file-export text-primary-500"></i> Export Report
            </h3>
            <button @click="showExport = !showExport" class="text-xs text-primary-600 font-bold hover:underline">
                <span x-text="showExport ? 'Hide Export Options' : 'Show Export Options'"></span>
            </button>
        </div>

        <form x-show="showExport" x-transition method="GET" action="{{ route('payouts.export.pdf') }}" class="space-y-4">
            <input type="hidden" name="status" value="{{ $activeStatus ?? request('status', 'SUCCESS') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" value="{{ request('end_date') }}">

            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Choose columns to include</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                    @foreach($availableColumns as $columnKey => $columnLabel)
                        <label class="flex items-center gap-2 text-xs bg-primary-50 dark:bg-dark-900 px-3 py-2 rounded-lg border border-primary-100 dark:border-dark-border">
                            <input type="checkbox" name="columns[]" value="{{ $columnKey }}"
                                   {{ in_array($columnKey, $selectedColumns ?? []) ? 'checked' : '' }}"
                                   class="rounded border-primary-200 text-primary-600 focus:ring-primary-500">
                            <span class="text-primary-700 dark:text-primary-300">{{ $columnLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-xs font-bold transition-all">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </button>
                <button type="submit" formaction="{{ route('payouts.export.excel') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-xs font-bold transition-all">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </button>
                <form action="{{ route('payouts.sync') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-bold transition-all">
                        <i class="fas fa-sync-alt me-1"></i> Sync
                    </button>
                </form>
                @if(auth()->user()->can_create_payouts)
                <a href="{{ route('payouts.create') }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white rounded-lg text-xs font-bold transition-all">
                    <i class="fas fa-plus me-1"></i> New Payout
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Transactions Table - Full system, fully responsive -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <p class="text-[11px] font-semibold text-primary-700 dark:text-primary-300 flex items-center gap-1.5"><i class="fas fa-hand-pointer text-primary-500"></i> Click any row to open right drawer — full details, copy IDs, bank & audit</p>
            <span class="text-[10px] text-primary-500 hidden lg:inline">Tip: Minimal table • Drawer has full system • Cards on mobile</span>
        </div>

        <!-- Desktop / Tablet Table — minimized to important columns only, rest in drawer -->
        <div class="hidden md:block overflow-x-auto">
            <table class="data-table min-w-[640px]">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Date</th>
                        <th>Reference</th>
                        <th>Recipient</th>
                        <th class="whitespace-nowrap">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($payouts as $payout)
                        @php
                            $callbackData = is_array($payout->callback_data ?? null) ? $payout->callback_data : [];
                            $beneficiary = $callbackData['beneficiary'] ?? [];

                            $recipientName = $payout->recipient_name
                                ?? $beneficiary['accountName']
                                ?? $payout->beneficiary_account_name
                                ?? 'N/A';

                            $actualRecipient = $payout->beneficiary_account_name
                                ?? $callbackData['accountName']
                                ?? $beneficiary['accountName']
                                ?? $recipientName;

                            $displayPhone = $payout->beneficiary_mobile
                                ?? $beneficiary['beneficiaryMobileNumber']
                                ?? $payout->recipient_phone
                                ?? 'N/A';

                            $displayDescription = $payout->resolvedDescription();

                            $status = strtoupper($payout->status ?? 'UNKNOWN');
                            $isSuccessful = in_array($status, ['SUCCESS', 'SETTLED']);

                            $createdAt = $payout->created_at ? \Illuminate\Support\Carbon::parse($payout->created_at) : null;
                            $updatedAt = $payout->updated_at ? \Illuminate\Support\Carbon::parse($payout->updated_at) : null;

                            $workflowStage = $payout->workflow_stage ?? 'UNKNOWN';
                            $workflowLabels = [
                                'INITIATION_OTP' => 'Waiting For Initiator OTP',
                                'APPROVAL_PENDING' => 'Waiting Approval & Authorization',
                                'PAYMENT_AUTHORIZATION_OTP' => 'Waiting Authorizer OTP',
                                'PROCESSING' => 'Processing',
                                'COMPLETED' => 'Completed',
                                'FAILED' => 'Failed',
                                'REJECTED' => 'Rejected',
                                'CANCELLED' => 'Cancelled',
                            ];
                            $workflowLabel = $workflowLabels[$workflowStage] ?? str_replace('_', ' ', $workflowStage);

                            $isPayoutInitiator = auth()->check() && (int) auth()->id() === (int) ($payout->initiated_by ?? 0);
                            $isAuthorizationRequester = auth()->check() && (int) auth()->id() === (int) ($payout->payment_otp_requested_by ?? 0);
                            $canApproveAndAuthorize = auth()->check()
                                && auth()->user()->can_create_payouts
                                && ($payout->workflow_stage ?? '') === 'APPROVAL_PENDING'
                                && !$isPayoutInitiator;
                            $canCancel = ($workflowStage === 'INITIATION_OTP' || $workflowStage === 'PENDING_VERIFICATION' || $status === 'PENDING_VERIFICATION') && $isPayoutInitiator;

                            $detailPayload = [
                                'reference' => $payout->order_reference,
                                'transaction_id' => $payout->transaction_id ?? $payout->clickpesa_payout_id ?? 'N/A',
                                'status' => $status,
                                'is_success' => $isSuccessful,
                                'amount' => (float) $payout->amount,
                                'currency' => $payout->currency ?? 'TZS',
                                'fee' => (float) ($payout->fee ?? 0),
                                'recipient_name' => $recipientName,
                                'account_name' => $actualRecipient,
                                'phone' => $displayPhone,
                                'email' => $payout->beneficiary_email,
                                'payout_type' => $payout->payout_type ?? 'N/A',
                                'channel' => $payout->channel ?? 'N/A',
                                'channel_provider' => $payout->channel_provider ?? 'N/A',
                                'transfer_type' => $payout->transfer_type ?? 'N/A',
                                'account_number' => $payout->beneficiary_account_number ?? $payout->bank_account_number ?? 'N/A',
                                'bank_name' => $payout->bank_name ?? 'N/A',
                                'bic' => $payout->bic ?? 'N/A',
                                'description' => $displayDescription,
                                'date' => $createdAt?->format('d M, Y'),
                                'time' => $createdAt?->format('H:i:s'),
                                'created_at' => $createdAt?->toIso8601String(),
                                'updated_at' => $updatedAt?->toIso8601String(),
                                'initiated_at' => $payout->initiated_at ? \Illuminate\Support\Carbon::parse($payout->initiated_at)->format('d M Y, H:i') : null,
                                'initiation_verified_at' => $payout->initiation_verified_at ? \Illuminate\Support\Carbon::parse($payout->initiation_verified_at)->format('d M Y, H:i') : null,
                                'approved_at' => $payout->approved_at ? \Illuminate\Support\Carbon::parse($payout->approved_at)->format('d M Y, H:i') : null,
                                'payment_authorized_at' => $payout->payment_authorized_at ? \Illuminate\Support\Carbon::parse($payout->payment_authorized_at)->format('d M Y, H:i') : null,
                                'rejected_at' => $payout->rejected_at ? \Illuminate\Support\Carbon::parse($payout->rejected_at)->format('d M Y, H:i') : null,
                                'rejection_reason' => $payout->rejection_reason,
                                'workflow_stage' => $workflowStage,
                                'workflow_label' => $workflowLabel,
                                'isInitiator' => $isPayoutInitiator,
                                'isAuthorizationRequester' => $isAuthorizationRequester,
                                'canApprove' => $canApproveAndAuthorize,
                                'canCancel' => $canCancel,
                                'initiated_by' => $payout->initiated_by,
                                'status_url' => route('payouts.status', ['orderReference' => $payout->order_reference]),
                                'receipt_url' => route('payouts.receipt', $payout->order_reference),
                                'verify_url' => route('payouts.verify-otp', $payout->order_reference),
                                'approve_url' => route('payouts.approve', $payout->order_reference),
                                'cancel_url' => route('payouts.cancel', $payout->order_reference),
                            ];
                        @endphp
                        <tr @click="openDetails(@js($detailPayload))" class="hover:bg-primary-50/70 dark:hover:bg-primary-900/10 transition-colors cursor-pointer group">
                            <td class="whitespace-nowrap py-3.5 px-4">
                                <div class="font-bold text-primary-900 dark:text-white text-xs">{{ $createdAt?->format('M d, Y') ?? 'N/A' }}</div>
                                <div class="text-[10px] text-primary-500">{{ $createdAt?->format('H:i:s') ?? '' }}</div>
                                @if($isSuccessful)
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[8px] font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">{{ $status }}</span>
                                @elseif(in_array($status, ['FAILED','CANCELLED','ERROR','REJECTED']))
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[8px] font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $status }}</span>
                                @else
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">{{ $status }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-1.5 max-w-[190px]">
                                    <span class="font-mono text-[11px] bg-primary-50 dark:bg-dark-900 px-2 py-1 rounded border border-primary-100 dark:border-dark-border text-primary-700 dark:text-primary-300 truncate" title="{{ $payout->order_reference }}">{{ \Illuminate\Support\Str::limit($payout->order_reference, 18) }}</span>
                                    <button type="button" @click.stop="copyText(@js($payout->order_reference), 'ref-{{ $payout->id }}')" class="shrink-0 w-7 h-7 rounded-lg bg-white border border-primary-100 dark:bg-dark-900 dark:border-dark-border text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all" title="Copy reference"><i class="fas text-[10px]" :class="copiedField === 'ref-{{ $payout->id }}' ? 'fa-check' : 'fa-copy'"></i></button>
                                </div>
                            </td>
                            <td class="py-3.5 px-3">
                                <div class="font-bold text-primary-900 dark:text-white text-xs truncate max-w-[180px]">{{ $recipientName }}</div>
                                <div class="text-[10px] text-primary-500 truncate max-w-[180px] font-mono">{{ $displayPhone }}</div>
                                <div class="text-[10px] text-primary-400 truncate max-w-[180px] hidden xl:block">{{ $actualRecipient }}</div>
                            </td>
                            <td class="whitespace-nowrap py-3.5 px-4">
                                <div class="font-bold text-primary-600 dark:text-primary-400 text-sm">{{ number_format((float)$payout->amount, 2) }}</div>
                                <div class="text-[10px] font-bold text-primary-500 uppercase">{{ $payout->currency ?? 'TZS' }}</div>
                                @if((float)($payout->fee ?? 0) > 0)
                                    <div class="text-[9px] text-red-500 font-bold">Fee {{ number_format((float)$payout->fee,2) }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-20">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-dark-900 flex items-center justify-center mb-4">
                                        <i class="fas fa-folder-open text-2xl text-primary-200"></i>
                                    </div>
                                    <h4 class="font-bold text-primary-900 dark:text-white">No Payouts Found</h4>
                                    <p class="text-xs text-primary-500">
                                        No {{ ($activeStatus ?? 'SUCCESS') === 'FAILED' ? 'failed' : (($activeStatus ?? 'SUCCESS') === 'PENDING' ? 'pending' : 'successful') }} payouts match your filters.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards - fully responsive fallback -->
        <div class="md:hidden divide-y divide-primary-50 dark:divide-dark-border">
            @forelse($payouts as $payout)
                @php
                    $callbackDataM = is_array($payout->callback_data ?? null) ? $payout->callback_data : [];
                    $beneficiaryM = $callbackDataM['beneficiary'] ?? [];
                    $recipientNameM = $payout->recipient_name ?? $beneficiaryM['accountName'] ?? $payout->beneficiary_account_name ?? 'N/A';
                    $displayPhoneM = $payout->beneficiary_mobile ?? $beneficiaryM['beneficiaryMobileNumber'] ?? $payout->recipient_phone ?? 'N/A';
                    $displayDescriptionM = $payout->resolvedDescription();
                    $statusM = strtoupper($payout->status ?? 'UNKNOWN');
                    $createdAtM = $payout->created_at ? \Illuminate\Support\Carbon::parse($payout->created_at) : null;
                    $workflowStageM = $payout->workflow_stage ?? 'UNKNOWN';
                    $workflowLabelsM = [
                        'INITIATION_OTP' => 'Waiting For Initiator OTP',
                        'APPROVAL_PENDING' => 'Waiting Approval',
                        'PAYMENT_AUTHORIZATION_OTP' => 'Waiting Authorizer OTP',
                        'PROCESSING' => 'Processing',
                        'COMPLETED' => 'Completed',
                        'FAILED' => 'Failed',
                        'REJECTED' => 'Rejected',
                        'CANCELLED' => 'Cancelled',
                    ];
                    $workflowLabelM = $workflowLabelsM[$workflowStageM] ?? str_replace('_', ' ', $workflowStageM);
                    $isPayoutInitiatorM = auth()->check() && (int) auth()->id() === (int) ($payout->initiated_by ?? 0);
                    $isAuthorizationRequesterM = auth()->check() && (int) auth()->id() === (int) ($payout->payment_otp_requested_by ?? 0);
                    $canApproveM = auth()->check() && auth()->user()->can_create_payouts && ($payout->workflow_stage ?? '') === 'APPROVAL_PENDING' && !$isPayoutInitiatorM;
                    $canCancelM = ($workflowStageM === 'INITIATION_OTP' || $workflowStageM === 'PENDING_VERIFICATION' || $statusM === 'PENDING_VERIFICATION') && $isPayoutInitiatorM;
                    $detailPayloadM = [
                        'reference' => $payout->order_reference,
                        'transaction_id' => $payout->transaction_id ?? $payout->clickpesa_payout_id ?? 'N/A',
                        'status' => $statusM,
                        'is_success' => in_array($statusM, ['SUCCESS','SETTLED']),
                        'amount' => (float) $payout->amount,
                        'currency' => $payout->currency ?? 'TZS',
                        'fee' => (float) ($payout->fee ?? 0),
                        'recipient_name' => $recipientNameM,
                        'account_name' => $payout->beneficiary_account_name ?? $callbackDataM['accountName'] ?? $beneficiaryM['accountName'] ?? $recipientNameM,
                        'phone' => $displayPhoneM,
                        'email' => $payout->beneficiary_email,
                        'payout_type' => $payout->payout_type ?? 'N/A',
                        'channel' => $payout->channel ?? 'N/A',
                        'channel_provider' => $payout->channel_provider ?? 'N/A',
                        'transfer_type' => $payout->transfer_type ?? 'N/A',
                        'account_number' => $payout->beneficiary_account_number ?? $payout->bank_account_number ?? 'N/A',
                        'bank_name' => $payout->bank_name ?? 'N/A',
                        'bic' => $payout->bic ?? 'N/A',
                        'description' => $displayDescriptionM,
                        'date' => $createdAtM?->format('d M, Y'),
                        'time' => $createdAtM?->format('H:i:s'),
                        'created_at' => $createdAtM?->toIso8601String(),
                        'updated_at' => $payout->updated_at ? \Illuminate\Support\Carbon::parse($payout->updated_at)->toIso8601String() : null,
                        'initiated_at' => $payout->initiated_at ? \Illuminate\Support\Carbon::parse($payout->initiated_at)->format('d M Y, H:i') : null,
                        'initiation_verified_at' => $payout->initiation_verified_at ? \Illuminate\Support\Carbon::parse($payout->initiation_verified_at)->format('d M Y, H:i') : null,
                        'approved_at' => $payout->approved_at ? \Illuminate\Support\Carbon::parse($payout->approved_at)->format('d M Y, H:i') : null,
                        'payment_authorized_at' => $payout->payment_authorized_at ? \Illuminate\Support\Carbon::parse($payout->payment_authorized_at)->format('d M Y, H:i') : null,
                        'rejected_at' => $payout->rejected_at ? \Illuminate\Support\Carbon::parse($payout->rejected_at)->format('d M Y, H:i') : null,
                        'rejection_reason' => $payout->rejection_reason,
                        'workflow_stage' => $workflowStageM,
                        'workflow_label' => $workflowLabelM,
                        'isInitiator' => $isPayoutInitiatorM,
                        'isAuthorizationRequester' => $isAuthorizationRequesterM,
                        'canApprove' => $canApproveM,
                        'canCancel' => $canCancelM,
                        'status_url' => route('payouts.status', ['orderReference' => $payout->order_reference]),
                        'receipt_url' => route('payouts.receipt', $payout->order_reference),
                        'verify_url' => route('payouts.verify-otp', $payout->order_reference),
                        'approve_url' => route('payouts.approve', $payout->order_reference),
                        'cancel_url' => route('payouts.cancel', $payout->order_reference),
                    ];
                    $badgeM = in_array($statusM, ['SUCCESS','SETTLED']) ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : (in_array($statusM, ['FAILED','CANCELLED','ERROR','REJECTED']) ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300');
                @endphp
                <div @click="openDetails(@js($detailPayloadM))" class="p-4 flex items-center gap-3 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 cursor-pointer active:bg-primary-50">
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-[10px] {{ in_array($statusM, ['SUCCESS','SETTLED']) ? 'bg-green-500' : (in_array($statusM, ['FAILED','CANCELLED','ERROR','REJECTED']) ? 'bg-red-500' : 'bg-amber-500') }}">
                        {{ substr($payout->currency ?? 'TZS', 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold text-primary-900 dark:text-white truncate">{{ \Illuminate\Support\Str::limit($payout->order_reference, 18) }}</span>
                            <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[8px] font-bold {{ $badgeM }}">{{ $statusM }}</span>
                        </div>
                        <div class="text-xs font-semibold text-primary-700 dark:text-primary-300 truncate">{{ $recipientNameM }}</div>
                        <div class="text-[11px] text-primary-500 truncate">{{ $displayDescriptionM }}</div>
                        <div class="text-[10px] text-primary-400">{{ $createdAtM?->format('M d, H:i') ?? '' }} • <span class="font-mono">{{ $displayPhoneM }}</span></div>
                    </div>
                    <div class="shrink-0 text-right">
                        <div class="font-bold text-xs text-primary-600 dark:text-primary-400">{{ number_format((float)$payout->amount,2) }} <span class="text-[9px]">{{ $payout->currency ?? 'TZS' }}</span></div>
                        @if((float)($payout->fee ?? 0) > 0)
                            <div class="text-[9px] text-red-500 font-bold">Fee {{ number_format((float)$payout->fee,2) }}</div>
                        @endif
                        <i class="fas fa-chevron-right text-[10px] text-primary-300 mt-1"></i>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-dark-900 flex items-center justify-center mx-auto mb-3"><i class="fas fa-folder-open text-xl text-primary-200"></i></div>
                    <p class="text-sm font-bold text-primary-900 dark:text-white">No Payouts</p>
                    <p class="text-xs text-primary-500">No payouts match your filters.</p>
                </div>
            @endforelse
        </div>
        
        @if($payouts->hasPages())
            <div class="p-4 bg-primary-50/30 dark:bg-dark-900/30 border-t border-primary-50 dark:border-dark-border">
                {{ $payouts->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Right Drawer - full system view -->
    <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex justify-end overflow-hidden" @keydown.escape.window="closeDetails()">
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeDetails()"></div>
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[520px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="shrink-0 flex items-start justify-between gap-4 px-5 py-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/50 dark:bg-dark-800/50">
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-black text-primary-900 dark:text-white flex items-center gap-2"><i class="fas fa-wallet text-primary-600"></i> Payout Details</h3>
                    <p class="text-[10px] text-primary-500 uppercase tracking-widest">Full system view • Right drawer</p>
                    <p x-show="selected" class="font-mono text-xs font-bold text-primary-700 dark:text-primary-300 mt-1 truncate" x-text="selected?.reference"></p>
                </div>
                <button type="button" @click="closeDetails()" class="shrink-0 w-8 h-8 rounded-lg bg-white dark:bg-dark-800 border border-primary-100 dark:border-dark-border text-primary-600 hover:bg-primary-50 flex items-center justify-center"><i class="fas fa-times text-xs"></i></button>
            </div>

            <!-- Drawer Body scroll -->
            <div class="flex-1 min-h-0 overflow-y-auto p-5 space-y-5 overscroll-contain" x-show="selected">
                <template x-if="selected">
                    <div class="space-y-5">
                        <!-- Reference + Amount + Status -->
                        <div class="flex flex-wrap items-start justify-between gap-3 p-4 rounded-xl bg-primary-50/70 dark:bg-dark-800 border border-primary-100 dark:border-dark-border">
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-bold uppercase text-primary-500">Reference</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <p class="font-mono font-bold text-primary-900 dark:text-white break-all text-sm" x-text="selected.reference"></p>
                                    <button type="button" @click="copyText(selected.reference, 'reference')" class="shrink-0 w-7 h-7 rounded-lg bg-white dark:bg-dark-900 border border-primary-100 dark:border-dark-border text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all" title="Copy reference"><i class="fas text-[10px]" :class="copiedField === 'reference' ? 'fa-check' : 'fa-copy'"></i></button>
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="statusBadgeClass(selected.status)" x-text="selected.status"></span>
                                    <span class="text-[10px] text-primary-500 font-bold" x-text="selected.workflow_label"></span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[10px] font-bold uppercase text-primary-500">Amount</p>
                                <p class="text-lg font-black text-primary-600 dark:text-primary-400"><span x-text="selected.currency"></span> <span x-text="formatAmount(selected.amount)"></span></p>
                                <p class="text-[10px] font-bold text-red-600 dark:text-red-400 mt-0.5" x-show="selected.fee && Number(selected.fee) > 0">Fee <span x-text="formatAmount(selected.fee)"></span></p>
                                <p class="text-[10px] text-primary-500"><span x-text="selected.date"></span> <span x-text="selected.time"></span></p>
                            </div>
                        </div>

                        <!-- Beneficiary + Transaction grid -->
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Beneficiary -->
                            <div class="space-y-3 p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-user-circle"></i> Beneficiary Information</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase text-primary-400">Account Name</p>
                                        <p class="font-bold text-sm text-primary-900 dark:text-white break-all" x-text="selected.account_name"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase text-primary-400">Recipient Name</p>
                                        <p class="font-semibold text-sm" x-text="selected.recipient_name"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase text-primary-400">Phone</p>
                                        <p class="font-mono text-sm flex items-center gap-1.5"><span x-text="selected.phone"></span><button type="button" @click="copyText(selected.phone,'phone')" class="w-6 h-6 rounded border border-primary-100 dark:border-dark-border flex items-center justify-center hover:bg-primary-50 dark:hover:bg-dark-900"><i class="fas fa-copy text-[9px]"></i></button></p>
                                    </div>
                                    <template x-if="selected.email && selected.email !== 'N/A'"><div><p class="text-[10px] font-bold uppercase text-primary-400">Email</p><p class="text-sm break-all" x-text="selected.email"></p></div></template>
                                </div>
                                <div class="pt-3 border-t border-primary-50 dark:border-dark-border grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <template x-if="selected.account_number && selected.account_number !== 'N/A'">
                                        <div><p class="text-[10px] font-bold uppercase text-primary-400">Account Number</p><div class="flex items-center gap-1.5"><p class="font-mono text-sm font-bold" x-text="selected.account_number"></p><button type="button" @click="copyText(selected.account_number,'account_number')" class="w-6 h-6 rounded border border-primary-100 flex items-center justify-center hover:bg-primary-50"><i class="fas fa-copy text-[9px]"></i></button></div></div>
                                    </template>
                                    <template x-if="selected.bank_name && selected.bank_name !== 'N/A'"><div><p class="text-[10px] font-bold uppercase text-primary-400">Bank</p><p class="text-sm font-semibold" x-text="selected.bank_name"></p></div></template>
                                    <template x-if="selected.bic && selected.bic !== 'N/A'"><div class="sm:col-span-2"><p class="text-[10px] font-bold uppercase text-primary-400">BIC / SWIFT</p><p class="font-mono text-sm" x-text="selected.bic"></p></div></template>
                                </div>
                            </div>

                            <!-- Transaction -->
                            <div class="space-y-3 p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-receipt"></i> Transaction Details</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-start gap-2 border-b border-primary-50 dark:border-dark-border pb-2">
                                        <span class="text-xs text-primary-500 shrink-0">Transaction ID</span>
                                        <div class="flex items-center gap-1.5 min-w-0 justify-end">
                                            <span class="font-mono text-xs font-bold break-all text-right" x-text="selected.transaction_id"></span>
                                            <button type="button" @click="copyText(selected.transaction_id,'transaction_id')" :disabled="!selected.transaction_id || selected.transaction_id==='N/A'" class="shrink-0 w-7 h-7 rounded-lg bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border flex items-center justify-center hover:bg-primary-600 hover:text-white disabled:opacity-40"><i class="fas text-[10px]" :class="copiedField==='transaction_id'?'fa-check':'fa-copy'"></i></button>
                                        </div>
                                    </div>
                                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2"><span class="text-xs text-primary-500">Payout Type</span><span class="text-xs font-bold" x-text="selected.payout_type"></span></div>
                                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2"><span class="text-xs text-primary-500">Channel</span><span class="text-xs font-bold" x-text="selected.channel"></span></div>
                                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2"><span class="text-xs text-primary-500">Provider</span><span class="text-xs font-bold" x-text="selected.channel_provider"></span></div>
                                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2"><span class="text-xs text-primary-500">Transfer Type</span><span class="text-xs font-bold" x-text="selected.transfer_type"></span></div>
                                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2"><span class="text-xs text-primary-500">Fee</span><span class="text-xs font-bold text-red-600 dark:text-red-400" x-text="formatAmount(selected.fee)"></span></div>
                                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2"><span class="text-xs text-primary-500">Date & Time</span><span class="text-xs font-bold"><span x-text="selected.date"></span> <span x-text="selected.time"></span></span></div>
                                    <div class="flex justify-between"><span class="text-xs text-primary-500">Workflow</span><span class="text-xs font-bold text-primary-700 dark:text-primary-300" x-text="selected.workflow_stage"></span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <p class="text-[10px] font-bold uppercase text-primary-500 mb-1">Purpose / Description</p>
                            <p class="text-sm bg-primary-50/70 dark:bg-dark-800 rounded-xl p-3 border border-primary-100 dark:border-dark-border whitespace-pre-wrap" x-text="selected.description"></p>
                        </div>

                        <!-- Audit -->
                        <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-primary-50/30 dark:bg-dark-800/50 space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-diagram-project"></i> Audit Trail</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="p-3 rounded-lg bg-white dark:bg-dark-900 border border-primary-100 dark:border-dark-border">
                                    <p class="text-[10px] uppercase font-bold tracking-widest text-primary-500">Initiated</p>
                                    <p class="text-xs font-bold text-primary-900 dark:text-white mt-1" x-text="selected.initiated_at || 'Pending'"></p>
                                    <p class="text-[10px] text-primary-500" x-text="selected.isInitiator ? 'You' : ''"></p>
                                </div>
                                <div class="p-3 rounded-lg bg-white dark:bg-dark-900 border border-primary-100 dark:border-dark-border">
                                    <p class="text-[10px] uppercase font-bold tracking-widest text-primary-500">Initiation Verified</p>
                                    <p class="text-xs font-bold text-primary-900 dark:text-white mt-1" x-text="selected.initiation_verified_at || 'Waiting'"></p>
                                </div>
                                <div class="p-3 rounded-lg bg-white dark:bg-dark-900 border border-primary-100 dark:border-dark-border">
                                    <p class="text-[10px] uppercase font-bold tracking-widest text-primary-500">Approved</p>
                                    <p class="text-xs font-bold text-primary-900 dark:text-white mt-1" x-text="selected.approved_at || 'Waiting'"></p>
                                </div>
                                <div class="p-3 rounded-lg bg-white dark:bg-dark-900 border border-primary-100 dark:border-dark-border">
                                    <p class="text-[10px] uppercase font-bold tracking-widest text-primary-500">Payment Authorized</p>
                                    <p class="text-xs font-bold text-primary-900 dark:text-white mt-1" x-text="selected.payment_authorized_at || 'Waiting'"></p>
                                </div>
                            </div>
                            <template x-if="selected.rejection_reason">
                                <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30">
                                    <p class="text-[10px] font-bold uppercase text-red-600">Rejection / Cancellation Reason</p>
                                    <p class="text-xs font-bold text-red-700 dark:text-red-300 mt-1" x-text="selected.rejected_at ? 'At ' + selected.rejected_at : ''"></p>
                                    <p class="text-xs text-red-700 dark:text-red-300 mt-1 whitespace-pre-wrap" x-text="selected.rejection_reason"></p>
                                </div>
                            </template>
                            <div class="text-[10px] text-primary-500 font-mono break-all">
                                <div>Initiated_by: <span x-text="selected.initiated_by ?? 'N/A'"></span></div>
                                <div>Created: <span x-text="selected.created_at ? new Date(selected.created_at).toLocaleString() : ''"></span></div>
                                <div>Updated: <span x-text="selected.updated_at ? new Date(selected.updated_at).toLocaleString() : ''"></span></div>
                            </div>
                        </div>

                        <!-- Receipt Preview (inline, no download) -->
                        <template x-if="selected?.is_success && selected?.receipt_url">
                            <div class="space-y-2">
                                <button type="button" @click="showReceipt = !showReceipt" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed transition-all text-xs font-bold" :class="showReceipt ? 'bg-primary-600 border-primary-600 text-white' : 'bg-white dark:bg-dark-800 border-primary-200 hover:bg-primary-50 text-primary-700 dark:text-primary-300'">
                                    <i class="fas" :class="showReceipt ? 'fa-eye-slash' : 'fa-file-pdf'"></i>
                                    <span x-text="showReceipt ? 'Hide Receipt Preview' : 'Preview Receipt (in-drawer)'"></span>
                                </button>
                                <div x-show="showReceipt" x-transition class="rounded-xl border border-primary-200 dark:border-dark-border overflow-hidden bg-white">
                                    <div class="px-3 py-2 bg-primary-50 dark:bg-dark-800 border-b border-primary-100 flex items-center justify-between">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-primary-600">Receipt Preview — no download</span>
                                        <span class="text-[10px] font-mono text-primary-500 truncate max-w-[160px]" x-text="selected.reference"></span>
                                    </div>
                                    <iframe :src="selected.receipt_url + (selected.receipt_url.includes('?') ? '&' : '?') + 'preview=1#toolbar=0&navpanes=0&scrollbar=0'" class="w-full h-[520px] bg-white" loading="lazy" title="Receipt preview"></iframe>
                                    <div class="p-2 bg-amber-50 border-t border-amber-100 text-[10px] text-amber-700 text-center">Preview only — printing allowed, download disabled by policy.</div>
                                </div>
                            </div>
                        </template>

                        <!-- Actions -->
                        <div class="flex flex-wrap gap-2 pt-2">
                            <a :href="selected.status_url" class="flex-1 min-w-[120px] px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold text-center transition-all"><i class="fas fa-external-link-alt me-1"></i> Full Payout Page</a>
                            <template x-if="selected.canApprove">
                                <form :action="selected.approve_url" method="POST" class="flex-1 min-w-[140px]">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all"><i class="fas fa-check me-1"></i> Approve & Authorize</button>
                                </form>
                            </template>
                            <template x-if="(selected.workflow_stage === 'INITIATION_OTP' && selected.isInitiator) || (selected.workflow_stage === 'PAYMENT_AUTHORIZATION_OTP' && selected.isAuthorizationRequester)">
                                <a :href="selected.verify_url" class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold text-center transition-all"><i class="fas fa-shield-alt me-1"></i> Verify OTP</a>
                            </template>
                            <template x-if="selected.canCancel">
                                <button type="button" @click="openCancel(selected.reference, selected.cancel_url); closeDetails()" class="px-4 py-2.5 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 border border-red-100 dark:border-red-900/30 text-xs font-bold hover:bg-red-600 hover:text-white transition-all"><i class="fas fa-ban me-1"></i> Cancel</button>
                            </template>
                            <button type="button" @click="closeDetails()" class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-dark-border text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">Close</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Cancel Payout Modal -->
    <div x-show="cancelOpen" x-cloak class="fixed inset-0 z-[60] overflow-y-auto p-4 flex items-start justify-center" @keydown.escape.window="closeCancel()">
        <div class="absolute inset-0 bg-black/50" @click="closeCancel()"></div>
        <div class="relative w-full max-w-lg card p-6 my-8 max-h-[90vh] overflow-y-auto animate-fade-in" @click.stop>
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-black text-primary-900 dark:text-white">Cancel Payout</h3>
                    <p class="text-[10px] text-primary-500 uppercase tracking-widest mt-1">Pending Verification Only</p>
                </div>
                <button type="button" @click="closeCancel()" class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-dark-900 text-primary-600 hover:bg-primary-100 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="cancelAction" method="POST" class="space-y-4">
                @csrf
                <div class="rounded-xl border border-red-100 bg-red-50/70 dark:border-red-900/30 dark:bg-red-900/10 p-4">
                    <p class="text-xs font-bold text-red-700 dark:text-red-300">
                        You are cancelling payout <span class="font-mono" x-text="cancelReference"></span>.
                    </p>
                    <p class="text-[11px] text-red-600 dark:text-red-300 mt-1">
                        This action is allowed only while the payout is still waiting for initiation verification.
                    </p>
                </div>

                <div>
                    <label for="cancellation_reason" class="block text-[10px] font-bold uppercase tracking-widest text-primary-500 mb-2">
                        Cancellation Reason
                    </label>
                    <textarea id="cancellation_reason"
                              name="cancellation_reason"
                              x-model="cancelReason"
                              rows="4"
                              required
                              maxlength="1000"
                              class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-red-500"
                              placeholder="Enter the reason for cancelling this payout"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="closeCancel()" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-dark-border text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-200 transition-all">
                        Keep Payout
                    </button>
                    <button type="submit"
                            :disabled="!cancelReason.trim()"
                            class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold transition-all">
                        Cancel Payout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; } .scrollbar-hide::-webkit-scrollbar{display:none} .scrollbar-hide{-ms-overflow-style:none;scrollbar-width:none}</style>
@endsection

@push('scripts')
<script>
function payoutHistoryDetails() {
    let debounceTimer = null;
    return {
        open: false,
        selected: null,
        showReceipt: false,
        cancelOpen: false,
        cancelReference: null,
        cancelAction: '',
        cancelReason: '',
        copiedField: null,
        copyTimeout: null,
        init() {
            const searchInput = document.getElementById('searchInput');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const filterForm = document.getElementById('filterForm');
            if (searchInput && filterForm) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => { filterForm.submit(); }, 500);
                });
            }
            if (startDate && filterForm) startDate.addEventListener('change', () => filterForm.submit());
            if (endDate && filterForm) endDate.addEventListener('change', () => filterForm.submit());
        },
        openDetails(payload) {
            this.selected = payload;
            this.showReceipt = false;
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        closeDetails() {
            this.open = false;
            this.showReceipt = false;
            setTimeout(() => { if (!this.open) this.selected = null; }, 300);
            if (!this.cancelOpen) {
                document.body.style.overflow = '';
            }
        },
        openCancel(reference, action) {
            this.cancelReference = reference;
            this.cancelAction = action;
            this.cancelReason = '';
            this.cancelOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeCancel() {
            this.cancelOpen = false;
            this.cancelReference = null;
            this.cancelAction = '';
            this.cancelReason = '';
            if (!this.open) {
                document.body.style.overflow = '';
            }
        },
        async copyText(text, field) {
            const value = String(text ?? '').trim();
            if (!value || value === 'N/A') return;
            try {
                await navigator.clipboard.writeText(value);
            } catch {
                const ta = document.createElement('textarea');
                ta.value = value;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
            this.copiedField = field;
            clearTimeout(this.copyTimeout);
            this.copyTimeout = setTimeout(() => { this.copiedField = null; }, 1800);
        },
        formatAmount(value) {
            return new Intl.NumberFormat('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value || 0));
        },
        statusBadgeClass(status) {
            const s = String(status || '').toUpperCase();
            if (['SUCCESS', 'SETTLED', 'COMPLETED'].includes(s)) return 'badge-green';
            if (['FAILED', 'ERROR', 'CANCELLED', 'REJECTED'].includes(s)) return 'badge-red';
            return 'badge-yellow';
        }
    };
}
</script>
@endpush
