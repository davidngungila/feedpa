@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Account Overview</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Account</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Account Balance Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Account Balance</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($error)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $error }}
                            </div>
                        @elseif($balanceData)
                            @foreach($balanceData as $balance)
                                <div class="info-box">
                                    <span class="info-box-icon bg-success">
                                        <i class="fas fa-wallet"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ $balance['currency'] ?? 'TZS' }} Balance</span>
                                        <span class="info-box-number">
                                            {{ number_format($balance['balance'] ?? 0, 2) }} {{ $balance['currency'] ?? 'TZS' }}
                                        </span>
                                    </div>
                                </div>
                                
                                @if(isset($balance['availableBalance']))
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info">
                                            <i class="fas fa-unlock"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Available Balance</span>
                                            <span class="info-box-number">
                                                {{ number_format($balance['availableBalance'], 2) }} {{ $balance['currency'] ?? 'TZS' }}
                                            </span>
                                        </div>
                                    </div>
                                @endif
                                
                                @if(isset($balance['pendingBalance']))
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning">
                                            <i class="fas fa-clock"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Pending Balance</span>
                                            <span class="info-box-number">
                                                {{ number_format($balance['pendingBalance'], 2) }} {{ $balance['currency'] ?? 'TZS' }}
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Click "Get Balance" to view your account balance</p>
                                <form method="GET" action="{{ route('account.index') }}">
                                    <input type="hidden" name="get_balance" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-sync-alt mr-2"></i> Get Balance
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Account Statement Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Account Statement</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('account.index') }}">
                            <input type="hidden" name="get_statement" value="1">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="currency">Currency</label>
                                        <select class="form-control" name="currency">
                                            <option value="TZS" {{ request('currency') == 'TZS' ? 'selected' : '' }}>TZS</option>
                                            <option value="USD" {{ request('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_date">Start Date</label>
                                        <input type="date" class="form-control" name="start_date" 
                                               value="{{ request('start_date') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_date">End Date</label>
                                        <input type="date" class="form-control" name="end_date" 
                                               value="{{ request('end_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-info">
                                                <i class="fas fa-search"></i> Get Statement
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        @if($statementData && isset($statementData['transactions']))
                            <div class="mt-3">
                                <h6>Recent Transactions</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Description</th>
                                                <th>Amount</th>
                                                <th>Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(array_slice($statementData['transactions'], 0, 5) as $transaction)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($transaction['date'] ?? 'now')->format('M d, Y') }}</td>
                                                    <td>{{ $transaction['description'] ?? 'N/A' }}</td>
                                                    <td class="{{ ($transaction['entry'] ?? 'CREDIT') == 'DEBIT' ? 'text-danger' : 'text-success' }}">
                                                        {{ ($transaction['entry'] ?? 'CREDIT') == 'DEBIT' ? '-' : '+' }}
                                                        {{ number_format($transaction['amount'] ?? 0, 2) }}
                                                    </td>
                                                    <td>{{ number_format($transaction['balance'] ?? 0, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                @if(count($statementData['transactions']) > 5)
                                    <small class="text-muted">Showing 5 of {{ count($statementData['transactions']) }} transactions</small>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats Row -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total'] ?? 0 }}</h3>
                        <p>Total Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['successful'] ?? 0 }}</h3>
                        <p>Completed payments</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['pending'] ?? 0 }}</h3>
                        <p>Processing payments</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['failed'] ?? 0 }}</h3>
                        <p>Failed transactions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        
        @if($stats && ($stats['total_credits'] > 0 || $stats['total_debits'] > 0))
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Transaction Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success">
                                        <i class="fas fa-arrow-up"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Credits</span>
                                        <span class="info-box-number">
                                            {{ number_format($stats['total_credits'] ?? 0, 2) }} TZS
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger">
                                        <i class="fas fa-arrow-down"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Debits</span>
                                        <span class="info-box-number">
                                            {{ number_format($stats['total_debits'] ?? 0, 2) }} TZS
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
// Auto-refresh balance every 30 seconds
setInterval(function() {
    fetch('{{ route("account.balance") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update balance display
                console.log('Balance updated:', data.data);
            }
        })
        .catch(error => {
            console.error('Error fetching balance:', error);
        });
}, 30000);
</script>
@endpush
@endsection
