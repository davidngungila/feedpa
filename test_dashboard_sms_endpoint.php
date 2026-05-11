<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Dashboard SMS Endpoint Test ===\n\n";

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;

try {
    
    // Create controller instance
    $controller = new DashboardController(app(\App\Services\ClickPesaAPIService::class));
    
    // Create unique reference
    $uniqueRef = 'TEST' . uniqid();
    
    // Create mock request
    $request = new Request([
        'reference' => $uniqueRef,
        'phone_number' => '255622239304',
        'customer_name' => 'Test Customer',
        'amount' => 10000
    ]);
    
    echo "1. Testing DashboardController sendManualSMS method...\n";
    echo "Request data:\n";
    echo json_encode($request->all(), JSON_PRETTY_PRINT) . "\n\n";
    
    // Create a test transaction first
    $transaction = new \App\Models\Transaction();
    $transaction->order_reference = $uniqueRef;
    $transaction->transaction_id = 'test-' . uniqid();
    $transaction->status = 'SUCCESS';
    $transaction->amount = 10000;
    $transaction->currency = 'TZS';
    $transaction->payment_method = 'Mobile Money';
    $transaction->phone = '255622239304';
    $transaction->payer_name = 'Test Customer';
    $transaction->save();
    
    echo "2. Test transaction created with ID: " . $transaction->id . "\n\n";
    
    // Call the sendManualSMS method
    $response = $controller->sendManualSMS($request);
    
    echo "3. Response from sendManualSMS:\n";
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n\n";
    
    // Clean up test transaction
    $transaction->delete();
    echo "4. Test transaction cleaned up\n\n";
    
    echo "=== Test Complete ===\n";
    echo "If this test works, the SMS functionality is working correctly.\n";
    echo "The issue might be:\n";
    echo "1. Browser JavaScript errors preventing the AJAX call\n";
    echo "2. CSRF token issues\n";
    echo "3. Network connectivity from browser\n";
    echo "4. Browser extensions interfering with JavaScript\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
