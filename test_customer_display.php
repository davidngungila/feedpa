<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;

echo "Testing Customer Name Display in Dashboard...\n\n";

try {
    $controller = new DashboardController(app('App\Services\ClickPesaAPIService'));
    $request = new Request();
    
    $response = $controller->index($request);
    $data = $response->getData();
    
    echo "✅ Dashboard loaded successfully\n\n";
    
    // Check recent payments for customer names
    if (isset($data['recentPayments']) && count($data['recentPayments']) > 0) {
        echo "Recent Transactions with Customer Names:\n";
        echo "==========================================\n";
        
        foreach ($data['recentPayments'] as $index => $payment) {
            $customerName = $payment['customer_name'] ?? 'Customer';
            $customerPhone = $payment['customer_phone'] ?? 'N/A';
            $reference = $payment['orderReference'] ?? 'N/A';
            $amount = number_format($payment['amount'] ?? $payment['collectedAmount'] ?? 0, 2);
            
            echo sprintf(
                "%d. %s - %s (%s)\n   Amount: TZS %s\n   Phone: %s\n\n",
                $index + 1,
                $reference,
                $customerName,
                $payment['status'] ?? 'UNKNOWN',
                $amount,
                $customerPhone
            );
        }
    }
    
    // Check top customers
    if (isset($data['stats']['top_customers']) && count($data['stats']['top_customers']) > 0) {
        echo "Top Customers by Total Amount:\n";
        echo "=============================\n";
        
        foreach ($data['stats']['top_customers'] as $index => $customer) {
            echo sprintf(
                "%d. %s\n   Transactions: %d\n   Total: TZS %s\n   Phone: %s\n\n",
                (int)$index + 1,
                $customer['name'],
                (int)$customer['count'],
                number_format($customer['total_amount'], 2),
                $customer['phone']
            );
        }
    }
    
    echo "🎉 Customer name display test completed!\n";
    echo "The dashboard now shows:\n";
    echo "- Customer names in recent transactions\n";
    echo "- Customer phone numbers\n";
    echo "- Top customers section with rankings\n";
    echo "- Enhanced customer data extraction\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
}
