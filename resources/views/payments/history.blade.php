@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="font-size: 1.5rem;">
                    <i class="fas fa-history me-2"></i>
                    Payment History
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Payment History</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3 class="mb-0">{{ number_format($totalCount ?? 0) }}</h3>
                                <p class="mb-0">Total Transactions</p>
                            </div>
                            <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-circle">
                                <i class="fas fa-receipt text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3 class="mb-0">{{ number_format($successCount ?? 0) }}</h3>
                                <p class="mb-0">Successful</p>
                            </div>
                            <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-circle">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient-warning text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3 class="mb-0">{{ number_format($pendingCount ?? 0) }}</h3>
                                <p class="mb-0">Pending</p>
                            </div>
                            <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-circle">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient-danger text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3 class="mb-0">{{ number_format($failedCount ?? 0) }}</h3>
                                <p class="mb-0">Failed</p>
                            </div>
                            <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-circle">
                                <i class="fas fa-exclamation-triangle text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Advanced Filters
                        </h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleFilters()">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" id="filtersSection" style="display: none;">
                        <form method="GET" action="{{ route('payments.history') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-search me-1"></i>
                                    Order Reference
                                </label>
                                <input type="text" class="form-control" name="order_reference" 
                                       placeholder="Enter order reference..." 
                                       value="{{ request('order_reference') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Status
                                </label>
                                <select class="form-control" name="status">
                                    <option value="">All Status</option>
                                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>
                                        <i class="fas fa-clock me-1"></i> Pending
                                    </option>
                                    <option value="PROCESSING" {{ request('status') == 'PROCESSING' ? 'selected' : '' }}>
                                        <i class="fas fa-spinner me-1"></i> Processing
                                    </option>
                                    <option value="SUCCESS" {{ request('status') == 'SUCCESS' ? 'selected' : '' }}>
                                        <i class="fas fa-check-circle me-1"></i> Success
                                    </option>
                                    <option value="SETTLED" {{ request('status') == 'SETTLED' ? 'selected' : '' }}>
                                        <i class="fas fa-check-double me-1"></i> Settled
                                    </option>
                                    <option value="FAILED" {{ request('status') == 'FAILED' ? 'selected' : '' }}>
                                        <i class="fas fa-exclamation-triangle me-1"></i> Failed
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    Currency
                                </label>
                                <select class="form-control" name="currency">
                                    <option value="">All Currencies</option>
                                    <option value="TZS" {{ request('currency') == 'TZS' ? 'selected' : '' }}>
                                        <i class="fas fa-shilling-sign me-1"></i> TZS
                                    </option>
                                    <option value="USD" {{ request('currency') == 'USD' ? 'selected' : '' }}>
                                        <i class="fas fa-dollar-sign me-1"></i> USD
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar me-1"></i>
                                    Payment Method
                                </label>
                                <select class="form-control" name="payment_method">
                                    <option value="">All Methods</option>
                                    <option value="halopesa" {{ request('payment_method') == 'halopesa' ? 'selected' : '' }}>
                                        <i class="fas fa-mobile-alt me-1"></i> Halopesa
                                    </option>
                                    <option value="tigopesa" {{ request('payment_method') == 'tigopesa' ? 'selected' : '' }}>
                                        <i class="fas fa-mobile-alt me-1"></i> Tigo Pesa
                                    </option>
                                    <option value="airtelmoney" {{ request('payment_method') == 'airtelmoney' ? 'selected' : '' }}>
                                        <i class="fas fa-mobile-alt me-1"></i> Airtel Money
                                    </option>
                                    <option value="mpesa" {{ request('payment_method') == 'mpesa' ? 'selected' : '' }}>
                                        <i class="fas fa-mobile-alt me-1"></i> M-Pesa
                                    </option>
                                    <option value="ezypesa" {{ request('payment_method') == 'ezypesa' ? 'selected' : '' }}>
                                        <i class="fas fa-mobile-alt me-1"></i> Ezy Pesa
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Start Date
                                </label>
                                <input type="date" class="form-control" name="start_date" 
                                       value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    End Date
                                </label>
                                <input type="date" class="form-control" name="end_date" 
                                       value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-12">
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>
                                        Apply Filters
                                    </button>
                                    <a href="{{ route('payments.history') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        Clear All
                                    </a>
                                    <button type="button" class="btn btn-outline-info" onclick="exportData()">
                                        <i class="fas fa-download me-1"></i>
                                        Export
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-2 col-sm-4 col-6">
                                <a href="{{ route('payments.create') }}" class="btn btn-primary w-100">
                                    <i class="fas fa-plus me-2"></i>
                                    New Payment
                                </a>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6">
                                <a href="{{ route('payments.export.pdf') }}" class="btn btn-danger w-100">
                                    <i class="fas fa-file-pdf me-2"></i>
                                    Export PDF
                                </a>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6">
                                <a href="{{ route('payments.export.excel') }}" class="btn btn-success w-100">
                                    <i class="fas fa-file-excel me-2"></i>
                                    Export Excel
                                </a>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6">
                                <button type="button" class="btn btn-outline-info w-100" onclick="refreshData()">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    Refresh
                                </button>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6">
                                <button type="button" class="btn btn-outline-warning w-100" onclick="printData()">
                                    <i class="fas fa-print me-2"></i>
                                    Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Filters Display -->
        @if(request()->hasAny(['status', 'currency', 'order_reference', 'start_date', 'end_date', 'payment_method']))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info alert-dismissible">
                        <i class="fas fa-filter me-2"></i>
                        <strong>Active Filters:</strong>
                        @if(request()->filled('status')) Status: {{ request('status') }} @endif
                        @if(request()->filled('currency')) Currency: {{ request('currency') }} @endif
                        @if(request()->filled('order_reference')) Reference: {{ request('order_reference') }} @endif
                        @if(request()->filled('payment_method')) Method: {{ request('payment_method') }} @endif
                        @if(request()->filled('start_date')) From: {{ request('start_date') }} @endif
                        @if(request()->filled('end_date')) To: {{ request('end_date') }} @endif
                        <a href="{{ route('payments.history') }}" class="btn btn-sm btn-outline-secondary float-end">
                            <i class="fas fa-times me-1"></i>
                            Clear Filters
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Payment Transactions Table -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>
                            Payment Transactions
                            <span class="badge bg-primary float-end">{{ count($payments ?? 0) }} Records</span>
                        </h5>
                        <div class="card-tools">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleTableView()">
                                    <i class="fas fa-th me-1"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleCardView()">
                                    <i class="fas fa-th-large me-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($error)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ $error }}
                            </div>
                        @endif
                        
                        @if(empty($payments))
                            <div class="text-center py-5">
                                <div class="avatar avatar-xl bg-light rounded-circle mx-auto mb-3">
                                    <i class="fas fa-search fs-1 text-muted"></i>
                                </div>
                                <h3 class="text-muted">No Payment Transactions Found</h3>
                                <p class="text-muted mb-4">No payment transactions match your current filters.</p>
                                <a href="{{ route('payments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>
                                    Create First Payment
                                </a>
                            </div>
                        @else
                            <!-- Table View -->
                            <div id="tableView">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped" id="paymentsTable">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                                </th>
                                                <th>Order Reference</th>
                                                <th>Transaction ID</th>
                                                <th>Customer</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Payment Method</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($payments as $payment)
                                                <tr class="payment-row" data-status="{{ $payment['status'] ?? '' }}">
                                                    <td>
                                                        <input type="checkbox" class="row-checkbox" value="{{ $payment['orderReference'] ?? '' }}">
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('payments.status', ['reference' => $payment['orderReference'] ?? '']) }}" 
                                                           class="text-decoration-none fw-bold">
                                                            {{ $payment['orderReference'] ?? 'N/A' }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="text-monospace">{{ $payment['transactionId'] ?? $payment['id'] ?? 'N/A' }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm bg-light rounded-circle me-2">
                                                                <i class="fas fa-user text-muted fs-xs"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold">{{ $payment['customerName'] ?? $payment['payer_name'] ?? 'N/A' }}</div>
                                                                <small class="text-muted">{{ $payment['paymentPhoneNumber'] ?? $payment['customer']['customerPhoneNumber'] ?? 'N/A' }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold">{{ number_format($payment['collectedAmount'] ?? $payment['amount'] ?? 0, 2) }}</span>
                                                        <small class="text-muted">{{ $payment['collectedCurrency'] ?? $payment['currency'] ?? 'TZS' }}</small>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusColor = match($payment['status'] ?? '') {
                                                                'SUCCESS', 'SETTLED' => 'success',
                                                                'PROCESSING', 'PENDING' => 'warning',
                                                                'FAILED' => 'danger',
                                                                default => 'secondary'
                                                            };
                                                        @endphp
                                                        <span class="badge bg-label-{{ $statusColor }} fs-6">
                                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                            {{ $payment['status'] ?? 'UNKNOWN' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="payment-method-icon me-2">
                                                                @php
                                                                    $methodIcon = match(strtolower($payment['paymentMethod'] ?? $payment['channel'] ?? $payment['source'] ?? $payment['provider'] ?? '')) {
                                                                        'halopesa' => 'fa-mobile-alt',
                                                                        'tigopesa' => 'fa-mobile-alt',
                                                                        'airtelmoney' => 'fa-mobile-alt',
                                                                        'mpesa' => 'fa-mobile-alt',
                                                                        'ezypesa' => 'fa-mobile-alt',
                                                                        default => 'fa-credit-card'
                                                                    };
                                                                @endphp
                                                                <i class="fas {{ $methodIcon }} text-muted"></i>
                                                            </div>
                                                            <span>{{ $payment['paymentMethod'] ?? $payment['channel'] ?? $payment['source'] ?? $payment['provider'] ?? 'N/A' }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-muted">
                                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($payment['createdAt'] ?? 'now')->format('M d, Y') }}</div>
                                                            <small>{{ \Carbon\Carbon::parse($payment['createdAt'] ?? 'now')->format('H:i A') }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="{{ route('payments.status', ['reference' => $payment['orderReference'] ?? '']) }}" 
                                                               class="btn btn-sm btn-info" title="View Status">
                                                                View
                                                            </a>
                                                            @if(in_array($payment['status'] ?? '', ['SUCCESS', 'SETTLED']))
                                                                <a href="{{ route('payments.receipt', $payment['orderReference'] ?? '') }}" 
                                                                   class="btn btn-sm btn-success" title="Download Receipt" target="_blank">
                                                                    Receipt
                                                                </a>
                                                            @endif
                                                            <a href="{{ route('payments.export.pdf') }}?order_reference={{ $payment['orderReference'] ?? '' }}" 
                                                               class="btn btn-sm btn-danger" title="Export to PDF">
                                                                PDF
                                                            </a>
                                                            <a href="{{ route('payments.export.excel') }}?order_reference={{ $payment['orderReference'] ?? '' }}" 
                                                               class="btn btn-sm btn-outline-success" title="Export to Excel">
                                                                Excel
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Card View (Hidden by default) -->
                            <div id="cardView" style="display: none;">
                                <div class="row g-3" id="cardContainer">
                                    @foreach($payments as $payment)
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="card shadow-sm payment-card" data-status="{{ $payment['status'] ?? '' }}">
                                                <div class="card-header bg-light">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h6 class="card-title mb-0">
                                                            {{ $payment['orderReference'] ?? 'N/A' }}
                                                        </h6>
                                                        @php
                                                            $statusColor = match($payment['status'] ?? '') {
                                                                'SUCCESS', 'SETTLED' => 'success',
                                                                'PROCESSING', 'PENDING' => 'warning',
                                                                'FAILED' => 'danger',
                                                                default => 'secondary'
                                                            };
                                                        @endphp
                                                        <span class="badge bg-label-{{ $statusColor }}">
                                                            {{ $payment['status'] ?? 'UNKNOWN' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="avatar avatar-lg bg-light rounded-circle me-3">
                                                            @php
                                                                $methodIcon = match(strtolower($payment['paymentMethod'] ?? $payment['channel'] ?? $payment['source'] ?? $payment['provider'] ?? '')) {
                                                                    'halopesa' => 'fa-mobile-alt',
                                                                    'tigopesa' => 'fa-mobile-alt',
                                                                    'airtelmoney' => 'fa-mobile-alt',
                                                                    'mpesa' => 'fa-mobile-alt',
                                                                    'ezypesa' => 'fa-mobile-alt',
                                                                    default => 'fa-credit-card'
                                                                };
                                                            @endphp
                                                            <i class="fas {{ $methodIcon }} text-muted"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h5 class="mb-1">{{ number_format($payment['collectedAmount'] ?? $payment['amount'] ?? 0, 2) }} {{ $payment['collectedCurrency'] ?? $payment['currency'] ?? 'TZS' }}</h5>
                                                            <p class="text-muted mb-1">{{ $payment['customerName'] ?? $payment['payer_name'] ?? 'N/A' }}</p>
                                                            <small class="text-muted">{{ $payment['paymentPhoneNumber'] ?? $payment['customer']['customerPhoneNumber'] ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="text-center">
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            {{ \Carbon\Carbon::parse($payment['createdAt'] ?? 'now')->format('M d, Y H:i') }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-light">
                                                    <div class="btn-group w-100">
                                                        <a href="{{ route('payments.status', ['reference' => $payment['orderReference'] ?? '']) }}" 
                                                           class="btn btn-sm btn-info">
                                                            View Details
                                                        </a>
                                                        @if(in_array($payment['status'] ?? '', ['SUCCESS', 'SETTLED']))
                                                            <a href="{{ route('payments.receipt', $payment['orderReference'] ?? '') }}" 
                                                               class="btn btn-sm btn-success" target="_blank">
                                                                Receipt
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Pagination -->
        @if(!empty($payments) && $totalCount > 20)
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="dataTables_info">
                                    <strong>Showing {{ min(20, $totalCount + ((request()->get('page', 1) - 1) * 20)) }} to {{ min(20, $totalCount + ((request()->get('page', 1) - 1) * 20)) }} of {{ $totalCount }} entries</strong>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="me-2">Page {{ request()->get('page', 1) }} of {{ $totalPages = ceil($totalCount / 20) }}</span>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" onclick="goToPage(1)" {{ request()->get('page', 1) == 1 ? 'disabled' : '' }}>
                                            <i class="fas fa-angle-double-left"></i> First
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="goToPage({{ max(1, request()->get('page', 1) - 1) }})" {{ request()->get('page', 1) <= 1 ? 'disabled' : '' }}>
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" onclick="goToPage({{ min($totalPages, request()->get('page', 1) + 1) }})" {{ request()->get('page', 1) >= $totalPages ? 'disabled' : '' }}>
                                            Next <i class="fas fa-chevron-right"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="goToPage({{ $totalPages }})" {{ request()->get('page', 1) == $totalPages ? 'disabled' : '' }}>
                                            Last <i class="fas fa-angle-double-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Page Number Navigation -->
                            <div class="d-flex justify-content-center">
                                <ul class="pagination">
                                    @php
                                        $currentPage = request()->get('page', 1);
                                        $totalPages = ceil($totalCount / 20);
                                        $startPage = max(1, $currentPage - 3);
                                        $endPage = min($totalPages, $currentPage + 3);
                                    @endphp
                                    
                                    <!-- First page if not visible -->
                                    @if($startPage > 1)
                                        <li class="paginate_button page-item">
                                            <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}" class="page-link">1</a>
                                        </li>
                                        @if($startPage > 2)
                                            <li class="paginate_button page-item disabled">
                                                <a href="#" class="page-link">...</a>
                                            </li>
                                        @endif
                                    @endif
                                    
                                    <!-- Visible page range -->
                                    @for($i = $startPage; $i <= $endPage; $i++)
                                        <li class="paginate_button page-item {{ $i == $currentPage ? 'active' : '' }}">
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" class="page-link">{{ $i }}</a>
                                        </li>
                                    @endfor
                                    
                                    <!-- Last page if not visible -->
                                    @if($endPage < $totalPages)
                                        @if($endPage < $totalPages - 1)
                                            <li class="paginate_button page-item disabled">
                                                <a href="#" class="page-link">...</a>
                                            </li>
                                        @endif
                                        <li class="paginate_button page-item">
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $totalPages]) }}" class="page-link">{{ $totalPages }}</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            
                            <!-- Quick Jump to Page -->
                            <div class="d-flex justify-content-center align-items-center mt-3">
                                <span class="me-2">Go to page:</span>
                                <div class="input-group" style="max-width: 200px;">
                                    <input type="number" class="form-control" id="pageJumpInput" min="1" max="{{ $totalPages }}" value="{{ request()->get('page', 1) }}">
                                    <button type="button" class="btn btn-primary" onclick="jumpToPage()">
                                        Go
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
}

.payment-card {
    transition: transform 0.2s ease-in-out;
}

.payment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.payment-row {
    transition: background-color 0.3s ease;
}

.payment-row:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.table th {
    border-top: none;
    background-color: #343a40;
    color: white;
    font-weight: 600;
}

.table td {
    vertical-align: middle;
}

.pagination .page-link {
    color: #007bff;
    border: 1px solid #dee2e6;
}

.pagination .page-link:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.payment-method-icon {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    border-radius: 50%;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function toggleFilters() {
    const filtersSection = document.getElementById('filtersSection');
    if (filtersSection.style.display === 'none') {
        filtersSection.style.display = 'block';
    } else {
        filtersSection.style.display = 'none';
    }
}

function toggleTableView() {
    document.getElementById('tableView').style.display = 'block';
    document.getElementById('cardView').style.display = 'none';
}

function toggleCardView() {
    document.getElementById('tableView').style.display = 'none';
    document.getElementById('cardView').style.display = 'block';
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function refreshData() {
    window.location.reload();
}

function exportData() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('payments.export.excel') }}';
    form.target = '_blank';
    
    const filters = new URLSearchParams(window.location.search);
    filters.forEach((value, key) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function printData() {
    window.print();
}

// Initialize table sorting
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('paymentsTable');
    if (table) {
        // Add sorting functionality to table headers
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            if (index > 0 && index < headers.length - 1) {
                header.style.cursor = 'pointer';
                header.addEventListener('click', function() {
                    sortTable(index);
                });
            }
        });
    }
});

function sortTable(columnIndex) {
    const table = document.getElementById('paymentsTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const isAscending = table.getAttribute('data-sort-order') !== 'asc';
    
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        if (columnIndex === 2) { // Amount column
            return parseFloat(aValue.replace(/[^0-9.-]+/g, '')) - parseFloat(bValue.replace(/[^0-9.-]+/g, ''));
        }
        
        return aValue.localeCompare(bValue);
    });
    
    // Clear existing rows
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }
    
    // Add sorted rows
    rows.forEach(row => tbody.appendChild(row));
    
    // Toggle sort order
    table.setAttribute('data-sort-order', isAscending ? 'asc' : 'desc');
}

// Enhanced pagination functions
function goToPage(page) {
    if (page < 1) return;
    
    const totalPages = {{ $totalPages ?? 1 }};
    if (page > totalPages) return;
    
    const url = new URL(window.location);
    url.searchParams.set('page', page);
    window.location.href = url.toString();
}

function jumpToPage() {
    const input = document.getElementById('pageJumpInput');
    const page = parseInt(input.value);
    
    if (isNaN(page) || page < 1) {
        alert('Please enter a valid page number');
        input.value = '{{ request()->get('page', 1) }}';
        return;
    }
    
    const totalPages = {{ $totalPages ?? 1 }};
    if (page > totalPages) {
        alert(`Page ${page} does not exist. Maximum page is ${totalPages}`);
        input.value = '{{ request()->get('page', 1) }}';
        return;
    }
    
    goToPage(page);
}

// Handle Enter key in page jump input
document.addEventListener('DOMContentLoaded', function() {
    const pageJumpInput = document.getElementById('pageJumpInput');
    if (pageJumpInput) {
        pageJumpInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                jumpToPage();
            }
        });
    }
});
</script>
@endpush

@endsection
