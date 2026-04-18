<script>
// SweetAlert Notification System
window.showNotification = function(type, title, message, options = {}) {
    const defaultOptions = {
        timer: 5000,
        showConfirmButton: type === 'error' || type === 'warning',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3085d6',
        backdrop: type === 'error' || type === 'warning',
        allowOutsideClick: type !== 'error' && type !== 'warning'
    };

    const swalOptions = {
        ...defaultOptions,
        ...options,
        title: title,
        html: message,
        icon: type,
        position: 'center',
        toast: type === 'success' || type === 'info',
        customClass: {
            popup: 'sweet-alert-popup',
            title: 'sweet-alert-title',
            content: 'sweet-alert-content',
            actions: 'sweet-alert-actions',
            confirmButton: 'sweet-alert-confirm',
            cancelButton: 'sweet-alert-cancel',
            container: 'sweet-alert-container'
        }
    };

    // Special handling for insufficient funds
    if (type === 'warning' && message.includes('Insufficient Funds')) {
        swalOptions.html = `
            <div class="insufficient-funds-alert">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                </div>
                <h5 class="alert-title">${title}</h5>
                <p class="alert-message">${message}</p>
                <div class="alert-actions bg-light p-3 rounded mb-3">
                    <h6 class="text-dark mb-2">What to do:</h6>
                    <ul class="text-left mb-0">
                        <li><strong>Top up your Halopesa account</strong> - Visit your nearest Halopesa agent or use mobile banking</li>
                        <li><strong>Check your balance</strong> - Ensure you have sufficient funds for this transaction</li>
                        <li><strong>Try again</strong> - After topping up, submit this form again</li>
                        <li><strong>Contact support</strong> - If you need assistance with topping up your account</li>
                    </ul>
                </div>
            </div>
        `;
        swalOptions.showCancelButton = true;
        swalOptions.cancelButtonText = 'Cancel';
        swalOptions.cancelButtonColor = '#6c757d';
        swalOptions.confirmButtonText = 'Got it, I will top up';
        swalOptions.timer = null;
        swalOptions.backdrop = true;
        swalOptions.allowOutsideClick = false;
    }

    Swal.fire(swalOptions);
};

// Auto-show notifications from session data
document.addEventListener('DOMContentLoaded', function() {
    // Check for Laravel session notifications
    @if(session('error'))
        @if(session('warning_type') == 'insufficient_funds')
            showNotification('warning', 'Insufficient Funds Alert', `{{ session('error') }}`, {
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Got it, I will top up',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d'
            });
        @else
            showNotification('error', 'Error', `{{ session('error') }}`);
        @endif
    @endif

    @if(session('success'))
        showNotification('success', 'Success', `{{ session('success') }}`, {
            position: 'center',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#28a745',
            timer: 5000,
            backdrop: true,
            allowOutsideClick: false,
            customClass: {
                popup: 'success-popup-centered',
                title: 'success-title-centered',
                content: 'success-content-centered'
            }
        });
    @endif

    @if(session('info'))
        showNotification('info', 'Information', `{{ session('info') }}`);
    @endif

    @if(session('warning'))
        showNotification('warning', 'Warning', `{{ session('warning') }}`);
    @endif
});

// Custom styles for SweetAlert
const style = document.createElement('style');
style.textContent = `
    .sweet-alert-container {
        z-index: 999999 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 1vh 1vw !important;
        box-sizing: border-box !important;
    }
    
    .sweet-alert-popup {
        border-radius: 12px !important;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
        max-width: min(95vw, 800px) !important;
        max-height: min(95vh, 900px) !important;
        width: auto !important;
        height: auto !important;
        margin: 0 !important;
        z-index: 1000000 !important;
        position: relative !important;
        overflow: visible !important;
        display: flex !important;
        flex-direction: column !important;
    }
    
    .sweet-alert-title {
        font-size: 1.5rem !important;
        font-weight: 600 !important;
        margin-bottom: 1rem !important;
    }
    
    .sweet-alert-content {
        font-size: 1rem !important;
        line-height: 1.5 !important;
    }
    
    .sweet-alert-actions {
        margin-top: 1.5rem !important;
    }
    
    .sweet-alert-confirm {
        border-radius: 8px !important;
        padding: 0.75rem 1.5rem !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }
    
    .sweet-alert-cancel {
        border-radius: 8px !important;
        padding: 0.75rem 1.5rem !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }
    
    .insufficient-funds-alert {
        text-align: center;
        padding: 2rem;
        max-height: none;
        overflow: visible;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .insufficient-funds-alert .alert-icon {
        margin-bottom: 1rem;
        flex-shrink: 0;
    }
    
    .insufficient-funds-alert .alert-title {
        color: #856404;
        font-weight: 600;
        margin-bottom: 0.75rem;
        font-size: 1.25rem;
    }
    
    .insufficient-funds-alert .alert-message {
        color: #856404;
        margin-bottom: 1.5rem;
        font-size: 1rem;
        line-height: 1.5;
    }
    
    .insufficient-funds-alert .alert-actions {
        text-align: left;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1.5rem;
        flex-shrink: 0;
        background-color: #f8f9fa;
    }
    
    .insufficient-funds-alert .alert-actions h6 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 0.75rem;
        font-size: 1rem;
    }
    
    .insufficient-funds-alert .alert-actions ul {
        color: #6c757d;
        font-size: 0.95rem;
        padding-left: 1.2rem;
        margin: 0;
    }
    
    .insufficient-funds-alert .alert-actions li {
        margin-bottom: 0.75rem;
        line-height: 1.4;
    }
    
    .insufficient-funds-alert .alert-actions li:last-child {
        margin-bottom: 0;
    }
    
    .insufficient-funds-alert .alert-actions strong {
        color: #495057;
    }
    
    /* Success Popup Centered Styles */
    .success-popup-centered {
        max-width: 600px !important;
        width: 90vw !important;
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        z-index: 999999 !important;
        border-radius: 1rem !important;
        box-shadow: 0 10px 40px rgba(40, 167, 69, 0.3) !important;
        border: 2px solid #28a745 !important;
        background: linear-gradient(135deg, #ffffff 0%, #f8fff9 100%) !important;
    }
    
    .success-title-centered {
        font-size: 1.75rem !important;
        font-weight: bold !important;
        color: #155724 !important;
        margin-bottom: 1rem !important;
        text-align: center !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.5rem !important;
    }
    
    .success-title-centered::before {
        content: '//' !important;
        color: #28a745 !important;
        font-size: 2rem !important;
    }
    
    .success-title-centered::after {
        content: '//' !important;
        color: #28a745 !important;
        font-size: 2rem !important;
    }
    
    .success-content-centered {
        font-size: 1.1rem !important;
        line-height: 1.6 !important;
        color: #155724 !important;
        text-align: center !important;
        padding: 1rem 2rem !important;
        font-weight: 500 !important;
        background: rgba(40, 167, 69, 0.1) !important;
        border-radius: 0.5rem !important;
        margin: 0.5rem 0 !important;
        border: 1px solid rgba(40, 167, 69, 0.2) !important;
    }
    
    /* Success animation */
    @keyframes successPulse {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.02); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .success-popup-centered {
        animation: successPulse 0.5s ease-in-out !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 576px) {
        .sweet-alert-popup {
            margin: 1rem !important;
            width: 95% !important;
        }
        
        .sweet-alert-title {
            font-size: 1.25rem !important;
        }
        
        .sweet-alert-content {
            font-size: 0.9rem !important;
        }
        
        .insufficient-funds-alert .alert-actions {
            font-size: 0.8rem;
        }
    }
`;
document.head.appendChild(style);
</script>
