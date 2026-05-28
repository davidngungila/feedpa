@extends('layouts.app')

@section('title', 'Account Statement')

@section('content')
<div class="space-y-6" x-data="statementDetails()">
    <!-- Filters Card -->
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-sm text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-filter text-primary-500"></i> Statement Filters
            </h3>
            <div class="flex gap-2">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg text-xs font-bold hover:bg-red-600 hover:text-white transition-all">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="px-3 py-1.5 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-lg text-xs font-bold hover:bg-green-600 hover:text-white transition-all">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </a>
            </div>
        </div>
        
        <form method="GET" action="{{ route('account.statement') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Source</label>
                <select name="tab" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
                    <option value="database" {{ $activeTab === 'database' ? 'selected' : '' }}>Internal Database</option>
                    <option value="api" {{ $activeTab === 'api' ? 'selected' : '' }}>ClickPesa API</option>
                </select>
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
                    <option value="TZS" {{ $currencyFilter === 'TZS' ? 'selected' : '' }}>TZS</option>
                    <option value="USD" {{ $currencyFilter === 'USD' ? 'selected' : '' }}>USD</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-500 text-white py-2 rounded-lg text-xs font-bold transition-all">
                    Generate Statement
                </button>
            </div>
        </form>
    </div>

    @if($error)
        <div class="card p-4 border-l-4 border-l-red-500 bg-red-50/60 dark:bg-red-900/10">
            <p class="text-xs font-bold text-red-700 dark:text-red-300"><i class="fas fa-circle-exclamation me-1"></i> {{ $error }}</p>
        </div>
    @endif

    <!-- Statement Table Card -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30 flex flex-wrap items-center justify-between gap-2">
            <h3 class="font-bold text-xs text-primary-700 dark:text-primary-300 uppercase tracking-widest">
                Showing Records from: {{ $activeTab === 'api' ? 'ClickPesa API' : 'Internal Database' }}
            </h3>
            <p class="text-[10px] text-primary-500">Click <i class="fas fa-eye"></i> on any row for full transaction details</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th class="text-right">Amount</th>
                        @if($activeTab === 'api')
                            <th class="text-center">Sync</th>
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
                            $isSettled = in_array($status, ['SETTLED', 'SUCCESS']);
                            $isFailed = in_array($status, ['FAILED', 'CANCELLED', 'ERROR']);

                            $rawDate = $t['created_at'] ?? $t['createdAt'] ?? $t['date'] ?? null;
                            $formattedDate = $rawDate ? \Carbon\Carbon::parse($rawDate)->format('d M, Y') : 'N/A';
                            $formattedTime = $rawDate ? \Carbon\Carbon::parse($rawDate)->format('H:i:s') : 'N/A';

                            $reference = $t['order_reference'] ?? $t['orderReference'] ?? $t['reference'] ?? 'N/A';
                            $amount = (float) ($t['amount'] ?? $t['collectedAmount'] ?? 0);
                            $currency = $t['currency'] ?? $t['collectedCurrency'] ?? 'TZS';

                            $memberName = $t['customer_name'] ?? $customer['customerName'] ?? $t['customerName'] ?? $t['payer_name'] ?? 'N/A';
                            $actualPayer = $t['payer_name'] ?? $customer['customerName'] ?? $memberName;
                            $customerPhone = $t['phone'] ?? $t['paymentPhoneNumber'] ?? ($customer['customerPhoneNumber'] ?? 'N/A');
                            $paymentMethod = $t['payment_method'] ?? $t['channel'] ?? $t['paymentMethod'] ?? 'N/A';
                            $transactionId = $t['transaction_id'] ?? $t['id'] ?? 'N/A';
                            $description = trim((string) ($t['description'] ?? $t['narrative'] ?? $t['message'] ?? ''));
                            if ($description === '' || strtoupper($description) === 'N/A') {
                                $description = 'No description';
                            }

                            $detailPayload = [
                                'reference' => $reference,
                                'transaction_id' => $transactionId,
                                'status' => $status,
                                'amount' => $amount,
                                'currency' => $currency,
                                'entry' => $t['entry'] ?? 'CREDIT',
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
                                'status_url' => ($reference !== 'N/A' && ($t['source'] ?? '') === 'DATABASE')
                                    ? route('payments.status', ['reference' => $reference])
                                    : null,
                            ];
                        @endphp
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="whitespace-nowrap">
                                <div class="font-bold text-primary-900 dark:text-white">{{ $formattedDate }}</div>
                                <div class="text-[10px] text-primary-500">{{ $formattedTime }}</div>
                            </td>
                            <td>
                                <span class="font-mono text-[11px] text-primary-600 dark:text-primary-400">{{ $reference }}</span>
                            </td>
                            <td>
                                <div class="text-xs font-bold text-primary-900 dark:text-white">{{ $memberName }}</div>
                                @if(strcasecmp($memberName, $actualPayer) !== 0)
                                    <div class="text-[10px] text-primary-500">Payer: {{ $actualPayer }}</div>
                                @endif
                                <div class="text-[10px] text-primary-500">
                                    <span class="font-mono">{{ $customerPhone }}</span> • {{ $paymentMethod }}
                                </div>
                                <div class="text-[10px] text-primary-500 italic truncate max-w-[220px]" title="{{ $description }}">
                                    {{ $description }}
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $isSettled ? 'badge-green' : ($isFailed ? 'badge-red' : 'badge-yellow') }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="text-right font-mono font-bold text-primary-900 dark:text-white whitespace-nowrap">
                                {{ number_format($amount, 2) }}
                                <div class="text-[10px] text-primary-500 font-bold">{{ $currency }}</div>
                            </td>
                            @if($activeTab === 'api')
                                <td class="text-center">
                                    @if($t['is_synced'] ?? false)
                                        <i class="fas fa-check-double text-primary-500" title="Synced to DB"></i>
                                    @else
                                        <i class="fas fa-cloud-download-alt text-gray-300" title="API Only"></i>
                                    @endif
                                </td>
                            @endif
                            <td class="text-center">
                                <button type="button"
                                        @click="openDetails(@js($detailPayload))"
                                        class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 inline-flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                        title="View full details">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activeTab === 'api' ? 7 : 6 }}" class="text-center py-20">
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
        
        @if(method_exists($displayTransactions, 'links'))
            <div class="p-4 bg-primary-50/30 dark:bg-dark-900/30 border-t border-primary-50 dark:border-dark-border">
                {{ $displayTransactions->links() }}
            </div>
        @endif
    </div>

    <!-- Transaction detail modal -->
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
                        <div>
                            <p class="text-[10px] font-bold uppercase text-primary-500">Reference</p>
                            <p class="font-mono font-bold text-primary-900 dark:text-white" x-text="selected.reference"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold uppercase text-primary-500">Amount</p>
                            <p class="text-xl font-black text-primary-600 dark:text-primary-400">
                                <span x-text="selected.currency"></span>
                                <span x-text="formatAmount(selected.amount)"></span>
                            </p>
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
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Transaction ID</span>
                                <span class="font-mono text-xs font-bold" x-text="selected.transaction_id"></span>
                            </div>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Status</span>
                                <span class="badge badge-green text-[10px]" x-text="selected.status"></span>
                            </div>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Entry Type</span>
                                <span class="text-xs font-bold" x-text="selected.entry"></span>
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

                    <div class="flex flex-wrap gap-2 pt-2">
                        <template x-if="selected.status_url">
                            <a :href="selected.status_url" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                                <i class="fas fa-external-link-alt me-1"></i> Open Full Payment Page
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
    return {
        open: false,
        selected: null,
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
        formatAmount(value) {
            return new Intl.NumberFormat('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value || 0));
        }
    };
}
</script>
@endpush
