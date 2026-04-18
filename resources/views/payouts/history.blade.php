@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Payout History</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Payout History</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Payout Transactions</h3>
                        <div class="card-tools">
                            <a href="{{ route('payouts.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> New Payout
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" action="{{ route('payouts.history') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="reference" 
                                           placeholder="Reference" value="{{ request('reference') }}">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control" name="status">
                                        <option value="">All Status</option>
                                        <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
                                        <option value="PROCESSING" {{ request('status') == 'PROCESSING' ? 'selected' : '' }}>Processing</option>
                                        <option value="SUCCESS" {{ request('status') == 'SUCCESS' ? 'selected' : '' }}>Success</option>
                                        <option value="SETTLED" {{ request('status') == 'SETTLED' ? 'selected' : '' }}>Settled</option>
                                        <option value="FAILED" {{ request('status') == 'FAILED' ? 'selected' : '' }}>Failed</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control" name="currency">
                                        <option value="">All Currencies</option>
                                        <option value="TZS" {{ request('currency') == 'TZS' ? 'selected' : '' }}>TZS</option>
                                        <option value="USD" {{ request('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control" name="payout_type">
                                        <option value="">All Types</option>
                                        <option value="MOBILE_MONEY" {{ request('payout_type') == 'MOBILE_MONEY' ? 'selected' : '' }}>Mobile Money</option>
                                        <option value="BANK" {{ request('payout_type') == 'BANK' ? 'selected' : '' }}>Bank Transfer</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" name="start_date" 
                                           value="{{ request('start_date') }}" placeholder="Start Date">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <a href="{{ route('payouts.history') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </form>

                        @if($error)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $error }}
                            </div>
                        @elseif(empty($payouts))
                            <div class="text-center py-4">
                                <i class="fas fa-money-withdraw fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No payout transactions found.</p>
                                <a href="{{ route('payouts.create') }}" class="btn btn-primary">Create First Payout</a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Transaction ID</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Channel</th>
                                            <th>Recipient</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payouts as $payout)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('payouts.status', ['reference' => $payout['orderReference'] ?? '']) }}">
                                                        {{ $payout['orderReference'] ?? 'N/A' }}
                                                    </a>
                                                </td>
                                                <td>{{ $payout['id'] ?? 'N/A' }}</td>
                                                <td>
                                                    {{ number_format($payout['amount'] ?? 0, 2) }} 
                                                    {{ $payout['currency'] ?? 'TZS' }}
                                                </td>
                                                <td>
                                                    @php
                                                        $statusColor = match($payout['status'] ?? '') {
                                                            'SUCCESS', 'SETTLED' => 'success',
                                                            'PROCESSING', 'PENDING' => 'warning',
                                                            'FAILED' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge bg-label-{{ $statusColor }}">
                                                        {{ $payout['status'] ?? 'UNKNOWN' }}
                                                    </span>
                                                </td>
                                                <td>{{ $payout['channel'] ?? 'N/A' }}</td>
                                                <td>
                                                    @if($payout['channel'] == 'MOBILE_MONEY')
                                                        {{ $payout['phoneNumber'] ?? 'N/A' }}
                                                    @elseif($payout['channel'] == 'BANK')
                                                        {{ $payout['accountName'] ?? 'N/A' }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($payout['createdAt'] ?? 'now')->format('M d, Y H:i') }}</td>
                                                <td>
                                                    <a href="{{ route('payouts.status', ['reference' => $payout['orderReference'] ?? '']) }}" 
                                                       class="btn btn-sm btn-info" title="View Status">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($totalCount > 20)
                                <div class="row">
                                    <div class="col-sm-12 col-md-5">
                                        <div class="dataTables_info">
                                            Showing {{ min(20, $totalCount) }} of {{ $totalCount }} entries
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-7">
                                        <div class="dataTables_paginate paging_simple_numbers">
                                            <ul class="pagination">
                                                <!-- Simple pagination - you might want to use Laravel's pagination -->
                                                <li class="paginate_button page-item previous disabled">
                                                    <a href="#" class="page-link">Previous</a>
                                                </li>
                                                <li class="paginate_button page-item active">
                                                    <a href="#" class="page-link">1</a>
                                                </li>
                                                <li class="paginate_button page-item next disabled">
                                                    <a href="#" class="page-link">Next</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
