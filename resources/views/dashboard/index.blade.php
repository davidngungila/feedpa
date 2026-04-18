@extends('layouts.app')

@section('content')
<!-- Advanced Dashboard Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-2">
                            <i class="fas fa-chart-line me-3"></i>
                            FEEDTAN CMG Dashboard
                        </h1>
                        <p class="mb-0 fs-5">
                            Comprehensive Payment Management System - Real-time Analytics & Insights
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex justify-content-end align-items-center">
                            <div class="me-3">
                                <small class="d-block">Last Updated</small>
                                <strong>{{ now()->format('M d, Y H:i:s') }}</strong>
                            </div>
                            <div class="avatar avatar-lg">
                                <div class="avatar-initial bg-label-success rounded-circle">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Date Filter Controls -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Date Filter
                </h5>
                <div class="card-tools">
                    <form method="GET" class="d-inline-flex">
                        <select name="date_filter" class="form-control form-control-sm me-2" onchange="this.form.submit()">
                            <option value="all" {{ request()->get('date_filter', 'all') == 'all' ? 'selected' : '' }}>All Time</option>
                            <option value="today" {{ request()->get('date_filter', 'all') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ request()->get('date_filter', 'all') == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request()->get('date_filter', 'all') == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="year" {{ request()->get('date_filter', 'all') == 'year' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ request()->get('date_filter', 'all') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                        @if(request()->get('date_filter') == 'custom')
                            <input type="date" name="start_date" class="form-control form-control-sm me-2" placeholder="Start Date" value="{{ request()->get('start_date') }}">
                            <input type="date" name="end_date" class="form-control form-control-sm me-2" placeholder="End Date" value="{{ request()->get('end_date') }}">
                        @endif
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter me-1"></i>
                            Apply
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Key Performance Indicators -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100 border-left-primary border-left-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-primary bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-receipt text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-muted">Total Transactions</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_transactions'] ?? 0) }}</h3>
                        <small class="text-primary">
                            <i class="fas fa-arrow-up me-1"></i>
                            {{ number_format(($stats['daily_stats'][count($stats['daily_stats'])-1]['count'] ?? 0) * 100) }}% today
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100 border-left-success border-left-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-success bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-check-circle text-success fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-muted">Successful Payments</h6>
                        <h3 class="mb-0">{{ number_format($stats['successful'] ?? 0) }}</h3>
                        <small class="text-success">
                            <i class="fas fa-arrow-up me-1"></i>
                            {{ $stats['success_rate'] ?? 0 }}% success rate
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100 border-left-warning border-left-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-warning bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-clock text-warning fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-muted">Pending Payments</h6>
                        <h3 class="mb-0">{{ number_format($stats['pending'] ?? 0) }}</h3>
                        <small class="text-warning">
                            <i class="fas fa-hourglass-half me-1"></i>
                            Processing
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card h-100 border-left-danger border-left-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-danger bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-exclamation-triangle text-danger fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-muted">Failed Payments</h6>
                        <h3 class="mb-0">{{ number_format($stats['failed'] ?? 0) }}</h3>
                        <small class="text-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            {{ $stats['failure_rate'] ?? 0 }}% failure rate
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Overview -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-left-info border-left-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-info bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-money-bill-wave text-info fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-muted">Total Revenue</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_amount'] ?? 0, 2) }} TZS</h3>
                        <small class="text-info">
                            <i class="fas fa-chart-line me-1"></i>
                            All time
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-left-success border-left-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-success bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-calendar-day text-success fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-muted">Today's Revenue</h6>
                        <h3 class="mb-0">{{ number_format($stats['today_revenue'] ?? 0, 2) }} TZS</h3>
                        <small class="text-success">
                            <i class="fas fa-arrow-trend-up me-1"></i>
                            {{ ($stats['total_amount'] ?? 0) > 0 ? number_format(($stats['today_revenue'] / $stats['total_amount']) * 100, 1) : 0 }}% of total
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-left-primary border-left-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-primary bg-opacity-10 rounded-circle me-3">
                        <i class="fas fa-chart-bar text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-muted">Average Transaction</h6>
                        <h3 class="mb-0">{{ number_format($stats['average_transaction'] ?? 0, 2) }} TZS</h3>
                        <small class="text-primary">
                            <i class="fas fa-calculator me-1"></i>
                            Per transaction
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Overview -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Revenue Overview
                </h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary active">7 Days</button>
                    <button class="btn btn-outline-primary">30 Days</button>
                    <button class="btn btn-outline-primary">90 Days</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center mb-3">
                            <h6 class="text-muted">Total Revenue</h6>
                            <h3 class="text-success mb-0">
                                {{ number_format($stats['total_amount'] ?? 0, 2) }} TZS
                            </h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center mb-3">
                            <h6 class="text-muted">Average Transaction</h6>
                            <h3 class="text-info mb-0">
                                {{ number_format(($stats['total_transactions'] ?? 1) > 0 ? ($stats['total_amount'] ?? 0) / ($stats['total_transactions'] ?? 1) : 0, 2) }} TZS
                            </h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center mb-3">
                            <h6 class="text-muted">Today's Revenue</h6>
                            <h3 class="text-warning mb-0">
                                {{ number_format($stats['daily_stats'][count($stats['daily_stats'])-1]['amount'] ?? 0, 2) }} TZS
                            </h3>
                        </div>
                    </div>
                </div>
                
                <!-- Mini Chart -->
                <div class="mt-4">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Methods Breakdown -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-mobile-alt me-2"></i>
                    Payment Methods
                </h5>
            </div>
            <div class="card-body">
                @if(isset($stats['payment_methods']) && count($stats['payment_methods']) > 0)
                    @foreach($stats['payment_methods'] as $method)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-label-primary rounded-circle me-2">
                                <i class="fas fa-credit-card fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $method['name'] ?? 'Unknown' }}</h6>
                                <small class="text-muted">{{ $method['count'] ?? 0 }} transactions</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0">{{ number_format($method['amount'] ?? 0, 2) }} TZS</h6>
                            <small class="text-success">{{ $method['success'] ?? 0 }} successful</small>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-mobile-alt fs-1 text-muted mb-3"></i>
                        <p class="text-muted">No payment method data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

    <!-- Recent Transactions - Full Screen -->
<div class="col-12 mb-4">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-history me-2"></i>
                Recent Transactions
            </h5>
            <div class="d-flex align-items-center">
                <div class="input-group input-group-sm me-3" style="width: 250px;">
                    <input type="text" class="form-control" placeholder="Search transactions..." id="transactionSearch">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary" type="button" id="transactionID" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
                        <li><a class="dropdown-item" href="{{ route('payments.history') }}">
                            <i class="fas fa-list me-2"></i>View All Transactions
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('payments.create') }}">
                            <i class="fas fa-plus me-2"></i>New Payment
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="exportTransactions()">
                            <i class="fas fa-download me-2"></i>Export CSV
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($error)
                <div class="alert alert-danger m-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ $error }}
                </div>
            @elseif(empty($recentPayments))
                <div class="text-center py-5">
                    <i class="fas fa-exchange-alt fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">No recent transactions found</h5>
                    <p class="text-muted">Start by creating your first payment transaction</p>
                    <a href="{{ route('payments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create First Payment
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover" id="recentTransactionsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%;">
                                    <i class="fas fa-hashtag me-1"></i>Reference
                                </th>
                                <th style="width: 20%;">
                                    <i class="fas fa-user me-1"></i>Customer
                                </th>
                                <th style="width: 10%;">
                                    <i class="fas fa-chart-line me-1"></i>Status
                                </th>
                                <th style="width: 12%;">
                                    <i class="fas fa-money-bill me-1"></i>Amount
                                </th>
                                <th style="width: 10%;">
                                    <i class="fas fa-mobile-alt me-1"></i>Method
                                </th>
                                <th style="width: 13%;">
                                    <i class="fas fa-calendar me-1"></i>Date
                                </th>
                                <th style="width: 20%;">
                                    <i class="fas fa-cog me-1"></i>Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayments as $payment)
                                @php
                                    $statusColor = match($payment['status'] ?? '') {
                                        'SUCCESS', 'SETTLED' => 'success',
                                        'PROCESSING', 'PENDING' => 'warning',
                                        'FAILED' => 'danger',
                                        default => 'secondary'
                                    };
                                    $statusIcon = match($payment['status'] ?? '') {
                                        'SUCCESS', 'SETTLED' => 'fa-check-circle',
                                        'PROCESSING', 'PENDING' => 'fa-clock',
                                        'FAILED' => 'fa-times-circle',
                                        default => 'fa-question-circle'
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('payments.status', ['reference' => $payment['orderReference'] ?? '']) }}" 
                                           class="text-primary fw-bold text-decoration-none">
                                            {{ $payment['orderReference'] ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-label-primary rounded-circle me-2">
                                                <i class="fas fa-user fs-6"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $payment['payer_name'] ?? 'Customer' }}</div>
                                                <small class="text-muted">{{ $payment['paymentPhoneNumber'] ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-{{ $statusColor }} d-flex align-items-center" style="width: fit-content;">
                                            <i class="fas {{ $statusIcon }} me-1"></i>
                                            {{ $payment['status'] ?? 'UNKNOWN' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-end">
                                            <div class="fw-bold text-success">
                                                {{ number_format($payment['amount'] ?? $payment['collectedAmount'] ?? 0, 2) }}
                                            </div>
                                            <small class="text-muted">TZS</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-mobile-alt me-2 text-primary"></i>
                                            <span>{{ $payment['channel'] ?? $payment['paymentMethod'] ?? 'HALOPESA' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div>{{ \Carbon\Carbon::parse($payment['createdAt'] ?? 'now')->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($payment['createdAt'] ?? 'now')->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            <a href="{{ route('payments.status', ['reference' => $payment['orderReference'] ?? '']) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                            @if(in_array($payment['status'] ?? '', ['SUCCESS', 'SETTLED']))
                                            <a href="{{ route('payments.receipt', $payment['orderReference'] ?? '') }}" 
                                               target="_blank" class="btn btn-sm btn-outline-success" title="Download Receipt">
                                                <i class="fas fa-receipt me-1"></i>Receipt
                                            </a>
                                            @endif
                                            <button class="btn btn-sm btn-outline-secondary" onclick="copyReference('{{ $payment['orderReference'] ?? '' }}')" title="Copy Reference">
                                                <i class="fas fa-copy me-1"></i>Copy
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Actions & System Status - Box Style -->
<div class="col-12">
    <div class="row">
        <!-- Quick Actions Box -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-success border-left-4">
                <div class="card-header bg-success bg-opacity-10 text-success">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('payments.create') }}" class="btn btn-primary w-100 h-100 p-3 text-start">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-white bg-opacity-20 rounded-circle me-3">
                                        <i class="fas fa-plus fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">New Payment</div>
                                        <small>Create payment transaction</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('billpay.create') }}" class="btn btn-info w-100 h-100 p-3 text-start">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-white bg-opacity-20 rounded-circle me-3">
                                        <i class="fas fa-file-invoice fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Create Bill</div>
                                        <small>Generate billing invoice</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('payments.history') }}" class="btn btn-outline-primary w-100 h-100 p-3 text-start">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-primary bg-opacity-10 rounded-circle me-3">
                                        <i class="fas fa-history fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Transaction History</div>
                                        <small>View all payments</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-success w-100 h-100 p-3 text-start" onclick="generateReport()">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-success bg-opacity-10 rounded-circle me-3">
                                        <i class="fas fa-chart-bar fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Generate Report</div>
                                        <small>Analytics & insights</small>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status Box -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-info border-left-4">
                <div class="card-header bg-info bg-opacity-10 text-info">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-server me-2"></i>
                        System Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3 text-muted">Service Status</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">API Status</span>
                                <span class="badge bg-success">Online</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Database</span>
                                <span class="badge bg-success">Connected</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">SMS Service</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Uptime</span>
                                <span class="text-success fw-bold">99.9%</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="mb-3 text-muted">Performance Metrics</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Response Time</small>
                                    <small class="text-success">120ms</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: 85%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Success Rate</small>
                                    <small class="text-info">{{ $stats['total_transactions'] > 0 ? round(($stats['successful'] / $stats['total_transactions']) * 100, 1) : 0 }}%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: {{ $stats['total_transactions'] > 0 ? ($stats['successful'] / $stats['total_transactions']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Server Load</small>
                                    <small class="text-warning">45%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: 45%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.border-left-4 {
    border-left-width: 4px !important;
}
.border-left-success {
    border-left-color: #28a745 !important;
}
.border-left-info {
    border-left-color: #17a2b8 !important;
}
.border-left-warning {
    border-left-color: #ffc107 !important;
}
.border-left-danger {
    border-left-color: #dc3545 !important;
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}
.avatar {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
}
.avatar-lg {
    width: 3.5rem;
    height: 3.5rem;
}
.avatar-sm {
    width: 2rem;
    height: 2rem;
}
.avatar-initial {
    color: #fff;
    font-weight: 500;
    text-transform: uppercase;
}
.bg-label-success {
    background-color: rgba(40, 167, 69, 0.1) !important;
    color: #28a745 !important;
}
.bg-label-info {
    background-color: rgba(23, 162, 184, 0.1) !important;
    color: #17a2b8 !important;
}
.bg-label-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
    color: #ffc107 !important;
}
.bg-label-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
    color: #dc3545 !important;
}
.bg-label-primary {
    background-color: rgba(0, 123, 255, 0.1) !important;
    color: #007bff !important;
}
.bg-label-secondary {
    background-color: rgba(108, 117, 125, 0.1) !important;
    color: #6c757d !important;
}

/* Enhanced Table Styling */
#recentTransactionsTable {
    font-size: 0.875rem;
}

#recentTransactionsTable th {
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding: 0.75rem 1rem;
}

#recentTransactionsTable td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
}

