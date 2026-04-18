@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Create BillPay Control Number</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('billpay.index') }}">BillPay</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Insufficient Funds Warning -->
@if(session('warning_type') == 'insufficient_funds')
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <div class="mr-3">
            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
        </div>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-1">Insufficient Funds Alert</h5>
            <p class="mb-2">{{ session('error') }}</p>
            <div class="bg-light p-3 rounded mb-2">
                <h6 class="text-dark mb-2">What to do:</h6>
                <ul class="mb-0">
                    <li><strong>Top up your Halopesa account</strong> - Visit your nearest Halopesa agent or use mobile banking</li>
                    <li><strong>Check your balance</strong> - Ensure you have sufficient funds for this transaction</li>
                    <li><strong>Try again</strong> - After topping up, submit this form again</li>
                    <li><strong>Contact support</strong> - If you need assistance with topping up your account</li>
                </ul>
            </div>
        </div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
@endif

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">BillPay Details</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('billpay.store') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bill_type">Bill Type</label>
                                        <select class="form-control @error('bill_type') is-invalid @enderror" 
                                                id="bill_type" name="bill_type" required>
                                            <option value="">Select Type</option>
                                            <option value="order" {{ old('bill_type') == 'order' ? 'selected' : '' }}>
                                                Order Control Number
                                            </option>
                                            <option value="customer" {{ old('bill_type') == 'customer' ? 'selected' : '' }}>
                                                Customer Control Number
                                            </option>
                                            <option value="bulk_order" {{ old('bill_type') == 'bulk_order' ? 'selected' : '' }}>
                                                Bulk Order Control Numbers
                                            </option>
                                            <option value="bulk_customer" {{ old('bill_type') == 'bulk_customer' ? 'selected' : '' }}>
                                                Bulk Customer Control Numbers
                                            </option>
                                        </select>
                                        @error('bill_type')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bill_payment_mode">Payment Mode</label>
                                        <select class="form-control @error('bill_payment_mode') is-invalid @enderror" 
                                                id="bill_payment_mode" name="bill_payment_mode">
                                            <option value="ALLOW_PARTIAL_AND_OVER_PAYMENT" {{ old('bill_payment_mode') == 'ALLOW_PARTIAL_AND_OVER_PAYMENT' ? 'selected' : '' }}>
                                                Allow Partial & Over Payment
                                            </option>
                                            <option value="EXACT" {{ old('bill_payment_mode') == 'EXACT' ? 'selected' : '' }}>
                                                Exact Payment Only
                                            </option>
                                        </select>
                                        @error('bill_payment_mode')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Order Control Number Fields -->
                            <div id="order_fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bill_description">Bill Description</label>
                                            <input type="text" class="form-control @error('bill_description') is-invalid @enderror" 
                                                   id="bill_description" name="bill_description" 
                                                   value="{{ old('bill_description') }}" 
                                                   placeholder="Order Payment">
                                            @error('bill_description')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bill_amount">Bill Amount *</label>
                                            <input type="number" class="form-control @error('bill_amount') is-invalid @enderror" 
                                                   id="bill_amount" name="bill_amount" 
                                                   value="{{ old('bill_amount') }}" 
                                                   step="0.01" min="100" required>
                                            @error('bill_amount')
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
                                            <label for="bill_reference">Bill Reference (Optional)</label>
                                            <input type="text" class="form-control @error('bill_reference') is-invalid @enderror" 
                                                   id="bill_reference" name="bill_reference" 
                                                   value="{{ old('bill_reference') }}" 
                                                   placeholder="FEEDTANPAY01">
                                            @error('bill_reference')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Customer Control Number Fields -->
                            <div id="customer_fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer_name">Customer Name *</label>
                                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                                   id="customer_name" name="customer_name" 
                                                   value="{{ old('customer_name') }}" required>
                                            @error('customer_name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer_email">Customer Email</label>
                                            <input type="email" class="form-control @error('customer_email') is-invalid @enderror" 
                                                   id="customer_email" name="customer_email" 
                                                   value="{{ old('customer_email') }}">
                                            @error('customer_email')
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
                                            <label for="customer_phone">Customer Phone</label>
                                            <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror" 
                                                   id="customer_phone" name="customer_phone" 
                                                   value="{{ old('customer_phone') }}" 
                                                   placeholder="255712345678">
                                            @error('customer_phone')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer_bill_amount">Bill Amount *</label>
                                            <input type="number" class="form-control @error('customer_bill_amount') is-invalid @enderror" 
                                                   id="customer_bill_amount" name="bill_amount" 
                                                   value="{{ old('bill_amount') }}" 
                                                   step="0.01" min="100" required>
                                            @error('customer_bill_amount')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bulk Fields -->
                            <div id="bulk_fields" style="display: none;">
                                <div class="form-group">
                                    <label for="bulk_data">Bulk Data (JSON Format)</label>
                                    <textarea class="form-control @error('bulk_data') is-invalid @enderror" 
                                              id="bulk_data" name="bulk_data" 
                                              rows="10" placeholder='[{"billDescription": "Bill 1", "billAmount": 1000}, {"billDescription": "Bill 2", "billAmount": 2000}]'>{{ old('bulk_data') }}</textarea>
                                    @error('bulk_data')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        For bulk orders: [{"billDescription": "Description", "billAmount": 1000, "billReference": "REF001"}]<br>
                                        For bulk customers: [{"customerName": "John Doe", "customerEmail": "john@example.com", "billDescription": "Bill 1"}]
                                    </small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="icheck-primary">
                                    <input type="checkbox" id="confirm" name="confirm" required>
                                    <label for="confirm">I confirm that bill details are correct</label>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus mr-2"></i> Create Control Number
                                    </button>
                                    <a href="{{ route('billpay.index') }}" class="btn btn-secondary">
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
                        <h3 class="card-title">BillPay Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-receipt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Control Numbers</span>
                                <span class="info-box-number">FEEDTANPAY</span>
                            </div>
                        </div>
                        
                        <h5>BillPay Types:</h5>
                        <ul>
                            <li><strong>Order:</strong> Generic bill for any order</li>
                            <li><strong>Customer:</strong> Bill tied to specific customer</li>
                            <li><strong>Bulk Orders:</strong> Multiple order bills at once</li>
                            <li><strong>Bulk Customers:</strong> Multiple customer bills at once</li>
                        </ul>
                        
                        <h5>Payment Modes:</h5>
                        <ul>
                            <li><strong>Allow Partial & Over:</strong> Customers can pay partial or excess amounts</li>
                            <li><strong>Exact Only:</strong> Customers must pay exact amount</li>
                        </ul>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Control numbers will be generated automatically if not provided.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
// Form submission debugging
document.querySelector('form').addEventListener('submit', function(e) {
    console.log('Form submission triggered');
    console.log('Bill type:', document.getElementById('bill_type').value);
    console.log('Bill amount:', document.querySelector('input[name="bill_amount"]').value);
    console.log('Confirm checked:', document.getElementById('confirm').checked);
    
    // Ensure bill amount field has required attribute
    const billAmountInput = document.querySelector('input[name="bill_amount"]');
    if (!billAmountInput.hasAttribute('required')) {
        billAmountInput.setAttribute('required', 'required');
        console.log('Added required attribute to bill amount');
    }
    
    // Prevent submission if validation fails
    if (!this.checkValidity()) {
        e.preventDefault();
        console.log('Form validation failed');
        return false;
    }
    
    console.log('Form validation passed, submitting...');
});

document.getElementById('bill_type').addEventListener('change', function() {
    console.log('Bill type changed to:', this.value);
    const orderFields = document.getElementById('order_fields');
    const customerFields = document.getElementById('customer_fields');
    const bulkFields = document.getElementById('bulk_fields');
    
    // Hide all fields first
    orderFields.style.display = 'none';
    customerFields.style.display = 'none';
    bulkFields.style.display = 'none';
    
    // Remove required attributes except bill_amount
    const allInputs = document.querySelectorAll('#order_fields input, #customer_fields input, #bulk_fields textarea');
    allInputs.forEach(input => {
        if (input.id !== 'bill_amount' && input.id !== 'customer_bill_amount') {
            input.removeAttribute('required');
        }
    });
    
    // Ensure bill amount always has required attribute and is visible
    const billAmountInput = document.querySelector('input[name="bill_amount"]');
    const customerBillAmountInput = document.querySelector('input[name="bill_amount"]');
    
    if (billAmountInput) {
        billAmountInput.setAttribute('required', 'required');
        billAmountInput.style.display = 'block';
        billAmountInput.style.visibility = 'visible';
        billAmountInput.style.opacity = '1';
        billAmountInput.style.position = 'static';
        billAmountInput.removeAttribute('aria-hidden');
        billAmountInput.tabIndex = '0';
    }
    
    if (customerBillAmountInput && customerBillAmountInput !== billAmountInput) {
        customerBillAmountInput.setAttribute('required', 'required');
        customerBillAmountInput.style.display = 'block';
        customerBillAmountInput.style.visibility = 'visible';
        customerBillAmountInput.style.opacity = '1';
        customerBillAmountInput.style.position = 'static';
        customerBillAmountInput.removeAttribute('aria-hidden');
        customerBillAmountInput.tabIndex = '0';
    }
    
    // Show relevant fields and set required
    switch(this.value) {
        case 'order':
            orderFields.style.display = 'block';
            document.getElementById('bill_description').removeAttribute('required');
            break;
        case 'customer':
            customerFields.style.display = 'block';
            document.getElementById('customer_name').setAttribute('required', 'required');
            break;
        case 'bulk_order':
        case 'bulk_customer':
            bulkFields.style.display = 'block';
            document.getElementById('bulk_data').setAttribute('required', 'required');
            break;
    }
});

document.getElementById('customer_phone').addEventListener('input', function() {
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

// Initialize form on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('BillPay form loaded');
    
    // Remove and recreate all bill amount inputs to fix focusable issue
    const billAmountInputs = document.querySelectorAll('input[name="bill_amount"]');
    billAmountInputs.forEach(input => {
        if (input) {
            const parent = input.parentNode;
            const newInput = document.createElement('input');
            
            // Copy all attributes
            for (let attr of input.attributes) {
                newInput.setAttribute(attr.name, attr.value);
            }
            
            // Ensure proper attributes for focusability
            newInput.setAttribute('type', 'number');
            newInput.setAttribute('name', 'bill_amount');
            newInput.setAttribute('class', 'form-control');
            newInput.setAttribute('step', '0.01');
            newInput.setAttribute('min', '100');
            newInput.setAttribute('required', 'required');
            newInput.setAttribute('tabindex', '0');
            
            // Ensure visible styles
            newInput.style.display = 'block';
            newInput.style.visibility = 'visible';
            newInput.style.opacity = '1';
            newInput.style.position = 'static';
            
            // Replace old input
            parent.replaceChild(newInput, input);
            console.log('Recreated bill amount input:', newInput.id || 'unnamed');
        }
    });
    
    // Add click event listener to form submit button
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.addEventListener('click', function(e) {
            console.log('Submit button clicked');
            
            // Check form validity before submission
            const form = document.querySelector('form');
            if (form && !form.checkValidity()) {
                e.preventDefault();
                console.log('Form validation failed, preventing submission');
                
                // Focus on first invalid field
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    console.log('Focused on invalid field:', firstInvalid.name || firstInvalid.id);
                }
                return false;
            }
            
            console.log('Form validation passed, submitting form');
            form.submit();
        });
    }
});
</script>
@endpush
@endsection
