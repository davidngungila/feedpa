@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">BillPay Control Numbers</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">BillPay</li>
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
                        <h3 class="card-title">BillPay Numbers</h3>
                        <div class="card-tools">
                            <a href="{{ route('billpay.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> New BillPay
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" action="{{ route('billpay.index') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Search by number or description" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" name="status">
                                        <option value="">All Status</option>
                                        <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                                        <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <a href="{{ route('billpay.index') }}" class="btn btn-secondary">
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
                        @elseif(empty($billPayNumbers))
                            <div class="text-center py-4">
                                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No BillPay control numbers found.</p>
                                <a href="{{ route('billpay.create') }}" class="btn btn-primary">Create First BillPay</a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Control Number</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Currency</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($billPayNumbers as $billPay)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('billpay.show', $billPay->bill_pay_number) }}">
                                                        {{ $billPay->bill_pay_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $billPay->bill_description ?? 'N/A' }}</td>
                                                <td>{{ number_format($billPay->bill_amount ?? 0, 2) }}</td>
                                                <td>{{ $billPay->bill_currency ?? 'TZS' }}</td>
                                                <td>
                                                    <span class="badge bg-label-{{ $billPay->bill_status == 'ACTIVE' ? 'success' : 'secondary' }}">
                                                        {{ $billPay->bill_status }}
                                                    </span>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($billPay->created_at ?? 'now')->format('M d, Y H:i') }}</td>
                                                <td>
                                                    <a href="{{ route('billpay.show', $billPay->bill_pay_number) }}" 
                                                       class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
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
        </div>
        
        <!-- Quick Stats -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ count($billPayNumbers) }}</h3>
                        <p>Total BillPay Numbers</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ collect($billPayNumbers)->where('status', 'ACTIVE')->count() }}</h3>
                        <p>Active Numbers</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ collect($billPayNumbers)->sum('amount') }}</h3>
                        <p>Total Amount</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>FEEDTANPAY</h3>
                        <p>Control Prefix</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hashtag"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
