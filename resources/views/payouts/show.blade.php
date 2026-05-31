@extends('layouts.app')

@section('title', 'Payout Status')

@section('content')
<div class="space-y-6 animate-fade-in">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-wallet text-primary-500"></i>
                Payout Status
            </h2>
            <p class="text-xs text-primary-500 mt-1">Reference: {{ $payout->order_reference }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('dashboard.index') }}" class="px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                <i class="fas fa-home me-1"></i> Dashboard
            </a>
            <a href="{{ route('payouts.index') }}" class="px-4 py-2 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-xs font-bold text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-all">
                <i class="fas fa-history me-1"></i> Payout History
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

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Status Card -->
        <div class="xl:col-span-2 space-y-4">
            <div class="card p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i> Payout Details
                    </h3>
                    @if(!in_array($payout->status, ['SUCCESS', 'FAILED', 'SETTLED']))
                        <form action="{{ route('payouts.refresh', $payout->order_reference) }}" method="POST">
                            @csrf
                            <button type="submit" class="flex items-center gap-1 px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </form>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                        <p class="text-[10px] font-bold text-primary-500 uppercase">Status</p>
                        @if(in_array($payout->status, ['SUCCESS', 'SETTLED']))
                            <p class="text-lg font-black text-green-600 dark:text-green-400">
                                <i class="fas fa-check-circle me-1"></i> {{ $payout->status }}
                            </p>
                        @elseif(in_array($payout->status, ['FAILED', 'CANCELLED']))
                            <p class="text-lg font-black text-red-600 dark:text-red-400">
                                <i class="fas fa-times-circle me-1"></i> {{ $payout->status }}
                            </p>
                        @else
                            <p class="text-lg font-black text-yellow-600 dark:text-yellow-400">
                                <i class="fas fa-clock me-1"></i> {{ $payout->status }}
                            </p>
                        @endif
                    </div>

                    <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                        <p class="text-[10px] font-bold text-primary-500 uppercase">Amount</p>
                        <p class="text-lg font-black text-primary-600 dark:text-primary-400">{{ $payout->currency }} {{ number_format($payout->amount, 2) }}</p>
                    </div>

                    <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                        <p class="text-[10px] font-bold text-primary-500 uppercase">Recipient Name</p>
                        <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $payout->recipient_name }}</p>
                    </div>

                    <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                        <p class="text-[10px] font-bold text-primary-500 uppercase">Payout Type</p>
                        <p class="text-sm font-semibold text-primary-900 dark:text-white">
                            {{ $payout->payout_type === 'MOBILE_MONEY' ? 'Mobile Money' : 'Bank Transfer' }}
                        </p>
                    </div>

                    @if($payout->recipient_phone)
                        <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                            <p class="text-[10px] font-bold text-primary-500 uppercase">Phone Number</p>
                            <p class="text-sm font-mono text-primary-900 dark:text-white">{{ $payout->recipient_phone }}</p>
                        </div>
                    @endif

                    @if($payout->bank_name)
                        <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                            <p class="text-[10px] font-bold text-primary-500 uppercase">Bank Details</p>
                            <p class="text-xs text-primary-900 dark:text-white">{{ $payout->bank_name }} • {{ $payout->bank_account_number }}</p>
                        </div>
                    @endif

                    <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                        <p class="text-[10px] font-bold text-primary-500 uppercase">Order Reference</p>
                        <p class="text-sm font-mono text-primary-900 dark:text-white">{{ $payout->order_reference }}</p>
                    </div>

                    <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                        <p class="text-[10px] font-bold text-primary-500 uppercase">Transaction ID</p>
                        <p class="text-sm font-mono text-primary-900 dark:text-white">{{ $payout->transaction_id ?? '-' }}</p>
                    </div>

                    <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl">
                        <p class="text-[10px] font-bold text-primary-500 uppercase">Date Created</p>
                        <p class="text-sm text-primary-900 dark:text-white">{{ $payout->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>

                @if($payout->description)
                    <div class="mt-4">
                        <p class="text-[10px] font-bold text-primary-500 uppercase mb-1">Description</p>
                        <div class="p-3 bg-primary-50 dark:bg-dark-800 rounded-xl italic text-xs text-primary-800 dark:text-primary-300">
                            {{ $payout->description }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="text-xs font-black text-primary-900 dark:text-white uppercase tracking-wider mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('payouts.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all w-full">
                        <i class="fas fa-plus"></i> New Payout
                    </a>
                    <a href="{{ route('payouts.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-dark-border dark:hover:bg-dark-700 text-gray-700 dark:text-gray-200 text-xs font-bold transition-all w-full">
                        <i class="fas fa-list"></i> All Payouts
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
