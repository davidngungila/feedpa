<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 15px;
            background: #fff;
        }
        .receipt-container {
            max-width: 650px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
            background: #fff;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 20px;
            font-weight: bold;
        }
        .header p {
            margin: 2px 0;
            color: #666;
            font-size: 9px;
        }
        .company-info {
            margin-bottom: 10px;
            text-align: center;
        }
        .company-info h2 {
            margin: 0;
            color: #333;
            font-size: 14px;
        }
        .company-info p {
            margin: 2px 0;
            color: #666;
            font-size: 9px;
        }
        .receipt-details {
            margin-bottom: 15px;
        }
        .receipt-details h3 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 13px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 2px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            padding: 1px 0;
        }
        .detail-label {
            font-weight: bold;
            color: #333;
            min-width: 110px;
            font-size: 9px;
        }
        .detail-value {
            color: #666;
            text-align: right;
            flex: 1;
            font-size: 9px;
        }
        .amount-row {
            font-size: 12px;
            font-weight: bold;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            padding: 6px 0;
            margin: 10px 0;
            background: #f8f9fa;
        }
        .status-success {
            color: #1e7e34;
            font-weight: bold;
            font-size: 8px;
            padding: 1px 4px;
            background: #d4edda;
            border-radius: 2px;
            display: inline-block;
        }
        .status-warning {
            color: #856404;
            font-weight: bold;
            font-size: 8px;
            padding: 1px 4px;
            background: #fff3cd;
            border-radius: 2px;
            display: inline-block;
        }
        .status-danger {
            color: #721c24;
            font-weight: bold;
            font-size: 8px;
            padding: 1px 4px;
            background: #f8d7da;
            border-radius: 2px;
            display: inline-block;
        }
        .status-secondary {
            color: #6c757d;
            font-weight: bold;
            font-size: 8px;
            padding: 1px 4px;
            background: #e9ecef;
            border-radius: 2px;
            display: inline-block;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            color: #666;
            font-size: 8px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: #ccc;
            opacity: 0.1;
            z-index: -1;
        }
        .qr-section {
            text-align: center;
            margin: 0;
            padding: 5px;
            width: 100%;
        }
        .qr-section h4 {
            margin: 0 0 4px 0;
            color: #333;
            font-size: 10px;
            font-weight: bold;
        }
        .qr-code {
            margin: 4px 0;
        }
        .qr-code img {
            max-width: 100px;
            height: auto;
        }
        .verification-info {
            font-size: 7px;
            color: #666;
            margin-top: 4px;
            line-height: 1.1;
        }
        .two-column {
            display: flex;
            gap: 30px;
            margin-bottom: 15px;
            align-items: flex-start;
        }
        .two-column .left {
            flex: 2;
            min-width: 0;
        }
        .two-column .right {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="watermark">PAID</div>
    
    <div class="receipt-container">
        <div class="header">
            <h1>PAYMENT RECEIPT</h1>
            <p>Official Receipt - ClickPesa Payment System</p>
            <p>Receipt No: {{ $paymentData['orderReference'] ?? 'N/A' }}</p>
        </div>

        <div class="company-info">
            <h2>ClickPesa</h2>
            <p>Tanzania's Premier Payment Gateway</p>
            <p>Email: support@clickpesa.com | Phone: +255 712 345 678</p>
        </div>

        <div class="two-column">
            <div class="left">
                <div class="receipt-details">
                    <h3>Payment Details</h3>
                    
                    <div class="detail-row">
                        <span class="detail-label">Order Reference:</span>
                        <span class="detail-value">{{ $paymentData['orderReference'] ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Transaction ID:</span>
                        <span class="detail-value">{{ $paymentData['id'] ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Amount:</span>
                        <span class="detail-value">{{ number_format($paymentData['collectedAmount'] ?? 0, 2) }} {{ $paymentData['collectedCurrency'] ?? 'TZS' }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            @php
                                $statusColor = match($paymentData['status'] ?? '') {
                                    'SUCCESS', 'SETTLED' => 'success',
                                    'PROCESSING', 'PENDING' => 'warning',
                                    'FAILED' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="status-{{ $statusColor }}">{{ $paymentData['status'] ?? 'UNKNOWN' }}</span>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Phone Number:</span>
                        <span class="detail-value">{{ $paymentData['paymentPhoneNumber'] ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Channel:</span>
                        <span class="detail-value">{{ $paymentData['channel'] ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Created At:</span>
                        <span class="detail-value">{{ isset($paymentData['createdAt']) ? \Carbon\Carbon::parse($paymentData['createdAt'])->format('M d, Y H:i:s') : 'N/A' }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Updated At:</span>
                        <span class="detail-value">{{ isset($paymentData['updatedAt']) ? \Carbon\Carbon::parse($paymentData['updatedAt'])->format('M d, Y H:i:s') : 'N/A' }}</span>
                    </div>
                    
                    @if(isset($paymentData['message']))
                        <div class="detail-row">
                            <span class="detail-label">Message:</span>
                            <span class="detail-value">{{ $paymentData['message'] }}</span>
                        </div>
                    @endif
                    
                    @if(isset($paymentData['customer']))
                        <div class="detail-row">
                            <span class="detail-label">Customer Name:</span>
                            <span class="detail-value">{{ $paymentData['customer']['customerName'] ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Customer Phone:</span>
                            <span class="detail-value">{{ $paymentData['customer']['customerPhoneNumber'] ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Customer Email:</span>
                            <span class="detail-value">{{ $paymentData['customer']['customerEmail'] ?? 'N/A' }}</span>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="right">
                <div class="qr-section">
                    <h4>Verification QR Code</h4>
                    <div class="qr-code">
                        <img src="{{ $qrCodeImage ?? '' }}" alt="QR Code" style="max-width: 120px; height: auto;" />
                    </div>
                    <div class="verification-info">
                        Scan to verify payment details online
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p><strong>Thank you for your payment!</strong></p>
            <p>This receipt serves as proof of payment for your records.</p>
            <p>For any inquiries, please contact our customer support.</p>
            <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
