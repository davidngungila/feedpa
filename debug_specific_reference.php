<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging Specific Reference: FEEDTAN911CB1EE48552\n";
echo "===============================================\n\n";

$reference = 'FEEDTAN911CB1EE48552';

echo "1. Database Check:\n";
try {
    $transaction = \App\Models\Transaction::where('order_reference', $reference)->first();
    
    if ($transaction) {
        echo "✅ Transaction FOUND in database:\n";
        echo "   ID: " . $transaction->id . "\n";
        echo "   Status: " . $transaction->status . "\n";
        echo "   Amount: TZS " . number_format($transaction->amount, 2) . "\n";
        echo "   Customer: " . $transaction->payer_name . "\n";
        echo "   Phone: " . $transaction->phone . "\n";
        echo "   Created: " . $transaction->created_at . "\n";
        echo "   Updated: " . $transaction->updated_at . "\n";
        echo "   SMS Sent: " . ($transaction->sms_sent ? 'YES' : 'NO') . "\n";
        echo "   SMS Message: " . ($transaction->sms_message ?? 'NOT SET') . "\n";
        echo "   SMS Sent At: " . ($transaction->sms_sent_at ?? 'NOT SET') . "\n";
        echo "   SMS Error: " . ($transaction->sms_error ?? 'NONE') . "\n";
        
        echo "\n✅ Transaction exists, should display correctly!\n";
        
    } else {
        echo "❌ Transaction NOT found in database\n";
        echo "   This is likely causing the error\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database check failed: " . $e->getMessage() . "\n";
}

echo "\n2. Testing PaymentController Directly:\n";
try {
    $controller = new \App\Http\Controllers\PaymentController(
        app(\App\Services\ClickPesaAPIService::class),
        app(\App\Services\MessagingServiceAPI::class)
    );
    
    $request = new \Illuminate\Http\Request(['reference' => $reference]);
    
    // Execute status method
    $response = $controller->status($request);
    
    echo "✅ PaymentController executed successfully\n";
    echo "   Response type: " . get_class($response) . "\n";
    
    // Get view data
    $viewData = $response->getData();
    $paymentData = $viewData['paymentData'] ?? null;
    $error = $viewData['error'] ?? null;
    $orderReference = $viewData['orderReference'] ?? null;
    
    if ($error) {
        echo "❌ Error in response: {$error}\n";
        echo "   This is the error you're seeing!\n";
    } elseif ($paymentData) {
        echo "✅ Payment data found in response\n";
        
        // Handle array format
        if (is_array($paymentData) && isset($paymentData[0])) {
            $paymentData = $paymentData[0];
        }
        
        $status = $paymentData['status'] ?? 'N/A';
        $amount = $paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 0;
        
        echo "   Status: {$status}\n";
        echo "   Amount: TZS " . number_format($amount, 2) . "\n";
        echo "   Customer: " . ($paymentData['payer_name'] ?? $paymentData['customer']['customerName'] ?? 'N/A') . "\n";
        echo "   Phone: " . ($paymentData['phone'] ?? $paymentData['customer']['customerPhoneNumber'] ?? 'N/A') . "\n";
        
        if (isset($paymentData['sms_sent'])) {
            echo "   SMS Sent: " . ($paymentData['sms_sent'] ? 'YES' : 'NO') . "\n";
            echo "   SMS Message: " . ($paymentData['sms_message'] ?? 'NOT SET') . "\n";
            echo "   SMS Sent At: " . ($paymentData['sms_sent_at'] ?? 'NOT SET') . "\n";
        } else {
            echo "   SMS Data: NOT INCLUDED\n";
        }
        
        echo "\n✅ Payment status should work!\n";
        
    } else {
        echo "❌ No payment data and no error\n";
        echo "   This indicates an issue\n";
    }
    
} catch (Exception $e) {
    echo "❌ PaymentController test failed: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n3. Testing API Directly:\n";
try {
    echo "Testing ClickPesa API directly...\n";
    $apiService = app(\App\Services\ClickPesaAPIService::class);
    
    // Generate token
    echo "   Generating API token...\n";
    $token = $apiService->generateToken();
    echo "   ✅ Token generated\n";
    
    // Query payment status
    echo "   Querying payment status for {$reference}...\n";
    $apiData = $apiService->queryPaymentStatus($reference);
    
    if ($apiData && is_array($apiData)) {
        echo "✅ API returned data:\n";
        echo "   Status: " . ($apiData['status'] ?? 'N/A') . "\n";
        echo "   Amount: TZS " . number_format($apiData['collectedAmount'] ?? $apiData['amount'] ?? 0, 2) . "\n";
        echo "   Customer: " . ($apiData['customer']['customerName'] ?? 'N/A') . "\n";
        echo "   Phone: " . ($apiData['customer']['customerPhoneNumber'] ?? 'N/A') . "\n";
        echo "   Channel: " . ($apiData['channel'] ?? 'N/A') . "\n";
        echo "   Created: " . ($apiData['createdAt'] ?? 'N/A') . "\n";
        
        echo "\n✅ API has data for this reference!\n";
        
    } else {
        echo "❌ API returned no data or error\n";
        echo "   This reference may not exist in ClickPesa\n";
    }
    
} catch (Exception $e) {
    echo "❌ API test failed: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "DIAGNOSIS SUMMARY:\n";
echo str_repeat("=", 60) . "\n";

echo "\n🔍 Root Cause Analysis:\n";
echo "The error 'Unable to fetch payment status' occurs when:\n";
echo "1. Transaction not found in database AND API fails\n";
echo "2. Exception occurs in PaymentController::status()\n";
echo "3. Laravel configuration issues\n\n";

echo "🎯 For Reference FEEDTAN911CB1EE48552:\n";

// Check database directly without Laravel
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=feedtanclickpesa', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE order_reference = ?");
    $stmt->execute([$reference]);
    $dbTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dbTransaction) {
        echo "✅ Database contains this transaction\n";
        echo "   Status: " . $dbTransaction['status'] . "\n";
        echo "   Amount: TZS " . number_format($dbTransaction['amount'], 2) . "\n";
        echo "   This should display correctly!\n\n";
        
        echo "🔧 POSSIBLE ISSUE:\n";
        echo "The error might be in Laravel bootstrap or .env parsing\n";
        echo "Try clearing Laravel cache and testing again\n";
        
    } else {
        echo "❌ Database does NOT contain this transaction\n";
        echo "   This is likely the root cause!\n";
        echo "   Reference may be wrong or not in system\n\n";
        
        echo "🔧 SOLUTION:\n";
        echo "1. Verify reference number is correct\n";
        echo "2. Check ClickPesa dashboard for this reference\n";
        echo "3. Try with a known good reference\n";
    }
    
} catch (Exception $e) {
    echo "❌ Direct database check failed: " . $e->getMessage() . "\n";
}

echo "\n🚀 IMMEDIATE ACTIONS:\n";
echo "1. Clear Laravel cache: php artisan config:clear\n";
echo "2. Clear application cache: php artisan cache:clear\n";
echo "3. Test with known good reference first\n";
echo "4. Check Laravel logs for errors\n";
echo "5. Verify .env file is properly formatted\n";

echo "\n✅ Debugging completed!\n";
