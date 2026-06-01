@extends('layouts.app')

@section('title', 'Balance Sheet')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-primary-900 dark:text-white">Balance Sheet</h1>
                <p class="text-sm text-primary-600 dark:text-primary-400">As of {{ $balanceSheet['as_of_date'] }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.balance-sheet.export.pdf', request()->query()) }}" 
                   class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-sm font-bold transition-all">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </a>
            </div>
        </div>
        
        <form method="GET" action="{{ route('reports.balance-sheet') }}" class="flex flex-wrap gap-4 items-end mb-6">
            <div>
                <label class="block text-sm font-bold text-primary-900 dark:text-white mb-1">As of Date</label>
                <input type="date" name="as_of_date" value="{{ $balanceSheet['as_of_date'] }}" 
                       class="bg-primary-50 dark:bg-dark-900 border border-primary-200 dark:border-dark-border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white rounded-lg text-sm font-bold transition-all">
                    Generate Report
                </button>
                <a href="{{ route('reports.balance-sheet') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-border hover:bg-gray-200 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-bold transition-all">
                    Reset
                </a>
            </div>
        </form>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Assets -->
            <div class="border border-primary-200 dark:border-dark-border rounded-lg">
                <div class="p-4 bg-primary-50 dark:bg-dark-900 border-b border-primary-200 dark:border-dark-border">
                    <h2 class="text-lg font-bold text-primary-900 dark:text-white">Assets</h2>
                </div>
                <div class="p-4">
                    @foreach($balanceSheet['assets'] as $asset)
                    <div class="flex justify-between items-center py-2">
                        <span class="text-primary-900 dark:text-white">{{ $asset['name'] }}</span>
                        <span class="font-mono text-primary-900 dark:text-white">{{ number_format($asset['value'], 2) }}</span>
                    </div>
                    @endforeach
                    <div class="flex justify-between items-center py-3 mt-4 border-t border-primary-200 dark:border-dark-border font-bold">
                        <span>Total Assets</span>
                        <span class="font-mono">{{ number_format($balanceSheet['totals']['assets'], 2) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Liabilities & Equity -->
            <div class="border border-primary-200 dark:border-dark-border rounded-lg">
                <div class="p-4 bg-primary-50 dark:bg-dark-900 border-b border-primary-200 dark:border-dark-border">
                    <h2 class="text-lg font-bold text-primary-900 dark:text-white">Liabilities & Equity</h2>
                </div>
                <div class="p-4">
                    @if(!empty($balanceSheet['liabilities']))
                    <h3 class="font-bold text-primary-900 dark:text-white mb-2">Liabilities</h3>
                    @foreach($balanceSheet['liabilities'] as $liability)
                    <div class="flex justify-between items-center py-2">
                        <span class="text-primary-900 dark:text-white">{{ $liability['name'] }}</span>
                        <span class="font-mono text-primary-900 dark:text-white">{{ number_format($liability['value'], 2) }}</span>
                    </div>
                    @endforeach
                    @endif
                    
                    <h3 class="font-bold text-primary-900 dark:text-white mb-2 mt-4">Equity</h3>
                    @foreach($balanceSheet['equity'] as $equity)
                    <div class="flex justify-between items-center py-2">
                        <span class="text-primary-900 dark:text-white">{{ $equity['name'] }}</span>
                        <span class="font-mono text-primary-900 dark:text-white">{{ number_format($equity['value'], 2) }}</span>
                    </div>
                    @endforeach
                    
                    <div class="flex justify-between items-center py-3 mt-4 border-t border-primary-200 dark:border-dark-border font-bold">
                        <span>Total Liabilities & Equity</span>
                        <span class="font-mono">{{ number_format($balanceSheet['totals']['total_liabilities_equity'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
