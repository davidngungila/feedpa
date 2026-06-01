@extends('layouts.app')

@section('title', 'Profit & Loss Statement')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-primary-900 dark:text-white">Profit & Loss</h1>
                <p class="text-sm text-primary-600 dark:text-primary-400">For the period {{ $profitLoss['start_date'] }} to {{ $profitLoss['end_date'] }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.profit-loss.export.pdf', request()->query()) }}" 
                   class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-sm font-bold transition-all">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </a>
            </div>
        </div>
        
        <form method="GET" action="{{ route('reports.profit-loss') }}" class="flex flex-wrap gap-4 items-end mb-6">
            <div>
                <label class="block text-sm font-bold text-primary-900 dark:text-white mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $profitLoss['start_date'] }}" 
                       class="bg-primary-50 dark:bg-dark-900 border border-primary-200 dark:border-dark-border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-primary-900 dark:text-white mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $profitLoss['end_date'] }}" 
                       class="bg-primary-50 dark:bg-dark-900 border border-primary-200 dark:border-dark-border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white rounded-lg text-sm font-bold transition-all">
                    Generate Report
                </button>
                <a href="{{ route('reports.profit-loss') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-border hover:bg-gray-200 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-bold transition-all">
                    Reset
                </a>
            </div>
        </form>
        
        <div class="space-y-6">
            <!-- Revenue -->
            <div class="border border-primary-200 dark:border-dark-border rounded-lg overflow-hidden">
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border-b border-green-200 dark:border-green-800">
                    <h2 class="text-lg font-bold text-green-800 dark:text-green-200">Revenue</h2>
                </div>
                <div class="p-4">
                    @foreach($profitLoss['revenue'] as $item)
                    <div class="flex justify-between items-center py-2">
                        <span class="text-primary-900 dark:text-white">{{ $item['name'] }}</span>
                        <span class="font-mono text-primary-900 dark:text-white">{{ number_format($item['amount'], 2) }}</span>
                    </div>
                    @endforeach
                    <div class="flex justify-between items-center py-3 mt-4 border-t border-primary-200 dark:border-dark-border font-bold text-green-800 dark:text-green-200">
                        <span>Total Revenue</span>
                        <span class="font-mono">{{ number_format($profitLoss['totals']['total_revenue'], 2) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Expenses -->
            @if(!empty($profitLoss['expenses']))
            <div class="border border-primary-200 dark:border-dark-border rounded-lg overflow-hidden">
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800">
                    <h2 class="text-lg font-bold text-red-800 dark:text-red-200">Expenses</h2>
                </div>
                <div class="p-4">
                    @foreach($profitLoss['expenses'] as $item)
                    <div class="flex justify-between items-center py-2">
                        <span class="text-primary-900 dark:text-white">{{ $item['name'] }}</span>
                        <span class="font-mono text-primary-900 dark:text-white">{{ number_format($item['amount'], 2) }}</span>
                    </div>
                    @endforeach
                    <div class="flex justify-between items-center py-3 mt-4 border-t border-primary-200 dark:border-dark-border font-bold text-red-800 dark:text-red-200">
                        <span>Total Expenses</span>
                        <span class="font-mono">{{ number_format($profitLoss['totals']['total_expenses'], 2) }}</span>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Net Profit -->
            <div class="border border-primary-200 dark:border-dark-border rounded-lg overflow-hidden">
                <div class="p-4 bg-primary-600 text-white border-b border-primary-500">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-bold">Net Profit</h2>
                        <span class="font-mono text-xl font-bold">{{ number_format($profitLoss['totals']['net_profit'], 2) }} TZS</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
