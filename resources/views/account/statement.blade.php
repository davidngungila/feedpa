@extends('layouts.app')

@section('title', 'Account Statement')

@section('content')
<div class="space-y-6">
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

    <!-- Statement Table Card -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30">
            <h3 class="font-bold text-xs text-primary-700 dark:text-primary-300 uppercase tracking-widest">
                Showing Records from: {{ $activeTab === 'api' ? 'ClickPesa API' : 'Internal Database' }}
            </h3>
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($displayTransactions as $transaction)
                        @php
                            $t = (array) $transaction;

                            $status = strtoupper($t['status'] ?? 'UNKNOWN');
                            $isSettled = in_array($status, ['SETTLED', 'SUCCESS']);
                            $isFailed = in_array($status, ['FAILED', 'CANCELLED', 'ERROR']);

                            $rawDate = $t['created_at'] ?? $t['createdAt'] ?? $t['date'] ?? null;
                            $formattedDate = $rawDate ? \Carbon\Carbon::parse($rawDate)->format('d M, Y') : 'N/A';

                            $reference = $t['order_reference'] ?? $t['orderReference'] ?? $t['reference'] ?? 'N/A';
                            $amount = (float) ($t['amount'] ?? $t['collectedAmount'] ?? 0);

                            $customer = $t['customer'] ?? [];
                            $customerName = $t['customer_name'] ?? $t['payer_name'] ?? $t['customerName'] ?? ($customer['customerName'] ?? 'N/A');
                            $customerPhone = $t['phone'] ?? $t['paymentPhoneNumber'] ?? ($customer['customerPhoneNumber'] ?? 'N/A');
                            $paymentMethod = $t['payment_method'] ?? $t['channel'] ?? $t['paymentMethod'] ?? 'N/A';
                            $transactionId = $t['transaction_id'] ?? $t['id'] ?? 'N/A';
                            $description = $t['description'] ?? $t['narrative'] ?? $t['message'] ?? 'No description';
                        @endphp
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="whitespace-nowrap">
                                <div class="font-bold text-primary-900 dark:text-white">
                                    {{ $formattedDate }}
                                </div>
                            </td>
                            <td>
                                <span class="font-mono text-[11px] text-primary-600 dark:text-primary-400">
                                    {{ $reference }}
                                </span>
                            </td>
                            <td>
                                <div class="text-xs font-bold text-primary-900 dark:text-white">
                                    {{ $customerName }}
                                </div>
                                <div class="text-[10px] text-primary-500">
                                    <span class="font-mono">{{ $customerPhone }}</span> • {{ $paymentMethod }}
                                </div>
                                <div class="text-[10px] text-primary-500">
                                    TXID: <span class="font-mono">{{ $transactionId }}</span>
                                </div>
                                <div class="text-[10px] text-primary-500 italic truncate max-w-[220px]">
                                    {{ $description }}
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $isSettled ? 'badge-green' : ($isFailed ? 'badge-red' : 'badge-yellow') }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="text-right font-mono font-bold text-primary-900 dark:text-white">
                                {{ number_format($amount, 2) }}
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activeTab === 'api' ? 6 : 5 }}" class="text-center py-20">
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
</div>
@endsection