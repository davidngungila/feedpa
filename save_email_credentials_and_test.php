<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailCredential;
use App\Services\EmailNotificationService;

// Email credentials to save
$emailCredentials = [
    'email_address' => 'feedtan15@gmail.com',
    'password' => 'xgbs yqgn kmjy buqn',
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'encryption' => 'tls',
    'from_name' => 'FeedTan Community Microfinance Group',
    'is_active' => true
];

echo "Saving email credentials to database...\n";

// Save credentials to database
try {
    // Check if credentials already exist
    $existing = EmailCredential::where('email_address', 'feedtan15@gmail.com')->first();
    
    if ($existing) {
        echo "Email credentials already exist. Updating...\n";
        $existing->update($emailCredentials);
        $credential = $existing;
    } else {
        echo "Creating new email credentials...\n";
        $credential = EmailCredential::create($emailCredentials);
    }
    
    echo "✅ Email credentials saved successfully!\n";
    echo "ID: {$credential->id}\n";
    echo "Email: {$credential->email_address}\n";
    echo "SMTP Host: {$credential->smtp_host}\n";
    echo "SMTP Port: {$credential->smtp_port}\n\n";
    
} catch (Exception $e) {
    echo "❌ Failed to save email credentials: " . $e->getMessage() . "\n";
    exit(1);
}

// Now send a test email
echo "Sending test email notification...\n";

// Test payment data
$testPaymentData = [
    'event' => 'PAYMENT RECEIVED',
    'status' => 'SUCCESS',
    'orderReference' => 'TEST-' . date('YmdHis'),
    'collectedAmount' => '10000',
    'collectedCurrency' => 'TZS',
    'channel' => 'Mobile Money',
    'customer' => [
        'customerName' => 'Mteja wa Majaribio',
        'customerPhoneNumber' => '255700000000'
    ],
    'createdAt' => now()->toISOString(),
    'id' => 'FTN-' . date('Ymd') . '-TEST'
];

try {
    $emailService = new EmailNotificationService();
    $result = $emailService->sendPaymentSuccessNotification($testPaymentData);
    
    if ($result) {
        echo "✅ Test email sent successfully!\n";
        echo "Test Reference: {$testPaymentData['orderReference']}\n";
        echo "Test Amount: TZS 10,000\n";
        echo "Recipients: davidngungila@gmail.com, ecolishe@gmail.com, feedtanbackup@gmail.com, feedtan15@gmail.com\n\n";
        
        echo "Email content preview:\n";
        echo "====================\n";
        echo "Malipo yamefanikiwa. Tumepokea kiasi cha TZS 10,000.00 kutoka kwa Mteja wa Majaribio kupitia Mobile Money tarehe " . date('d M Y, H:i') . ". Kumbukumbu ya muamala: {$testPaymentData['id']}, Rejea: {$testPaymentData['orderReference']}. Asante kwa kutumia huduma zetu.\n\n";
        
    } else {
        echo "❌ Failed to send test email\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error sending test email: " . $e->getMessage() . "\n";
    echo "Please check your .env file email configuration.\n";
}

echo "\nProcess completed!\n";
echo "================\n";
echo "1. ✅ Email credentials saved to database\n";
echo "2. " . ($result ? "✅" : "❌") . " Test email sent\n";
echo "\nNote: If email sending failed, make sure to add these lines to your .env file:\n";
echo "MAIL_MAILER=smtp\n";
echo "MAIL_HOST=smtp.gmail.com\n";
echo "MAIL_PORT=587\n";
echo "MAIL_USERNAME=feedtan15@gmail.com\n";
echo "MAIL_PASSWORD=xgbs yqgn kmjy buqn\n";
echo "MAIL_ENCRYPTION=tls\n";
echo "MAIL_FROM_ADDRESS=feedtan15@gmail.com\n";
echo "MAIL_FROM_NAME=\"FeedTan Community Microfinance Group\"\n";
