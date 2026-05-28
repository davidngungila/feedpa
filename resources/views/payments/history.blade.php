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
                                <div class="font-bold text-primary-900 dark:text-white">{{ $payment->customer_name ?? $payment->payer_name ?? 'N/A' }}</div>
                                @if($payment->customer_name && $payment->payer_name && strtolower($payment->customer_name) !== strtolower($payment->payer_name))
                                    <div class="text-[10px] text-primary-500">Payer: {{ $payment->payer_name }}</div>
                                @endif
                                <div class="text-[10px] text-primary-500 font-mono">{{ $payment->phone }}</div>
                            </td>
                            <td>
                                <div class="text-xs text-primary-700 dark:text-primary-400 max-w-[200px] truncate" title="{{ $payment->description }}">
                                    {{ $payment->description && $payment->description !== 'N/A' ? $payment->description : 'Malipo ya FEEDTAN' }}
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