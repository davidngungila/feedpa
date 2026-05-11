<?php

require_once 'vendor/autoload.php';

use App\Services\ClickPesaAPIService;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Direct API Test ===\n\n";

$api = app(ClickPesaAPIService::class);

$references = [
    'FEEDTAN4252413898864', // Working reference
    'FEEDTAN42A82EC725898',  // Non-working reference
    'FEEDTAN4252413899999',  // Definitely non-existent
];

foreach ($references as $reference) {
    echo "Testing reference: $reference\n";
    echo str_repeat("-", 50) . "\n";
    
    try {
        $result = $api->queryPaymentStatus($reference);
        
        if ($result) {
            echo "API Response: DATA FOUND\n";
            echo "Response type: " . gettype($result) . "\n";
            
            if (is_array($result)) {
                echo "Keys: " . implode(', ', array_keys($result)) . "\n";
                echo "Status: " . ($result['status'] ?? 'NOT_SET') . "\n";
                echo "Amount: " . ($result['collectedAmount'] ?? $result['amount'] ?? 'NOT_SET') . "\n";
                echo "Currency: " . ($result['collectedCurrency'] ?? $result['currency'] ?? 'NOT_SET') . "\n";
                echo "Order Reference: " . ($result['orderReference'] ?? 'NOT_SET') . "\n";
                echo "Transaction ID: " . ($result['id'] ?? $result['transactionId'] ?? 'NOT_SET') . "\n";
                
                if (isset($result['customer'])) {
                    echo "Customer Name: " . ($result['customer']['customerName'] ?? 'NOT_SET') . "\n";
                    echo "Customer Phone: " . ($result['customer']['customerPhoneNumber'] ?? 'NOT_SET') . "\n";
                }
                
                // Check if it's an error response
                if (isset($result['error']) || isset($result['message'])) {
                    echo "Error Message: " . ($result['error'] ?? $result['message'] ?? 'NONE') . "\n";
                }
            }
        } else {
            echo "API Response: NO DATA (null/empty)\n";
        }
        
    } catch (Exception $e) {
        echo "API Exception: " . $e->getMessage() . "\n";
        echo "Exception Type: " . get_class($e) . "\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
}

echo "=== API Test Complete ===\n";
