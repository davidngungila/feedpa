<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin PIN - FeedPesa</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .pin-container {
            max-width: 400px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .pin-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 15px 15px 0 0;
        }

        .pin-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .pin-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .pin-body {
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
            text-align: center;
            font-size: 20px;
            letter-spacing: 2px;
        }

        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            outline: none;
        }

        .btn-pin {
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

        .btn-pin:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }

        .btn-pin:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            border: none;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .security-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }

        .security-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid #28a745;
        }

        .security-info h6 {
            color: #28a745;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .security-info p {
            margin: 0;
            font-size: 13px;
            color: #6c757d;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .pin-container {
                max-width: 95%;
                margin: 10px;
            }
            
            .pin-body {
                padding: 30px 20px;
            }
            
            .btn-pin {
                padding: 12px 20px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="pin-container">
        <!-- Header -->
        <div class="pin-header">
            <h2><i class="fas fa-shield-alt me-2"></i>Admin Dashboard</h2>
            <p>Ingiza PIN ili kufikia dashboard ya admin</p>
        </div>

        <!-- Body -->
        <div class="pin-body">
            <!-- Error Message -->
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Security Info -->
            <div class="security-info">
                <h6><i class="fas fa-info-circle me-2"></i>Maelezo ya Usalama</h6>
                <p>Hii ni dashboard ya admin iliyolindwa. Unahitaji PIN sahihi ili kuendelea.</p>
            </div>

            <!-- PIN Form -->
            <form action="{{ route('admin.pin.verify') }}" method="POST">
                @csrf
                
                <div class="text-center mb-4">
                    <i class="fas fa-lock security-icon"></i>
                </div>

                <div class="form-group">
                    <label for="admin_pin" class="form-label">
                        <i class="fas fa-key me-2"></i>Admin PIN
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="admin_pin" 
                           name="admin_pin" 
                           placeholder="****" 
                           maxlength="4" 
                           pattern="[0-9]{4}" 
                           required 
                           autofocus>
                    <small class="text-muted">Ingiza PIN ya 4 namba</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-pin" id="submitBtn">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Ingia Dashboard
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const pinInput = document.getElementById('admin_pin');
            const submitBtn = document.getElementById('submitBtn');
            
            // Auto-focus on PIN input
            pinInput.focus();
            
            // Form submission
            form.addEventListener('submit', function(e) {
                const pin = pinInput.value;
                
                // Validate PIN format
                if (!/^[0-9]{4}$/.test(pin)) {
                    e.preventDefault();
                    showAlert('error', 'PIN lazima iwe namba 4 tu.');
                    return;
                }
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Inakagua...';
            });
            
            // Only allow numbers
            pinInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            // Show alert function
            function showAlert(type, message) {
                // Remove any existing alerts
                const existingAlerts = document.querySelectorAll('.alert');
                existingAlerts.forEach(alert => alert.remove());
                
                // Create alert
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type}`;
                alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${message}
                `;
                
                // Insert before form
                form.parentNode.insertBefore(alertDiv, form);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            }
        });
    </script>
</body>
</html>
