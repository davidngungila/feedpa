@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Create Payout</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payouts.history') }}">Payouts</a></li>
                    <li class="breadcrumb-item active">Create</li>
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
                        <form action="{{ route('payouts.store') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payout_type">Payout Type</label>
                                        <select class="form-control @error('payout_type') is-invalid @enderror" 
                                                id="payout_type" name="payout_type" required>
                                            <option value="">Select Type</option>
                                            <option value="mobile_money" {{ old('payout_type') == 'mobile_money' ? 'selected' : '' }}>
                                                Mobile Money
                                            </option>
                                            <option value="bank" {{ old('payout_type') == 'bank' ? 'selected' : '' }}>
                                                Bank Transfer
                                            </option>
                                        </select>
                                        @error('payout_type')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="currency">Currency</label>
                                        <select class="form-control @error('currency') is-invalid @enderror" 
                                                id="currency" name="currency" required>
                                            <option value="TZS" {{ old('currency') == 'TZS' ? 'selected' : '' }}>TZS</option>
                                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                        </select>
                                        @error('currency')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">Amount</label>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" name="amount" 
                                               value="{{ old('amount') }}" 
                                               min="100" max="10000000" step="0.01" required>
                                        @error('amount')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        <small class="form-text text-muted">Minimum: 100, Maximum: 10,000,000</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="narrative">Narrative (Optional)</label>
                                        <input type="text" class="form-control @error('narrative') is-invalid @enderror" 
                                               id="narrative" name="narrative" 
                                               value="{{ old('narrative') }}" 
                                               maxlength="255">
                                        @error('narrative')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mobile Money Fields -->
                            <div id="mobile_money_fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="phone_number">Phone Number</label>
                                            <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" 
                                                   id="phone_number" name="phone_number" 
                                                   value="{{ old('phone_number') }}" 
                                                   placeholder="255712345678">
                                            @error('phone_number')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <small class="form-text text-muted">Format: 255712345678 (Tanzania numbers only)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank Fields -->
                            <div id="bank_fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bank_code">Bank Code</label>
                                            <select class="form-control @error('bank_code') is-invalid @enderror" 
                                                    id="bank_code" name="bank_code">
                                                <option value="">Select Bank</option>
                                                @foreach($banksList as $bank)
                                                    <option value="{{ $bank['code'] ?? $bank['bankCode'] ?? '' }}" 
                                                            {{ old('bank_code') == ($bank['code'] ?? $bank['bankCode'] ?? '') ? 'selected' : '' }}>
                                                        {{ $bank['name'] ?? $bank['bankName'] ?? 'Unknown Bank' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('bank_code')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bank_account">Bank Account Number</label>
                                            <input type="text" class="form-control @error('bank_account') is-invalid @enderror" 
                                                   id="bank_account" name="bank_account" 
                                                   value="{{ old('bank_account') }}">
                                            @error('bank_account')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="account_name">Account Name</label>
                                            <input type="text" class="form-control @error('account_name') is-invalid @enderror" 
                                                   id="account_name" name="account_name" 
                                                   value="{{ old('account_name') }}">
                                            @error('account_name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="icheck-primary">
                                    <input type="checkbox" id="confirm" name="confirm" required>
                                    <label for="confirm">I confirm that payout details are correct</label>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane mr-2"></i> Initiate Payout
                                    </button>
                                    <a href="{{ route('payouts.history') }}" class="btn btn-secondary">
                                        <i class="fas fa-times mr-2"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Payout Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-info-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Supported Methods</span>
                                <span class="info-box-number">Mobile & Bank</span>
                            </div>
                        </div>
                        
                        <h5>Payout Process:</h5>
                        <ol>
                            <li>Select payout type (Mobile Money or Bank)</li>
                            <li>Enter recipient details</li>
                            <li>Enter amount and narrative</li>
                            <li>Click "Initiate Payout"</li>
                            <li>Payout processed and confirmed</li>
                        </ol>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Payouts may take 1-3 business days to process depending on the method.
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Important:</strong> Double-check all recipient details before submitting.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.getElementById('payout_type').addEventListener('change', function() {
    const mobileFields = document.getElementById('mobile_money_fields');
    const bankFields = document.getElementById('bank_fields');
    
    if (this.value === 'mobile_money') {
        mobileFields.style.display = 'block';
        bankFields.style.display = 'none';
        document.getElementById('phone_number').required = true;
        document.getElementById('bank_code').required = false;
        document.getElementById('bank_account').required = false;
        document.getElementById('account_name').required = false;
    } else if (this.value === 'bank') {
        mobileFields.style.display = 'none';
        bankFields.style.display = 'block';
        document.getElementById('phone_number').required = false;
        document.getElementById('bank_code').required = true;
        document.getElementById('bank_account').required = true;
        document.getElementById('account_name').required = true;
    } else {
        mobileFields.style.display = 'none';
        bankFields.style.display = 'none';
        document.getElementById('phone_number').required = false;
        document.getElementById('bank_code').required = false;
        document.getElementById('bank_account').required = false;
        document.getElementById('account_name').required = false;
    }
});

document.getElementById('phone_number').addEventListener('input', function() {
    // Remove any non-digit characters
    this.value = this.value.replace(/\D/g, '');
    
    // Ensure it starts with 255
    if (this.value.length > 0 && !this.value.startsWith('255')) {
        this.value = '255' + this.value;
    }
    
    // Limit to 12 digits (255 + 9 digits)
    if (this.value.length > 12) {
        this.value = this.value.substring(0, 12);
    }
});
</script>
@endpush
@endsection
