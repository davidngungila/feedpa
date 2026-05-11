<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\PaymentController;
use App\Services\ClickPesaAPIService;
use App\Services\MessagingServiceAPI;
use Illuminate\Http\Request;
use App\Models\Transaction;

echo "Testing Payment Status and SMS Issues\n";
echo "=====================================\n\n";

$reference = 'FEEDTAN4252413898864';

echo "Testing Payment Status for Reference: {$reference}\n";
echo "==========================================\n";

try {
    // Test 1: Check payment status endpoint
    $controller = new PaymentController(
        app(ClickPesaAPIService::class),
        app(MessagingServiceAPI::class)
    );
    
    $request = new Request(['reference' => $reference]);
    
    echo "1. Checking database for transaction...\n";
    $transaction = Transaction::where('order_reference', $reference)->first();
    
    if ($transaction) {
        echo "✅ Transaction found in database:\n";
        echo "   ID: {$transaction->id}\n";
        echo "   Status: {$transaction->status}\n";
        echo "   Amount: TZS " . number_format($transaction->amount, 2) . "\n";
        echo "   Phone: {$transaction->phone}\n";
        echo "   Payer: {$transaction->payer_name}\n";
        echo "   Created: {$transaction->created_at}\n";
        echo "   Updated: {$transaction->updated_at}\n\n";
        
        // Check if status is SETTLED but not showing correctly
        if ($transaction->status === 'SETTLED') {
            echo "✅ Transaction is SETTLED in database\n";
            echo "   This should display as 'Payment Settled' in the status view\n\n";
        } elseif ($transaction->status === 'SUCCESS') {
            echo "✅ Transaction is SUCCESS in database\n";
            echo "   This should display as 'Payment Successful' in the status view\n\n";
        } else {
            echo "⚠️  Transaction status is: {$transaction->status}\n";
            echo "   Expected: SUCCESS or SETTLED for completed payments\n\n";
        }
        
    } else {
        echo "❌ Transaction not found in database\n";
        echo "   Trying API directly...\n\n";
    }
    
    // Test 2: Check API status
    echo "2. Checking ClickPesa API status...\n";
    $api = new ClickPesaAPIService();
    
    try {
        $apiData = $api->queryPaymentStatus($reference);
        
        if ($apiData && is_array($apiData)) {
            echo "✅ API returned data:\n";
            echo "   Status: " . ($apiData['status'] ?? 'N/A') . "\n";
            echo "   Amount: TZS " . number_format($apiData['collectedAmount'] ?? $apiData['amount'] ?? 0, 2) . "\n";
            echo "   Customer: " . ($apiData['customer']['customerName'] ?? 'N/A') . "\n";
            echo "   Phone: " . ($apiData['customer']['customerPhoneNumber'] ?? 'N/A') . "\n";
            echo "   Channel: " . ($apiData['channel'] ?? 'N/A') . "\n";
            echo "   Created: " . ($apiData['createdAt'] ?? 'N/A') . "\n\n";
            
            // Check if API shows SETTLED status
            if (($apiData['status'] ?? '') === 'SETTLED') {
                echo "✅ API shows SETTLED status\n";
                echo "   This should display as 'Payment Settled' with green color\n\n";
            } elseif (($apiData['status'] ?? '') === 'SUCCESS') {
                echo "✅ API shows SUCCESS status\n";
                echo "   This should display as 'Payment Successful' with green color\n\n";
            } else {
                echo "⚠️  API status is: " . ($apiData['status'] ?? 'N/A') . "\n";
                echo "   This may show as processing/pending in the view\n\n";
            }
        } else {
            echo "❌ API returned no data or error\n\n";
        }
    } catch (Exception $e) {
        echo "❌ API call failed: " . $e->getMessage() . "\n\n";
    }
    
    // Test 3: Check SMS configuration
    echo "3. Checking SMS Configuration...\n";
    $messagingConfig = config('messaging');
    
    if ($messagingConfig) {
        echo "✅ Messaging configuration found:\n";
        echo "   Enabled: " . ($messagingConfig['enabled'] ? 'Yes' : 'No') . "\n";
        echo "   Payment Confirmation: " . ($messagingConfig['notifications']['payment_confirmation'] ? 'Yes' : 'No') . "\n";
        echo "   Provider: " . ($messagingConfig['provider'] ?? 'N/A') . "\n";
        echo "   API Key: " . (empty($messagingConfig['api_key']) ? 'Not Set' : 'Set') . "\n";
        echo "   From: " . ($messagingConfig['from'] ?? 'N/A') . "\n\n";
        
        if (!$messagingConfig['enabled']) {
            echo "❌ SMS messaging is DISABLED in config\n";
            echo "   This is why SMS are not being sent!\n\n";
        } elseif (!$messagingConfig['notifications']['payment_confirmation']) {
            echo "❌ Payment confirmation SMS is DISABLED\n";
            echo "   This is why payment SMS are not being sent!\n\n";
        } else {
            echo "✅ SMS configuration appears to be enabled\n\n";
        }
    } else {
        echo "❌ No messaging configuration found\n";
        echo "   SMS notifications will not work without proper config\n\n";
    }
    
    // Test 4: Simulate webhook callback to test SMS
    echo "4. Testing SMS notification system...\n";
    
    if ($transaction) {
        // Simulate webhook data
        $webhookData = [
            'event' => 'PAYMENT RECEIVED',
            'status' => $transaction->status,
            'orderReference' => $reference,
            'collectedAmount' => $transaction->amount,
            'customer' => [
                'customerName' => $transaction->payer_name,
                'customerPhoneNumber' => $transaction->phone
            ],
            'channel' => $transaction->payment_method ?? 'Mobile Money',
            'id' => $transaction->transaction_id
        ];
        
        echo "Simulating webhook with data:\n";
        echo "   Event: " . $webhookData['event'] . "\n";
        echo "   Status: " . $webhookData['status'] . "\n";
        echo "   Amount: TZS " . number_format($webhookData['collectedAmount'], 2) . "\n";
        echo "   Customer: " . $webhookData['customer']['customerName'] . "\n";
        echo "   Phone: " . $webhookData['customer']['customerPhoneNumber'] . "\n\n";
        
        // Check if webhook would trigger SMS
        if ($webhookData['event'] === 'PAYMENT RECEIVED' && $webhookData['status'] === 'SUCCESS') {
            echo "✅ Webhook conditions met for SMS sending\n";
            
            if ($messagingConfig['enabled'] && $messagingConfig['notifications']['payment_confirmation']) {
                echo "✅ SMS should be sent\n";
                
                // Test actual SMS sending
                try {
                    $messaging = new MessagingServiceAPI();
                    $result = $messaging->sendPaymentConfirmation(
                        $webhookData['customer']['customerPhoneNumber'],
                        $webhookData
                    );
                    
                    echo "✅ SMS test result: " . ($result ? 'Success' : 'Failed') . "\n";
                } catch (Exception $e) {
                    echo "❌ SMS test failed: " . $e->getMessage() . "\n";
                }
            } else {
                echo "❌ SMS configuration disabled - SMS will not be sent\n";
            }
        } else {
            echo "⚠️  Webhook conditions not met for SMS:\n";
            echo "   Event: " . $webhookData['event'] . " (needs: PAYMENT RECEIVED)\n";
            echo "   Status: " . $webhookData['status'] . " (needs: SUCCESS)\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "SUMMARY OF ISSUES:\n";
    echo str_repeat("=", 50) . "\n";
    
    echo "\n🔧 Payment Status Issues:\n";
    echo "- Check if transaction status in database matches expected values\n";
    echo "- Verify API returns correct status (SUCCESS/SETTLED)\n";
    echo "- Status view should show 'Payment Settled' for SETTLED status\n";
    echo "- Status view should show 'Payment Successful' for SUCCESS status\n\n";
    
    echo "🔧 SMS Issues:\n";
    echo "- Check messaging configuration in config/messaging.php\n";
    echo "- Verify 'enabled' => true in messaging config\n";
    echo "- Verify 'notifications.payment_confirmation' => true\n";
    echo "- Check API key and provider settings\n";
    echo "- Webhook sends SMS only for PAYMENT RECEIVED + SUCCESS events\n\n";
    
    echo "🔧 Fixes Needed:\n";
    echo "1. Update transaction status to SUCCESS/SETTLED if incorrect\n";
    echo "2. Enable SMS messaging in configuration\n";
    echo "3. Test webhook endpoint to ensure SMS triggers\n";
    echo "4. Verify SMS provider API credentials\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
}

echo "\n✅ Test completed!\n";
