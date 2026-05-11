<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\EmailConfigService;
use App\Models\EmailCredential;

echo "Updating Email Configuration in Database\n";
echo "========================================\n\n";

// Email configuration to update
$emailConfig = [
    'mailer' => 'smtp',
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'feedtan15@gmail.com',
    'password' => 'xgbs yqgn kmjy buqn',
    'encryption' => 'tls',
    'from_address' => 'feedtan15@gmail.com',
    'from_name' => 'FeedTan Community Microfinance Group'
];

echo "Email Configuration:\n";
echo "===================\n";
foreach ($emailConfig as $key => $value) {
    if ($key === 'password') {
        echo sprintf("%-15s: %s\n", $key, '***CONFIGURED***');
    } else {
        echo sprintf("%-15s: %s\n", $key, $value);
    }
}

echo "\n";

try {
    // Check if configuration exists
    $existing = EmailCredential::where('email_address', 'feedtan15@gmail.com')->first();
    
    if ($existing) {
        echo "Found existing configuration, updating...\n";
        
        // Update existing record
        $existing->update([
            'password' => $emailConfig['password'],
            'smtp_host' => $emailConfig['host'],
            'smtp_port' => $emailConfig['port'],
            'encryption' => $emailConfig['encryption'],
            'from_name' => $emailConfig['from_name'],
            'from_address' => $emailConfig['from_address'],
            'mailer' => $emailConfig['mailer'],
            'is_active' => true
        ]);
        
        $credential = $existing;
        echo "✅ Email configuration updated successfully!\n";
        
    } else {
        echo "Creating new configuration...\n";
        
        // Create new configuration
        $emailConfigService = new EmailConfigService();
        $credential = $emailConfigService->saveEmailConfig($emailConfig);
        
        echo "✅ Email configuration created successfully!\n";
    }
    
    echo "Database ID: {$credential->id}\n";
    echo "Email: {$credential->email_address}\n";
    echo "SMTP Host: {$credential->smtp_host}\n";
    echo "SMTP Port: {$credential->smtp_port}\n";
    echo "From Address: {$credential->from_address}\n";
    echo "From Name: {$credential->from_name}\n";
    echo "Active: " . ($credential->is_active ? 'Yes' : 'No') . "\n\n";
    
    // Test the configuration
    echo "Testing email configuration...\n";
    echo "===============================\n";
    
    $emailConfigService = new EmailConfigService();
    
    if ($emailConfigService->testEmailConfig()) {
        echo "✅ Email configuration test successful!\n";
        echo "Test email sent to: feedtan15@gmail.com\n";
        echo "Please check your inbox (including spam folder)\n\n";
        
        echo "🎉 Email System Ready!\n";
        echo "======================\n";
        echo "✅ Configuration saved to database\n";
        echo "✅ Email sending tested successfully\n";
        echo "✅ All recipients will now receive emails\n";
        echo "✅ No .env file modifications needed\n\n";
        
        echo "Email Recipients:\n";
        echo "- davidngungila@gmail.com\n";
        echo "- ecolishe@gmail.com\n";
        echo "- feedtanbackup@gmail.com\n";
        echo "- feedtan15@gmail.com\n\n";
        
        echo "Next Steps:\n";
        echo "1. Check your email inbox for the test email\n";
        echo "2. If received, the system is ready for production\n";
        echo "3. All payment notifications will use this database config\n";
        
    } else {
        echo "❌ Email configuration test failed\n";
        echo "Please check:\n";
        echo "- Gmail app password: xgbs yqgn kmjy buqn\n";
        echo "- Internet connection\n";
        echo "- Gmail security settings\n";
        
        echo "\nTrying alternative password format...\n";
        
        // Try without spaces
        $altConfig = $emailConfig;
        $altConfig['password'] = 'xgbsyqgnkmjybuqn';
        
        $existing->update(['password' => $altConfig['password']]);
        
        if ($emailConfigService->testEmailConfig()) {
            echo "✅ Alternative password format works!\n";
            echo "Password updated without spaces\n";
        } else {
            echo "❌ Both password formats failed\n";
            echo "Please check Gmail app password settings\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error updating email configuration: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check database connection\n";
    echo "2. Verify email_credentials table exists\n";
    echo "3. Check migration status\n";
}

echo "\n✅ Process completed!\n";