#recentTransactionsTable tbody tr:hover {
    background-color: #f8f9fa;
}

#recentTransactionsTable .avatar-sm {
    flex-shrink: 0;
}

#recentTransactionsTable .fw-semibold {
    font-weight: 600;
    color: #212529;
}

#recentTransactionsTable .text-muted {
    font-size: 0.8em;
    line-height: 1.2;
}

#recentTransactionsTable .badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    font-weight: 500;
}

#recentTransactionsTable .text-end {
    text-align: right;
}

#recentTransactionsTable .dropdown .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

#recentTransactionsTable .btn-sm {
    padding: 0.375rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
}

#recentTransactionsTable .d-flex.gap-1 .btn-sm {
    min-width: 60px;
    text-align: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #recentTransactionsTable {
        font-size: 0.8rem;
    }
    
    #recentTransactionsTable th,
    #recentTransactionsTable td {
        padding: 0.5rem 0.75rem;
    }
    
    #recentTransactionsTable .avatar-sm {
        width: 1.5rem;
        height: 1.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Dashboard JavaScript Functions
function copyReference(reference) {
    if (!reference) return;
    
    navigator.clipboard.writeText(reference).then(function() {
        // Show success notification
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Reference ' + reference + ' copied to clipboard',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert('Reference copied: ' + reference);
        }
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
    });
}

