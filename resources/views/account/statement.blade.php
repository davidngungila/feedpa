@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="font-size: 1.5rem;">
                    <i class="fas fa-file-invoice me-2"></i>
                    Account Statement
                </h1>
            </div>
            <div class="col-sm-6 text-end">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-danger dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}">Current View</a></li>
                    </ul>
                    
                    <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">Current View</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Summary Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-uppercase small mb-2">Total Unique Entries</h6>
                        <h3 class="mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-uppercase small mb-2">Total Credits</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_credits'] ?? 0, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-uppercase small mb-2">Total Debits</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_debits'] ?? 0, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-uppercase small mb-2">Successful Payments</h6>
                        <h3 class="mb-0">{{ number_format($stats['successful'] ?? 0) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('account.statement') }}" class="row g-3">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <div class="col-md-3">
                        <label class="form-label">Currency</label>
                        <select class="form-select" name="currency">
                            <option value="TZS" {{ $currency == 'TZS' ? 'selected' : '' }}>TZS (Tanzanian Shilling)</option>
                            <option value="USD" {{ $currency == 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sync-alt me-1"></i> Update Results
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($error)
            <div class="alert alert-warning shadow-sm mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> {{ $error }}
            </div>
        @endif

        <!-- MAIN TABS (Source) -->
        <div class="card shadow-sm mb-0 rounded-bottom-0">
            <div class="card-header bg-white p-0">
                <ul class="nav nav-tabs nav-fill" id="statementTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link py-3 {{ $activeTab === 'database' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }}" 
                           href="{{ request()->fullUrlWithQuery(['tab' => 'database']) }}">
                            <i class="fas fa-database me-2"></i> DATABASE RECORDS
                            <span class="badge bg-secondary ms-2">{{ $dbCount }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-3 {{ $activeTab === 'api' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }}" 
                           href="{{ request()->fullUrlWithQuery(['tab' => 'api']) }}">
                            <i class="fas fa-cloud me-2"></i> CLICKPESA API
                            <span class="badge bg-secondary ms-2">{{ $apiCount }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- SUB TABS (Status) -->
        <div class="card shadow-sm mb-4 border-top-0 rounded-top-0">
            <div class="card-header bg-light py-2">
                <ul class="nav nav-pills card-header-pills small">
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'all' ? 'active' : '' }}" 
                           href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}">
                           All Status
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'settled' ? 'active' : '' }}" 
                           href="{{ request()->fullUrlWithQuery(['status' => 'settled']) }}">
                           Settled / Success <span class="badge bg-white text-dark ms-1">{{ $settledCount }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $statusFilter === 'failed' ? 'active' : '' }}" 
                           href="{{ request()->fullUrlWithQuery(['status' => 'failed']) }}">
                           Failed <span class="badge bg-white text-dark ms-1">{{ $failedCount }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Date & Time</th>
                                <th>Reference / ID</th>
                                <th>Description / Purpose</th>
                                @if($activeTab === 'api')
                                    <th>Status in DB</th>
                                @endif
                                <th>Status</th>
                                <th class="text-end pe-4">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($transaction['date'])->format('H:i A') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">
                                            <a href="{{ route('payments.status', ['reference' => $transaction['reference']]) }}" class="text-decoration-none" title="View Full Details">
                                                <code>{{ $transaction['reference'] ?? 'N/A' }}</code>
                                                <i class="fas fa-external-link-alt small ms-1 text-muted"></i>
                                            </a>
                                        </div>
                                        <small class="text-muted">{{ $transaction['transaction_id'] ?? 'No Transaction ID' }}</small>
                                    </td>
                                    <td>
                                        <div class="text-wrap" style="max-width: 300px;">
                                            {{ $transaction['description'] ?? 'N/A' }}
                                        </div>
                                        @if(isset($transaction['payer_name']))
                                            <small class="text-muted d-block">Payer: {{ $transaction['payer_name'] }}</small>
                                        @endif
                                    </td>
                                    @if($activeTab === 'api')
                                        <td>
                                            @if($transaction['is_synced'])
                                                <span class="badge bg-light text-success border border-success">
                                                    <i class="fas fa-check-circle me-1"></i> Recorded
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border">
                                                    <i class="fas fa-minus-circle me-1"></i> API Only
                                                </span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        @php
                                            $status = strtoupper($transaction['status'] ?? 'UNKNOWN');
                                            $statusClass = match($status) {
                                                'SUCCESS', 'SETTLED' => 'bg-success',
                                                'PENDING', 'PROCESSING' => 'bg-warning text-dark',
                                                'FAILED' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $status }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        @php
                                            $isDebit = ($transaction['entry'] ?? 'CREDIT') == 'DEBIT';
                                        @endphp
                                        <div class="fw-bold {{ $isDebit ? 'text-danger' : 'text-success' }}">
                                            {{ $isDebit ? '-' : '+' }} {{ number_format($transaction['amount'], 2) }}
                                        </div>
                                        <small class="text-muted">{{ $currency }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $activeTab === 'api' ? 6 : 5 }}" class="text-center py-5">
                                        <div class="text-muted mb-3"><i class="fas fa-search fs-1"></i></div>
                                        <h5>No records found in {{ strtoupper($activeTab) }}{{ $activeTab === 'database' ? ' with status ' . strtoupper($statusFilter) : '' }}</h5>
                                        <p>Adjust your filters or dates to find transactions.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .card { border-radius: 12px; border: none; }
    .nav-tabs .nav-link { border: none; transition: all 0.3s; }
    .nav-tabs .nav-link:hover { background-color: #f8f9fa; }
    .nav-pills .nav-link { border-radius: 6px; padding: 5px 15px; }
    .table thead th { font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
</style>
@endpush
@endsection
