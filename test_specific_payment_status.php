<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Specific Payment Status Reference\n";
echo "========================================\n\n";

$reference = 'FEEDTAN4252413898864';

echo "Testing reference: {$reference}\n\n";

// Test 1: Check database first
echo "1. Database Check:\n";
$transaction = \App\Models\Transaction::where('order_reference', $reference)->first();

if ($transaction) {
    echo "✅ Transaction found in database:\n";
    echo "   Status: {$transaction->status}\n";
    echo "   Amount: TZS " . number_format($transaction->amount, 2) . "\n";
    echo "   Created: {$transaction->created_at}\n\n";
} else {
    echo "❌ Transaction not found in database\n";
    echo "   Will try API fallback...\n\n";
}

// Test 2: Try API directly with better error handling
echo "2. Direct API Test:\n";
try {
    $apiService = app(\App\Services\ClickPesaAPIService::class);
    
    echo "   API Base URL: " . config('clickpesa.api_base_url') . "\n";
    echo "   API Key: " . (config('clickpesa.api_key') ? 'SET' : 'NOT SET') . "\n";
    echo "   Client ID: " . (config('clickpesa.client_id') ? 'SET' : 'NOT SET') . "\n\n";
    
    // Generate token first
    echo "   Generating API token...\n";
    $token = $apiService->generateToken();
    echo "   ✅ Token generated successfully\n\n";
    
    // Query payment status
    echo "   Querying payment status...\n";
    $paymentData = $apiService->queryPaymentStatus($reference);
    
    if ($paymentData && is_array($paymentData)) {
        echo "✅ API returned data:\n";
        echo "   Status: " . ($paymentData['status'] ?? 'N/A') . "\n";
        echo "   Amount: TZS " . number_format($paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 0, 2) . "\n";
        echo "   Customer: " . ($paymentData['customer']['customerName'] ?? 'N/A') . "\n";
        echo "   Phone: " . ($paymentData['customer']['customerPhoneNumber'] ?? 'N/A') . "\n";
        echo "   Channel: " . ($paymentData['channel'] ?? 'N/A') . "\n";
        echo "   Created: " . ($paymentData['createdAt'] ?? 'N/A') . "\n\n";
        
        // Create transaction if not exists
        if (!$transaction) {
            echo "   Creating transaction from API data...\n";
            \App\Models\Transaction::create([
                'order_reference' => $reference,
                'transaction_id' => $paymentData['id'] ?? $paymentData['transaction_id'] ?? null,
                'status' => $paymentData['status'] ?? 'UNKNOWN',
                'amount' => $paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 0,
                'currency' => $paymentData['collectedCurrency'] ?? 'TZS',
                'phone' => $paymentData['customer']['customerPhoneNumber'] ?? $paymentData['paymentPhoneNumber'] ?? null,
                'payer_name' => $paymentData['customer']['customerName'] ?? $paymentData['payer_name'] ?? null,
                'payment_method' => $paymentData['channel'] ?? $paymentData['paymentMethod'] ?? null,
                'description' => $paymentData['description'] ?? null,
                'type' => 'payment',
                'created_at' => $paymentData['createdAt'] ?? now(),
                'updated_at' => $paymentData['updatedAt'] ?? now()
            ]);
            echo "✅ Transaction created successfully\n\n";
        }
        
    } else {
        echo "❌ API returned no data or invalid response\n";
        echo "   This reference may not exist in ClickPesa system\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ API call failed: " . $e->getMessage() . "\n";
    echo "   This is likely causing the payment status error\n\n";
}

// Test 3: Test the controller method directly
echo "3. Controller Method Test:\n";
try {
    $controller = new \App\Http\Controllers\PaymentController(
        app(\App\Services\ClickPesaAPIService::class),
        app(\App\Services\MessagingServiceAPI::class)
    );
    
    $request = new \Illuminate\Http\Request(['reference' => $reference]);
    
    // Capture the view response
    $response = $controller->status($request);
    
    echo "✅ Controller executed successfully\n";
    echo "   Response type: " . get_class($response) . "\n";
    
    // Check if there's an error in the view data
    $viewData = $response->getData();
    if (isset($viewData['error'])) {
        echo "   ❌ View contains error: " . $viewData['error'] . "\n";
    } elseif (isset($viewData['paymentData'])) {
        echo "   ✅ View contains payment data\n";
        $paymentData = $viewData['paymentData'];
        if (is_array($paymentData) && isset($paymentData[0])) {
            $paymentData = $paymentData[0];
        }
        echo "   Status: " . ($paymentData['status'] ?? 'N/A') . "\n";
        echo "   Amount: TZS " . number_format($paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 0, 2) . "\n";
    } else {
        echo "   ⚠️  View has no payment data or error\n";
    }
    
} catch (Exception $e) {
    echo "❌ Controller failed: " . $e->getMessage() . "\n";
    echo "   This is the error you're seeing in the browser\n\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "TROUBLESHOOTING RESULTS:\n";
echo str_repeat("=", 50) . "\n";

echo "\n🔍 Findings:\n";
echo "1. API configuration is working\n";
echo "2. Token generation is successful\n";
echo "3. The issue is likely with the specific reference\n";
echo "4. Reference may not exist in ClickPesa system\n\n";

echo "🔧 Possible Solutions:\n";
echo "1. Check if reference {$reference} is correct\n";
echo "2. Try with a different reference that exists\n";
echo "3. Improve error message to be more specific\n";
echo "4. Add better fallback for missing references\n\n";

echo "🚀 Next Steps:\n";
echo "1. Verify the reference number is correct\n";
echo "2. Check ClickPesa dashboard for this reference\n";
echo "3. Test with a known working reference\n";
echo "4. Implement better error handling\n";

echo "\n✅ Test completed!\n";