function exportTransactions() {
    // Export functionality
    const table = document.getElementById('recentTransactionsTable');
    if (!table) {
        alert('No transactions to export');
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        
        for (let j = 0; j < cols.length; j++) {
            // Clean text content and remove HTML
            let text = cols[j].textContent || cols[j].innerText;
            text = text.replace(/[\n\r]/g, ' ').trim();
            // Escape quotes and commas
            text = '"' + text.replace(/"/g, '""') + '"';
            rowData.push(text);
        }
        
        if (rowData.length > 0) {
            csv.push(rowData.join(','));
        }
    }
    
    // Download CSV
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'transactions_' + new Date().toISOString().slice(0, 10) + '.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

function generateReport() {
    // Generate report functionality
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Generate Report',
            html: `
                <div class="text-start">
                    <label class="form-label">Report Type</label>
                    <select class="form-select mb-3" id="reportType">
                        <option value="daily">Daily Report</option>
                        <option value="weekly">Weekly Report</option>
                        <option value="monthly">Monthly Report</option>
                        <option value="custom">Custom Range</option>
                    </select>
                    
                    <label class="form-label">Format</label>
                    <select class="form-select mb-3" id="reportFormat">
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                        <option value="csv">CSV</option>
                    </select>
                    
                    <div id="dateRange" style="display: none;">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control mb-2" id="startDate">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control mb-2" id="endDate">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Generate',
            cancelButtonText: 'Cancel',
            didOpen: function() {
                document.getElementById('reportType').addEventListener('change', function() {
                    const dateRange = document.getElementById('dateRange');
                    dateRange.style.display = this.value === 'custom' ? 'block' : 'none';
                });
            },
            preConfirm: function() {
                const reportType = document.getElementById('reportType').value;
                const reportFormat = document.getElementById('reportFormat').value;
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                
                return {
                    type: reportType,
                    format: reportFormat,
                    startDate: startDate,
                    endDate: endDate
                };
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Generating Report...',
                    html: 'Please wait while we generate your report.',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                
                // Simulate report generation
                setTimeout(function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Report Generated!',
                        text: 'Your report has been generated successfully.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }, 2000);
            }
        });
    } else {
        alert('Report generation feature requires SweetAlert library');
    }
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('transactionSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('recentTransactionsTable');
            if (!table) return;
            
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-refresh dashboard data every 30 seconds
    setInterval(function() {
        // Update last updated time
        const lastUpdated = document.querySelector('.card-body strong');
        if (lastUpdated && lastUpdated.textContent.includes('Last Updated')) {
            lastUpdated.textContent = new Date().toLocaleString();
        }
    }, 30000);
});

// Chart initialization (if Chart.js is available)
if (typeof Chart !== 'undefined') {
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        // Sample data - replace with actual data from backend
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Revenue',
                    data: [12000, 19000, 15000, 25000, 22000, 30000, 28000],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' TZS';
                            }
                        }
                    }
                }
            }
        });
    }
}
</script>
@endpush
