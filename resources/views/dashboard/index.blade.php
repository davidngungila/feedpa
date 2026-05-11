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




    <!-- Recent Transactions with Tabs -->
<div class="row mb-4">
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
            @elseif(empty($recentPayments) && empty($successfulPayments) && empty($failedPayments))
                <div class="text-center py-5">
                    <i class="fas fa-exchange-alt fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">No recent transactions found</h5>
                    <p class="text-muted">Start by creating your first payment transaction</p>
                    <a href="{{ route('payments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create First Payment
                    </a>
                </div>
            @else
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs nav-linetabs nav-linetabs-borderless nav-linetabs-bottom-border d-flex justify-content-center" id="transactionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="successful-tab" data-bs-toggle="tab" data-bs-target="#successful" type="button" role="tab" aria-controls="successful" aria-selected="true">
                            <i class="fas fa-check-circle me-2"></i>
                            Successful ({{ count($successfulPayments) }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="failed-tab" data-bs-toggle="tab" data-bs-target="#failed" type="button" role="tab" aria-controls="failed" aria-selected="false">
                            <i class="fas fa-times-circle me-2"></i>
                            Failed ({{ count($failedPayments) }})
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="transactionTabContent">
                    <!-- Successful Transactions Tab -->
                    <div class="tab-pane fade show active" id="successful" role="tabpanel" aria-labelledby="successful-tab">
                        @if(empty($successfulPayments))
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fs-1 text-success mb-3"></i>
                                <h5 class="text-muted">No successful transactions</h5>
                                <p class="text-muted">All transactions are currently pending or failed</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover" id="successfulTransactionsTable">
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
                                        @foreach($successfulPayments as $payment)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('payments.status', ['reference' => $payment['orderReference'] ?? '']) }}" 
                                                       class="text-primary fw-bold text-decoration-none">
                                                        {{ $payment['orderReference'] ?? 'N/A' }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm bg-label-success rounded-circle me-2">
                                                            <i class="fas fa-user fs-6"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold text-success">{{ $payment['customer_name'] ?? 'Customer' }}</div>
                                                            <small class="text-muted">{{ $payment['customer_phone'] ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-label-success d-flex align-items-center" style="width: fit-content;">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        {{ $payment['status'] ?? 'SUCCESS' }}
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
                                                        <i class="fas fa-mobile-alt me-2 text-success"></i>
                                                        <span>{{ $payment['channel'] ?? $payment['paymentMethod'] ?? 'Mobile Money' }}</span>
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
                                                        <a href="{{ route('payments.receipt', $payment['orderReference'] ?? '') }}" 
                                                           target="_blank" class="btn btn-sm btn-outline-success" title="Download Receipt">
                                                            <i class="fas fa-receipt me-1"></i>Receipt
                                                        </a>
                                                        @if($payment['status'] === 'SUCCESS' || $payment['status'] === 'SETTLED')
                                                            <button class="btn btn-sm btn-outline-info" onclick="sendManualSMS('{{ $payment['orderReference'] ?? '' }}', '{{ $payment['customer_phone'] ?? $payment['customer']['customerPhoneNumber'] ?? '' }}', '{{ $payment['customer_name'] ?? $payment['customer']['customerName'] ?? 'Customer' }}', {{ number_format($payment['amount'] ?? $payment['collectedAmount'] ?? 0, 2) }})" title="Send SMS">
                                                                <i class="fas fa-sms me-1"></i>SMS
                                                            </button>
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

                    <!-- Failed Transactions Tab -->
                    <div class="tab-pane fade" id="failed" role="tabpanel" aria-labelledby="failed-tab">
                        @if(empty($failedPayments))
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fs-1 text-success mb-3"></i>
                                <h5 class="text-muted">No failed transactions</h5>
                                <p class="text-muted">All transactions are successful or pending</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover" id="failedTransactionsTable">
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
                                        @foreach($failedPayments as $payment)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('payments.status', ['reference' => $payment['orderReference'] ?? '']) }}" 
                                                       class="text-primary fw-bold text-decoration-none">
                                                        {{ $payment['orderReference'] ?? 'N/A' }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm bg-label-danger rounded-circle me-2">
                                                            <i class="fas fa-user fs-6"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold text-danger">{{ $payment['customer_name'] ?? 'Customer' }}</div>
                                                            <small class="text-muted">{{ $payment['customer_phone'] ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-label-danger d-flex align-items-center" style="width: fit-content;">
                                                        <i class="fas fa-times-circle me-1"></i>
                                                        {{ $payment['status'] ?? 'FAILED' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-danger">
                                                            {{ number_format($payment['amount'] ?? $payment['collectedAmount'] ?? 0, 2) }}
                                                        </div>
                                                        <small class="text-muted">TZS</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-mobile-alt me-2 text-danger"></i>
                                                        <span>{{ $payment['channel'] ?? $payment['paymentMethod'] ?? 'Mobile Money' }}</span>
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
                                                        <button class="btn btn-sm btn-outline-warning" onclick="retryPayment('{{ $payment['orderReference'] ?? '' }}')" title="Retry Payment">
                                                            <i class="fas fa-redo me-1"></i>Retry
                                                        </button>
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
            @endif
        </div>
    </div>
</div>
</div>


@endsection

@push('scripts')
<script>
// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    // Copy reference function
    window.copyReference = function(reference) {
        navigator.clipboard.writeText(reference).then(function() {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Reference ' + reference + ' copied to clipboard',
                showConfirmButton: false,
                timer: 1500,
                toast: true,
                position: 'top-end'
            });
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
            Swal.fire({
                icon: 'error',
                title: 'Failed to copy',
                text: 'Please try again',
                toast: true,
                position: 'top-end',
                timer: 2000
            });
        });
    };

    // Retry payment function
    window.retryPayment = function(reference) {
        Swal.fire({
            title: 'Retry Payment?',
            text: 'Do you want to retry payment for reference: ' + reference,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, retry it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to payment creation with retry logic
                window.location.href = '{{ route("payments.create") }}?retry=' + reference;
            }
        });
    };

    // Search functionality for transactions
    const searchInput = document.getElementById('transactionSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const activeTab = document.querySelector('.tab-pane.active');
            const rows = activeTab.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Export transactions function
    window.exportTransactions = function() {
        const activeTab = document.querySelector('.tab-pane.active');
        const tabId = activeTab.id;
        
        Swal.fire({
            title: 'Export Transactions',
            text: 'Export ' + (tabId === 'successful' ? 'successful' : 'failed') + ' transactions?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Export CSV'
        }).then((result) => {
            if (result.isConfirmed) {
                // Simulate export (you can implement actual CSV export here)
                Swal.fire({
                    icon: 'success',
                    title: 'Export Started',
                    text: 'Your CSV file will be downloaded shortly.',
                    timer: 2000,
                    toast: true,
                    position: 'top-end'
                });
            }
        });
    };

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush

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

function sendManualSMS(reference, phoneNumber, customerName, amount) {
    if (!reference || !phoneNumber) {
        alert('Missing required information for SMS');
        return;
    }
    
    if (confirm(`Send payment confirmation SMS to ${customerName} for TZS ${amount}?\n\nReference: ${reference}\nPhone: ${phoneNumber}`)) {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        button.disabled = true;
        
        // Send SMS via AJAX
        fetch('{{ route("dashboard.send.manual.sms") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                reference: reference,
                phone_number: phoneNumber,
                customer_name: customerName,
                amount: amount
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('SMS sent successfully to ' + customerName);
                // Update button to show success
                button.innerHTML = '<i class="fas fa-check me-2"></i>SMS Sent';
                button.classList.remove('btn-outline-info');
                button.classList.add('btn-success');
                
                // Refresh the transactions list after 2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                alert('Failed to send SMS: ' + data.message);
                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error sending SMS:', error);
            alert('Error sending SMS. Please try again.');
            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
        });
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
