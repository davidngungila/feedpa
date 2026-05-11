<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Direct SMS Test ===\n\n";

use App\Services\MessagingServiceAPI;
use Illuminate\Support\Facades\Log;

try {
    
    // Test SMS service
    $messaging = new MessagingServiceAPI();
    
    $testPhone = '255622239304';
    $testMessage = 'Test message from FeedTan system - ' . date('Y-m-d H:i:s');
    
    echo "1. Testing direct SMS send...\n";
    echo "Phone: $testPhone\n";
    echo "Message: $testMessage\n";
    echo "API URL: https://messaging-service.co.tz/api/sms/v2/text/single\n";
    echo "Token: " . substr(config('messaging.token', 'f9a89f439206e27169ead766463ca92c'), 0, 10) . "...\n\n";
    
    // Try to send SMS
    $result = $messaging->sendSMS($testPhone, $testMessage);
    
    echo "2. SMS Result:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "3. Check recent logs...\n";
    
    // Get recent log entries
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $recentLogs = substr($logs, -2000); // Get last 2000 characters
        echo "Recent log entries:\n";
        echo $recentLogs . "\n";
    } else {
        echo "No log file found.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    // Check if it's a network error
    if (strpos($e->getMessage(), 'cURL') !== false || strpos($e->getMessage(), 'HTTP') !== false) {
        echo "\n🔍 This appears to be a network/API connectivity issue.\n";
        echo "Please check:\n";
        echo "1. Internet connectivity\n";
        echo "2. messaging-service.co.tz accessibility\n";
        echo "3. API token validity\n";
    }
}

echo "\n=== Test Complete ===\n";
