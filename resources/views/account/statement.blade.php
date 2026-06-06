@extends('layouts.app')

@section('title', 'Account Statement')

@section('content')
<div class="space-y-6" x-data="statementDetails()">
    <!-- Source Tabs -->
    <div class="card p-1">
        <div class="flex gap-1">
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'database', 'page' => 1]) }}"
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ $activeTab === 'database' ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/20' : 'text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/30' }}">
                <i class="fas fa-database"></i>
                Internal Database
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ $activeTab === 'database' ? 'bg-white/20' : 'bg-primary-100 dark:bg-primary-900/40' }}">
                    {{ number_format($dbCount) }}
                </span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'api', 'page' => 1]) }}"
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ $activeTab === 'api' ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/20' : 'text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/30' }}">
                <i class="fas fa-cloud"></i>
                ClickPesa API
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ $activeTab === 'api' ? 'bg-white/20' : 'bg-primary-100 dark:bg-primary-900/40' }}">
                    {{ number_format($apiCount) }}
                </span>
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div x-data="{ showFilters: true }" class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-sm text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-filter text-primary-500"></i> Advanced Filters
            </h3>
            <button @click="showFilters = !showFilters" class="text-xs text-primary-600 font-bold hover:underline">
                <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
            </button>
        </div>
        
        <form x-show="showFilters" x-transition method="GET" action="{{ route('account.statement') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4" id="filterForm">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Search</label>
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Reference, name, phone...">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Status</label>
                <select name="status" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="settled" {{ $statusFilter === 'settled' ? 'selected' : '' }}>Settled</option>
                    <option value="failed" {{ $statusFilter === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Currency</label>
                <select name="currency" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
                    <option value="TZS" {{ $currency === 'TZS' ? 'selected' : '' }}>TZS</option>
                    <option value="USD" {{ $currency === 'USD' ? 'selected' : '' }}>USD</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Start Date</label>
                <input type="date" name="start_date" id="startDate" value="{{ request('start_date') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-500 text-white py-2 rounded-lg text-xs font-bold transition-all">
                    Apply Filter
                </button>
                <a href="{{ route('account.statement', ['tab' => $activeTab]) }}" class="px-3 py-2 bg-gray-100 dark:bg-dark-border rounded-lg text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-200 transition-all">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    @if($error)
        <div class="card p-4 border-l-4 border-l-red-500 bg-red-50/60 dark:bg-red-900/10">
            <p class="text-xs font-bold text-red-700 dark:text-red-300"><i class="fas fa-circle-exclamation me-1"></i> {{ $error }}</p>
        </div>
    @endif

    <!-- Transactions Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30">
            <p class="text-[10px] text-primary-500">Click <i class="fas fa-eye"></i> on any row to preview full details</p>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Reference</th>
                        <th>Member Name</th>
                        <th>Purpose / Description</th>
                        <th>Amount</th>
                        @if($activeTab === 'database')
                            <th>SMS Status</th>
                            <th>Email Status</th>
                        @else
                            <th>Sync Status</th>
                        @endif
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($displayTransactions as $transaction)
                        @php
                            $t = (array) $transaction;
                            $customer = is_array($t['customer'] ?? null) ? $t['customer'] : [];

                            $status = strtoupper($t['status'] ?? 'UNKNOWN');
                            $isSettled = in_array($status, ['SETTLED', 'SUCCESS', 'COMPLETED']);
                            $isFailed = in_array($status, ['FAILED', 'ERROR', 'CANCELLED']);

                            $rawDate = $t['created_at'] ?? $t['createdAt'] ?? $t['date'] ?? null;
                            $createdAt = $rawDate ? \Illuminate\Support\Carbon::parse($rawDate) : null;
                            $formattedDate = $createdAt?->format('M d, Y') ?? 'N/A';
                            $formattedTime = $createdAt?->format('H:i:s') ?? '';

                            $reference = $t['order_reference'] ?? $t['orderReference'] ?? $t['reference'] ?? 'N/A';
                            $amount = (float) ($t['amount'] ?? $t['collectedAmount'] ?? 0);
                            $currency = $t['currency'] ?? $t['collectedCurrency'] ?? 'TZS';
                            $entry = $t['entry'] ?? 'CREDIT';

                            $memberName = $t['customer_name'] ?? $customer['customerName'] ?? $t['customerName'] ?? $t['payer_name'] ?? 'N/A';
                            $actualPayer = $t['payer_name'] ?? $customer['customerName'] ?? $memberName;
                            $customerPhone = $t['phone'] ?? $t['paymentPhoneNumber'] ?? ($customer['customerPhoneNumber'] ?? 'N/A');
                            $paymentMethod = $t['payment_method'] ?? $t['channel'] ?? $t['paymentMethod'] ?? 'N/A';
                            $transactionId = $t['transaction_id'] ?? $t['id'] ?? 'N/A';
                            $description = trim((string) ($t['description'] ?? $t['narrative'] ?? ''));
                            if ($description === '' || strtoupper($description) === 'N/A') {
                                $description = 'No description';
                            }
                            $runningBalance = (float) ($t['running_balance'] ?? 0);

                            $detailPayload = [
                                'reference' => $reference,
                                'transaction_id' => $transactionId,
                                'status' => $status,
                                'isSettled' => $isSettled,
                                'amount' => $amount,
                                'currency' => $currency,
                                'entry' => $entry,
                                'member_name' => $memberName,
                                'payer_name' => $actualPayer,
                                'phone' => $customerPhone,
                                'email' => $t['email'] ?? $customer['customerEmail'] ?? null,
                                'payment_method' => $paymentMethod,
                                'description' => $description,
                                'source' => $t['source'] ?? ($activeTab === 'api' ? 'API' : 'DATABASE'),
                                'type' => $t['type'] ?? 'payment',
                                'is_synced' => (bool) ($t['is_synced'] ?? false),
                                'date' => $formattedDate,
                                'time' => $formattedTime,
                                'created_at' => $rawDate,
                                'updated_at' => $t['updated_at'] ?? $t['updatedAt'] ?? null,
                                'status_url' => ($reference !== 'N/A' && ($t['source'] ?? '') === 'DATABASE' && $entry === 'CREDIT')
                                    ? route('payments.status', ['reference' => $reference])
                                    : null,
                                'sms_sent' => (bool) ($t['sms_sent'] ?? false),
                                'sms_sent_at' => $t['sms_sent_at'] ?? null,
                                'sms_message' => $t['sms_message'] ?? null,
                                'sms_error' => $t['sms_error'] ?? null,
                                'email_sent' => (bool) ($t['email_sent'] ?? false),
                                'email_sent_at' => $t['email_sent_at'] ?? null,
                                'email_error' => $t['email_error'] ?? null,
                            ];
                        @endphp
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="whitespace-nowrap">
                                <div class="font-bold text-primary-900 dark:text-white">{{ $formattedDate }}</div>
                                <div class="text-[10px] text-primary-500">{{ $formattedTime }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5 max-w-[200px]">
                                    <span class="font-mono text-[11px] bg-primary-50 dark:bg-dark-900 px-2 py-1 rounded border border-primary-100 dark:border-dark-border text-primary-700 dark:text-primary-300 truncate" title="{{ $reference }}">
                                        {{ $reference }}
                                    </span>
                                    <button type="button"
                                            @click.stop="copyText(@js($reference), 'ref-{{ $loop->index }}')"
                                            class="shrink-0 w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                            title="Copy reference">
                                        <i class="fas text-[10px]" :class="copiedField === 'ref-{{ $loop->index }}' ? 'fa-check' : 'fa-copy'"></i>
                                    </button>
                                </div>
                                @if($transactionId !== 'N/A')
                                    <div class="flex items-center gap-1.5 mt-1 max-w-[200px]">
                                        <span class="font-mono text-[9px] text-primary-500 truncate" title="{{ $transactionId }}">TX: {{ $transactionId }}</span>
                                        <button type="button"
                                                @click.stop="copyText(@js($transactionId), 'tx-{{ $loop->index }}')"
                                                class="shrink-0 w-6 h-6 rounded-md bg-primary-50 dark:bg-primary-900/20 text-primary-500 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                                title="Copy transaction ID">
                                            <i class="fas text-[9px]" :class="copiedField === 'tx-{{ $loop->index }}' ? 'fa-check' : 'fa-copy'"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="font-bold text-primary-900 dark:text-white">{{ $memberName }}</div>
                                <div class="text-[10px] text-primary-500">Payer: {{ $actualPayer }}</div>
                                <div class="text-[10px] text-primary-500 font-mono">{{ $customerPhone }}</div>
                            </td>
                            <td>
                                <div class="text-xs text-primary-700 dark:text-primary-400 max-w-[220px] truncate" title="{{ $description }}">
                                    {{ $description }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap">
                                @if($entry === 'DEBIT')
                                    <div class="font-bold text-red-600 dark:text-red-400">
                                        - {{ number_format($amount, 2) }}
                                    </div>
                                @else
                                    <div class="font-bold text-green-600 dark:text-green-400">
                                        + {{ number_format($amount, 2) }}
                                    </div>
                                @endif
                                <div class="text-[10px] text-primary-500 uppercase font-bold">{{ $currency }}</div>
                            </td>
                            @if($activeTab === 'database')
                                @if($entry === 'CREDIT')
                                    <td class="whitespace-nowrap">
                                        @if($t['sms_sent'] ?? false)
                                            <span class="badge badge-green text-[10px]">
                                                <i class="fas fa-check me-1"></i> Sent
                                            </span>
                                            @if($t['sms_sent_at'])
                                                <div class="text-[9px] text-primary-500 mt-1">{{ \Illuminate\Support\Carbon::parse($t['sms_sent_at'])->format('d M, H:i') }}</div>
                                            @endif
                                        @elseif($t['sms_error'] ?? false)
                                            <span class="badge badge-red text-[10px]">
                                                <i class="fas fa-times me-1"></i> Failed
                                            </span>
                                        @elseif($isSettled)
                                            <span class="badge badge-yellow text-[10px]">Not Sent</span>
                                        @else
                                            <span class="text-[10px] text-primary-400">—</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap">
                                        @if($t['email_sent'] ?? false)
                                            <span class="badge badge-green text-[10px]">
                                                <i class="fas fa-check me-1"></i> Sent
                                            </span>
                                            @if($t['email_sent_at'])
                                                <div class="text-[9px] text-primary-500 mt-1">{{ \Illuminate\Support\Carbon::parse($t['email_sent_at'])->format('d M, H:i') }}</div>
                                            @endif
                                        @elseif($t['email_error'] ?? false)
                                            <span class="badge badge-red text-[10px]">
                                                <i class="fas fa-times me-1"></i> Failed
                                            </span>
                                        @elseif($isSettled)
                                            <span class="badge badge-yellow text-[10px]">Not Sent</span>
                                        @else
                                            <span class="text-[10px] text-primary-400">—</span>
                                        @endif
                                    </td>
                                @else
                                    <td class="whitespace-nowrap text-center">
                                        <span class="text-[10px] text-primary-400">—</span>
                                    </td>
                                    <td class="whitespace-nowrap text-center">
                                        <span class="text-[10px] text-primary-400">—</span>
                                    </td>
                                @endif
                            @else
                                <td class="whitespace-nowrap">
                                    @if($t['is_synced'] ?? false)
                                        <span class="badge badge-green text-[10px]">
                                            <i class="fas fa-check-double me-1"></i> Synced
                                        </span>
                                    @else
                                        <span class="badge badge-yellow text-[10px]">
                                            <i class="fas fa-cloud-download-alt me-1"></i> Not Synced
                                        </span>
                                    @endif
                                </td>
                            @endif
                            <td>
                                <div class="flex gap-2 justify-center">
                                    <button type="button"
                                            @click="openDetails(@js($detailPayload))"
                                            class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                            title="Preview details">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                    @if($activeTab === 'api')
                                        <button type="button"
                                                @click="fetchTransaction('{{ $reference }}')"
                                                class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-800/30 text-gray-600 flex items-center justify-center hover:bg-gray-200 hover:text-gray-800 transition-all"
                                                title="Fetch latest from API">
                                            <i class="fas fa-sync text-xs"></i>
                                        </button>
                                        @if($isSettled && !($t['is_synced'] ?? false))
                                            <button type="button"
                                                    @click="syncTransaction('{{ $reference }}')"
                                                    class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 flex items-center justify-center hover:bg-green-600 hover:text-white transition-all"
                                                    title="Sync to Database">
                                                <i class="fas fa-cloud-upload-alt text-xs"></i>
                                            </button>
                                        @endif
                                    @endif
                                    @if($activeTab === 'database' && $isSettled && $detailPayload['status_url'])
                                        <a href="{{ $detailPayload['status_url'] }}"
                                           class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                           title="Full Payment Page">
                                            <i class="fas fa-external-link-alt text-xs"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activeTab === 'database' ? 7 : 7 }}" class="text-center py-20">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-dark-900 flex items-center justify-center mb-4">
                                        <i class="fas fa-search text-2xl text-primary-200"></i>
                                    </div>
                                    <h4 class="font-bold text-primary-900 dark:text-white">No records found</h4>
                                    <p class="text-xs text-primary-500">Adjust your filters or dates to find transactions.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transaction Detail Modal -->
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeDetails()">
        <div class="absolute inset-0 bg-black/50" @click="closeDetails()"></div>
        <div class="relative w-full max-w-2xl card p-6 max-h-[90vh] overflow-y-auto animate-fade-in" @click.stop>
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-black text-primary-900 dark:text-white">Transaction Details</h3>
                    <p class="text-[10px] text-primary-500 uppercase tracking-widest mt-1" x-text="selected?.source || 'Statement'"></p>
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
                                <i class="fas fa-user-circle"></i> Member Information
                            </h4>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Member Name</p>
                                <p class="font-bold text-primary-900 dark:text-white" x-text="selected.member_name"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Actual Payer</p>
                                <p class="font-semibold text-sm text-primary-800 dark:text-primary-200" x-text="selected.payer_name"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Phone</p>
                                <p class="font-mono text-sm" x-text="selected.phone"></p>
                            </div>
                            <template x-if="selected.email">
                                <div>
                                    <p class="text-[10px] text-primary-500 uppercase font-bold">Email</p>
                                    <p class="text-sm" x-text="selected.email"></p>
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
                                            class="shrink-0 w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-900 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all disabled:opacity-40 disabled:pointer-events-none"
                                            title="Copy transaction ID">
                                        <i class="fas text-[10px]" :class="copiedField === 'transaction_id' ? 'fa-check' : 'fa-copy'"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Payment Method</span>
                                <span class="text-xs font-bold" x-text="selected.payment_method"></span>
                            </div>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Date & Time</span>
                                <span class="text-xs font-bold"><span x-text="selected.date"></span> <span x-text="selected.time"></span></span>
                            </div>
                            <template x-if="selected.is_synced">
                                <div class="flex justify-between">
                                    <span class="text-xs text-primary-500">Database Sync</span>
                                    <span class="text-xs font-bold text-primary-600">Synced</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] text-primary-500 uppercase font-bold mb-1">Purpose / Description</p>
                        <p class="text-sm text-primary-800 dark:text-primary-200 bg-primary-50/50 dark:bg-dark-900/50 rounded-xl p-3 border border-primary-100 dark:border-dark-border" x-text="selected.description"></p>
                    </div>

                    <template x-if="selected.source === 'DATABASE'">
                        <div class="space-y-4">
                            <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30 space-y-3">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                    <i class="fas fa-sms"></i> SMS Notification
                                </h4>
                                <div class="flex items-center gap-2">
                                    <template x-if="selected.sms_sent">
                                        <span class="badge badge-green text-[10px]"><i class="fas fa-check me-1"></i> SMS Sent</span>
                                    </template>
                                    <template x-if="!selected.sms_sent && selected.sms_error">
                                        <span class="badge badge-red text-[10px]"><i class="fas fa-times me-1"></i> SMS Failed</span>
                                    </template>
                                    <template x-if="!selected.sms_sent && !selected.sms_error">
                                        <span class="badge badge-yellow text-[10px]">Not Sent</span>
                                    </template>
                                    <template x-if="selected.sms_sent_at">
                                        <span class="text-[10px] text-primary-500" x-text="'at ' + selected.sms_sent_at"></span>
                                    </template>
                                </div>
                                <template x-if="selected.sms_error">
                                    <p class="text-xs text-red-600 dark:text-red-400 font-bold" x-text="'Error: ' + selected.sms_error"></p>
                                </template>
                                <template x-if="selected.sms_message">
                                    <div>
                                        <p class="text-[10px] text-primary-500 uppercase font-bold mb-1">Message Sent</p>
                                        <p class="text-xs text-primary-800 dark:text-primary-200 bg-white dark:bg-dark-900 rounded-lg p-3 border border-primary-100 dark:border-dark-border whitespace-pre-wrap max-h-40 overflow-y-auto" x-text="selected.sms_message"></p>
                                    </div>
                                </template>
                            </div>

                            <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30 space-y-3">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                    <i class="fas fa-envelope"></i> Email Notification
                                </h4>
                                <div class="flex items-center gap-2">
                                    <template x-if="selected.email_sent">
                                        <span class="badge badge-green text-[10px]"><i class="fas fa-check me-1"></i> Email Sent</span>
                                    </template>
                                    <template x-if="!selected.email_sent && selected.email_error">
                                        <span class="badge badge-red text-[10px]"><i class="fas fa-times me-1"></i> Email Failed</span>
                                    </template>
                                    <template x-if="!selected.email_sent && !selected.email_error">
                                        <span class="badge badge-yellow text-[10px]">Not Sent</span>
                                    </template>
                                    <template x-if="selected.email_sent_at">
                                        <span class="text-[10px] text-primary-500" x-text="'at ' + selected.email_sent_at"></span>
                                    </template>
                                </div>
                                <template x-if="selected.email_error">
                                    <p class="text-xs text-red-600 dark:text-red-400 font-bold" x-text="'Error: ' + selected.email_error"></p>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <template x-if="selected.status_url">
                            <a :href="selected.status_url" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                                <i class="fas fa-external-link-alt me-1"></i> Full Payment Page
                            </a>
                        </template>
                        <button type="button" @click="closeDetails()" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-dark-border text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-200 transition-all">
                            Close
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection

@push('scripts')
<script>
function statementDetails() {
    let debounceTimer = null;
    return {
        open: false,
        selected: null,
        copiedField: null,
        copyTimeout: null,
        init() {
            // Add real-time search
            const searchInput = document.getElementById('searchInput');
            const startDate = document.getElementById('startDate');
            const filterForm = document.getElementById('filterForm');

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        filterForm.submit();
                    }, 500);
                });
            }

            if (startDate) {
                startDate.addEventListener('change', () => filterForm.submit());
            }
        },
        openDetails(payload) {
            this.selected = payload;
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        closeDetails() {
            this.open = false;
            this.selected = null;
            document.body.style.overflow = '';
        },
        async copyText(text, field) {
            const value = String(text || '').trim();
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
            if (['SETTLED', 'SUCCESS'].includes(s)) return 'badge-green';
            if (['FAILED', 'ERROR', 'CANCELLED'].includes(s)) return 'badge-red';
            return 'badge-yellow';
        },
        async syncTransaction(orderReference) {
            if (confirm('Are you sure you want to sync this transaction to the database?')) {
                try {
                    const response = await fetch('{{ route('account.transaction.sync') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order_reference: orderReference })
                    });
                    const data = await response.json();
                    if (data.success) {
                        alert('Transaction synced successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || data.error));
                    }
                } catch (e) {
                    alert('Error syncing transaction: ' + e.message);
                }
            }
        },
        async fetchTransaction(orderReference) {
            try {
                const response = await fetch('{{ route('account.transaction.fetch') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order_reference: orderReference })
                });
                const data = await response.json();
                if (data.success) {
                    alert('Transaction fetched successfully! Check console for details');
                    console.log('Fetched Transaction:', data.data);
                } else {
                    alert('Error fetching transaction: ' + (data.message || data.error));
                }
            } catch (e) {
                alert('Error fetching transaction: ' + e.message);
            }
        }
    };
}
</script>
@endpush
