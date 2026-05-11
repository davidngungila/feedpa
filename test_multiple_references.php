<?php

require_once 'vendor/autoload.php';

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Multiple References ===\n\n";

$controller = app(PaymentController::class);

$references = [
    'FEEDTAN42A82EC725898', // Previously non-working
    'FEEDTAN4252413898864', // Working reference
    'FEEDTAN4252413899999',  // Non-existent
];

foreach ($references as $reference) {
    echo "Testing reference: $reference\n";
    echo str_repeat("-", 40) . "\n";
    
    // Check if it exists in database first
    $existingTransaction = \App\Models\Transaction::where('order_reference', $reference)->first();
    echo "Exists in DB before test: " . ($existingTransaction ? "YES" : "NO") . "\n";
    
    // Create a request with the reference
    $request = Request::create('/payments/status', 'GET', ['reference' => $reference]);
    
    try {
        // Clear any previous view data
        view()->share('paymentData', null);
        view()->share('error', null);
        
        // Call the controller
        $response = $controller->status($request);
        
        // Check results
        $paymentData = view()->shared('paymentData');
        $error = view()->shared('error');
        
        if ($paymentData) {
            echo "✓ Payment data captured\n";
            echo "  Status: " . ($paymentData['status'] ?? 'N/A') . "\n";
            echo "  Amount: " . ($paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 'N/A') . "\n";
        } else {
            echo "✗ No payment data\n";
        }
        
        if ($error) {
            echo "✗ Error: $error\n";
        }
        
        // Check if transaction was created/updated
        $afterTransaction = \App\Models\Transaction::where('order_reference', $reference)->first();
        if ($afterTransaction) {
            echo "✓ Transaction in DB after test\n";
            if (!$existingTransaction) {
                echo "  → New transaction created\n";
            }
        } else {
            echo "✗ No transaction in DB after test\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Exception: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== Test Complete ===\n";
