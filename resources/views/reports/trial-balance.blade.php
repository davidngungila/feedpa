@extends('layouts.app')

@section('title', 'Trial Balance')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-primary-900 dark:text-white">Trial Balance</h1>
                <p class="text-sm text-primary-600 dark:text-primary-400">Check that your debits equal your credits</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.trial-balance.export.pdf', request()->query()) }}" 
                   class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-sm font-bold transition-all">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </a>
            </div>
        </div>
        
        <form method="GET" action="{{ route('reports.trial-balance') }}" class="flex flex-wrap gap-4 items-end mb-6">
            <div>
                <label class="block text-sm font-bold text-primary-900 dark:text-white mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" 
                       class="bg-primary-50 dark:bg-dark-900 border border-primary-200 dark:border-dark-border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-primary-900 dark:text-white mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" 
                       class="bg-primary-50 dark:bg-dark-900 border border-primary-200 dark:border-dark-border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white rounded-lg text-sm font-bold transition-all">
                    Generate Report
                </button>
                <a href="{{ route('reports.trial-balance') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-border hover:bg-gray-200 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-bold transition-all">
                    Reset
                </a>
            </div>
        </form>
        
        <div class="overflow-x-auto">
            <table class="w-full data-table">
                <thead>
                    <tr>
                        <th class="text-left">Account</th>
                        <th class="text-right">Debit (TZS)</th>
                        <th class="text-right">Credit (TZS)</th>
                        <th class="text-right">Balance (TZS)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trialBalance['accounts'] as $account)
                    <tr>
                        <td class="font-bold">{{ $account['name'] }}</td>
                        <td class="text-right font-mono">{{ number_format($account['debit'], 2) }}</td>
                        <td class="text-right font-mono">{{ number_format($account['credit'], 2) }}</td>
                        <td class="text-right font-mono {{ $account['balance'] >= 0 ? 'text-primary-700' : 'text-red-600' }}">
                            {{ number_format($account['balance'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-primary-50 dark:bg-dark-900">
                        <td class="font-bold">Total</td>
                        <td class="text-right font-mono font-bold">{{ number_format($trialBalance['totals']['debit'], 2) }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($trialBalance['totals']['credit'], 2) }}</td>
                        <td class="text-right font-mono font-bold">
                            {{ number_format($trialBalance['totals']['debit'] - $trialBalance['totals']['credit'], 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        @if($trialBalance['totals']['debit'] == $trialBalance['totals']['credit'])
        <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                <span class="font-bold text-green-800">Trial Balance is balanced!</span>
            </div>
        </div>
        @else
        <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                <span class="font-bold text-red-800">Trial Balance is not balanced!</span>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
