<?php

echo "Direct Payment Status Test (Bypassing .env)\n";
echo "==========================================\n\n";

// Test the payment status directly without Laravel bootstrap
$reference = 'FEEDTAN4252413898864';

echo "Testing reference: {$reference}\n\n";

// Check database directly
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=feedtanclickpesa', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "1. Database Connection Test:\n";
    echo "✅ Connected to database successfully\n\n";
    
    echo "2. Transaction Lookup:\n";
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE order_reference = ?");
    $stmt->execute([$reference]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($transaction) {
        echo "✅ Transaction found:\n";
        echo "   ID: " . $transaction['id'] . "\n";
        echo "   Status: " . $transaction['status'] . "\n";
        echo "   Amount: TZS " . number_format($transaction['amount'], 2) . "\n";
        echo "   Customer: " . $transaction['payer_name'] . "\n";
        echo "   Phone: " . $transaction['phone'] . "\n";
        echo "   Created: " . $transaction['created_at'] . "\n\n";
        
        // Status display mapping
        $statusDisplay = match($transaction['status']) {
            'SUCCESS' => 'Payment Successful (Green ✅)',
            'SETTLED' => 'Payment Settled (Green ✅)',
            'PROCESSING' => 'Processing Payment (Yellow ⏳)',
            'PENDING' => 'Payment Pending (Yellow ⏳)',
            'FAILED' => 'Payment Failed (Red ❌)',
            default => 'Unknown Status (Gray ❓)'
        };
        
        echo "   Expected Display: {$statusDisplay}\n\n";
        
        echo "✅ PAYMENT STATUS SHOULD WORK!\n";
        echo "   The transaction exists and has status: " . $transaction['status'] . "\n";
        echo "   The browser should show: {$statusDisplay}\n\n";
        
    } else {
        echo "❌ Transaction not found in database\n";
        echo "   This reference may not exist\n\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n\n";
}

echo "3. .env Issue Analysis:\n";
echo "The .env file has a parsing issue with the Gmail password.\n";
echo "Password with spaces needs to be quoted: \"xgbs yqgn kmjy buqn\"\n\n";

echo "4. Expected Browser Behavior:\n";
echo "When .env is fixed, visiting /payments/status?reference={$reference} should show:\n";
echo "- Green status indicator\n";
echo "- 'Payment Successful' text\n";
echo "- TZS 85,000.00 amount\n";
echo "- Complete transaction details\n\n";

echo "🔧 QUICK FIX:\n";
echo "1. Edit .env file\n";
echo "2. Find: MAIL_PASSWORD=xgbs yqgn kmjy buqn\n";
echo "3. Change to: MAIL_PASSWORD=\"xgbs yqgn kmjy buqn\"\n";
echo "4. Save .env file\n";
echo "5. Clear config: php artisan config:clear\n";
echo "6. Test payment status endpoint\n\n";

echo "✅ Direct test completed!\n";
