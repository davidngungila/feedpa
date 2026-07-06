@extends('layouts.app')

@section('title', 'Customer Report')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-primary-900 dark:text-white">Customer Report</h1>
                <p class="text-sm text-primary-600 dark:text-primary-400">Filter and export customer payment transactions</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.customer-report.export.pdf', request()->query()) }}" 
                   class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-sm font-bold transition-all">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </a>
                <a href="{{ route('reports.customer-report.export.excel', request()->query()) }}" 
                   class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-sm font-bold transition-all">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </a>
            </div>
        </div>
        
        <form method="GET" action="{{ route('reports.customer-report') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end mb-6">
            <div>
                <label class="block text-sm font-bold text-primary-900 dark:text-white mb-1">Customer Name</label>
                <select name="customer_name" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-200 dark:border-dark-border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer }}" {{ $customerName == $customer ? 'selected' : '' }}>
                            {{ $customer }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-primary-900 dark:text-white mb-1">Phone Number</label>
                <input type="text" name="phone" value="{{ $phone }}" placeholder="Search by phone"
                       class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-200 dark:border-dark-border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-primary-900 dark:text-white mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" 
                       class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-200 dark:border-dark-border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-primary-900 dark:text-white mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" 
                       class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-200 dark:border-dark-border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-primary-900 dark:text-white mb-1">Status</label>
                <select name="status" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-200 dark:border-dark-border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="settled" {{ $status === 'settled' ? 'selected' : '' }}>Settled/Success</option>
                    <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed/Error</option>
                </select>
            </div>
            <div class="flex gap-2 md:col-span-2 lg:col-span-5">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white rounded-lg text-sm font-bold transition-all">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('reports.customer-report') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-border hover:bg-gray-200 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-bold transition-all">
                    <i class="fas fa-undo me-1"></i> Reset
                </a>
            </div>
        </form>
        
        <div class="mb-6 p-4 bg-primary-50 border border-primary-200 rounded-lg">
            <div class="flex items-center gap-3">
                <div class="text-2xl font-bold text-primary-700">
                    TZS {{ number_format($totalAmount, 2) }}
                </div>
                <div class="text-sm text-primary-600">
                    Total Settled Amount
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Payer</th>
                        <th>Phone</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment Method</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td class="whitespace-nowrap">
                            {{ $payment->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="font-mono text-sm">
                            {{ $payment->order_reference }}
                        </td>
                        <td class="font-medium">
                            {{ $payment->customer_name ?? $payment->payer_name }}
                        </td>
                        <td>
                            {{ $payment->payer_name }}
                        </td>
                        <td class="font-mono text-sm">
                            {{ $payment->phone }}
                        </td>
                        <td class="max-w-xs truncate" title="{{ $payment->description }}">
                            {{ $payment->description }}
                        </td>
                        <td class="text-right font-mono font-bold">
                            {{ $payment->currency ?? 'TZS' }} {{ number_format($payment->amount, 2) }}
                        </td>
                        <td>
                            @php
                                $statusClass = in_array(strtoupper($payment->status), ['SETTLED', 'SUCCESS']) ? 'badge-green' : 
                                              (in_array(strtoupper($payment->status), ['FAILED', 'ERROR']) ? 'badge-red' : 'badge-yellow');
                            @endphp
                            <span class="badge {{ $statusClass }}">
                                {{ $payment->status }}
                            </span>
                        </td>
                        <td>
                            {{ $payment->payment_method ?? 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($payments->hasPages())
        <div class="mt-6">
            {{ $payments->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
