@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Payout Status</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payouts.history') }}">Payouts</a></li>
                    <li class="breadcrumb-item active">Status</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Payout Details</h3>
                    </div>
                    <div class="card-body">
                        @if($error)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $error }}
                            </div>
                        @elseif($payoutData)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payout Reference</label>
                                        <p class="form-control-plaintext">{{ $payoutData['orderReference'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Transaction ID</label>
                                        <p class="form-control-plaintext">{{ $payoutData['id'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Amount</label>
                                        <p class="form-control-plaintext">
                                            {{ number_format($payoutData['amount'] ?? 0, 2) }} {{ $payoutData['currency'] ?? 'TZS' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <p class="form-control-plaintext">
                                            @php
                                                $statusColor = match($payoutData['status'] ?? '') {
                                                    'SUCCESS', 'SETTLED' => 'success',
                                                    'PROCESSING', 'PENDING' => 'warning',
                                                    'FAILED' => 'danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge bg-label-{{ $statusColor }}">{{ $payoutData['status'] ?? 'UNKNOWN' }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Channel</label>
                                        <p class="form-control-plaintext">{{ $payoutData['channel'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Recipient</label>
                                        <p class="form-control-plaintext">
                                            @if($payoutData['channel'] == 'MOBILE_MONEY')
                                                {{ $payoutData['phoneNumber'] ?? 'N/A' }}
                                            @elseif($payoutData['channel'] == 'BANK')
                                                {{ $payoutData['accountName'] ?? 'N/A' }} ({{ $payoutData['bankAccount'] ?? 'N/A' }})
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Created At</label>
                                        <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($payoutData['createdAt'] ?? 'now')->format('M d, Y H:i:s') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Updated At</label>
                                        <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($payoutData['updatedAt'] ?? 'now')->format('M d, Y H:i:s') }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            @if(isset($payoutData['narrative']))
                                <div class="form-group">
                                    <label>Narrative</label>
                                    <p class="form-control-plaintext">{{ $payoutData['narrative'] }}</p>
                                </div>
                            @endif
                            
                            <div class="row">
                                <div class="col-12">
                                    <a href="{{ route('payouts.history') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-2"></i> Back to History
                                    </a>
                                    <button type="button" class="btn btn-primary" onclick="checkPayoutStatus()">
                                        <i class="fas fa-sync-alt mr-2"></i> Refresh Status
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-money-withdraw fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No payout details found.</p>
                                <a href="{{ route('payouts.history') }}" class="btn btn-primary">View Payout History</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Status Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-info-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Payout Status</span>
                                <span class="info-box-number">
                                    @if($payoutData)
                                        {{ $payoutData['status'] ?? 'UNKNOWN' }}
                                    @else
                                        Not Found
                                    @endif
                                </span>
                            </div>
                        </div>
                        
                        <h5>Status Meanings:</h5>
                        <ul>
                            <li><strong>PENDING:</strong> Payout initiated, awaiting processing</li>
                            <li><strong>PROCESSING:</strong> Payout is being processed</li>
                            <li><strong>SUCCESS:</strong> Payout completed successfully</li>
                            <li><strong>SETTLED:</strong> Payout has been settled</li>
                            <li><strong>FAILED:</strong> Payout failed</li>
                        </ul>
                        
                        @if($payoutData && in_array($payoutData['status'] ?? '', ['PENDING', 'PROCESSING']))
                            <div class="alert alert-info">
                                <i class="fas fa-clock"></i>
                                <strong>Auto-refresh:</strong> Status will automatically refresh every 15 seconds.
                            </div>
                        @endif
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Important:</strong> Payouts typically take 1-3 business days to complete.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
let autoRefreshInterval;

function checkPayoutStatus() {
    const payoutReference = '{{ $payoutReference ?? '' }}';
    if (!payoutReference) return;
    
    fetch(`/payouts/api/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            payout_reference: payoutReference
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to show updated status
            window.location.reload();
        } else {
            console.error('Failed to check status:', data.error);
        }
    })
    .catch(error => {
        console.error('Error checking status:', error);
    });
}

// Auto-refresh for pending/processing payouts
@if($payoutData && in_array($payoutData['status'] ?? '', ['PENDING', 'PROCESSING']))
autoRefreshInterval = setInterval(checkPayoutStatus, 15000); // Refresh every 15 seconds
@endif

// Clean up interval when page is unloaded
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
</script>
@endpush
@endsection
