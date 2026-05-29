@extends('layouts.app')

@section('title', 'Bill Details - ' . $bill->bill_pay_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-primary-900 dark:text-white">Bill Details</h1>
            <p class="text-sm text-primary-500 mt-1">Control Number: {{ $bill->bill_pay_number }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('bills.pdf', $bill->id) }}" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-bold rounded-xl transition-all">
                <i class="fas fa-file-pdf mr-1"></i> Download PDF
            </a>
            <a href="{{ route('bills.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 text-sm font-bold rounded-xl transition-all">
                <i class="fas fa-arrow-left mr-1"></i> Back to Bills
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-circle-check mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    @if(session('error'))
        <div class="card p-4 border-l-4 border-l-red-500 bg-red-50/60 dark:bg-red-900/10">
            <p class="text-xs font-bold text-red-700 dark:text-red-300">
                <i class="fas fa-circle-exclamation mr-1"></i> {{ session('error') }}
            </p>
        </div>
    @endif

    <!-- Main Card -->
    <div class="card p-6">
        <div class="flex flex-col md:flex-row gap-8">
            <!-- QR Code Section -->
            <div class="flex flex-col items-center gap-4">
                <div class="p-3 bg-white border border-primary-200 rounded-xl">
                    <img src="{{ $qrCodeImage }}" alt="QR Code" class="w-40 h-40">
                </div>
                <p class="text-xs text-primary-500 text-center">Scan for bill details</p>
            </div>

            <!-- Bill Info -->
            <div class="flex-1 space-y-6">
                <!-- Status & Type -->
                <div class="flex flex-wrap gap-4">
                    <span class="px-4 py-2 rounded-full text-xs font-bold {{ $bill->bill_status === 'ACTIVE' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                        {{ $bill->bill_status }}
                    </span>
                    <span class="px-4 py-2 rounded-full text-xs font-bold {{ $bill->bill_type === 'order' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                        {{ ucfirst($bill->bill_type) }}
                    </span>
                </div>

                <!-- Amount -->
                <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800">
                    <p class="text-xs font-bold uppercase text-primary-500 mb-1">Total Amount</p>
                    <p class="text-3xl font-black text-primary-900 dark:text-white">
                        {{ $bill->bill_currency }} {{ number_format($bill->bill_amount, 2) }}
                    </p>
                    @if($bill->total_paid > 0)
                    <p class="text-sm text-green-600 dark:text-green-400 font-bold mt-2">
                        Paid: {{ $bill->bill_currency }} {{ number_format($bill->total_paid, 2) }}
                    </p>
                    @endif
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500">Bill Information</h3>
                        
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-primary-500 mb-1">Description</p>
                                <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $bill->bill_description }}</p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-primary-500 mb-1">Payment Mode</p>
                                <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $bill->bill_payment_mode }}</p>
                            </div>
                            
                            @if($bill->bill_reference)
                            <div>
                                <p class="text-xs text-primary-500 mb-1">Reference</p>
                                <p class="text-sm font-mono font-semibold text-primary-900 dark:text-white">{{ $bill->bill_reference }}</p>
                            </div>
                            @endif
                            
                            <div>
                                <p class="text-xs text-primary-500 mb-1">Created On</p>
                                <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $bill->created_at->format('F d, Y H:i:s') }}</p>
                            </div>
                            
                            @if($bill->last_payment_at)
                            <div>
                                <p class="text-xs text-primary-500 mb-1">Last Payment</p>
                                <p class="text-sm font-semibold text-green-600 dark:text-green-400">{{ $bill->last_payment_at->format('F d, Y H:i:s') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($bill->bill_type === 'customer')
                    <div class="space-y-4">
                        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500">Customer Information</h3>
                        
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-primary-500 mb-1">Name</p>
                                <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $bill->customer_name }}</p>
                            </div>
                            
                            @if($bill->customer_phone)
                            <div>
                                <p class="text-xs text-primary-500 mb-1">Phone</p>
                                <p class="text-sm font-mono text-primary-900 dark:text-white">{{ $bill->customer_phone }}</p>
                            </div>
                            @endif
                            
                            @if($bill->customer_email)
                            <div>
                                <p class="text-xs text-primary-500 mb-1">Email</p>
                                <p class="text-sm text-primary-900 dark:text-white">{{ $bill->customer_email }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('bills.pdf', $bill->id) }}" class="flex-1 md:flex-none px-6 py-3 bg-red-600 hover:bg-red-500 text-white font-bold rounded-xl transition-all text-center">
            <i class="fas fa-download mr-2"></i> Download PDF
        </a>
        <a href="{{ route('bills.index') }}" class="flex-1 md:flex-none px-6 py-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 font-bold rounded-xl transition-all text-center">
            <i class="fas fa-list mr-2"></i> All Bills
        </a>
    </div>
    
    <!-- Transactions Section -->
    @if($transactions->count() > 0)
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-primary-100 dark:border-primary-800">
            <h3 class="text-sm font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-history text-primary-500"></i> Payment History
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Order Reference</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Payer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @foreach($transactions as $txn)
                    <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $txn->created_at->format('M d, Y H:i:s') }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-mono text-primary-900 dark:text-white">{{ $txn->order_reference }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $txn->currency }} {{ number_format($txn->amount, 2) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ in_array($txn->status, ['SUCCESS', 'SETTLED']) ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : (in_array($txn->status, ['FAILED', 'ERROR']) ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300') }}">
                                {{ $txn->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $txn->customer_name ?? $txn->payer_name ?? 'N/A' }}</p>
                            @if($txn->phone)
                            <p class="text-xs text-primary-500 font-mono">{{ $txn->phone }}</p>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
