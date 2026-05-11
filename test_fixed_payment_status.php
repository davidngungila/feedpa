<?php

require_once 'vendor/autoload.php';

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Fixed Payment Status ===\n\n";

$controller = app(PaymentController::class);

// Test the non-working reference
$nonWorkingReference = 'FEEDTAN42A82EC725898';

echo "Testing non-working reference: $nonWorkingReference\n";
echo str_repeat("-", 50) . "\n";

// Create a request with the reference
$request = Request::create('/payments/status', 'GET', ['reference' => $nonWorkingReference]);

try {
    // Capture the view output
    ob_start();
    $response = $controller->status($request);
    $output = ob_get_clean();
    
    echo "Controller executed successfully\n";
    
    // Check if view data contains payment data
    if (view()->shared('paymentData')) {
        $paymentData = view()->shared('paymentData');
        echo "Payment data found in view:\n";
        print_r($paymentData);
    } else {
        echo "No payment data found in view\n";
    }
    
    // Check for error
    if (view()->shared('error')) {
        $error = view()->shared('error');
        echo "Error found: $error\n";
    }
    
    // Check if transaction was created in database
    $transaction = \App\Models\Transaction::where('order_reference', $nonWorkingReference)->first();
    if ($transaction) {
        echo "\nTransaction was created in database:\n";
        echo "- ID: {$transaction->id}\n";
        echo "- Status: {$transaction->status}\n";
        echo "- Amount: {$transaction->amount}\n";
        echo "- Payment Method: {$transaction->payment_method}\n";
        echo "- Created: {$transaction->created_at}\n";
    } else {
        echo "\nNo transaction created in database\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
