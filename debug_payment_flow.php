<?php

require_once 'vendor/autoload.php';

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Payment Flow ===\n\n";

$reference = 'FEEDTAN42A82EC725898';

echo "Testing reference: $reference\n";
echo str_repeat("-", 50) . "\n";

// First, delete any existing transaction to test fresh
\Illuminate\Support\Facades\DB::table('transactions')->where('order_reference', $reference)->delete();
echo "Deleted any existing transaction for fresh test\n\n";

// Create a request with the reference
$request = Request::create('/payments/status', 'GET', ['reference' => $reference]);

// Mock the view system to capture what's being passed
$viewData = [];
$originalView = view();
view()->composer('*', function ($view) use (&$viewData) {
    $viewData = array_merge($viewData, $view->getData());
});

$controller = app(PaymentController::class);

try {
    echo "Calling PaymentController->status()...\n";
    $response = $controller->status($request);
    echo "Controller executed\n\n";
    
    echo "View data captured:\n";
    foreach ($viewData as $key => $value) {
        if ($key === 'paymentData') {
            echo "paymentData: " . ($value ? "PRESENT" : "MISSING") . "\n";
            if ($value) {
                echo "  - Keys: " . implode(', ', array_keys($value)) . "\n";
                echo "  - Status: " . ($value['status'] ?? 'N/A') . "\n";
                echo "  - Amount: " . ($value['collectedAmount'] ?? $value['amount'] ?? 'N/A') . "\n";
            }
        } elseif ($key === 'error') {
            echo "error: $value\n";
        } elseif ($key === 'orderReference') {
            echo "orderReference: $value\n";
        } else {
            echo "$key: " . (is_scalar($value) ? $value : gettype($value)) . "\n";
        }
    }
    
    // Check database
    echo "\nDatabase check:\n";
    $transaction = \App\Models\Transaction::where('order_reference', $reference)->first();
    if ($transaction) {
        echo "✓ Transaction exists in database\n";
        echo "  - ID: {$transaction->id}\n";
        echo "  - Status: {$transaction->status}\n";
        echo "  - Amount: {$transaction->amount}\n";
    } else {
        echo "✗ No transaction in database\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
