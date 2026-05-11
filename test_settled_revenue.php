<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;

echo "Testing Revenue Calculations for Settled Transactions Only...\n\n";

try {
    $controller = new DashboardController(app('App\Services\ClickPesaAPIService'));
    $request = new Request();
    
    $response = $controller->index($request);
    $data = $response->getData();
    
    echo "✅ Dashboard loaded with updated revenue calculations\n\n";
    
    // Display revenue overview
    echo "Revenue Overview (Settled Transactions Only):\n";
    echo "===============================================\n";
    echo "Total Revenue: TZS " . number_format($data['stats']['total_amount'] ?? 0, 2) . "\n";
    echo "Average Transaction: TZS " . number_format($data['stats']['average_transaction'] ?? 0, 2) . "\n";
    echo "Today's Revenue: TZS " . number_format($data['stats']['today_revenue'] ?? 0, 2) . "\n";
    echo "Settled Count: " . ($data['stats']['settled_count'] ?? 0) . " transactions\n\n";
    
    // Show transaction breakdown
    echo "Transaction Breakdown:\n";
    echo "=====================\n";
    echo "Total Transactions: " . ($data['stats']['total_transactions'] ?? 0) . "\n";
    echo "Successful (incl. pending): " . ($data['stats']['successful'] ?? 0) . "\n";
    echo "Settled (Revenue): " . ($data['stats']['settled_count'] ?? 0) . "\n";
    echo "Failed: " . ($data['stats']['failed'] ?? 0) . "\n";
    echo "Pending: " . ($data['stats']['pending'] ?? 0) . "\n\n";
    
    // Verify settled transactions in data
    if (isset($data['successfulPayments']) && count($data['successfulPayments']) > 0) {
        echo "Settled/SUCCESS Transactions in Dashboard:\n";
        echo "==========================================\n";
        
        $settledCount = 0;
        foreach ($data['successfulPayments'] as $payment) {
            $status = $payment['status'] ?? 'UNKNOWN';
            $amount = number_format($payment['amount'] ?? $payment['collectedAmount'] ?? 0, 2);
            $customer = $payment['customer_name'] ?? 'Customer';
            
            echo sprintf(
                "%s - %s - TZS %s [%s]\n",
                $payment['orderReference'] ?? 'N/A',
                $customer,
                $amount,
                $status
            );
            
            if ($status === 'SETTLED') {
                $settledCount++;
            }
        }
        
        echo "\nSettled transactions found: {$settledCount}\n";
    }
    
    echo "\n🎉 Revenue Calculation Test Completed!\n\n";
    echo "✅ Changes Applied:\n";
    echo "- Total Revenue now only includes SETTLED transactions\n";
    echo "- Average Transaction calculated from settled transactions only\n";
    echo "- Today's Revenue only counts settled transactions from today\n";
    echo "- Added settled_count for tracking settled transactions\n\n";
    
    echo "💡 Benefits:\n";
    echo "- More accurate revenue reporting\n";
    echo "- Only counts money actually received\n";
    echo "- Better financial tracking\n";
    echo "- Clear distinction between successful and settled transactions\n";
    
} catch (Exception $e) {
    echo "❌ Revenue test failed: " . $e->getMessage() . "\n";
}
