@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-plus-circle me-2"></i>
                    Create Payment
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payments.history') }}">Payments</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Advanced Payment Creation Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-left-4 border-left-primary shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="card-title mb-1">
                                    <i class="fas fa-credit-card me-2"></i>
                                    FEEDTAN CMG Payment Gateway
                                </h3>
                                <p class="text-muted mb-0">Create secure payment transactions with instant processing</p>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-success fs-6">Live</div>
                                <div class="text-muted small">All Networks Supported</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Payment Form -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-edit me-2"></i>
                            Payment Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
                            @csrf
                            
                            <!-- Customer Information Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-user me-2"></i>
                                        Customer Information
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="payer_name" class="form-label">
                                            <i class="fas fa-user me-1"></i>
                                            Customer Name
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" class="form-control @error('payer_name') is-invalid @enderror" 
                                                   id="payer_name" name="payer_name" 
                                                   value="{{ old('payer_name') }}" 
                                                   placeholder="Enter customer name"
                                                   maxlength="100" required>
                                        </div>
                                        @error('payer_name')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <small class="form-text text-muted">Full name of the customer</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="phone_number" class="form-label">
                                            <i class="fas fa-phone me-1"></i>
                                            Phone Number
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-phone"></i>
                                            </span>
                                            <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" 
                                                   id="phone_number" name="phone_number" 
                                                   value="{{ old('phone_number') }}" 
                                                   placeholder="255712345678" required>
                                        </div>
                                        @error('phone_number')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <small class="form-text text-muted">Format: 255712345678 (Tanzania numbers only)</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Details Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-money-bill-wave me-2"></i>
                                        Payment Details
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="amount" class="form-label">
                                            <i class="fas fa-money-bill-wave me-1"></i>
                                            Amount (TZS)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-tanzania-shilling"></i>
                                                TZS
                                            </span>
                                            <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                                   id="amount" name="amount" 
                                                   value="{{ old('amount') }}" 
                                                   min="100" max="1000000" step="0.01" required>
                                        </div>
                                        @error('amount')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <small class="form-text text-muted">Minimum: 100 TZS, Maximum: 1,000,000 TZS</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="payment_method" class="form-label">
                                            <i class="fas fa-mobile-alt me-1"></i>
                                            Payment Method
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-mobile-alt"></i>
                                            </span>
                                            <select class="form-select" id="payment_method" name="payment_method">
                                                <option value="halopesa">Halopesa</option>
                                                <option value="tigopesa">Tigopesa</option>
                                                <option value="airtelmoney">Airtel Money</option>
                                                <option value="mpesa">M-Pesa</option>
                                                <option value="ezypesa">Ezy Pesa</option>
                                            </select>
                                        </div>
                                        <small class="form-text text-muted">Select customer's mobile money provider</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Additional Information
                                    </h6>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label for="description" class="form-label">
                                            <i class="fas fa-comment me-1"></i>
                                            Description (Optional)
                                        </label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" 
                                                  rows="3" maxlength="255" 
                                                  placeholder="Enter payment description or notes">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <div class="d-flex justify-content-between">
                                            <small class="form-text text-muted">Additional details about this payment</small>
                                            <small class="form-text text-muted">
                                                <span id="charCount">{{ 255 - strlen(old('description') ?? '') }}</span> characters remaining
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Confirmation Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-warning alert-dismissible">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Important:</strong> Please verify all payment details before proceeding. The customer will receive a USSD prompt to confirm this payment.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input @error('confirm') is-invalid @enderror" 
                                               type="checkbox" id="confirm" name="confirm" required>
                                        <label class="form-check-label" for="confirm">
                                            <i class="fas fa-check-circle me-1"></i>
                                            I confirm that all payment details are correct and I authorize this transaction
                                        </label>
                                        @error('confirm')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                            <i class="fas fa-paper-plane me-2"></i>
                                            Initiate Payment
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetForm()">
                                            <i class="fas fa-redo me-2"></i>
                                            Reset Form
                                        </button>
                                        <a href="{{ route('payments.history') }}" class="btn btn-outline-danger btn-lg">
                                            <i class="fas fa-times me-2"></i>
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar Information -->
            <div class="col-lg-4">
                <!-- Payment Process Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Payment Process
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="process-timeline">
                            <div class="process-step">
                                <div class="process-marker bg-primary">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="process-content">
                                    <h6>1. Enter Details</h6>
                                    <small class="text-muted">Fill in customer and payment information</small>
                                </div>
                            </div>
                            <div class="process-step">
                                <div class="process-marker bg-info">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <div class="process-content">
                                    <h6>2. Initiate Payment</h6>
                                    <small class="text-muted">Click to send USSD prompt to customer</small>
                                </div>
                            </div>
                            <div class="process-step">
                                <div class="process-marker bg-warning">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div class="process-content">
                                    <h6>3. Customer Confirms</h6>
                                    <small class="text-muted">Customer approves via USSD</small>
                                </div>
                            </div>
                            <div class="process-step">
                                <div class="process-marker bg-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="process-content">
                                    <h6>4. Payment Complete</h6>
                                    <small class="text-muted">Transaction processed and confirmed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supported Networks Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-network-wired me-2"></i>
                            Supported Networks
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="network-logo">
                                    <div class="avatar avatar-lg bg-success rounded-circle mx-auto mb-2">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <small class="fw-bold">Halopesa</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="network-logo">
                                    <div class="avatar avatar-lg bg-info rounded-circle mx-auto mb-2">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <small class="fw-bold">Tigopesa</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="network-logo">
                                    <div class="avatar avatar-lg bg-danger rounded-circle mx-auto mb-2">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <small class="fw-bold">Airtel Money</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="network-logo">
                                    <div class="avatar avatar-lg bg-primary rounded-circle mx-auto mb-2">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <small class="fw-bold">M-Pesa</small>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <small class="text-muted">All major Tanzanian mobile money networks supported</small>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            Today's Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="stat-item">
                                    <h3 class="text-primary mb-0">24</h3>
                                    <small class="text-muted">Total Payments</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item">
                                    <h3 class="text-success mb-0">98%</h3>
                                    <small class="text-muted">Success Rate</small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Average processing time: <strong>2.5 seconds</strong></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
