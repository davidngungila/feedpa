@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-plus-circle me-2"></i>
                    Simple Payment
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payments.history') }}">Payments</a></li>
                    <li class="breadcrumb-item active">Simple Payment</li>
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
                                                   placeholder="+255 7xx xxx xxx" 
                                                   maxlength="12" required>
                                        </div>
                                        @error('phone_number')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <small class="form-text text-muted">Format: 255712345678</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Details Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="amount" class="form-label">
                                            <i class="fas fa-money-bill-wave me-1"></i>
                                            Amount (TZS)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </span>
                                            <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                                   id="amount" name="amount" 
                                                   placeholder="Enter amount" 
                                                   min="100" max="1000000" required>
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
                                        <label for="description" class="form-label">
                                            <i class="fas fa-comment me-1"></i>
                                            Description (Optional)
                                        </label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" 
                                                  rows="3" 
                                                  placeholder="Payment description"></textarea>
                                        </div>
                                        @error('description')
                                            <div class="invalid-feedback">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <small class="form-text text-muted">Optional payment description</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="confirm" required>
                                        <label class="form-check-label" for="confirm">
                                            I confirm that all payment details are correct and I authorize this transaction
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                        <i class="fas fa-lock me-2"></i>
                                        Process Payment
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    body {
        background-color: #ffffff;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .payment-container {
        max-width: 800px;
        width: 100%;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .payment-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 30px;
        text-align: center;
        border-radius: 12px 12px 0 0;
    }

    .payment-header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
    }

    .payment-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
    }

    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 15px;
    }

    .card-title {
        color: #495057;
        font-weight: 600;
        margin: 0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
        display: block;
    }

    .input-group {
        position: relative;
        display: flex;
        align-items: stretch;
    }

    .input-group-text {
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        border-right: none;
        padding: 12px;
        border-radius: 8px 0 0 8px 0 8px;
        display: flex;
        align-items: center;
        font-size: 14px;
        color: #6c757d;
    }

    .form-control {
        border: 1px solid #e9ecef;
        border-radius: 0 8px 8px 0 8px;
        padding: 12px 15px;
        font-size: 16px;
        transition: all 0.3s ease;
        flex: 1;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: none;
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 15px 30px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
    }

    .form-check {
        margin-bottom: 20px;
    }

    .form-check-input {
        margin-right: 10px;
    }

    .form-check-label {
        font-weight: 500;
        color: #495057;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
        list-style: none;
        display: flex;
        flex-wrap: wrap;
    }

    .breadcrumb-item {
        font-size: 0.875rem;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        padding: 0 8px;
        color: #6c757d;
    }

    .breadcrumb-item.active {
        color: #495057;
        font-weight: 600;
    }

    .breadcrumb-item a {
        color: #6c757d;
        text-decoration: none;
    }

    .breadcrumb-item a:hover {
        color: #495057;
        text-decoration: underline;
    }

    .badge {
        padding: 4px 8px;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 4px;
    }

    .bg-success {
        background-color: #28a745;
        color: white;
    }

    .text-muted {
        color: #6c757d;
        font-size: 0.875rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .payment-container {
            max-width: 95%;
            margin: 10px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .btn-lg {
            padding: 12px 20px;
            font-size: 16px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Payment form handling
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('paymentForm');
        const submitBtn = document.getElementById('submitBtn');
        const confirmCheckbox = document.getElementById('confirm');
        
        // Form validation
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if confirmation is checked
            if (!confirmCheckbox.checked) {
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
            
            // Validate customer name
            const nameInput = document.getElementById('payer_name');
            if (!nameInput.value.trim()) {
                e.preventDefault();
                showAlert('error', 'Please enter customer name.');
                nameInput.focus();
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Payment...';
            
            // Show processing modal
            showProcessingModal();
            
            // Submit form
            form.submit();
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
        
        phoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length === 9 && value.startsWith('0')) {
                // Convert 07... to 2557...
                value = '255' + value.substring(1);
                this.value = value;
            }
        });
        
        // Show alert function
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
                                <p class="text-muted">Please wait while we process your payment...</p>
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
    });
</script>
@endpush

@endsection
