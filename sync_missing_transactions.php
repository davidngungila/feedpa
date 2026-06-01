<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Transaction;
use App\Support\TransactionFieldResolver;
use Illuminate\Support\Str;

echo "==============================================\n";
echo "Starting Transaction Sync from ClickPesa API\n";
echo "==============================================\n\n";

// Step 1: Get all existing order references from our database
echo "Step 1: Fetching existing transactions from database...\n";
$existingReferences = Transaction::pluck('order_reference')->filter()->unique()->toArray();
echo "✅ Found " . count($existingReferences) . " existing transactions in database\n\n";

// Step 2: Query ClickPesa API for all payments
echo "Step 2: Querying ClickPesa API for all payments...\n";
try {
    $api = app('App\Services\ClickPesaAPIService');
    $allPayments = $api->queryAllPayments([
        'limit' => 100, // Adjust this as needed
        'status' => 'all'
    ]);
    
    echo "✅ API responded successfully\n";
} catch (\Exception $e) {
    echo "❌ Error querying API: " . $e->getMessage() . "\n";
    exit(1);
}

// Check what format the data is in
$paymentsData = [];
if (isset($allPayments['data']) && is_array($allPayments['data'])) {
    $paymentsData = $allPayments['data'];
} elseif (is_array($allPayments)) {
    $paymentsData = $allPayments;
}

echo "📊 Found " . count($paymentsData) . " payments from API\n\n";

// Step 3: Process each payment
echo "Step 3: Processing payments...\n";
$syncedCount = 0;
$skippedCount = 0;

foreach ($paymentsData as $payment) {
    // Get order reference from API payment data
    $orderRef = $payment['orderReference'] ?? $payment['order_reference'] ?? null;
    
    if (!$orderRef) {
        echo "⚠️ Skipping payment with no order reference\n";
        continue;
    }
    
    // Check if we already have this transaction
    if (in_array($orderRef, $existingReferences)) {
        $skippedCount++;
        continue;
    }
    
    // Extract data from API payment
    $transactionId = $payment['id'] ?? $payment['transaction_id'] ?? null;
    $status = $payment['status'] ?? 'UNKNOWN';
    $amount = $payment['collectedAmount'] ?? $payment['amount'] ?? 0;
    $currency = $payment['collectedCurrency'] ?? $payment['currency'] ?? 'TZS';
    $phone = $payment['customer']['customerPhoneNumber'] ?? $payment['paymentPhoneNumber'] ?? $payment['phone'] ?? null;
    $payerName = $payment['customer']['customerName'] ?? $payment['payer_name'] ?? $payment['customerName'] ?? 'Unknown';
    $description = $payment['description'] ?? $payment['narrative'] ?? 'Payment';
    $paymentMethod = $payment['channel'] ?? $payment['paymentMethod'] ?? $payment['payment_method'] ?? null;
    $createdAt = $payment['createdAt'] ?? now();
    $updatedAt = $payment['updatedAt'] ?? now();
    
    // Create transaction in database
    try {
        Transaction::create([
            'id' => (string) Str::uuid(),
            'order_reference' => $orderRef,
            'transaction_id' => $transactionId,
            'status' => $status,
            'amount' => $amount,
            'currency' => $currency,
            'phone' => $phone,
            'payer_name' => $payerName,
            'customer_name' => $payerName,
            'description' => $description,
            'payment_method' => $paymentMethod,
            'callback_data' => $payment,
            'callback_received_at' => now(),
            'type' => 'payment',
            'created_at' => $createdAt,
            'updated_at' => $updatedAt
        ]);
        
        $syncedCount++;
        echo "✅ Synced transaction: {$orderRef} (Status: {$status}, Amount: {$amount} {$currency})\n";
        
    } catch (\Exception $e) {
        echo "❌ Failed to sync {$orderRef}: " . $e->getMessage() . "\n";
    }
}

// Summary
echo "\n==============================================\n";
echo "Sync Complete!\n";
echo "==============================================\n";
echo "✅ Synced {$syncedCount} new transactions\n";
echo "⚠️ Skipped {$skippedCount} existing transactions\n";
echo "Total processed: " . ($syncedCount + $skippedCount) . "\n";
echo "==============================================\n";
