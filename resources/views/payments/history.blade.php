@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
<div class="space-y-6">
    <!-- Status Tabs -->
    <div class="card p-1">
        <div class="flex gap-1">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'SETTLED']) }}" 
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ request('status', 'SETTLED') === 'SETTLED' ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/20' : 'text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/30' }}">
                <i class="fas fa-check-circle"></i> SETTLED
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'FAILED']) }}" 
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ request('status') === 'FAILED' ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/20' : 'text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/30' }}">
                <i class="fas fa-times-circle"></i> FAILED
            </a>
        </div>
    </div>

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
        
        <form x-show="showFilters" x-transition method="GET" action="{{ route('payments.history') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="status" value="{{ request('status', 'SETTLED') }}">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Search Reference</label>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-primary-500 outline-none" placeholder="REF-XXXX">
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
                <a href="{{ route('payments.history') }}" class="px-3 py-2 bg-gray-100 dark:bg-dark-border rounded-lg text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-200 transition-all">
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

        <form x-show="showExport" x-transition method="GET" action="{{ route('payments.export.pdf') }}" class="space-y-4">
            <input type="hidden" name="status" value="{{ request('status', 'SETTLED') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
            <input type="hidden" name="currency" value="{{ request('currency') }}">

            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Choose columns to include</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($availableColumns as $columnKey => $columnLabel)
                        <label class="flex items-center gap-2 text-xs bg-primary-50 dark:bg-dark-900 px-3 py-2 rounded-lg border border-primary-100 dark:border-dark-border">
                            <input type="checkbox" name="columns[]" value="{{ $columnKey }}"
                                   {{ in_array($columnKey, $selectedColumns ?? []) ? 'checked' : '' }}
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
                <button type="submit" formaction="{{ route('payments.export.excel') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-xs font-bold transition-all">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </button>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Reference</th>
                        <th>Member Name</th>
                        <th>Purpose / Description</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($payments as $payment)
                        @php
                            $callbackData = is_array($payment->callback_data ?? null) ? $payment->callback_data : [];
                            $callbackCustomer = is_array($callbackData['customer'] ?? null) ? $callbackData['customer'] : [];

                            $memberName = $payment->customer_name
                                ?? $callbackCustomer['customerName']
                                ?? $callbackData['customerName']
                                ?? $payment->payer_name
                                ?? 'N/A';

                            $actualPayer = $payment->payer_name
                                ?? $callbackData['payer_name']
                                ?? $callbackCustomer['customerName']
                                ?? $memberName;

                            $displayPhone = $payment->phone
                                ?? $callbackData['paymentPhoneNumber']
                                ?? $callbackCustomer['customerPhoneNumber']
                                ?? 'N/A';

                            $displayDescription = trim((string) (
                                $payment->description
                                ?? $callbackData['description']
                                ?? $callbackData['narrative']
                                ?? ''
                            ));
                            if ($displayDescription === '' || strtoupper($displayDescription) === 'N/A') {
                                $displayDescription = 'Malipo ya FEEDTAN';
                            }
                        @endphp
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="whitespace-nowrap">
                                <div class="font-bold text-primary-900 dark:text-white">{{ $payment->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] text-primary-500">{{ $payment->created_at->format('H:i:s') }}</div>
                            </td>
                            <td>
                                <span class="font-mono text-[11px] bg-primary-50 dark:bg-dark-900 px-2 py-1 rounded border border-primary-100 dark:border-dark-border text-primary-700 dark:text-primary-300">
                                    {{ $payment->order_reference }}
                                </span>
                            </td>
                            <td>
                                <div class="font-bold text-primary-900 dark:text-white">{{ $memberName }}</div>
                                <div class="text-[10px] text-primary-500">Payer: {{ $actualPayer }}</div>
                                <div class="text-[10px] text-primary-500 font-mono">{{ $displayPhone }}</div>
                            </td>
                            <td>
                                <div class="text-xs text-primary-700 dark:text-primary-400 max-w-[260px] truncate" title="{{ $displayDescription }}">
                                    {{ $displayDescription }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="font-bold text-primary-600 dark:text-primary-400">
                                    {{ number_format($payment->amount, 2) }}
                                </div>
                                <div class="text-[10px] text-primary-500 uppercase font-bold">{{ $payment->currency }}</div>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="{{ route('payments.status', ['reference' => $payment->order_reference]) }}" 
                                       class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('payments.receipt', $payment->order_reference) }}" target="_blank"
                                       class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all">
                                        <i class="fas fa-file-invoice text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-20">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-dark-900 flex items-center justify-center mb-4">
                                        <i class="fas fa-folder-open text-2xl text-primary-200"></i>
                                    </div>
                                    <h4 class="font-bold text-primary-900 dark:text-white">No Transactions Found</h4>
                                    <p class="text-xs text-primary-500">There are no {{ strtolower(request('status', 'SETTLED')) }} payments to display.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($payments->hasPages())
            <div class="p-4 bg-primary-50/30 dark:bg-dark-900/30 border-t border-primary-50 dark:border-dark-border">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection