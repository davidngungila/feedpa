@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Account Balance</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('account.index') }}">Account</a></li>
                    <li class="breadcrumb-item active">Balance</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Balance Cards -->
        <div class="row">
            @if($balanceData && is_array($balanceData) && !empty($balanceData))
                @foreach($balanceData as $balance)
                    <div class="col-md-4">
                        <div class="small-box bg-{{ ($balance['currency'] ?? 'TZS') == 'TZS' ? 'success' : 'info' }}">
                            <div class="inner">
                                <h3>{{ number_format($balance['balance'] ?? 0, 2) }}</h3>
                                <p>{{ $balance['currency'] ?? 'TZS' }} Balance</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="small-box-footer">
                                <a href="{{ route('account.statement', ['currency' => $balance['currency'] ?? 'TZS']) }}" class="text-white">
                                    View Statement <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No balance data available. Please check your API configuration.
                        @if(config('app.debug'))
                            <br><small><strong>Debug:</strong> Balance data: {{ json_encode($balanceData) }}</small>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Balance Overview Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Balance Overview</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($balanceData && is_array($balanceData) && !empty($balanceData))
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Currency</th>
                                            <th>Available Balance</th>
                                            <th>Status</th>
                                            <th>Last Updated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($balanceData as $balance)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-{{ ($balance['currency'] ?? 'TZS') == 'TZS' ? 'success' : 'info' }}">
                                                        {{ $balance['currency'] ?? 'TZS' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>{{ number_format($balance['balance'] ?? 0, 2) }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Active
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ now()->format('M d, Y H:i:s') }}</small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('account.statement', ['currency' => $balance['currency'] ?? 'TZS']) }}" 
                                                           class="btn btn-outline-info">
                                                            <i class="fas fa-file-alt"></i> Statement
                                                        </a>
                                                        <button type="button" class="btn btn-outline-primary" onclick="refreshBalance()">
                                                            <i class="fas fa-sync"></i> Refresh
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Unable to retrieve balance information.</p>
                                <button type="button" class="btn btn-primary" onclick="refreshBalance()">
                                    <i class="fas fa-sync-alt"></i> Try Again
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-paper-plane"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Quick Payout</span>
                        <span class="info-box-number">Send Money</span>
                    </div>
                    <a href="{{ route('payouts.create') }}" class="info-box-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success">
                        <i class="fas fa-credit-card"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Receive Payment</span>
                        <span class="info-box-number">Create Bill</span>
                    </div>
                    <a href="{{ route('billpay.create') }}" class="info-box-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning">
                        <i class="fas fa-history"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Transaction History</span>
                        <span class="info-box-number">View All</span>
                    </div>
                    <a href="{{ route('account.statement') }}" class="info-box-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-danger">
                        <i class="fas fa-chart-line"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Analytics</span>
                        <span class="info-box-number">Dashboard</span>
                    </div>
                    <a href="{{ route('dashboard.index') }}" class="info-box-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .small-box {
        border-radius: 0.5rem;
        transition: transform 0.2s ease;
    }
    .small-box:hover {
        transform: translateY(-2px);
    }
    .info-box {
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }
    .info-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .balance-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 1rem;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .balance-amount {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 1rem 0;
    }
    .balance-currency {
        font-size: 1.2rem;
        opacity: 0.9;
    }
</style>
@endpush

@push('scripts')
<script>
function refreshBalance() {
    location.reload();
}

// Auto-refresh balance every 30 seconds
setInterval(function() {
    if (document.visibilityState === 'visible') {
        refreshBalance();
    }
}, 30000);

// Add loading state for refresh button
document.addEventListener('DOMContentLoaded', function() {
    const refreshButtons = document.querySelectorAll('button[onclick*="refreshBalance"]');
    refreshButtons.forEach(button => {
        button.addEventListener('click', function() {
            const originalContent = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = originalContent;
                this.disabled = false;
            }, 2000);
        });
    });
});
</script>
@endpush
