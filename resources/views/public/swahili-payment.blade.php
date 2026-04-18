<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
 
    <title>Malipo ya Wanachama - FeedTan CMG</title>

    <meta name="description" content="Fanya malipo ya wanachama wa FeedTan Community Microfinance Group kwa urahisi kupitia Tigo Pesa, M-Pesa, Airtel Money na Halopesa. Malipo salama kupitia ClickPesa.">

    <meta name="keywords" content="FeedTan, malipo ya simu, microfinance Tanzania, ClickPesa, M-Pesa, Tigo Pesa, Airtel Money">

    <meta property="og:title" content="FeedTan CMG - Malipo ya Wanachama">
    <meta property="og:description" content="Malipo salama na ya haraka kwa wanachama wa FeedTan kupitia mobile money.">
    <meta property="og:site_name" content="FeedTan CMG">

    
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
            <h2>FeedTan CMG</h2>
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
    Lipa Sasa <span id="btnAmount">0</span> TZS
</button>
                </div>
             <center>  <p>Let's Grow Together</p></center>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
   <script>
document.addEventListener('DOMContentLoaded', function() {

    const form = document.getElementById('paymentForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);

        const data = {
            amount: formData.get('amount'),
            phone_number: formData.get('phone_number'),
            payer_name: formData.get('payer_name'),
            description: formData.get('description')
        };

        // =========================
        // VALIDATION (FRIENDLY)
        // =========================
        if (!data.payer_name || data.payer_name.trim() === '') {
            showAlert('error', 'Tafadhali ingiza jina lako kamili.');
            return;
        }

        if (!data.phone_number || !data.phone_number.match(/^255[67]\d{8}$/)) {
            showAlert('error', 'Namba ya simu si sahihi. Mfano: 255712345678');
            return;
        }

        if (!data.amount || data.amount < 500) {
            showAlert('error', 'Kiasi cha chini ni TZS 500.');
            return;
        }

        if (data.amount > 5000000) {
            showAlert('error', 'Kiasi cha juu ni TZS 5,000,000.');
            return;
        }

        // =========================
        // LOADING STATE
        // =========================
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Tunatuma Malipo...';

        showProcessingModal();

        // =========================
        // API REQUEST
        // =========================
        fetch('/payments/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(result => {

            const modal = bootstrap.Modal.getInstance(document.getElementById('processingModal'));
            if (modal) modal.hide();

            if (result.success) {
                showUSSDNotification(result);
            } else {
                showAlert('error', result.message || 'Imeshindikwa kutuma malipo. Jaribu tena.');
                resetButton();
            }

        })
        .catch(error => {

            const modal = bootstrap.Modal.getInstance(document.getElementById('processingModal'));
            if (modal) modal.hide();

            console.log('Payment error:', error);

            showAlert('warning', 'Tatizo la mtandao. Tafadhali jaribu tena.');
            resetButton();
        });
    });






    const amountInput = document.getElementById('amount');
const btnAmount = document.getElementById('btnAmount');

amountInput.addEventListener('input', function () {
    let value = this.value || 0;
    btnAmount.textContent = value;
});



    // =========================
    // RESET BUTTON
    // =========================
    function resetButton() {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Lipa Sasa';
    }

    // =========================
    // PROCESSING MODAL (FRIENDLY)
    // =========================
    function showProcessingModal() {
        const modalHtml = `
            <div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content text-center">
                        <div class="modal-body py-4">

                            <div class="spinner-border text-primary mb-3"></div>

                            <h5 class="fw-bold">Tunaandaa Malipo Yako...</h5>

                            <p class="text-muted">
                                Tafadhali subiri kidogo, USSD inatumwa kwenye simu yako.
                            </p>

                            <div class="alert alert-info text-start">
                                📲 Fuata hatua hizi:<br>
                                ✔ Angalia simu yako<br>
                                ✔ Fungua USSD<br>
                                ✔ Weka PIN yako<br>
                                ✔ Thibitisha malipo
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

    // =========================
    // SUCCESS MODAL (FRIENDLY)
    // =========================
    function showUSSDNotification(data) {

        const html = `
        <div class="modal fade" id="ussdNotification" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center">

                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">🎉 Malipo Yameanzishwa</h5>
                    </div>

                    <div class="modal-body py-4">

                        <i class="fas fa-mobile-alt text-success" style="font-size: 3rem;"></i>

                        <h6 class="mt-3">
                            USSD imetumwa kwenye simu yako
                        </h6>

                        <div class="alert alert-primary mt-3">
                            📱 ${data.phone_number || data.phone}
                        </div>

                        <div class="alert alert-success">
                            💰 TZS ${data.amount}
                        </div>

                        <div class="alert alert-warning text-start">
                            <b>Hatua zako:</b><br>
                            1️⃣ Fungua USSD kwenye simu yako<br>
                            2️⃣ Weka PIN yako<br>
                            3️⃣ Thibitisha malipo<br>
                            4️⃣ Subiri uthibitisho
                        </div>

                        <small class="text-muted">
                            ⚡ Malipo yatakamilika baada ya uthibitisho wako
                        </small>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success w-100" onclick="closeUSSDNotification()">
                            Nimeelewa 👍
                        </button>
                    </div>

                </div>
            </div>
        </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
        const modal = new bootstrap.Modal(document.getElementById('ussdNotification'));
        modal.show();

        form.reset();
        resetButton();
    }

    // =========================
    // CLOSE SUCCESS MODAL
    // =========================
    window.closeUSSDNotification = function() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('ussdNotification'));
        if (modal) modal.hide();
    }

    // =========================
    // PHONE FORMAT AUTO FIX
    // =========================
    const phoneInput = document.getElementById('phone_number');
    phoneInput.addEventListener('blur', function() {
        let value = this.value.replace(/\D/g, '');

        if (value.length === 10 && value.startsWith('0')) {
            this.value = '255' + value.substring(1);
        }
    });

    // =========================
    // FRIENDLY ALERT SYSTEM
    // =========================
    function showAlert(type, message) {

        const overlay = document.createElement('div');
        overlay.className = 'custom-alert-overlay';
        overlay.style.cssText = `
            position: fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background: rgba(0,0,0,0.5);
            display:flex;
            justify-content:center;
            align-items:center;
            z-index:99999;
        `;

        let icon = 'info-circle';
        let color = 'primary';

        if (type === 'success') { icon = 'check-circle'; color = 'success'; }
        if (type === 'error') { icon = 'times-circle'; color = 'danger'; }
        if (type === 'warning') { icon = 'exclamation-triangle'; color = 'warning'; }

        overlay.innerHTML = `
            <div class="alert alert-${color} text-center p-4" style="max-width:400px;">
                <i class="fas fa-${icon}" style="font-size:2rem;"></i>
                <h5 class="mt-2 text-capitalize">${type}</h5>
                <p>${message}</p>
                <button class="btn btn-dark btn-sm" onclick="this.closest('.custom-alert-overlay').remove()">
                    OK
                </button>
            </div>
        `;

        document.body.appendChild(overlay);
    }

});
</script>
</body>
</html>
