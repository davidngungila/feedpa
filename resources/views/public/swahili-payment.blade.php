<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Malipo - ClickPesa</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
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
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .payment-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 15px 15px 0 0;
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
            font-size: 14px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            outline: none;
        }

        .btn-payment {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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

        .btn-payment:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }

        .btn-payment:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .payment-container {
                max-width: 95%;
                margin: 10px;
            }
            
            .payment-body {
                padding: 30px 20px;
            }
            
            .btn-payment {
                padding: 12px 20px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <!-- Header -->
        <div class="payment-header">
            <h2>Malipo Salama</h2>
            <p>Fanya malipo salama kwa Tigo Pesa, M-Pesa, Halopesa, na Airtel Money</p>
        </div>

        <!-- Body -->
        <div class="payment-body">
            <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
                @csrf
                
                <!-- Customer Name -->
                <div class="form-group">
                    <label for="payer_name" class="form-label">
                        Jina La Mwanachama
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="payer_name" 
                           name="payer_name" 
                           placeholder="Andika jina lako kamili" 
                           maxlength="100" 
                           required>
                </div>

                <!-- Phone Number -->
                <div class="form-group">
                    <label for="phone_number" class="form-label">
                        Namba Ya Simu
                    </label>
                    <input type="tel" 
                           class="form-control" 
                           id="phone_number" 
                           name="phone_number" 
                           placeholder="255 7xx xxx xxx" 
                           maxlength="12" 
                           required>
                </div>

                <!-- Amount -->
                <div class="form-group">
                    <label for="amount" class="form-label">
                        Kiasi (TZS)
                    </label>
                    <input type="number" 
                           class="form-control" 
                           id="amount" 
                           name="amount" 
                           placeholder="Andika kiasi" 
                           min="500" 
                           max="5000000" 
                           required>
                </div>

                <!-- Payment Description -->
                <div class="form-group">
                    <label for="description" class="form-label">
                        Malipo Kwaajili Ya:
                    </label>
                    <textarea class="form-control" 
                              id="description" 
                              name="description" 
                              rows="3" 
                              placeholder="Andika maelezo ya malipo"></textarea>
                    <small class="text-muted">Maelezo ya ziada kuhusu malipo yako</small>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="btn-payment" id="submitBtn">
                        <i class="fas fa-lock me-2"></i>
                        Lipa Sasa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Payment form handling
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('paymentForm');
            const submitBtn = document.getElementById('submitBtn');
            
            // Form validation and submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = new FormData(form);
                const data = {
                    amount: formData.get('amount'),
                    phone_number: formData.get('phone_number'),
                    payer_name: formData.get('payer_name'),
                    description: formData.get('description')
                };
                
                // Validate form
                if (!data.payer_name || data.payer_name.trim() === '') {
                    showAlert('error', 'Tafadhali jina lako kamili.');
                    return;
                }
                
                if (!data.phone_number || !data.phone_number.match(/^255[67]\d{8}$/)) {
                    showAlert('error', 'Tafadhali namba ya simu sahi. Mfano: 255712345678');
                    return;
                }
                
                if (!data.amount || data.amount < 500) {
                    showAlert('error', 'Kiasi lazima ni TZS 500.');
                    return;
                }
                
                if (data.amount > 5000000) {
                    showAlert('error', 'Kiasi ya juu ni TZS 5,000,000.');
                    return;
                }
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Inatuma Malipo...';
                
                // Show processing modal
                showProcessingModal();
                
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
                        // Show success notification in Swahili
                        showUSSDNotification(result);
                    } else {
                        showAlert('error', result.message || 'Imeshindikwa malipo. Tafadhali jaribu tena.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Lipa Sasa';
                    }
                })
                .catch(error => {
                    // Close processing modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('processingModal'));
                    if (modal) modal.hide();
                    
                    // Reset button state but don't show error popup
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Lipa Sasa';
                    
                    // Log error to console for debugging but don't show to user
                    console.log('Payment error:', error);
                });
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
        
        // Show processing modal
        function showProcessingModal() {
            const modalHtml = `
                <div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body text-center py-4">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Inatuma...</span>
                                </div>
                                <h5>Inatuma Malipo</h5>
                                <h5 class="text-success">Tafadhari Thibitisha malipo yako kwa kuandika PIN</h5>
                                
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
        
        // Show USSD notification in Swahili
        function showUSSDNotification(data) {
            const notificationHtml = `
                <div class="modal fade" id="ussdNotification" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Malipo Yamechakatwa!
                                </h5>
                            </div>
                            <div class="modal-body text-center py-4">
                                <div class="mb-4">
                                    <i class="fas fa-mobile-alt text-success" style="font-size: 3rem;"></i>
                                </div>
                                
                                <div class="alert alert-info">
                                    <strong>${data.phone_number || data.phone}</strong>
                                </div>
                                <p class="mb-3">Kiasi:</p>
                                <div class="alert alert-success">
                                    <strong>TZS ${data.amount}</strong>
                                </div>
                                 </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" onclick="closeUSSDNotification()">
                                    <i class="fas fa-check me-2"></i>
                                    Nimefanya
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', notificationHtml);
            const notificationModal = new bootstrap.Modal(document.getElementById('ussdNotification'));
            notificationModal.show();
            
            // Reset form
            form.reset();
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Lipa Sasa';
        }
        
        // Close USSD notification
        function closeUSSDNotification() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('ussdNotification'));
            if (modal) modal.hide();
        }
    </script>
</body>
</html>
