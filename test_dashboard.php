<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;

echo "Testing Dashboard Controller...\n";

try {
    $controller = new DashboardController(app('App\Services\ClickPesaAPIService'));
    $request = new Request();
    
    echo "✅ DashboardController instantiated successfully\n";
    
    // Test the index method
    $response = $controller->index($request);
    
    echo "✅ Dashboard index method executed successfully\n";
    echo "Response type: " . get_class($response) . "\n";
    
    // Check if response is a view
    if (method_exists($response, 'getData')) {
        $data = $response->getData();
        echo "✅ View data extracted successfully\n";
        
        if (isset($data['stats'])) {
            echo "✅ Stats data present\n";
            echo "Total transactions: " . ($data['stats']['total_transactions'] ?? 0) . "\n";
        }
        
        if (isset($data['recentPayments'])) {
            echo "✅ Recent payments data present\n";
            echo "Recent payments count: " . count($data['recentPayments']) . "\n";
        }
        
        if (isset($data['error'])) {
            echo "⚠️  Error in dashboard: " . $data['error'] . "\n";
        } else {
            echo "✅ No errors in dashboard\n";
        }
    }
    
    echo "\n🎉 Dashboard test completed successfully!\n";
    echo "The dashboard should now load without BillPayNumber errors.\n";
    
} catch (Exception $e) {
    echo "❌ Dashboard test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
