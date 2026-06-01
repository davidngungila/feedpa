<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\EmailConfigService;
use App\Models\EmailCredential;
use Illuminate\Support\Facades\Mail;

echo "==============================================\n";
echo "Setting Up New Email Configuration\n";
echo "==============================================\n\n";

// New email configuration
$newConfig = [
    'mailer' => 'smtp',
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'feedtan15@gmail.com',
    'password' => 'bzzw vdul vlqs wcjm', // New app password
    'encryption' => 'tls',
    'from_address' => 'feedtan15@gmail.com',
    'from_name' => 'FeedTan Community Microfinance Group'
];

echo "Email Configuration:\n";
echo str_repeat("-", 50) . "\n";
foreach ($newConfig as $key => $value) {
    if ($key === 'password') {
        echo sprintf("%-15s: %s\n", $key, '***HIDDEN***');
    } else {
        echo sprintf("%-15s: %s\n", $key, $value);
    }
}
echo "\n";

// Step 1: Save to database
echo "Step 1: Saving configuration to database...\n";
try {
    // Check if exists
    $credential = EmailCredential::where('email_address', $newConfig['username'])->first();
    
    if ($credential) {
        // Update existing
        $credential->update([
            'password' => $newConfig['password'],
            'smtp_host' => $newConfig['host'],
            'smtp_port' => $newConfig['port'],
            'encryption' => $newConfig['encryption'],
            'from_address' => $newConfig['from_address'],
            'from_name' => $newConfig['from_name'],
            'mailer' => $newConfig['mailer'],
            'is_active' => true
        ]);
        echo "✅ Updated existing config! (ID: {$credential->id})\n\n";
    } else {
        // Deactivate any existing config
        EmailCredential::query()->update(['is_active' => false]);
        
        // Create new config
        $credential = EmailCredential::create([
            'email_address' => $newConfig['username'],
            'password' => $newConfig['password'],
            'smtp_host' => $newConfig['host'],
            'smtp_port' => $newConfig['port'],
            'encryption' => $newConfig['encryption'],
            'from_address' => $newConfig['from_address'],
            'from_name' => $newConfig['from_name'],
            'mailer' => $newConfig['mailer'],
            'is_active' => true
        ]);
        echo "✅ Created new config! (ID: {$credential->id})\n\n";
    }
} catch (Exception $e) {
    echo "❌ Failed to save config: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 2: Test sending to davidngungila@gmail.com
echo "Step 2: Testing email to davidngungila@gmail.com...\n";
try {
    $emailConfigService = new EmailConfigService();
    $emailConfigService->configureMail();
    
    $testEmailData = [
        'orderReference' => 'TEST-' . time(),
        'status' => 'COMPLETED',
        'collectedAmount' => 10000,
        'collectedCurrency' => 'TZS',
        'customer' => [
            'customerName' => 'Test Customer',
            'customerPhoneNumber' => '0712345678'
        ],
        'description' => 'Test payment',
        'createdAt' => now()->toDateTimeString()
    ];
    
    // Build professional email template
    $emailTemplate = buildEmailTemplate($testEmailData);
    
    // Send to davidngungila@gmail.com
    Mail::html($emailTemplate['html'], function ($message) use ($emailTemplate) {
        $config = (new EmailConfigService())->getEmailConfig();
        $message->to('davidngungila@gmail.com')
                ->subject($emailTemplate['subject'])
                ->from($config['from_address'], $config['from_name']);
    });
    
    echo "✅ Test email sent successfully to davidngungila@gmail.com!\n";
    echo "   Check inbox (and spam folder)\n\n";
    
} catch (Exception $e) {
    echo "❌ Failed to send test email: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "==============================================\n";
echo "🎉 All Done!\n";
echo "==============================================\n";
echo "\n";
echo "Next Steps:\n";
echo "1. Check davidngungila@gmail.com inbox for test email\n";
echo "2. If received, email system is ready!\n";
echo "3. Now, let's set up transaction alerts to officer users!\n";

function buildEmailTemplate($paymentData): array
{
    $subject = "🔔 New Payment Notification - " . ($paymentData['orderReference'] ?? 'N/A');
    
    $customerName = $paymentData['customer']['customerName'] ?? $paymentData['payer_name'] ?? 'Unknown';
    $customerPhone = $paymentData['customer']['customerPhoneNumber'] ?? $paymentData['phone'] ?? 'N/A';
    $amount = $paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 0;
    $currency = $paymentData['collectedCurrency'] ?? $paymentData['currency'] ?? 'TZS';
    $status = $paymentData['status'] ?? 'UNKNOWN';
    $reference = $paymentData['orderReference'] ?? $paymentData['order_reference'] ?? 'N/A';
    $date = $paymentData['createdAt'] ?? now()->toDateTimeString();
    $description = $paymentData['description'] ?? $paymentData['narrative'] ?? 'Payment received';
    
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
    <style>
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #10b981;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #10b981;
            margin: 0;
            font-size: 24px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
        }
        .status-completed {
            background-color: #d1fae5;
            color: #059669;
        }
        .details {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-label {
            font-weight: 600;
            color: #4b5563;
        }
        .detail-value {
            color: #1f2937;
            font-weight: 500;
        }
        .alert {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 0 8px 8px 0;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #10b981;
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 New Payment Received!</h1>
            <div class="status-badge status-completed">{$status}</div>
        </div>
        
        <p>Hi Officer,</p>
        <p>A new payment has been successfully made. Please login to record this transaction in the system.</p>
        
        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Order Reference:</span>
                <span class="detail-value">{$reference}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Customer Name:</span>
                <span class="detail-value">{$customerName}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone Number:</span>
                <span class="detail-value">{$customerPhone}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount Paid:</span>
                <span class="detail-value">{$amount} {$currency}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date & Time:</span>
                <span class="detail-value">{$date}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Description:</span>
                <span class="detail-value">{$description}</span>
            </div>
        </div>
        
        <div class="alert">
            <strong>⚠️ Action Required:</strong> Please login to the system to record this payment transaction in our records.
        </div>
        
        <p style="text-align: center;">
            <a href="{{ config('app.url') }}/login" class="button">🔑 Login to System</a>
        </p>
        
        <div class="footer">
            <p>FeedTan Community Microfinance Group<br>
            "Let's Grow Together"</p>
        </div>
    </div>
</body>
</html>
HTML;

    return ['html' => $html, 'subject' => $subject];
}
