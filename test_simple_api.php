<?php

require_once 'vendor/autoload.php';

use App\Services\ClickPesaAPIService;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Simple API Test ===\n\n";

$reference = 'FEEDTAN42A82EC725898';

echo "Testing API for: $reference\n";
echo str_repeat("-", 40) . "\n";

$api = app(ClickPesaAPIService::class);

try {
    $response = $api->queryPaymentStatus($reference);
    
    echo "API Response Type: " . gettype($response) . "\n";
    
    if ($response && is_array($response)) {
        echo "Response keys: " . implode(', ', array_keys($response)) . "\n";
        
        if (isset($response[0])) {
            echo "Data found at index 0\n";
            $paymentData = $response[0];
            echo "Payment data keys: " . implode(', ', array_keys($paymentData)) . "\n";
            echo "Status: " . ($paymentData['status'] ?? 'N/A') . "\n";
            echo "Amount: " . ($paymentData['collectedAmount'] ?? 'N/A') . "\n";
            echo "Order Reference: " . ($paymentData['orderReference'] ?? 'N/A') . "\n";
        }
    } else {
        echo "No valid response from API\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
