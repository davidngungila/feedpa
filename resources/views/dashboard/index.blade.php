@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Transactions -->
        <div class="card p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600 dark:text-primary-400">
                <i class="fas fa-exchange-alt text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-primary-500">Total Transactions</p>
                <h3 class="text-2xl font-black text-primary-900 dark:text-white">{{ number_format($stats['total_transactions'] ?? 0) }}</h3>
            </div>
        </div>

        <!-- Successful -->
        <div class="card p-5 flex items-center gap-4 border-l-4 border-l-green-500">
            <div class="w-12 h-12 rounded-2xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600">
                <i class="fas fa-check-double text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Successful</p>
                <h3 class="text-2xl font-black text-primary-900 dark:text-white">{{ number_format($stats['successful'] ?? 0) }}</h3>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="card p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-primary-600 flex items-center justify-center text-white shadow-lg shadow-primary-900/20">
                <i class="fas fa-wallet text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-primary-500">Total Revenue</p>
                <h3 class="text-xl font-black text-primary-900 dark:text-white">
                    <span class="text-xs font-bold text-primary-500">TZS</span> {{ number_format($stats['total_amount'] ?? 0, 0) }}
                </h3>
            </div>
        </div>

        <!-- Success Rate -->
        <div class="card p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600">
                <i class="fas fa-chart-pie text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Success Rate</p>
                <h3 class="text-2xl font-black text-primary-900 dark:text-white">{{ $stats['success_rate'] ?? 0 }}%</h3>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activities -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-primary-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-bolt text-yellow-500"></i> Recent Payments
                </h3>
                <a href="{{ route('payments.history') }}" class="text-xs font-bold text-primary-600 hover:underline">View All</a>
            </div>
            
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Member / Payer</th>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                            @forelse($recentPayments ?? [] as $payment)
                                <tr class="hover:bg-primary-50/30 dark:hover:bg-primary-900/5 transition-colors">
                                    <td>
                                        <div class="font-bold text-primary-900 dark:text-white">{{ $payment['customer_name'] ?? 'Mteja' }}</div>
                                        <div class="text-[10px] text-primary-500 font-mono">{{ $payment['customer_phone'] ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <span class="font-mono text-[10px] bg-primary-50 dark:bg-dark-900 px-2 py-1 rounded text-primary-700 dark:text-primary-300">
                                            {{ $payment['orderReference'] ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="font-bold text-primary-900 dark:text-white">
                                        {{ number_format($payment['amount'] ?? $payment['collectedAmount'] ?? 0, 0) }}
                                    </td>
                                    <td>
                                        <span class="badge {{ in_array($payment['status'] ?? '', ['SUCCESS', 'SETTLED']) ? 'badge-green' : (in_array($payment['status'] ?? '', ['FAILED', 'ERROR']) ? 'badge-red' : 'badge-yellow') }}">
                                            {{ $payment['status'] ?? 'PENDING' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-10 text-primary-500 text-xs italic">No recent activities</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Info / Top Customers -->
        <div class="space-y-4">
            <h3 class="font-bold text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-trophy text-orange-500"></i> Top Members
            </h3>
            <div class="card p-5 space-y-4">
                @forelse($stats['top_customers'] ?? [] as $customer)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 font-bold text-xs uppercase">
                                {{ substr($customer['name'] ?? 'C', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-primary-900 dark:text-white truncate max-w-[120px]">{{ $customer['name'] }}</p>
                                <p class="text-[10px] text-primary-500">{{ $customer['count'] }} payments</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black text-primary-600 dark:text-primary-400">{{ number_format($customer['total_amount'] ?? $customer['amount'] ?? 0, 0) }}</p>
                            <p class="text-[9px] text-gray-400 uppercase font-bold">TZS</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center py-4 text-primary-500 text-xs italic">No data available</p>
                @endforelse
            </div>

            <!-- Payment Methods -->
            <h3 class="font-bold text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-credit-card text-blue-500"></i> Payment Channels
            </h3>
            <div class="card p-5 space-y-3">
                @forelse($stats['payment_methods'] ?? [] as $method)
                    <div>
                        <div class="flex justify-between text-[10px] font-bold uppercase mb-1">
                            <span class="text-primary-700 dark:text-primary-300">{{ $method['method'] ?? $method['name'] ?? 'Unknown' }}</span>
                            <span class="text-primary-500">{{ number_format($method['count'] ?? 0) }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-primary-50 dark:bg-dark-900 rounded-full overflow-hidden">
                            @php 
                                $methodCount = $method['count'] ?? 0;
                                $percent = ($stats['total_transactions'] ?? 0) > 0 ? ($methodCount / $stats['total_transactions'] * 100) : 0;
                            @endphp
                            <div class="h-full bg-primary-600 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-center py-2 text-primary-500 text-xs italic">No data</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
