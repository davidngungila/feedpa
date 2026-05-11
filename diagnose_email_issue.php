<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Mail;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Email Delivery Diagnosis\n";
echo "========================\n\n";

// Check .env configuration
echo "1. Checking Email Configuration:\n";
echo "===============================\n";

$config = [
    'MAIL_MAILER' => env('MAIL_MAILER'),
    'MAIL_HOST' => env('MAIL_HOST'),
    'MAIL_PORT' => env('MAIL_PORT'),
    'MAIL_USERNAME' => env('MAIL_USERNAME'),
    'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? '***SET***' : 'NOT SET',
    'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
    'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
    'MAIL_FROM_NAME' => env('MAIL_FROM_NAME')
];

foreach ($config as $key => $value) {
    echo sprintf("%-20s: %s\n", $key, $value);
}

echo "\n";

// Check if required settings are missing
$required = ['MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_ENCRYPTION'];
$missing = [];
foreach ($required as $req) {
    if (!env($req)) {
        $missing[] = $req;
    }
}

if (!empty($missing)) {
    echo "❌ Missing Required Settings:\n";
    echo "============================\n";
    foreach ($missing as $m) {
        echo "- {$m}\n";
    }
    echo "\n";
    echo "SOLUTION: Add these lines to your .env file:\n";
    echo "MAIL_MAILER=smtp\n";
    echo "MAIL_HOST=smtp.gmail.com\n";
    echo "MAIL_PORT=587\n";
    echo "MAIL_USERNAME=feedtan15@gmail.com\n";
    echo "MAIL_PASSWORD=xgbs yqgn kmjy buqn\n";
    echo "MAIL_ENCRYPTION=tls\n";
    echo "MAIL_FROM_ADDRESS=feedtan15@gmail.com\n";
    echo "MAIL_FROM_NAME=\"FeedTan Community Microfinance Group\"\n";
    exit(1);
}

echo "✅ All required email settings are present\n\n";

// Test email configuration
echo "2. Testing Email Configuration:\n";
echo "================================\n";

try {
    // Create a simple test email
    
    $testData = [
        'to' => 'feedtan15@gmail.com',
        'subject' => 'TEST EMAIL - FeedTan Configuration',
        'content' => 'This is a test email to verify the email configuration is working properly.',
        'time' => now()->format('Y-m-d H:i:s')
    ];
    
    echo "Sending test email to {$testData['to']}...\n";
    
    // Try to send a simple test email
    Mail::raw($testData['content'], function ($message) use ($testData) {
        $message->to($testData['to'])
                ->subject($testData['subject'])
                ->from('feedtan15@gmail.com', 'FeedTan Community Microfinance Group');
    });
    
    echo "✅ Test email sent successfully!\n";
    echo "Please check your inbox (including spam folder)\n\n";
    
} catch (\Exception $e) {
    echo "❌ Email sending failed: " . $e->getMessage() . "\n\n";
    
    echo "Common Gmail Issues and Solutions:\n";
    echo "===================================\n";
    echo "1. Gmail App Password Issue:\n";
    echo "   - Go to: https://myaccount.google.com/apppasswords\n";
    echo "   - Generate a new app password for this application\n";
    echo "   - Use the 16-character password (without spaces)\n\n";
    
    echo "2. Gmail Security Settings:\n";
    echo "   - Make sure 'Less secure app access' is ON\n";
    echo "   - Or use 2-Step Verification with app passwords\n\n";
    
    echo "3. Gmail Account Issues:\n";
    echo "   - Check if Gmail account is blocked\n";
    echo "   - Verify Gmail account can send emails\n";
    echo "   - Check Gmail storage limits\n\n";
}

echo "3. Gmail Troubleshooting Checklist:\n";
echo "===================================\n";
echo "□ Check Gmail inbox (including promotions/spam folders)\n";
echo "□ Verify app password is correct: xgbs yqgn kmjy buqn\n";
echo "□ Try removing spaces from password: xgbsyqgnkmjybuqn\n";
echo "□ Check if Gmail allows less secure apps\n";
echo "□ Try generating a new app password\n";
echo "□ Verify Gmail account isn't blocked\n";
echo "□ Check internet connection\n\n";

echo "4. Alternative Solutions:\n";
echo "========================\n";
echo "If Gmail doesn't work, try:\n";
echo "- Use a different email provider (SendGrid, Mailgun)\n";
echo "- Configure SMTP with different settings\n";
echo "- Use Laravel's built-in mail testing\n\n";

echo "🔧 Next Steps:\n";
echo "1. Check the error message above\n";
echo "2. Follow the troubleshooting steps\n";
echo "3. Try the alternative password format\n";
echo "4. Generate a new app password if needed\n";