.border-left-4 {
    border-left-width: 4px !important;
}
.border-left-primary {
    border-left-color: #007bff !important;
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
.avatar-lg {
    width: 3.5rem;
    height: 3.5rem;
}

.process-timeline {
    position: relative;
}

.process-step {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    position: relative;
}

.process-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 2.5rem;
    left: 1.25rem;
    width: 2px;
    height: calc(100% + 0.5rem);
    background-color: #e9ecef;
}

.process-marker {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 1rem;
    flex-shrink: 0;
    font-size: 0.875rem;
}

.process-content {
    flex: 1;
    padding-top: 0.25rem;
}

.process-content h6 {
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.network-logo {
    text-align: center;
}

.stat-item h3 {
    font-size: 2rem;
    font-weight: 700;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
}

.card.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

@media (max-width: 768px) {
    .process-step {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .process-step:not(:last-child)::after {
        display: none;
    }
    
    .process-marker {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
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
// Character counter for description
document.getElementById('description').addEventListener('input', function() {
    const remaining = 255 - this.value.length;
    const charCountElement = document.getElementById('charCount');
    if (charCountElement) {
        charCountElement.textContent = remaining;
        if (remaining < 20) {
            charCountElement.classList.add('text-danger');
        } else {
            charCountElement.classList.remove('text-danger');
        }
    }
});

// Phone number validation and formatting
document.getElementById('phone_number').addEventListener('input', function() {
    // Remove any non-digit characters
    this.value = this.value.replace(/\D/g, '');
    
    // Validate Tanzania phone number format
    const phoneRegex = /^255[67]\d{8}$/;
    const isValid = phoneRegex.test(this.value);
    
    if (this.value.length > 0 && !isValid && this.value.length >= 10) {
        this.classList.add('is-invalid');
        showPhoneError('Invalid Tanzania phone number format. Use: 255712345678');
    } else {
        this.classList.remove('is-invalid');
        hidePhoneError();
    }
});

// Amount validation
document.getElementById('amount').addEventListener('input', function() {
    const amount = parseFloat(this.value);
    const minAmount = 100;
    const maxAmount = 1000000;
    
    if (amount < minAmount) {
        this.classList.add('is-invalid');
        showAmountError('Minimum amount is 100 TZS');
    } else if (amount > maxAmount) {
        this.classList.add('is-invalid');
        showAmountError('Maximum amount is 1,000,000 TZS');
    } else {
        this.classList.remove('is-invalid');
        hideAmountError();
    }
});

// Form validation before submission
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const confirmCheckbox = document.getElementById('confirm');
    
    // Check if confirmation is checked
    if (!confirmCheckbox.checked) {
        e.preventDefault();
        showAlert('warning', 'Please confirm that all payment details are correct.');
        confirmCheckbox.focus();
        return;
    }
    
    // Validate phone number
    const phoneInput = document.getElementById('phone_number');
    const phoneRegex = /^255[67]\d{8}$/;
    if (!phoneRegex.test(phoneInput.value)) {
        e.preventDefault();
        showAlert('error', 'Please enter a valid Tanzania phone number.');
        phoneInput.focus();
        return;
    }
    
    // Validate amount
    const amountInput = document.getElementById('amount');
    const amount = parseFloat(amountInput.value);
    if (amount < 100 || amount > 1000000) {
        e.preventDefault();
        showAlert('error', 'Amount must be between 100 TZS and 1,000,000 TZS.');
        amountInput.focus();
        return;
    }
    
    // Prevent default form submission
    e.preventDefault();
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Payment...';
    
    // Show processing modal
    showProcessingModal();
    
    // Get form data
    const formData = new FormData(this);
    const data = {
        amount: formData.get('amount'),
        phone_number: formData.get('phone_number'),
        payer_name: formData.get('payer_name'),
        description: formData.get('description')
    };
    
    // Make Ajax request
    fetch('/payments/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).getAttribute('content') || '',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        // Close processing modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('processingModal'));
        if (modal) modal.hide();
        
        if (result.success) {
            // Show success notification
            showAlert('success', result.message || 'Payment initiated successfully! USSD Push sent to your phone.');
            
            // Reset form after successful submission
            this.reset();
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Process Payment';
            
            // Update character count
            const charCountElement = document.getElementById('charCount');
            if (charCountElement) {
                charCountElement.textContent = '255';
            }
            
            // Clear any validation states
            document.querySelectorAll('.is-invalid').forEach(element => {
                element.classList.remove('is-invalid');
            });
        } else {
            showAlert('error', result.message || 'Payment initiation failed. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Process Payment';
        }
    })
    .catch(error => {
        // Close processing modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('processingModal'));
        if (modal) modal.hide();
        
        showAlert('error', 'Network error. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Process Payment';
    });
});

// Reset form function
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        document.getElementById('paymentForm').reset();
        document.getElementById('charCount').textContent = '255';
        
        // Clear any validation states
        document.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid');
        });
        
        showAlert('info', 'Form has been reset successfully.');
    }
}

