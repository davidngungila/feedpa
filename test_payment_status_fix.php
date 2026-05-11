<?php

require_once 'vendor/autoload.php';

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Payment Status Fix ===\n\n";

$controller = app(PaymentController::class);

// Test the working reference
$reference = 'FEEDTAN4252413898864';

echo "Testing reference: $reference\n";
echo str_repeat("-", 50) . "\n";

try {
    // Create a request with the reference
    $request = Request::create('/payments/status', 'GET', ['reference' => $reference]);
    
    // Call the controller
    $response = $controller->status($request);
    
    echo "✓ PaymentController executed successfully\n";
    
    // Check view data
    $paymentData = view()->shared('paymentData');
    $error = view()->shared('error');
    
    if ($paymentData) {
        echo "✓ Payment data found:\n";
        echo "  - Status: " . ($paymentData['status'] ?? 'N/A') . "\n";
        echo "  - Amount: " . ($paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 'N/A') . "\n";
        echo "  - Order Reference: " . ($paymentData['orderReference'] ?? 'N/A') . "\n";
    } else {
        echo "✗ No payment data found\n";
    }
    
    if ($error) {
        echo "✗ Error: $error\n";
    } else {
        echo "✓ No errors\n";
    }
    
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== Test Complete ===\n";
