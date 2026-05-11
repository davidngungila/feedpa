<?php

require_once 'vendor/autoload.php';

use App\Services\ClickPesaAPIService;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Detailed API Test ===\n\n";

$api = app(ClickPesaAPIService::class);

$reference = 'FEEDTAN4252413898864'; // Working reference

echo "Testing reference: $reference\n";
echo str_repeat("-", 50) . "\n";

try {
    $result = $api->queryPaymentStatus($reference);
    
    echo "Raw API Response:\n";
    var_dump($result);
    
    echo "\n\nDetailed Analysis:\n";
    
    if ($result && is_array($result)) {
        echo "Array structure:\n";
        print_r($result);
        
        // Check if it's a wrapped array (has numeric index 0)
        if (isset($result[0]) && is_array($result[0])) {
            echo "\nFound wrapped payment data at index 0:\n";
            $paymentData = $result[0];
            print_r($paymentData);
        }
    }
    
} catch (Exception $e) {
    echo "API Exception: " . $e->getMessage() . "\n";
    echo "Exception Type: " . get_class($e) . "\n";
}

echo "\n=== API Test Complete ===\n";
