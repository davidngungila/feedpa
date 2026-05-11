<?php

require_once 'vendor/autoload.php';

use App\Models\Transaction;
use App\Services\ClickPesaAPIService;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Payment Status Debug ===\n\n";

// Test references
$workingReference = 'FEEDTAN4252413898864';
$nonWorkingReference = 'FEEDTAN42A82EC725898';

echo "Testing working reference: $workingReference\n";
echo "Testing non-working reference: $nonWorkingReference\n\n";

// Check database
echo "=== DATABASE CHECK ===\n";
$workingTransaction = Transaction::where('order_reference', $workingReference)->first();
$nonWorkingTransaction = Transaction::where('order_reference', $nonWorkingReference)->first();

echo "Working reference in DB: " . ($workingTransaction ? "YES" : "NO") . "\n";
if ($workingTransaction) {
    echo "  - ID: {$workingTransaction->id}\n";
    echo "  - Status: {$workingTransaction->status}\n";
    echo "  - Amount: {$workingTransaction->amount}\n";
    echo "  - Created: {$workingTransaction->created_at}\n";
}

echo "Non-working reference in DB: " . ($nonWorkingTransaction ? "YES" : "NO") . "\n";
if ($nonWorkingTransaction) {
    echo "  - ID: {$nonWorkingTransaction->id}\n";
    echo "  - Status: {$nonWorkingTransaction->status}\n";
    echo "  - Amount: {$nonWorkingTransaction->amount}\n";
    echo "  - Created: {$nonWorkingTransaction->created_at}\n";
}

// Check API
echo "\n=== API CHECK ===\n";
$api = app(ClickPesaAPIService::class);

try {
    echo "Checking working reference via API...\n";
    $workingApiResponse = $api->queryPaymentStatus($workingReference);
    echo "Working reference API response: " . ($workingApiResponse ? "DATA FOUND" : "NO DATA") . "\n";
    if ($workingApiResponse) {
        echo "  - Status: " . ($workingApiResponse['status'] ?? 'N/A') . "\n";
        echo "  - Amount: " . ($workingApiResponse['collectedAmount'] ?? $workingApiResponse['amount'] ?? 'N/A') . "\n";
    }
} catch (Exception $e) {
    echo "Working reference API error: " . $e->getMessage() . "\n";
}

try {
    echo "\nChecking non-working reference via API...\n";
    $nonWorkingApiResponse = $api->queryPaymentStatus($nonWorkingReference);
    echo "Non-working reference API response: " . ($nonWorkingApiResponse ? "DATA FOUND" : "NO DATA") . "\n";
    if ($nonWorkingApiResponse) {
        echo "  - Status: " . ($nonWorkingApiResponse['status'] ?? 'N/A') . "\n";
        echo "  - Amount: " . ($nonWorkingApiResponse['collectedAmount'] ?? $nonWorkingApiResponse['amount'] ?? 'N/A') . "\n";
    }
} catch (Exception $e) {
    echo "Non-working reference API error: " . $e->getMessage() . "\n";
}

// List all recent transactions
echo "\n=== RECENT TRANSACTIONS IN DB ===\n";
$recentTransactions = Transaction::orderBy('created_at', 'desc')->limit(10)->get();
foreach ($recentTransactions as $transaction) {
    echo "- {$transaction->order_reference} ({$transaction->status}) - {$transaction->amount} TZS\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
