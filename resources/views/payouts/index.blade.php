@extends('layouts.app')

@section('title', 'Payout History')

@section('content')
<div class="space-y-6" x-data="payoutHistoryDetails()">
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
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ ($activeStatus ?? request('status')) === 'PENDING' ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/20' : 'text-primary-600 hover:bg-primary-50 dark:text-primary-300 dark:hover:bg-primary-900/20' }}">
                <i class="fas fa-clock"></i>
                PENDING
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($activeStatus ?? request('status')) === 'PENDING' ? 'bg-white/20' : 'bg-primary-100 dark:bg-primary-900/40' }}">
                    {{ number_format($pendingCount ?? 0) }}
                </span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'FAILED', 'page' => 1]) }}"
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ ($activeStatus ?? request('status')) === 'FAILED' ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/20' : 'text-primary-600 hover:bg-primary-50 dark:text-primary-300 dark:hover:bg-primary-900/20' }}">
                <i class="fas fa-times-circle"></i>
                FAILED
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($activeStatus ?? request('status')) === 'FAILED' ? 'bg-white/20' : 'bg-primary-100 dark:bg-primary-900/40' }}">
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
            <h3 class="font-bold text-xs text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-filter text-primary-500"></i> Advanced Filters
            </h3>
            <button @click="showFilters = !showFilters" class="text-xs text-primary-600 font-bold hover:underline">
                <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
            </button>
        </div>
        
        <form x-show="showFilters" x-transition method="GET" action="{{ route('payouts.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="status" value="{{ $activeStatus ?? request('status', 'SUCCESS') }}">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Reference, name, phone...">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
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
            <h3 class="font-bold text-xs text-primary-900 dark:text-white flex items-center gap-2">
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
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
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

    <!-- Transactions Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30">
            <p class="text-[10px] text-primary-500">Click <i class="fas fa-eye"></i> on any row to preview full payout details</p>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Reference</th>
                        <th>Recipient</th>
                        <th>Amount</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
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

                            $detailPayload = [
                                'reference' => $payout->order_reference,
                                'transaction_id' => $payout->transaction_id ?? $payout->clickpesa_payout_id ?? 'N/A',
                                'status' => $status,
                                'amount' => (float) $payout->amount,
                                'currency' => $payout->currency,
                                'fee' => (float) $payout->fee,
                                'recipient_name' => $recipientName,
                                'account_name' => $actualRecipient,
                                'phone' => $displayPhone,
                                'email' => $payout->beneficiary_email,
                                'payout_type' => $payout->payout_type,
                                'channel' => $payout->channel,
                                'channel_provider' => $payout->channel_provider,
                                'transfer_type' => $payout->transfer_type,
                                'account_number' => $payout->beneficiary_account_number ?? $payout->bank_account_number,
                                'bic' => $payout->bic,
                                'description' => $displayDescription,
                                'date' => $createdAt?->format('d M, Y'),
                                'time' => $createdAt?->format('H:i:s'),
                                'created_at' => $createdAt?->toIso8601String(),
                                'updated_at' => $updatedAt?->toIso8601String(),
                                'status_url' => route('payouts.status', ['orderReference' => $payout->order_reference]),
                                'receipt_url' => route('payouts.receipt', $payout->order_reference),
                            ];
                        @endphp
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="whitespace-nowrap">
                                <div class="font-bold text-primary-900 dark:text-white">{{ $createdAt?->format('M d, Y') ?? 'N/A' }}</div>
                                <div class="text-[10px] text-primary-500">{{ $createdAt?->format('H:i:s') ?? '' }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5 max-w-[200px]">
                                    <span class="font-mono text-[11px] bg-primary-50 dark:bg-dark-900 px-2 py-1 rounded border border-primary-100 dark:border-dark-border text-primary-700 dark:text-primary-300 truncate" title="{{ $payout->order_reference }}">
                                        {{ $payout->order_reference }}
                                    </span>
                                    <button type="button"
                                            @click.stop="copyText(@js($payout->order_reference), 'ref-{{ $payout->id }}')"
                                            class="shrink-0 w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                            title="Copy reference">
                                        <i class="fas text-[10px]" :class="copiedField === 'ref-{{ $payout->id }}' ? 'fa-check' : 'fa-copy'"></i>
                                    </button>
                                </div>
                                @if($payout->transaction_id || $payout->clickpesa_payout_id)
                                    <div class="flex items-center gap-1.5 mt-1 max-w-[200px]">
                                        <span class="font-mono text-[9px] text-primary-500 truncate" title="{{ $payout->transaction_id ?? $payout->clickpesa_payout_id }}">TX: {{ $payout->transaction_id ?? $payout->clickpesa_payout_id }}</span>
                                        <button type="button"
                                                @click.stop="copyText(@js($payout->transaction_id ?? $payout->clickpesa_payout_id), 'tx-{{ $payout->id }}')"
                                                class="shrink-0 w-6 h-6 rounded-md bg-primary-50 dark:bg-primary-900/20 text-primary-500 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                                title="Copy transaction ID">
                                            <i class="fas text-[9px]" :class="copiedField === 'tx-{{ $payout->id }}' ? 'fa-check' : 'fa-copy'"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="font-bold text-primary-900 dark:text-white">{{ $recipientName }}</div>
                                <div class="text-[10px] text-primary-500">Account: {{ $actualRecipient }}</div>
                                <div class="text-[10px] text-primary-500 font-mono">{{ $displayPhone }}</div>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="font-bold text-primary-600 dark:text-primary-400">
                                    {{ number_format($payout->amount, 2) }}
                                </div>
                                <div class="text-[10px] text-primary-500 uppercase font-bold">{{ $payout->currency }}</div>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="font-bold text-primary-500 dark:text-primary-300">
                                    {{ number_format($payout->fee, 2) }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap">
                                @if($isSuccessful)
                                    <span class="badge badge-green text-[10px]">
                                        <i class="fas fa-check me-1"></i> {{ $status }}
                                    </span>
                                @elseif(in_array($status, ['FAILED', 'CANCELLED', 'ERROR']))
                                    <span class="badge badge-red text-[10px]">
                                        <i class="fas fa-times me-1"></i> {{ $status }}
                                    </span>
                                @else
                                    <span class="badge badge-yellow text-[10px]">
                                        <i class="fas fa-clock me-1"></i> {{ $status }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="flex gap-2 justify-center">
                                    <button type="button"
                                            @click="openDetails(@js($detailPayload))"
                                            class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                            title="Preview details">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                    <a href="{{ route('payouts.receipt', $payout->order_reference) }}" target="_blank"
                                       class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                       title="Download receipt">
                                        <i class="fas fa-file-invoice text-xs"></i>
                                    </a>
                                    <a href="{{ route('payouts.status', $payout->order_reference) }}"
                                       class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                       title="View details">
                                        <i class="fas fa-external-link-alt text-xs"></i>
                                    </a>
                                    @php
                                        $isPayoutInitiator = auth()->check() && (int) auth()->id() === (int) ($payout->initiated_by ?? 0);
                                        $canApproveAndAuthorize = auth()->check()
                                            && auth()->user()->can_create_payouts
                                            && in_array($payout->workflow_stage ?? '', ['APPROVAL_PENDING', 'PAYMENT_AUTHORIZATION_OTP'], true)
                                            && !$isPayoutInitiator;
                                    @endphp
                                    @if(($payout->workflow_stage ?? '') === 'INITIATION_OTP' && $isPayoutInitiator)
                                        <a href="{{ route('payouts.verify-otp', $payout->order_reference) }}"
                                           class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all"
                                           title="Verify initiation OTP">
                                            <i class="fas fa-shield-alt text-xs"></i>
                                        </a>
                                        @if(($payout->workflow_stage ?? '') === 'INITIATION_OTP' || ($payout->status ?? '') === 'PENDING_VERIFICATION')
                                            <button type="button"
                                                    @click="openCancel('{{ $payout->order_reference }}', '{{ route('payouts.cancel', $payout->order_reference) }}')"
                                                    class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all"
                                                    title="Cancel payout">
                                                <i class="fas fa-ban text-xs"></i>
                                            </button>
                                        @endif
                                    @elseif($canApproveAndAuthorize)
                                        <form action="{{ route('payouts.approve', $payout->order_reference) }}" method="POST" class="contents">
                                            @csrf
                                            <button type="submit"
                                                    class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all"
                                                    title="Approve and authorize payout">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-20">
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
        
        @if($payouts->hasPages())
            <div class="p-4 bg-primary-50/30 dark:bg-dark-900/30 border-t border-primary-50 dark:border-dark-border">
                {{ $payouts->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Payout detail modal -->
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeDetails()">
        <div class="absolute inset-0 bg-black/50" @click="closeDetails()"></div>
        <div class="relative w-full max-w-2xl card p-6 max-h-[90vh] overflow-y-auto animate-fade-in" @click.stop>
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-black text-primary-900 dark:text-white">Payout Details</h3>
                    <p class="text-[10px] text-primary-500 uppercase tracking-widest mt-1">Payout History Preview</p>
                </div>
                <button type="button" @click="closeDetails()" class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-dark-900 text-primary-600 hover:bg-primary-100 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <template x-if="selected">
                <div class="space-y-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl bg-primary-50/50 dark:bg-dark-900/50 border border-primary-100 dark:border-dark-border">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold uppercase text-primary-500">Reference</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <p class="font-mono font-bold text-primary-900 dark:text-white break-all" x-text="selected.reference"></p>
                                <button type="button"
                                        @click="copyText(selected.reference, 'reference')"
                                        class="shrink-0 w-8 h-8 rounded-lg bg-white dark:bg-dark-900 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white border border-primary-100 dark:border-dark-border transition-all"
                                        title="Copy reference">
                                    <i class="fas text-xs" :class="copiedField === 'reference' ? 'fa-check' : 'fa-copy'"></i>
                                </button>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold uppercase text-primary-500">Amount</p>
                            <p class="text-xl font-black text-primary-600 dark:text-primary-400">
                                <span x-text="selected.currency"></span>
                                <span x-text="formatAmount(selected.amount)"></span>
                            </p>
                            <span class="badge text-[10px] mt-1" :class="statusBadgeClass(selected.status)" x-text="selected.status"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                <i class="fas fa-user-circle"></i> Beneficiary Information
                            </h4>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Account Name</p>
                                <p class="font-bold text-primary-900 dark:text-white" x-text="selected.account_name"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Recipient Name</p>
                                <p class="font-semibold text-xs text-primary-800 dark:text-primary-200" x-text="selected.recipient_name"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Phone</p>
                                <p class="font-mono text-xs" x-text="selected.phone"></p>
                            </div>
                            <template x-if="selected.email">
                                <div>
                                    <p class="text-[10px] text-primary-500 uppercase font-bold">Email</p>
                                    <p class="text-xs" x-text="selected.email"></p>
                                </div>
                            </template>
                        </div>

                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                <i class="fas fa-receipt"></i> Transaction Details
                            </h4>
                            <div class="flex justify-between items-start gap-2 border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500 shrink-0">Transaction ID</span>
                                <div class="flex items-center gap-2 min-w-0 justify-end">
                                    <span class="font-mono text-xs font-bold break-all text-right" x-text="selected.transaction_id"></span>
                                    <button type="button"
                                            @click="copyText(selected.transaction_id, 'transaction_id')"
                                            :disabled="!selected.transaction_id || selected.transaction_id === 'N/A'"
                                            class="shrink-0 w-7 h-7 rounded-lg bg-primary-50 dark:bg-dark-900 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all disabled:opacity-40 disabled:pointer-events-none"
                                            title="Copy transaction ID">
                                        <i class="fas text-[10px]" :class="copiedField === 'transaction_id' ? 'fa-check' : 'fa-copy'"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Payout Type</span>
                                <span class="text-xs font-bold" x-text="selected.payout_type"></span>
                            </div>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Channel</span>
                                <span class="text-xs font-bold" x-text="selected.channel ?? selected.channel_provider ?? 'N/A'"></span>
                            </div>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Transfer Type</span>
                                <span class="text-xs font-bold" x-text="selected.transfer_type ?? 'N/A'"></span>
                            </div>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Fee</span>
                                <span class="text-xs font-bold text-red-600 dark:text-red-400" x-text="formatAmount(selected.fee ?? 0)"></span>
                            </div>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Date & Time</span>
                                <span class="text-xs font-bold"><span x-text="selected.date"></span> <span x-text="selected.time"></span></span>
                            </div>
                            <template x-if="selected.account_number">
                                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                    <span class="text-xs text-primary-500">Account Number</span>
                                    <span class="text-xs font-bold font-mono" x-text="selected.account_number"></span>
                                </div>
                            </template>
                            <template x-if="selected.bic">
                                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                    <span class="text-xs text-primary-500">BIC/SWIFT</span>
                                    <span class="text-xs font-bold font-mono" x-text="selected.bic"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] text-primary-500 uppercase font-bold mb-1">Purpose / Description</p>
                        <p class="text-xs text-primary-800 dark:text-primary-200 bg-primary-50/50 dark:bg-dark-900/50 rounded-xl p-3 border border-primary-100 dark:border-dark-border" x-text="selected.description"></p>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <a :href="selected.status_url" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                            <i class="fas fa-external-link-alt me-1"></i> Full Payout Page
                        </a>
                        <a :href="selected.receipt_url" target="_blank" class="px-4 py-2 rounded-xl bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-bold border border-primary-100 dark:border-dark-border hover:bg-primary-100 transition-all">
                            <i class="fas fa-file-pdf me-1"></i> Receipt PDF
                        </a>
                        <button type="button" @click="closeDetails()" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-dark-border text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-200 transition-all">
                            Close
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="cancelOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeCancel()">
        <div class="absolute inset-0 bg-black/50" @click="closeCancel()"></div>
        <div class="relative w-full max-w-lg card p-6 animate-fade-in" @click.stop>
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

<style>[x-cloak] { display: none !important; }</style>
@endsection

@push('scripts')
<script>
function payoutHistoryDetails() {
    return {
        open: false,
        selected: null,
        cancelOpen: false,
        cancelReference: null,
        cancelAction: '',
        cancelReason: '',
        copiedField: null,
        copyTimeout: null,
        openDetails(payload) {
            this.selected = payload;
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        openCancel(reference, action) {
            this.cancelReference = reference;
            this.cancelAction = action;
            this.cancelReason = '';
            this.cancelOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeDetails() {
            this.open = false;
            this.selected = null;
            if (!this.cancelOpen) {
                document.body.style.overflow = '';
            }
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
            this.copyTimeout = setTimeout(() => { this.copiedField = null; }, 2000);
        },
        formatAmount(value) {
            return new Intl.NumberFormat('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value || 0));
        },
        statusBadgeClass(status) {
            const s = String(status || '').toUpperCase();
            if (['SUCCESS', 'SETTLED'].includes(s)) return 'badge-green';
            if (['FAILED', 'ERROR', 'CANCELLED'].includes(s)) return 'badge-red';
            return 'badge-yellow';
        }
    };
}
</script>
@endpush
