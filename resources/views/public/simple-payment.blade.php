<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Simple Payment - ClickPesa</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        
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
            max-width: 500px;
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
        }

        .payment-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .payment-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .payment-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .btn-payment {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 15px;
            font-size: 18px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-payment:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }

        .btn-payment:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        /* Alert animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        .custom-alert-overlay {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .alert {
            border: none;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            opacity: 0.5;
        }

        .btn-close:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <!-- Header -->
        <div class="payment-header">
            <h2><i class="fas fa-credit-card me-2"></i>Simple Payment</h2>
            <p>Secure and fast payment processing</p>
        </div>

        <!-- Body -->
        <div class="payment-body">
            <!-- Alert Container -->
            <div id="alertContainer"></div>

            <!-- Payment Form -->
            <form id="paymentForm">
                <!-- Amount -->
                <div class="form-group">
                    <label for="amount" class="form-label">
                        <i class="fas fa-money-bill-wave me-2"></i>Amount (TZS)
                    </label>
                    <input type="number" 
                           class="form-control" 
                           id="amount" 
                           name="amount" 
                           placeholder="Enter amount" 
                           min="100" 
                           step="100" 
                           required>
                </div>

                <!-- Phone Number -->
                <div class="form-group">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone me-2"></i>Phone Number
                    </label>
                    <input type="tel" 
                           class="form-control" 
                           id="phone" 
                           name="phone" 
                           placeholder="+255 7xx xxx xxx" 
                           pattern="[+][0-9]{12}" 
                           required>
                </div>

                <!-- Customer Name -->
                <div class="form-group">
                    <label for="customerName" class="form-label">
                        <i class="fas fa-user me-2"></i>Customer Name
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="customerName" 
                           name="customerName" 
                           placeholder="Enter your name" 
                           required>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-2"></i>Email (Optional)
                    </label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="your@email.com">
                </div>

                
                <!-- Description -->
                <div class="form-group">
                    <label for="description" class="form-label">
                        <i class="fas fa-comment me-2"></i>Description (Optional)
                    </label>
                    <textarea class="form-control" 
                              id="description" 
                              name="description" 
                              rows="3" 
                              placeholder="Payment description"></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-payment" id="submitBtn">
                    <i class="fas fa-lock me-2"></i>Process Payment
                </button>
            </form>

            <!-- Loading Spinner -->
            <div class="loading-spinner" id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Processing...</span>
                </div>
                <p class="mt-3">Processing your payment...</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Payment form handling
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('paymentForm');
            const submitBtn = document.getElementById('submitBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const alertContainer = document.getElementById('alertContainer');
            
            // Auto-format phone number as user types
            const phoneInput = document.getElementById('phone');
            phoneInput.addEventListener('blur', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length === 9 && value.startsWith('0')) {
                    // Convert 07... to 2557...
                    value = '255' + value.substring(1);
                    this.value = value;
                }
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = new FormData(form);
                const data = {
                    amount: formData.get('amount'),
                    phone_number: formData.get('phone'),
                    payer_name: formData.get('customerName'),
                    description: formData.get('description')
                };

                // Validate
                if (!validateForm(data)) {
                    return;
                }

                // Show processing modal immediately
                showProcessingModal();

                // Make actual API call to process payment with timeout
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout
                
                fetch('/payments/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).getAttribute('content') || ''
                    },
                    body: JSON.stringify(data),
                    signal: controller.signal
                })
                .then(response => {
                    clearTimeout(timeoutId);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(result => {
                    // Close processing modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('processingModal'));
                    if (modal) modal.hide();
                    
                    if (result.success) {
                        showAlert('success', 'Payment initiated successfully! Please check your phone to complete the transaction. Reference: ' + (result.orderReference || 'Processing...'));
                        // Reset form
                        form.reset();
                        submitBtn.style.display = 'block';
                    } else {
                        showAlert('error', result.message || 'Failed to initiate payment. Please try again.');
                        submitBtn.style.display = 'block';
                    }
                })
                .catch(error => {
                    clearTimeout(timeoutId);
                    console.error('Payment API Error:', error);
                    
                    // Close processing modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('processingModal'));
                    if (modal) modal.hide();
                    
                    let errorMessage = 'Network error occurred. Please check your connection and try again.';
                    if (error.name === 'AbortError') {
                        errorMessage = 'Payment request timed out. Please try again.';
                    } else if (error.message) {
                        errorMessage = error.message;
                    }
                    
                    showAlert('error', errorMessage);
                    submitBtn.style.display = 'block';
                });
            });

            function validateForm(data) {
                // Clear any existing alerts first
                const existingAlerts = document.querySelectorAll('.custom-alert-overlay');
                existingAlerts.forEach(alert => alert.remove());
                
                if (!data.amount || data.amount < 100) {
                    showAlert('Please enter a valid amount (minimum 100 TZS)', 'danger');
                    return false;
                }
                
                // Check if phone exists and is valid format
                if (!data.phone_number) {
                    showAlert('Please enter a valid phone number (255712345678)', 'danger');
                    return false;
                }
                
                // Validate phone format
                if (!data.phone_number.match(/^255[67]\d{8}$/)) {
                    showAlert('Please enter a valid phone number (255712345678)', 'danger');
                    return false;
                }
                
                if (!data.payer_name) {
                    showAlert('Please enter your name', 'danger');
                    return false;
                }
                return true;
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
        });
    </script>
</body>
</html>
