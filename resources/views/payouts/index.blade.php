@extends('layouts.app')

@section('title', 'Payout History')

@section('content')
<div class="space-y-6 animate-fade-in">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-wallet text-primary-500"></i>
                Payout History
            </h2>
            <p class="text-xs text-primary-500 mt-1">View and manage all payouts.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('dashboard.index') }}" class="px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                <i class="fas fa-home me-1"></i> Dashboard
            </a>
            <form action="{{ route('payouts.sync') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-xs font-black text-white transition-all">
                    <i class="fas fa-sync-alt me-1"></i> Sync
                </button>
            </form>
            <a href="{{ route('payouts.create') }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-xs font-black text-white transition-all">
                <i class="fas fa-plus me-1"></i> New Payout
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

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-[10px] font-bold text-primary-500 uppercase tracking-wider mb-1">Total Payouts</p>
            <p class="text-xl font-black text-primary-900 dark:text-white">{{ $payouts->total() }}</p>
        </div>
        <div class="card p-5 border-l-4 border-l-yellow-500">
            <p class="text-[10px] font-bold text-primary-500 uppercase tracking-wider mb-1">Pending</p>
            <p class="text-xl font-black text-primary-900 dark:text-white">{{ \App\Models\Payout::where('status', 'PENDING')->orWhere('status', 'PENDING_VERIFICATION')->count() }}</p>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-[10px] font-bold text-primary-500 uppercase tracking-wider mb-1">Success</p>
            <p class="text-xl font-black text-primary-900 dark:text-white">{{ \App\Models\Payout::where('status', 'SUCCESS')->count() }}</p>
        </div>
        <div class="card p-5 border-l-4 border-l-red-500">
            <p class="text-[10px] font-bold text-primary-500 uppercase tracking-wider mb-1">Failed</p>
            <p class="text-xl font-black text-primary-900 dark:text-white">{{ \App\Models\Payout::where('status', 'FAILED')->count() }}</p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-100 dark:border-dark-border flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('payouts.index', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $status === 'all' ? 'bg-primary-600 text-white' : 'bg-primary-50 text-primary-600 hover:bg-primary-100' }} transition-all">
                    All
                </a>
                <a href="{{ route('payouts.index', ['status' => 'PENDING_VERIFICATION']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $status === 'PENDING_VERIFICATION' ? 'bg-primary-600 text-white' : 'bg-primary-50 text-primary-600 hover:bg-primary-100' }} transition-all">
                    Verification
                </a>
                <a href="{{ route('payouts.index', ['status' => 'PENDING']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $status === 'PENDING' ? 'bg-primary-600 text-white' : 'bg-primary-50 text-primary-600 hover:bg-primary-100' }} transition-all">
                    Pending
                </a>
                <a href="{{ route('payouts.index', ['status' => 'SUCCESS']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $status === 'SUCCESS' ? 'bg-primary-600 text-white' : 'bg-primary-50 text-primary-600 hover:bg-primary-100' }} transition-all">
                    Success
                </a>
                <a href="{{ route('payouts.index', ['status' => 'FAILED']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ $status === 'FAILED' ? 'bg-primary-600 text-white' : 'bg-primary-50 text-primary-600 hover:bg-primary-100' }} transition-all">
                    Failed
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-primary-100 dark:divide-dark-border">
                <thead>
                    <tr class="bg-primary-50 dark:bg-dark-800">
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-primary-500 uppercase tracking-wider">Order Ref</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-primary-500 uppercase tracking-wider">Recipient</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-primary-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-primary-500 uppercase tracking-wider">Amount</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-primary-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-primary-500 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-primary-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-dark-card divide-y divide-primary-100 dark:divide-dark-border">
                    @foreach($payouts as $payout)
                        <tr class="hover:bg-primary-50 dark:hover:bg-dark-700 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-mono text-xs text-primary-900 dark:text-white">{{ $payout->order_reference }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div>
                                    <div class="text-xs font-semibold text-primary-900 dark:text-white">{{ $payout->recipient_name }}</div>
                                    @if($payout->recipient_phone)
                                        <div class="text-[10px] text-primary-500">{{ $payout->recipient_phone }}</div>
                                    @endif
                                    @if($payout->bank_name)
                                        <div class="text-[10px] text-primary-500">{{ $payout->bank_name }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $payout->payout_type === 'MOBILE_MONEY' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' : 'bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400' }}">
                                    {{ $payout->payout_type === 'MOBILE_MONEY' ? 'Mobile Money' : 'Bank' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-black text-primary-600 dark:text-primary-400 text-xs">{{ $payout->currency }} {{ number_format($payout->amount, 2) }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if(in_array($payout->status, ['SUCCESS', 'SETTLED']))
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400">
                                        <i class="fas fa-check-circle me-1"></i> {{ $payout->status }}
                                    </span>
                                @elseif(in_array($payout->status, ['FAILED', 'CANCELLED']))
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400">
                                        <i class="fas fa-times-circle me-1"></i> {{ $payout->status }}
                                    </span>
                                @elseif($payout->status === 'PENDING_VERIFICATION')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400">
                                        <i class="fas fa-shield-alt me-1"></i> Verify
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">
                                        <i class="fas fa-clock me-1"></i> {{ $payout->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-[10px] text-primary-500">{{ $payout->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="flex justify-end gap-2">
                                    @if($payout->status === 'PENDING_VERIFICATION')
                                        <a href="{{ route('payouts.verify-otp', $payout->order_reference) }}" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-purple-100 text-purple-700 hover:bg-purple-200 transition-all">
                                            Verify
                                        </a>
                                    @endif
                                    <a href="{{ route('payouts.status', $payout->order_reference) }}" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-primary-100 text-primary-700 hover:bg-primary-200 transition-all">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($payouts->hasPages())
            <div class="p-4 border-t border-primary-100 dark:border-dark-border">
                {{ $payouts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
