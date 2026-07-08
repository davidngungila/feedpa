@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header with Sync and Export Buttons -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <h2 class="text-xl font-bold text-primary-900 dark:text-white">Dashboard Overview</h2>
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex flex-col gap-1">
                <p class="text-xs text-primary-600 dark:text-primary-400">
                    Transactions last sync: {{ cache()->get('api_last_sync') ? \Carbon\Carbon::createFromTimestamp(cache()->get('api_last_sync'))->diffForHumans() : 'Never' }}</p>
                <p class="text-xs text-purple-600 dark:text-purple-400">
                    Bills last sync: {{ cache()->get('api_bills_last_sync') ? \Carbon\Carbon::createFromTimestamp(cache()->get('api_bills_last_sync'))->diffForHumans() : 'Never' }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('api-sync') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-500 text-white text-xs font-bold rounded-xl shadow-lg hover:scale-105 transition-all">
                        <i class="fas fa-sync-alt mr-1"></i> Sync Transactions
                    </button>
                </form>
                <form action="{{ route('api-sync-bills') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-500 text-white text-xs font-bold rounded-xl shadow-lg hover:scale-105 transition-all">
                        <i class="fas fa-sync-alt mr-1"></i> Sync Bills
                    </button>
                </form>
                <div class="border-l border-gray-300 dark:border-gray-600 h-8 mx-2"></div>
                <a href="{{ route('dashboard.export.pdf', ['date_filter' => request('date_filter', 'today'), 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
                   class="px-4 py-2 bg-gradient-to-r from-amber-600 to-amber-500 text-white text-xs font-bold rounded-xl shadow-lg hover:scale-105 transition-all">
                    <i class="fas fa-file-pdf mr-1"></i> Export PDF
                </a>
                <button id="exportImageBtn" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-500 text-white text-xs font-bold rounded-xl shadow-lg hover:scale-105 transition-all">
                    <i class="fas fa-image mr-1"></i> Export Image
                </button>
            </div>
        </div>
    </div>
    
    <!-- Period Filter -->
    <div class="card p-4">
        <form method="GET" action="{{ route('dashboard.index') }}" class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-primary-500 shrink-0">
                    <i class="fas fa-filter me-1"></i> Filter period
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach([
                        'today' => 'Today',
                        'week' => 'This Week',
                        'month' => 'This Month',
                        'quarter' => '3 Months',
                        'year' => 'This Year',
                    ] as $value => $label)
                    <button type="submit" name="date_filter" value="{{ $value }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ ($dateFilter ?? 'today') === $value ? 'bg-primary-600 text-white shadow-md' : 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-300 hover:bg-primary-100' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>
            
            <!-- Custom Date Range -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-primary-500 shrink-0">
                    <i class="fas fa-calendar-alt me-1"></i> Custom Range
                </p>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-1.5 rounded-lg text-xs border border-primary-200 dark:border-primary-800 bg-white dark:bg-dark-card focus:ring-2 focus:ring-primary-500">
                    <span class="text-xs text-primary-400">to</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-1.5 rounded-lg text-xs border border-primary-200 dark:border-primary-800 bg-white dark:bg-dark-card focus:ring-2 focus:ring-primary-500">
                    <button type="submit" name="date_filter" value="custom" class="px-4 py-1.5 rounded-lg text-xs font-bold bg-gradient-to-r from-primary-600 to-primary-500 text-white hover:shadow-lg transition-all">
                        Apply Range
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Stats Grid -->
    <div id="dashboard-content" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Settled amount for period -->
        <div class="card p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600 dark:text-primary-400">
                <i class="fas fa-money-bill-wave text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-bold uppercase tracking-widest text-primary-500">{{ $periodLabel ?? 'Today' }} Payments</p>
                <p class="text-[9px] text-primary-400 normal-case">Money settled in period</p>
                <h3 class="text-xl font-black text-primary-900 dark:text-white mt-1">
                    <span class="text-xs font-bold text-primary-500">TZS</span> {{ number_format($stats['period_settled_amount'] ?? 0, 0) }}
                </h3>
                @if($stats['amount_growth_rate'] ?? 0 != 0)
                <p class="text-[10px] mt-1 flex items-center gap-1 {{ ($stats['amount_growth_rate'] ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                    <i class="fas fa-arrow-{{ ($stats['amount_growth_rate'] ?? 0) > 0 ? 'up' : 'down' }}"></i>
                    {{ abs($stats['amount_growth_rate']) }}% from previous period
                </p>
                @endif
            </div>
        </div>

        <!-- Successful count for period -->
        <div class="card p-5 flex items-center gap-4 border-l-4 border-l-green-500">
            <div class="w-12 h-12 rounded-2xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600">
                <i class="fas fa-check-double text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Successful for {{ strtolower($periodLabel ?? 'today') }}</p>
                <h3 class="text-2xl font-black text-primary-900 dark:text-white mt-1">{{ number_format($stats['period_successful_count'] ?? 0) }}</h3>
                @if($stats['count_growth_rate'] ?? 0 != 0)
                <p class="text-[10px] mt-1 flex items-center gap-1 {{ ($stats['count_growth_rate'] ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                    <i class="fas fa-arrow-{{ ($stats['count_growth_rate'] ?? 0) > 0 ? 'up' : 'down' }}"></i>
                    {{ abs($stats['count_growth_rate']) }}% from previous period
                </p>
                @endif
            </div>
        </div>

        <!-- Live ClickPesa account balance -->
        <div class="card p-5 flex items-center gap-4" id="accountBalanceCard">
            <div class="w-12 h-12 rounded-2xl bg-primary-600 flex items-center justify-center text-white shadow-lg shadow-primary-900/20">
                <i class="fas fa-wallet text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-widest text-primary-500">Total Revenue</p>
                <p class="text-[9px] text-primary-400 normal-case">Live ClickPesa balance (TZS)</p>
                <h3 class="text-xl font-black text-primary-900 dark:text-white mt-1" id="accountBalanceAmount">
                    <span class="text-xs font-bold text-primary-500">TZS</span>
                    <span id="accountBalanceValue">{{ number_format($accountBalance['balance'] ?? 0, 0) }}</span>
                </h3>
                <p class="text-[9px] text-primary-400 mt-1" id="accountBalanceSynced">
                    @if(!empty($accountBalance['synced_at']))
                    <i class="fas fa-circle text-[6px] {{ ($accountBalance['live'] ?? false) ? 'text-green-500' : 'text-amber-500' }} me-1"></i>
                    Updated {{ $accountBalance['synced_at']->diffForHumans() }}
                    @else
                    Balance not synced yet
                    @endif
                </p>
            </div>
        </div>

        <!-- Success rate for period -->
        <div class="card p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600">
                <i class="fas fa-chart-pie text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Success Rate</p>
                <p class="text-[9px] text-primary-400 normal-case">For {{ strtolower($periodLabel ?? 'today') }}</p>
                <h3 class="text-2xl font-black text-primary-900 dark:text-white mt-1">{{ $stats['success_rate'] ?? 0 }}%</h3>
            </div>
        </div>
    </div>

    <!-- Average Transaction Value -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-yellow-50 dark:bg-yellow-900/20 flex items-center justify-center text-yellow-600">
                <i class="fas fa-coins text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Average Transaction Value</p>
                <h3 class="text-2xl font-black text-primary-900 dark:text-white mt-1">
                    <span class="text-xs font-bold text-primary-500">TZS</span> {{ number_format($stats['average_transaction_value'] ?? 0, 0) }}
                </h3>
            </div>
        </div>
    </div>

    <!-- AI Insights Section -->
    <div class="card p-5">
        <h3 class="text-sm font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
            <i class="fas fa-robot"></i> AI Insights & Recommendations
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-primary-700">Key Insights</h4>
                @foreach($aiInsights['insights'] ?? [] as $insight)
                <div class="p-3 rounded-lg {{ $insight['bg'] ?? 'bg-blue-50' }} border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fas {{ $insight['icon'] ?? 'fa-info-circle' }} {{ $insight['color'] ?? 'text-blue-600' }}"></i>
                        <span class="text-xs font-bold text-primary-900 dark:text-white">{{ $insight['title'] ?? '' }}</span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ $insight['message'] ?? '' }}</p>
                </div>
                @endforeach
            </div>
            <div>
                <h4 class="text-xs font-bold text-primary-700 mb-3">Recommendations</h4>
                <ul class="space-y-2">
                    @foreach($aiInsights['recommendations'] ?? [] as $rec)
                    <li class="flex items-start gap-2 text-xs text-gray-700 dark:text-gray-300">
                        <i class="fas fa-lightbulb text-yellow-500 mt-0.5"></i>
                        {{ $rec }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Transactions Chart -->
        <div class="card p-5">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
                <i class="fas fa-chart-bar"></i> Settled Payments ({{ $periodLabel }})
            </h3>
            <div class="h-64">
                <canvas id="dailyTransactionsChart"></canvas>
            </div>
        </div>

        <!-- Payment Methods Pie Chart -->
        <div class="card p-5">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
                <i class="fas fa-chart-pie"></i> Payment Methods
            </h3>
            <div class="h-64">
                <canvas id="paymentMethodsChart"></canvas>
            </div>
        </div>

        <!-- Monthly Comparison Chart -->
        <div class="card p-5">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
                <i class="fas fa-chart-line"></i> Monthly Comparison
            </h3>
            <div class="h-64">
                <canvas id="monthlyComparisonChart"></canvas>
            </div>
        </div>

        <!-- Status Distribution Chart -->
        <div class="card p-5">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
                <i class="fas fa-chart-doughnut"></i> Transaction Status Distribution
            </h3>
            <div class="h-64">
                <canvas id="statusDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Purposes -->
    @if(!empty($stats['top_purposes']))
    <div class="card p-5">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
            <i class="fas fa-star"></i> Top Payment Purposes
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($stats['top_purposes'] as $purpose)
            <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-bold text-primary-900 dark:text-white truncate max-w-[150px]">{{ $purpose['description'] }}</span>
                </div>
                <div class="text-lg font-black text-primary-600">TZS {{ number_format($purpose['total_amount'], 0) }}</div>
                <div class="text-[10px] text-gray-500">{{ $purpose['count'] }} payments</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Payments -->
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

        <!-- Right Column: Top Customers + Payment Channels -->
        <div class="space-y-4">
            <!-- Top Customers -->
            <div>
                <h3 class="font-bold text-primary-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-trophy text-orange-500"></i> Top Members
                </h3>
                <div class="card p-4 space-y-3">
                    @forelse($stats['top_customers'] ?? [] as $customer)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 font-bold text-xs uppercase">
                                {{ substr($customer['name'] ?? 'C', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-primary-900 dark:text-white truncate max-w-[120px]">{{ $customer['name'] ?? 'Member' }}</p>
                                <p class="text-[10px] text-primary-500">{{ $customer['count'] ?? 0 }} payments</p>
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
            </div>

            <!-- Payment Methods -->
            <div>
                <h3 class="font-bold text-primary-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-credit-card text-blue-500"></i> Payment Channels
                </h3>
                <div class="card p-4 space-y-3">
                    @forelse($stats['payment_methods'] ?? [] as $method)
                    <div>
                        <div class="flex justify-between text-[10px] font-bold uppercase mb-1">
                            <span class="text-primary-700 dark:text-primary-300">{{ $method['method'] ?? $method['name'] ?? 'Unknown' }}</span>
                            <span class="text-primary-500">{{ number_format($method['count'] ?? 0) }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-primary-50 dark:bg-dark-900 rounded-full overflow-hidden">
                            @php 
                            $methodCount = $method['count'] ?? 0;
                            $percent = ($stats['period_successful_count'] ?? 0) > 0 ? ($methodCount / $stats['period_successful_count'] * 100) : 0;
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
</div>

<!-- html2canvas for image export -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script>
    // Auto-refresh live account balance from ClickPesa API
    function refreshAccountBalance() {
        fetch('{{ route('dashboard.account-balance') }}', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(payload => {
            if (!payload.success || !payload.data) return;
            const balance = payload.data.balance ?? 0;
            const valueEl = document.getElementById('accountBalanceValue');
            const syncedEl = document.getElementById('accountBalanceSynced');
            if (valueEl) {
                valueEl.textContent = new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(balance);
            }
            if (syncedEl) {
                const live = payload.data.live ? 'text-green-500' : 'text-amber-500';
                syncedEl.innerHTML = '<i class="fas fa-circle text-[6px] ' + live + ' me-1"></i>Updated just now';
            }
        })
        .catch(() => {});
    }
    refreshAccountBalance();
    setInterval(refreshAccountBalance, 60000);

    // Auto-sync transactions every second
    function syncTransactions() {
        fetch('{{ route('dashboard.sync-transactions') }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .catch(() => {});
    }

    // Auto-sync bills every second
    function syncBills() {
        fetch('{{ route('dashboard.sync-bills') }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .catch(() => {});
    }

    // Initial sync
    syncTransactions();
    syncBills();

    // Auto-sync every second
    setInterval(syncTransactions, 1000);
    setInterval(syncBills, 1000);

    // Export to image
    document.getElementById('exportImageBtn').addEventListener('click', function() {
        const element = document.getElementById('dashboard-content');
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Exporting...';
        this.disabled = true;
        
        html2canvas(document.body, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'dashboard-report-{{ $dateFilter }}-' + new Date().toISOString().slice(0,10) + '.png';
            link.href = canvas.toDataURL();
            link.click();
            
            this.innerHTML = '<i class="fas fa-image mr-1"></i> Export Image';
            this.disabled = false;
        }).catch(() => {
            this.innerHTML = '<i class="fas fa-image mr-1"></i> Export Image';
            this.disabled = false;
        });
    });

    // Daily Transactions Chart
    const dailyLabels = @json(array_column($stats['daily_stats'] ?? [], 'date'));
    const dailyData = @json(array_column($stats['daily_stats'] ?? [], 'amount'));
    const dailyCounts = @json(array_column($stats['daily_stats'] ?? [], 'count'));
    
    const dailyCtx = document.getElementById('dailyTransactionsChart');
    new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: dailyLabels,
            datasets: [
                {
                    label: 'Settled Amount (TZS)',
                    data: dailyData,
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Settled Count',
                    data: dailyCounts,
                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Amount'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'Count'
                    }
                }
            }
        }
    });

    // Payment Methods Pie Chart
    const methodNames = @json(array_column($stats['payment_methods'] ?? [], 'name'));
    const methodCounts = @json(array_column($stats['payment_methods'] ?? [], 'count'));
    const colors = [
        'rgba(34, 197, 94, 0.8)',
        'rgba(59, 130, 246, 0.8)',
        'rgba(245, 158, 11, 0.8)',
        'rgba(239, 68, 68, 0.8)',
        'rgba(168, 85, 247, 0.8)'
    ];
    
    const methodsCtx = document.getElementById('paymentMethodsChart');
    new Chart(methodsCtx, {
        type: 'pie',
        data: {
            labels: methodNames,
            datasets: [{
                data: methodCounts,
                backgroundColor: colors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Monthly Comparison Chart
    const monthlyLabels = @json(array_column($stats['monthly_stats'] ?? [], 'month'));
    const monthlyAmounts = @json(array_column($stats['monthly_stats'] ?? [], 'amount'));
    const monthlyCounts = @json(array_column($stats['monthly_stats'] ?? [], 'count'));
    
    const monthlyCtx = document.getElementById('monthlyComparisonChart');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [
                {
                    label: 'Amount (TZS)',
                    data: monthlyAmounts,
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y'
                },
                {
                    label: 'Count',
                    data: monthlyCounts,
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Amount'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'Count'
                    }
                }
            }
        }
    });

    // Status Distribution Chart
    const statusLabels = @json(array_column($stats['status_stats'] ?? [], 'status'));
    const statusCounts = @json(array_column($stats['status_stats'] ?? [], 'count'));
    const statusColors = [
        'rgba(34, 197, 94, 0.8)',
        'rgba(245, 158, 11, 0.8)',
        'rgba(239, 68, 68, 0.8)',
        'rgba(168, 85, 247, 0.8)'
    ];
    
    const statusCtx = document.getElementById('statusDistributionChart');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: statusColors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // AI Chat Functionality
    let chatHistory = [];
    
    function openChat() {
        document.getElementById('chatModal').classList.remove('hidden');
        document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
    }
    
    function closeChat() {
        document.getElementById('chatModal').classList.add('hidden');
    }
    
    async function sendMessage() {
        const messageInput = document.getElementById('chatInput');
        const message = messageInput.value.trim();
        if (!message) return;
        
        messageInput.value = '';
        
        // Add user message to UI
        addMessageToChat('user', message);
        chatHistory.push({role: 'user', text: message});
        
        // Show loading indicator
        const loadingMessage = addLoadingMessage();
        
        try {
            const response = await fetch('{{ route('dashboard.ai-chat') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    message: message,
                    history: chatHistory
                })
            });
            
            const data = await response.json();
            
            // Remove loading indicator
            loadingMessage.remove();
            
            if (data.success) {
                addMessageToChat('model', data.response);
                chatHistory.push({role: 'model', text: data.response});
            } else {
                addMessageToChat('model', 'Error: ' + (data.message || 'Something went wrong'));
            }
        } catch (error) {
            loadingMessage.remove();
            addMessageToChat('model', 'Error: ' + error.message);
        }
    }
    
    function addMessageToChat(role, text) {
        const chatMessages = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'p-4 rounded-lg mb-3 ' + 
            (role === 'user' 
                ? 'bg-green-100 text-gray-800 ml-auto max-w-[80%]' 
                : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-white max-w-[80%]');
        
        const formattedText = text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
        
        messageDiv.innerHTML = formattedText;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        return messageDiv;
    }
    
    function addLoadingMessage() {
        const chatMessages = document.getElementById('chatMessages');
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'p-4 rounded-lg mb-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-white max-w-[80%]';
        loadingDiv.innerHTML = '<div class="flex space-x-1"><span class="w-2 h-2 bg-green-600 rounded-full animate-bounce" style="animation-delay: 0s;"></span><span class="w-2 h-2 bg-green-600 rounded-full animate-bounce" style="animation-delay: 0.2s;"></span><span class="w-2 h-2 bg-green-600 rounded-full animate-bounce" style="animation-delay: 0.4s;"></span></div>';
        chatMessages.appendChild(loadingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        return loadingDiv;
    }
    
    // Handle enter key on chat input
    document.getElementById('chatInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
</script>

<!-- Floating AI Chat Button -->
<button 
    onclick="openChat()"
    class="fixed bottom-8 right-8 z-50 w-14 h-14 bg-gradient-to-r from-green-600 to-green-500 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110 active:scale-95 flex items-center justify-center"
>
    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
    </svg>
</button>

<!-- AI Chat Modal -->
<div id="chatModal" class="fixed bottom-24 right-8 z-40 w-96 max-w-full bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-green-500 text-white p-5 rounded-t-2xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">AI Assistant</h3>
                    <p class="text-sm text-green-100">Powered by Google Gemini</p>
                </div>
            </div>
            <button 
                onclick="closeChat()"
                class="text-white/80 hover:text-white transition-colors"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Messages -->
    <div 
        id="chatMessages" 
        class="p-5 h-80 overflow-y-auto bg-gray-50 dark:bg-gray-900"
    >
        <div class="p-4 rounded-lg mb-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-white max-w-[80%]">
            <p>Hi there! How can I help you with your payments and transactions today?</p>
        </div>
    </div>

    <!-- Input Area -->
    <div class="p-5 border-t border-gray-200 dark:border-gray-700">
        <div class="flex gap-3">
            <input 
                id="chatInput" 
                type="text" 
                placeholder="Ask me anything..."
                class="flex-1 px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500"
            >
            <button 
                onclick="sendMessage()"
                class="px-5 py-3 bg-gradient-to-r from-green-600 to-green-500 text-white rounded-xl hover:from-green-500 hover:to-green-400 transition-all font-semibold"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </div>
    </div>
</div>
@endsection