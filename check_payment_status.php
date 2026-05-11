<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;

echo "Checking Payment Status Issues\n";
echo "==============================\n\n";

$reference = 'FEEDTAN4252413898864';

echo "Checking Reference: {$reference}\n";
echo "========================\n";

// Check database first
echo "1. Database Check:\n";
$transaction = Transaction::where('order_reference', $reference)->first();

if ($transaction) {
    echo "✅ Transaction Found:\n";
    echo "   ID: {$transaction->id}\n";
    echo "   Status: {$transaction->status}\n";
    echo "   Amount: TZS " . number_format($transaction->amount, 2) . "\n";
    echo "   Phone: {$transaction->phone}\n";
    echo "   Payer: {$transaction->payer_name}\n";
    echo "   Payment Method: {$transaction->payment_method}\n";
    echo "   Created: {$transaction->created_at}\n";
    echo "   Updated: {$transaction->updated_at}\n\n";
    
    // Check status mapping
    echo "Status Display Analysis:\n";
    switch ($transaction->status) {
        case 'SUCCESS':
            echo "✅ Status 'SUCCESS' will display as: 'Payment Successful' (Green)\n";
            break;
        case 'SETTLED':
            echo "✅ Status 'SETTLED' will display as: 'Payment Settled' (Green)\n";
            break;
        case 'PROCESSING':
            echo "⚠️  Status 'PROCESSING' will display as: 'Processing Payment' (Yellow)\n";
            break;
        case 'PENDING':
            echo "⚠️  Status 'PENDING' will display as: 'Payment Pending' (Yellow)\n";
            break;
        case 'FAILED':
            echo "❌ Status 'FAILED' will display as: 'Payment Failed' (Red)\n";
            break;
        default:
            echo "❓ Status '{$transaction->status}' will display as: 'Unknown Status' (Gray)\n";
    }
    echo "\n";
    
} else {
    echo "❌ Transaction not found in database\n";
    echo "   This reference may not exist or may be in API only\n\n";
}

// Check recent transactions for similar references
echo "2. Recent Transactions Check:\n";
$recentTransactions = Transaction::orderBy('created_at', 'desc')
    ->where('order_reference', 'like', 'FEEDTAN%')
    ->limit(5)
    ->get();

echo "Recent FEEDTAN transactions:\n";
foreach ($recentTransactions as $trans) {
    echo "- {$trans->order_reference}: {$trans->status} (TZS " . number_format($trans->amount, 2) . ")\n";
}
echo "\n";

// Check SMS configuration
echo "3. SMS Configuration Check:\n";
$messagingConfig = config('messaging');

if ($messagingConfig) {
    echo "✅ Messaging config found:\n";
    echo "   Enabled: " . ($messagingConfig['enabled'] ? 'YES' : 'NO') . "\n";
    echo "   Payment Confirmation: " . ($messagingConfig['notifications']['payment_confirmation'] ? 'YES' : 'NO') . "\n";
    echo "   Provider: " . ($messagingConfig['provider'] ?? 'N/A') . "\n";
    echo "   API Key: " . (empty($messagingConfig['api_key']) ? 'NOT SET' : 'SET') . "\n";
    echo "   From: " . ($messagingConfig['from'] ?? 'N/A') . "\n";
    
    if (!$messagingConfig['enabled']) {
        echo "\n❌ PROBLEM: SMS messaging is DISABLED\n";
        echo "   Fix: Set 'enabled' => true in config/messaging.php\n";
    }
    
    if (!$messagingConfig['notifications']['payment_confirmation']) {
        echo "\n❌ PROBLEM: Payment confirmation SMS is DISABLED\n";
        echo "   Fix: Set 'notifications.payment_confirmation' => true in config/messaging.php\n";
    }
    
    if ($messagingConfig['enabled'] && $messagingConfig['notifications']['payment_confirmation']) {
        echo "\n✅ SMS configuration appears correct\n";
    }
} else {
    echo "❌ No messaging configuration found\n";
    echo "   Fix: Create config/messaging.php with proper settings\n";
}

echo "\n4. Status View Issues:\n";
echo "The status view at /payments/status?reference={$reference} should show:\n";
echo "- Green color for SUCCESS/SETTLED status\n";
echo "- 'Payment Settled' text for SETTLED status\n";
echo "- 'Payment Successful' text for SUCCESS status\n";
echo "- Complete transaction details\n";
echo "- Download receipt button for completed payments\n\n";

echo "5. SMS Issues:\n";
echo "SMS notifications are sent when:\n";
echo "- Webhook receives 'PAYMENT RECEIVED' event\n";
echo "- Status is 'SUCCESS'\n";
echo "- Messaging is enabled in config\n";
echo "- Payment confirmation notifications are enabled\n\n";

echo "🔧 Next Steps:\n";
echo "1. Check if transaction exists in database with correct status\n";
echo "2. Verify messaging configuration is enabled\n";
echo "3. Test webhook endpoint to trigger SMS\n";
echo "4. Check logs for SMS sending errors\n";

echo "\n✅ Check completed!\n";
