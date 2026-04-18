@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="font-size: 1.5rem;">
                    <i class="fas fa-receipt me-2"></i>
                    Payment Status Details
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payments.history') }}">Payments</a></li>
                    <li class="breadcrumb-item active">Status</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Success Flash Messages -->
        @if(session('success'))
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Success:</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        @endif
        
        @if($error)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Error:</strong> {{ $error }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        @elseif($paymentData)
            <!-- Handle API response that returns array with payment object -->
            @php
                $payment = is_array($paymentData) && isset($paymentData[0]) ? $paymentData[0] : $paymentData;
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
                $statusText = match($payment['status'] ?? '') {
                    'SUCCESS' => 'Payment Successful',
                    'SETTLED' => 'Payment Settled',
                    'PROCESSING' => 'Processing Payment',
                    'PENDING' => 'Payment Pending',
                    'FAILED' => 'Payment Failed',
                    default => 'Unknown Status'
                };
            @endphp
            
            <!-- Advanced Status Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-left-4 border-left-{{ $statusColor }} shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="card-title mb-2">
                                        <i class="fas {{ $statusIcon }} me-2 text-{{ $statusColor }}"></i>
                                        {{ $statusText }}
                                    </h3>
                                    <p class="text-muted mb-0">
                                        Reference: <span class="fw-bold">{{ $payment['orderReference'] ?? 'N/A' }}</span>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <div class="h2 mb-0 text-{{ $statusColor }}">
                                        {{ number_format($payment['collectedAmount'] ?? $payment['amount'] ?? 0, 2) }}
                                    </div>
                                    <small class="text-muted">{{ $payment['collectedCurrency'] ?? 'TZS' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                            
                            <!-- Payment Details Grid -->
<div class="row">
    <!-- Transaction Information -->
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Transaction Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-primary rounded-circle me-3">
                                <i class="fas fa-hashtag"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Reference Number</small>
                                <strong>{{ $payment['orderReference'] ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-info rounded-circle me-3">
                                <i class="fas fa-fingerprint"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Transaction ID</small>
                                <strong>{{ $payment['id'] ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-success rounded-circle me-3">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Amount</small>
                                <strong class="text-success">{{ number_format($payment['collectedAmount'] ?? $payment['amount'] ?? 0, 2) }} {{ $payment['collectedCurrency'] ?? 'TZS' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-warning rounded-circle me-3">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Status</small>
                                <span class="badge bg-label-{{ $statusColor }}">{{ $statusText }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-secondary rounded-circle me-3">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Payment Method</small>
                                <strong>{{ $payment['channel'] ?? $payment['paymentMethod'] ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>
                    Customer Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg bg-primary rounded-circle me-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Customer Name</small>
                                <strong>{{ $payment['customer']['customerName'] ?? $payment['payer_name'] ?? $payment['customerName'] ?? 'Customer' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg bg-info rounded-circle me-3">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Phone Number</small>
                                <strong>{{ $payment['customer']['customerPhoneNumber'] ?? $payment['paymentPhoneNumber'] ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg bg-secondary rounded-circle me-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Email Address</small>
                                <strong>{{ $payment['customer']['customerEmail'] ?? $payment['customerEmail'] ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Transaction Timeline
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <small class="text-muted d-block">Created At</small>
                                <strong>{{ \Carbon\Carbon::parse($payment['createdAt'] ?? 'now')->format('M d, Y H:i:s') }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="timeline-content">
                                <small class="text-muted d-block">Last Updated</small>
                                <strong>{{ \Carbon\Carbon::parse($payment['updatedAt'] ?? 'now')->format('M d, Y H:i:s') }}</strong>
                            </div>
                        </div>
                    </div>
                    @if(isset($payment['completedAt']))
                    <div class="col-md-6 mb-3">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <small class="text-muted d-block">Completed At</small>
                                <strong class="text-success">{{ \Carbon\Carbon::parse($payment['completedAt'])->format('M d, Y H:i:s') }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="timeline-content">
                                <small class="text-muted d-block">Processing Time</small>
                                <strong>
                                    @php
                                        $created = \Carbon\Carbon::parse($payment['createdAt'] ?? 'now');
                                        $completed = \Carbon\Carbon::parse($payment['completedAt']);
                                        $duration = $created->diff($completed);
                                    @endphp
                                    {{ $duration->i }}m {{ $duration->s }}s
                                </strong>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
                            
                            <!-- Status Sidebar & Actions -->
<div class="col-lg-4">
    <!-- Status Card -->
    <div class="card shadow-sm mb-4 border-left-4 border-left-{{ $statusColor }}">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Status Information
            </h5>
        </div>
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="avatar avatar-xl bg-{{ $statusColor }} bg-opacity-10 rounded-circle mx-auto mb-3">
                    <i class="fas {{ $statusIcon }} fs-1 text-{{ $statusColor }}"></i>
                </div>
                <h4 class="text-{{ $statusColor }}">{{ $statusText }}</h4>
                <p class="text-muted">Current payment status</p>
            </div>
            
            <div class="mb-3">
                <h6 class="text-muted mb-2">Status Details</h6>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-{{ $statusColor }}" style="width: {{ $payment['status'] === 'SUCCESS' || $payment['status'] === 'SETTLED' ? '100' : ($payment['status'] === 'PROCESSING' ? '60' : ($payment['status'] === 'PENDING' ? '30' : '0')) }}%"></div>
                </div>
            </div>
            
            <div class="status-legend">
                <small class="text-muted d-block mb-1"><strong>Status Meanings:</strong></small>
                <ul class="list-unstyled mb-0">
                    <li class="mb-1"><small><span class="badge bg-label-warning">PENDING</span> Payment initiated</small></li>
                    <li class="mb-1"><small><span class="badge bg-label-info">PROCESSING</span> Being processed</small></li>
                    <li class="mb-1"><small><span class="badge bg-label-success">SUCCESS</span> Completed successfully</small></li>
                    <li class="mb-1"><small><span class="badge bg-label-success">SETTLED</span> Payment settled</small></li>
                    <li class="mb-0"><small><span class="badge bg-label-danger">FAILED</span> Payment failed</small></li>
                </ul>
            </div>
            
            @if(in_array($payment['status'] ?? '', ['PENDING', 'PROCESSING', 'FAILED']))
            <div class="alert alert-warning alert-dismissible mt-3">
                <i class="fas fa-mobile-alt me-2"></i>
                <strong>USSD Available:</strong> Customer hasn't confirmed yet. Click "Resend USSD" to send a new prompt.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @if(in_array($payment['status'] ?? '', ['PENDING', 'PROCESSING']))
            <div class="alert alert-info alert-dismissible mt-3">
                <i class="fas fa-clock me-2"></i>
                <strong>Auto-refresh:</strong> Status updates every 10 seconds
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Actions Row -->
<div class="col-12">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">
                <i class="fas fa-bolt me-2"></i>
                Quick Actions
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3 col-sm-6">
                    @if(in_array($payment['status'] ?? '', ['PENDING', 'PROCESSING', 'FAILED']))
                    <button type="button" class="btn btn-warning w-100" onclick="resendUssd()">
                        <i class="fas fa-mobile-alt me-2"></i>Resend USSD
                    </button>
                    @endif
                </div>
                <div class="col-md-3 col-sm-6">
                    <button type="button" class="btn btn-primary w-100" onclick="checkPaymentStatus()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh Status
                    </button>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('payments.history') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-2"></i>Back to History
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    @if(in_array($payment['status'] ?? '', ['SUCCESS', 'SETTLED']))
                    <a href="{{ route('payments.receipt', $payment['orderReference'] ?? '') }}" class="btn btn-success w-100" target="_blank">
                        <i class="fas fa-receipt me-2"></i>Download Receipt
                    </a>
                    @else
                    <button class="btn btn-outline-info w-100" onclick="sharePayment()">
                        <i class="fas fa-share-alt me-2"></i>Share Details
                    </button>
                    @endif
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('payments.export.pdf') }}?order_reference={{ $payment['orderReference'] ?? '' }}" class="btn btn-outline-danger w-100">
                        <i class="fas fa-file-pdf me-1"></i>PDF
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('payments.export.excel') }}?order_reference={{ $payment['orderReference'] ?? '' }}" class="btn btn-outline-success w-100">
                        <i class="fas fa-file-excel me-1"></i>Excel
                    </a>
                </div>
                @if(in_array($payment['status'] ?? '', ['SUCCESS', 'SETTLED']))
                <div class="col-md-3 col-sm-6">
                    <button class="btn btn-outline-info w-100" onclick="sharePayment()">
                        <i class="fas fa-share-alt me-2"></i>Share Details
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Additional Details Row -->
@if(isset($payment['message']) || isset($payment['description']) || isset($payment['fee']) || isset($payment['tax']))
<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">
                <i class="fas fa-info me-2"></i>
                Additional Details
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @if(isset($payment['message']))
                <div class="col-md-4 mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-comment me-1"></i>
                        Message
                    </h6>
                    <p class="mb-0">{{ $payment['message'] }}</p>
                </div>
                @endif
                
                @if(isset($payment['description']))
                <div class="col-md-4 mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-file-alt me-1"></i>
                        Description
                    </h6>
                    <p class="mb-0">{{ $payment['description'] }}</p>
                </div>
                @endif
                
                @if(isset($payment['fee']) || isset($payment['tax']))
                <div class="col-md-4 mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-calculator me-1"></i>
                        Financial Details
                    </h6>
                    <div class="row">
                        @if(isset($payment['fee']))
                        <div class="col-6">
                            <small class="text-muted d-block">Fee</small>
                            <strong>{{ number_format($payment['fee'] ?? 0, 2) }} {{ $payment['currency'] ?? 'TZS' }}</strong>
                        </div>
                        @endif
                        @if(isset($payment['tax']))
                        <div class="col-6">
                            <small class="text-muted d-block">Tax</small>
                            <strong>{{ number_format($payment['tax'] ?? 0, 2) }} {{ $payment['currency'] ?? 'TZS' }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
</div>
@else
<div class="text-center py-5">
    <div class="avatar avatar-xl bg-light rounded-circle mx-auto mb-3">
        <i class="fas fa-search fs-1 text-muted"></i>
    </div>
    <h3 class="text-muted">Payment Not Found</h3>
    <p class="text-muted mb-4">No payment details found for the given reference.</p>
    <a href="{{ route('payments.history') }}" class="btn btn-primary">
        <i class="fas fa-history me-2"></i>View Payment History
    </a>
</div>
@endif
</div>
</section>

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
.border-left-secondary {
    border-left-color: #6c757d !important;
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
.avatar-xl {
    width: 5rem;
    height: 5rem;
}
.avatar-lg {
    width: 3.5rem;
    height: 3.5rem;
}
.avatar-sm {
    width: 2rem;
    height: 2rem;
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
.bg-label-secondary {
    background-color: rgba(108, 117, 125, 0.1) !important;
    color: #6c757d !important;
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.timeline-marker {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 1rem;
    flex-shrink: 0;
}

.timeline-content {
    flex: 1;
}

.status-legend ul {
    font-size: 0.875rem;
}

.status-legend .badge {
    font-size: 0.7rem;
    margin-right: 0.25rem;
}

.card.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.btn-group.w-100 .btn {
    flex: 1;
}

@media (max-width: 768px) {
    .avatar-xl {
        width: 4rem;
        height: 4rem;
    }
    
    .timeline-item {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .timeline-marker {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .custom-alert-overlay .alert-container {
        width: 95%;
        margin: 10px;
    }
}

/* Alert animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(-50px);
        opacity: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
let autoRefreshInterval;

function checkPaymentStatus() {
    const orderReference = '{{ $orderReference ?? '' }}';
    if (!orderReference) return;
    
    // Show loading state
    const refreshBtn = event.target;
    const originalText = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    refreshBtn.disabled = true;
    
    fetch(`/payments/status?reference=${orderReference}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to show updated status
            window.location.reload();
        } else {
            // Show error message
            showAlert('error', 'Failed to refresh status. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Network error. Please check your connection.');
    })
    .finally(() => {
        // Restore button state
        refreshBtn.innerHTML = originalText;
        refreshBtn.disabled = false;
    });
}

function resendUssd() {
    const orderReference = '{{ $payment['orderReference'] ?? '' }}';
    const phoneNumber = '{{ $payment['paymentPhoneNumber'] ?? $payment['customer']['customerPhoneNumber'] ?? '' }}';
    
    if (!orderReference) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Payment reference not found.',
                confirmButtonColor: '#dc3545',
                backdrop: true,
                allowOutsideClick: false,
                allowEscapeKey: false
            });
        } else {
            showAlert('error', 'Payment reference not found.');
        }
        return;
    }
    
    // Show confirmation dialog
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Resend USSD Notification',
            html: `
                <div class="text-start">
                    <p>Are you sure you want to resend the USSD notification?</p>
                    <div class="mb-3">
                        <small class="text-muted">Reference:</small><br>
                        <strong>${orderReference}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Phone Number:</small><br>
                        <strong>${phoneNumber || 'N/A'}</strong>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        The customer will receive a new USSD prompt to confirm this payment.
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Resend USSD',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            backdrop: true,
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                performUssdResend(orderReference);
            }
        });
    } else {
        // Fallback to simple confirmation
        if (confirm(`Resend USSD notification for ${orderReference}?\n\nThe customer will receive a new USSD prompt to confirm this payment.`)) {
            performUssdResend(orderReference);
        }
    }
}

function performUssdResend(orderReference) {
    // Show loading state
    const resendBtn = event.target;
    const originalText = resendBtn.innerHTML;
    resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending USSD...';
    resendBtn.disabled = true;
    
    // Make API call to resend USSD
    fetch(`/payments/resend-ussd`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            order_reference: orderReference
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'USSD Sent Successfully!',
                    html: `
                        <div class="text-start">
                            <p>USSD notification has been sent to the customer's phone.</p>
                            <div class="mb-3">
                                <small class="text-muted">Phone Number:</small><br>
                                <strong>${data.data?.phone_number || phoneNumber}</strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Amount:</small><br>
                                <strong>{{ number_format($payment['collectedAmount'] ?? $payment['amount'] ?? 0, 2) }} TZS</strong>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                The customer should receive the USSD prompt shortly. Please wait for them to confirm.
                            </div>
                        </div>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#28a745',
                    backdrop: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    // Auto-refresh status after 3 seconds
                    setTimeout(() => {
                        checkPaymentStatus();
                    }, 3000);
                });
            } else {
                showAlert('success', 'USSD notification sent successfully! The customer should receive the prompt shortly.');
                
                // Auto-refresh status after 3 seconds
                setTimeout(() => {
                    checkPaymentStatus();
                }, 3000);
            }
        } else {
            // Show detailed error information for debugging
            let errorMessage = data.message || 'Failed to send USSD notification. Please try again.';
            
            if (data.debug_info) {
                console.error('USSD Resend Debug Info:', data.debug_info);
                
                // Add debug information to error message for development
                if (data.debug_info.api_response) {
                    errorMessage += '\n\nDebug Info: ' + JSON.stringify(data.debug_info.api_response, null, 2);
                }
            }
            
            // Log detailed error for debugging
            console.error('USSD Resend Failed:', {
                error: data,
                debugInfo: data.debug_info,
                timestamp: new Date().toISOString()
            });
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'USSD Resend Failed',
                    html: `
                        <div class="text-start">
                            <p>${errorMessage}</p>
                            ${data.debug_info && data.debug_info.api_response ? `
                            <div class="mt-3">
                                <small class="text-muted">Technical Details:</small><br>
                                <code style="font-size: 0.8rem;">${JSON.stringify(data.debug_info.api_response, null, 2)}</code>
                            </div>
                            ` : ''}
                        </div>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545',
                    backdrop: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
            } else {
                showAlert('error', errorMessage);
            }
        }
    })
    .catch(error => {
        console.error('Error resending USSD:', error);
        showAlert('error', 'Network error. Please check your connection and try again.');
    })
    .finally(() => {
        // Restore button state
        resendBtn.innerHTML = originalText;
        resendBtn.disabled = false;
    });
}

function sharePayment() {
    const orderReference = '{{ $payment['orderReference'] ?? '' }}';
    const amount = '{{ number_format($payment['collectedAmount'] ?? $payment['amount'] ?? 0, 2) }} {{ $payment['collectedCurrency'] ?? 'TZS' }}';
    const status = '{{ $statusText }}';
    
    const shareText = `Payment Details:\nReference: ${orderReference}\nAmount: ${amount}\nStatus: ${status}\nDate: {{ \Carbon\Carbon::parse($payment['createdAt'] ?? 'now')->format('M d, Y H:i') }}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Payment Details',
            text: shareText,
            url: window.location.href
        }).catch(err => {
            console.log('Share failed:', err);
            copyToClipboard(shareText);
        });
    } else {
        copyToClipboard(shareText);
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('success', 'Payment details copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
        showAlert('error', 'Failed to copy payment details.');
    });
}

function showAlert(type, message) {
    // Remove any existing alerts
    const existingAlerts = document.querySelectorAll('.custom-alert-overlay');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create overlay container
    const overlay = document.createElement('div');
    overlay.className = 'custom-alert-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 99999;
        animation: fadeIn 0.3s ease-in-out;
    `;
    
    // Create alert container
    const alertContainer = document.createElement('div');
    alertContainer.style.cssText = `
        max-width: 500px;
        width: 90%;
        margin: 20px;
        animation: slideIn 0.3s ease-in-out;
    `;
    
    // Create alert content
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show d-flex align-items-center`;
    alertDiv.style.cssText = `
        margin: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        position: relative;
    `;
    
    // Set icon based on type
    let iconHtml = '';
    if (type === 'success') {
        iconHtml = '<i class="fas fa-check-circle me-3" style="font-size: 1.5rem;"></i>';
    } else if (type === 'error') {
        iconHtml = '<i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>';
    } else if (type === 'warning') {
        iconHtml = '<i class="fas fa-exclamation-circle me-3" style="font-size: 1.5rem;"></i>';
    } else {
        iconHtml = '<i class="fas fa-info-circle me-3" style="font-size: 1.5rem;"></i>';
    }
    
    alertDiv.innerHTML = `
        ${iconHtml}
        <div class="flex-grow-1">
            <div class="fw-bold">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
            <div class="small">${message}</div>
        </div>
        <button type="button" class="btn-close ms-3" onclick="closeAlert(this)"></button>
    `;
    
    alertContainer.appendChild(alertDiv);
    overlay.appendChild(alertContainer);
    document.body.appendChild(overlay);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        closeAlert(overlay.querySelector('.btn-close'));
    }, 5000);
}

// Close alert function
function closeAlert(button) {
    const overlay = button.closest('.custom-alert-overlay');
    if (overlay) {
        overlay.style.animation = 'fadeOut 0.3s ease-in-out';
        setTimeout(() => {
            overlay.remove();
        }, 300);
    }
}

function startAutoRefresh() {
    const paymentStatus = '{{ $payment['status'] ?? '' }}';
    if (paymentStatus === 'PENDING' || paymentStatus === 'PROCESSING') {
        autoRefreshInterval = setInterval(() => {
            checkPaymentStatus();
        }, 10000); // Refresh every 10 seconds
    }
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
    
    // Stop auto-refresh when user leaves the page
    window.addEventListener('beforeunload', stopAutoRefresh);
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + R to refresh status
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            checkPaymentStatus();
        }
        // Ctrl/Cmd + S to share
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            sharePayment();
        }
    });
});

// Clean up on page unload
window.addEventListener('unload', stopAutoRefresh);
</script>
@endpush

@endsection
