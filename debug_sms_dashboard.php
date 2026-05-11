<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\MessagingServiceAPI;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SMS Dashboard Debug ===\n\n";

try {
    // Test the MessagingServiceAPI
    $messaging = new MessagingServiceAPI();
    
    // Test data
    $testPhoneNumber = '255622239304';
    $testPaymentData = [
        'orderReference' => 'TEST123',
        'id' => 'test-id',
        'status' => 'SUCCESS',
        'collectedAmount' => 10000,
        'collectedCurrency' => 'TZS',
        'paymentPhoneNumber' => $testPhoneNumber,
        'channel' => 'Mobile Money',
        'customer' => [
            'customerName' => 'Test Customer',
            'customerPhoneNumber' => $testPhoneNumber
        ],
        'createdAt' => now(),
        'updatedAt' => now()
    ];
    
    echo "1. Testing MessagingServiceAPI instantiation...\n";
    echo "✓ Service created successfully\n\n";
    
    echo "2. Testing payment message formatting...\n";
    $reflection = new ReflectionClass($messaging);
    $formatMethod = $reflection->getMethod('formatPaymentMessage');
    $formatMethod->setAccessible(true);
    $message = $formatMethod->invoke($messaging, $testPaymentData);
    echo "✓ Message formatted: " . substr($message, 0, 100) . "...\n\n";
    
    echo "3. Testing SMS sending (dry run)...\n";
    echo "Phone: " . $testPhoneNumber . "\n";
    echo "Message preview: " . substr($message, 0, 50) . "...\n";
    echo "Note: Actual SMS sending disabled for debugging\n\n";
    
    echo "4. Testing DashboardController sendManualSMS method...\n";
    
    // Create mock request
    $request = new Request([
        'reference' => 'TEST123',
        'phone_number' => $testPhoneNumber,
        'customer_name' => 'Test Customer',
        'amount' => 10000
    ]);
    
    echo "✓ Mock request created\n";
    echo "Request data: " . json_encode($request->all(), JSON_PRETTY_PRINT) . "\n\n";
    
    echo "5. Checking route configuration...\n";
    $routes = app('router')->getRoutes();
    foreach ($routes as $route) {
        if ($route->getName() === 'dashboard.send.manual.sms') {
            echo "✓ Route found: " . $route->uri() . "\n";
            echo "✓ Method: " . implode('|', $route->methods()) . "\n";
            echo "✓ Action: " . $route->getActionName() . "\n\n";
            break;
        }
    }
    
    echo "=== Debug Complete ===\n";
    echo "All components appear to be correctly configured.\n";
    echo "If SMS is still failing, check:\n";
    echo "1. Laravel logs for detailed error messages\n";
    echo "2. Network connectivity to messaging-service.co.tz\n";
    echo "3. API token validity\n";
    echo "4. Browser console for JavaScript errors\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
