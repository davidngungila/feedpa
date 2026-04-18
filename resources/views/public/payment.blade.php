<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fanya Malipo Salama - ClickPesa</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .form-control-lg {
            border-radius: 10px;
        }

        .btn-lg {
            border-radius: 10px;
            padding: 12px 30px;
        }

        .card {
            border-radius: 15px;
        }

        .card-header {
            border-radius: 15px 15px 0 0 !important;
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
        }

        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #218838 0%, #1eb980 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-gradient-primary text-white text-center py-4">
                        <h3 class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            Fanya Malipo Salama
                        </h3>
                        <p class="mb-0 mt-2 small">Mfumo wa Malipo wa Kisasa na Salama</p>
                    </div>
                    
                    <div class="card-body p-5">
                        <form id="paymentForm" method="POST" action="/payments/store">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            
                            <!-- Amount Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="amount" class="form-label fw-bold">
                                        <i class="fas fa-money-bill-wave me-2"></i>
                                        Kiasi (TZS)
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text">TZS</span>
                                        <input type="number" 
                                               class="form-control" 
                                               id="amount" 
                                               name="amount"
                                               placeholder="Andika kiasi"
                                               min="100"
                                               max="1000000"
                                               step="100"
                                               required>
                                    </div>
                                    <div class="form-text small">Kiasi cha chini ni TZS 100, cha juu ni TZS 1,000,000</div>
                                </div>
                            </div>

                            <!-- Payer Name Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="payer_name" class="form-label fw-bold">
                                        <i class="fas fa-user me-2"></i>
                                        Jina la Mwanachama
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="payer_name" 
                                           name="payer_name"
                                           placeholder="Andika jina lako kamili"
                                           maxlength="100"
                                           required>
                                    <div class="form-text small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Tafadhali weka jina lako kamili kama lilivyo kwenye kitambulisho
                                    </div>
                                </div>
                            </div>

                            <!-- Phone Number Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="phone_number" class="form-label fw-bold">
                                        <i class="fas fa-phone me-2"></i>
                                        Namba ya Simu
                                    </label>
                                    <input type="tel" 
                                           class="form-control form-control-lg" 
                                           id="phone_number" 
                                           name="phone_number"
                                           placeholder="255712345678"
                                           pattern="255[67]\d{8}"
                                           maxlength="12"
                                           required>
                                    <div class="form-text small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Format: 255712345678 au 255612345678 (Tanzania)
                                    </div>
                                </div>
                            </div>

                            <!-- Description Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label for="description" class="form-label fw-bold">
                                        <i class="fas fa-bullseye me-2"></i>
                                        Maelezo ya Malipo
                                    </label>
                                    <textarea class="form-control form-control-lg" 
                                              id="description" 
                                              name="description"
                                              rows="3"
                                              placeholder="Eleza madhumuni ya malipo"
                                              maxlength="255"></textarea>
                                    <div class="form-text small">Tafadhali eleza kwa ufupi madhumuni ya malipo yako (mstari wa 255)</div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn">
                                        <i class="fas fa-lock me-2"></i>
                                        Fanya Malipo Sasa
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-footer text-center py-3 bg-light">
                        <small class="text-muted">
                            <i class="fas fa-headset me-2"></i>
                            Kwa msaada: +255 712 345 678 | support@clickpesa.com
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Inasubiri...</span>
                    </div>
                    <h5>Inachakata malipo yako...</h5>
                    <p class="text-muted mb-0">Tafadhali subiri, usifanye kitu.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('paymentForm');
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        
        // Phone number formatting
        const phoneInput = document.getElementById('phone_number');
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0 && !value.startsWith('255')) {
                value = '255' + value;
            }
            if (value.length > 12) {
                value = value.substring(0, 12);
            }
            e.target.value = value;
        });
        
        // Amount formatting
        const amountInput = document.getElementById('amount');
        amountInput.addEventListener('input', function(e) {
            let value = parseInt(e.target.value);
            if (value < 100 || value > 1000000) {
                e.target.setCustomValidity('Kiasi cha chini ni TZS 100, cha juu ni TZS 1,000,000');
            } else {
                e.target.setCustomValidity('');
            }
        });
        
        // Description character count
        const descriptionInput = document.getElementById('description');
        descriptionInput.addEventListener('input', function(e) {
            const remaining = 255 - e.target.value.length;
            const helpText = e.target.parentElement.querySelector('.form-text');
            if (helpText) {
                helpText.textContent = `Tafadhali eleza kwa ufupi madhumuni ya malipo yako (baki ${remaining} herufi)`;
            }
        });
        
        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get payer name input
            const payerNameInput = document.getElementById('payer_name');
            
            // Validate payer name
            const payerName = payerNameInput.value.trim();
            if (payerName.length < 2) {
                alert('Tafadhali weka jina kamili la mwanachama');
                payerNameInput.focus();
                return;
            }
            
            if (payerName.length > 100) {
                alert('Jina la mwanachama haliwezi kuwa zaidi ya herufi 100');
                payerNameInput.focus();
                return;
            }
            
            // Validate phone number
            const phone = phoneInput.value;
            if (!/^255[67]\d{8}$/.test(phone)) {
                alert('Tafadhali weka namba ya simu sahihi kwa mtindo wa Tanzania. Mifano: 255712345678 au 255612345678');
                phoneInput.focus();
                return;
            }
            
            // Validate amount
            const amount = parseFloat(amountInput.value);
            if (amount < 100) {
                alert('Kiasi cha chini cha malipo ni TZS 100');
                amountInput.focus();
                return;
            }
            
            if (amount > 1000000) {
                alert('Kiasi cha juu cha malipo ni TZS 1,000,000');
                amountInput.focus();
                return;
            }
            
            // Validate description length
            const description = descriptionInput.value;
            if (description.length > 255) {
                alert('Maelezo hayawezi kuwa zaidi ya herufi 255');
                descriptionInput.focus();
                return;
            }
            
            // Show loading modal
            loadingModal.show();
            
            // Submit form after a short delay for UX
            setTimeout(function() {
                form.submit();
            }, 1000);
        });
    });
    </script>
</body>
</html>