// Show phone error
function showPhoneError(message) {
    let errorDiv = document.getElementById('phoneError');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'phoneError';
        errorDiv.className = 'invalid-feedback';
        document.getElementById('phone_number').parentNode.appendChild(errorDiv);
    }
    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>' + message;
}

// Hide phone error
function hidePhoneError() {
    const errorDiv = document.getElementById('phoneError');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Show amount error
function showAmountError(message) {
    let errorDiv = document.getElementById('amountError');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'amountError';
        errorDiv.className = 'invalid-feedback';
        document.getElementById('amount').parentNode.appendChild(errorDiv);
    }
    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>' + message;
}

// Hide amount error
function hideAmountError() {
    const errorDiv = document.getElementById('amountError');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Show alert
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

// Show processing modal
function showProcessingModal() {
    const modalHtml = `
        <div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Processing...</span>
                        </div>
                        <h5>Processing Payment</h5>
                        <p class="text-muted">Please wait while we initiate the payment...</p>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('processingModal'));
    modal.show();
}

// Format amount with commas
function formatAmount(amount) {
    return new Intl.NumberFormat('en-TZ').format(amount);
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit form
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('paymentForm').requestSubmit();
        }
        // Escape to reset form
        if (e.key === 'Escape') {
            resetForm();
        }
    });
    
    // Auto-format phone number as user types
    const phoneInput = document.getElementById('phone_number');
    phoneInput.addEventListener('blur', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length === 9 && value.startsWith('0')) {
            // Convert 07... to 2557...
            value = '255' + value.substring(1);
            this.value = value;
        }
    });
});
</script>
@endpush

@endsection
