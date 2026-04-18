@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">BillPay Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('billpay.index') }}">BillPay</a></li>
                    <li class="breadcrumb-item active">{{ $billPayNumber }}</li>
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
                        <h3 class="card-title">Control Number Information</h3>
                    </div>
                    <div class="card-body">
                        @if($error)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $error }}
                            </div>
                        @elseif($billPayData)
                            <!-- Summary Card -->
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h4 class="card-title mb-1">
                                                {{ $billPayData['billDescription'] ?? 'BillPay Control Number' }}
                                            </h4>
                                            @if(isset($billPayData['billCustomerName']) && !empty($billPayData['billCustomerName']))
                                                <p class="card-text text-muted mb-0">
                                                    <i class="fas fa-user"></i> {{ $billPayData['billCustomerName'] }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="col-md-4 text-right">
                                            @if(isset($billPayData['billAmount']))
                                                <h3 class="text-primary mb-0">
                                                    {{ number_format($billPayData['billAmount'], 2) }} {{ $billPayData['billCurrency'] ?? $billPayData['currency'] ?? 'TZS' }}
                                                </h3>
                                            @else
                                                <h5 class="text-muted mb-0">No Amount Set</h5>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Control Number</label>
                                        <p class="form-control-plaintext font-weight-bold">{{ $billPayData['billPayNumber'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment Mode</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge bg-label-info">
                                                {{ $billPayData['billPaymentMode'] ?? 'ALLOW_PARTIAL_AND_OVER_PAYMENT' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bill Description</label>
                                        <p class="form-control-plaintext">{{ $billPayData['billDescription'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bill Amount</label>
                                        <p class="form-control-plaintext">
                                            @if(isset($billPayData['billAmount']))
                                                <strong>{{ number_format($billPayData['billAmount'], 2) }} {{ $billPayData['billCurrency'] ?? $billPayData['currency'] ?? 'TZS' }}</strong>
                                            @else
                                                <span class="text-muted">No Amount Set</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customer Name</label>
                                        <p class="form-control-plaintext">
                                            @if(isset($billPayData['billCustomerName']) && !empty($billPayData['billCustomerName']))
                                                <strong>{{ $billPayData['billCustomerName'] }}</strong>
                                            @else
                                                <span class="text-muted">Not Specified</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bill Reference</label>
                                        <p class="form-control-plaintext">{{ $billPayData['billReference'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Customer Information -->
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-user"></i> Customer Information
                                    </h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customer Email</label>
                                        <p class="form-control-plaintext">{{ $billPayData['customerEmail'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customer Phone</label>
                                        <p class="form-control-plaintext">{{ $billPayData['customerPhone'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Information -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5 class="text-success mb-3">
                                        <i class="fas fa-credit-card"></i> Payment Information
                                    </h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Total Paid</label>
                                        <p class="form-control-plaintext">
                                            <strong>{{ number_format($billPayData['totalPaid'] ?? 0, 2) }} {{ $billPayData['billCurrency'] ?? 'TZS' }}</strong>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Remaining Amount</label>
                                        <p class="form-control-plaintext">
                                            <strong>{{ number_format(($billPayData['billAmount'] ?? 0) - ($billPayData['totalPaid'] ?? 0), 2) }} {{ $billPayData['billCurrency'] ?? 'TZS' }}</strong>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Last Payment</label>
                                        <p class="form-control-plaintext">
                                            {{ $billPayData['lastPaymentAt'] ? \Carbon\Carbon::parse($billPayData['lastPaymentAt'])->format('M d, Y H:i') : 'No payments yet' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Additional Information -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5 class="text-info mb-3">
                                        <i class="fas fa-info-circle"></i> Additional Information
                                    </h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bill Type</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge bg-{{ $billPayData['billType'] == 'customer' ? 'primary' : 'info' }}">
                                                {{ ucfirst($billPayData['billType'] ?? 'Order') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bill Status</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge bg-{{ ($billPayData['billStatus'] ?? 'ACTIVE') == 'ACTIVE' ? 'success' : 'secondary' }}">
                                                {{ $billPayData['billStatus'] ?? 'ACTIVE' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            @if($billPayData['notes'])
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <p class="form-control-plaintext">{{ $billPayData['notes'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Timestamps -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5 class="text-secondary mb-3">
                                        <i class="fas fa-clock"></i> Timestamps
                                    </h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Created At</label>
                                        <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($billPayData['createdAt'] ?? 'now')->format('M d, Y H:i:s') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Updated At</label>
                                        <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($billPayData['updatedAt'] ?? 'now')->format('M d, Y H:i:s') }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            @if($billPayData['createdBy'])
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Created By</label>
                                            <p class="form-control-plaintext">User ID: {{ $billPayData['createdBy'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="row">
                                <div class="col-12">
                                    <a href="{{ route('billpay.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-2"></i> Back to BillPay
                                    </a>
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal">
                                        <i class="fas fa-edit mr-2"></i> Update BillPay
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No BillPay details found for this control number.</p>
                                <a href="{{ route('billpay.index') }}" class="btn btn-primary">View All BillPay Numbers</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Actions & Info</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-info-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Control Number</span>
                                <span class="info-box-number">{{ $billPayNumber }}</span>
                            </div>
                        </div>
                        
                        <h5>Payment Instructions:</h5>
                        <ul>
                            <li>Share this control number with customers</li>
                            <li>Customers can pay at any mobile money agent</li>
                            <li>Use control number as payment reference</li>
                            <li>Payments will be automatically credited</li>
                        </ul>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-share-alt"></i>
                            <strong>Share:</strong> Copy the control number and share it with your customers.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Control Number:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $billPayNumber }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Update Modal -->
@if($billPayData)
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update BillPay Control Number</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('billpay.update', $billPayNumber) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="bill_description">Bill Description</label>
                        <input type="text" class="form-control" id="bill_description" name="bill_description" 
                               value="{{ $billPayData['billDescription'] ?? '' }}">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="bill_amount">Bill Amount (Optional)</label>
                        <input type="number" class="form-control" id="bill_amount" name="bill_amount" 
                               value="{{ $billPayData['billAmount'] ?? '' }}" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="bill_payment_mode">Payment Mode</label>
                        <select class="form-control" id="bill_payment_mode" name="bill_payment_mode">
                            <option value="ALLOW_PARTIAL_AND_OVER_PAYMENT" 
                                    {{ ($billPayData['billPaymentMode'] ?? '') == 'ALLOW_PARTIAL_AND_OVER_PAYMENT' ? 'selected' : '' }}>
                                Allow Partial & Over Payment
                            </option>
                            <option value="EXACT" 
                                    {{ ($billPayData['billPaymentMode'] ?? '') == 'EXACT' ? 'selected' : '' }}>
                                Exact Payment Only
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="bill_status">Bill Status</label>
                        <select class="form-control" id="bill_status" name="bill_status">
                            <option value="ACTIVE" {{ ($billPayData['billStatus'] ?? '') == 'ACTIVE' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="INACTIVE" {{ ($billPayData['billStatus'] ?? '') == 'INACTIVE' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update BillPay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function copyToClipboard() {
    const controlNumber = '{{ $billPayNumber }}';
    navigator.clipboard.writeText(controlNumber).then(function() {
        // Show success message
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
        alert.innerHTML = `
            <i class="fas fa-check-circle"></i>
            Control number copied to clipboard!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        // Remove alert after 3 seconds
        setTimeout(function() {
            alert.remove();
        }, 3000);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
    });
}
</script>
@endpush
@endsection
