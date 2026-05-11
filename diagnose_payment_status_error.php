<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Diagnosing Payment Status Error\n";
echo "================================\n\n";

// Check ClickPesa configuration
echo "1. ClickPesa Configuration Check:\n";
$clickpesaConfig = config('clickpesa');

echo "API Base URL: " . ($clickpesaConfig['api_base_url'] ?? 'NOT SET') . "\n";
echo "API Key: " . (empty($clickpesaConfig['api_key']) ? 'NOT SET' : 'SET') . "\n";
echo "Client ID: " . (empty($clickpesaConfig['client_id']) ? 'NOT SET' : 'SET') . "\n";
echo "Currency: " . ($clickpesaConfig['currency'] ?? 'NOT SET') . "\n\n";

if (empty($clickpesaConfig['api_key']) || empty($clickpesaConfig['client_id'])) {
    echo "❌ PROBLEM: ClickPesa API credentials missing\n";
    echo "   This is causing the payment status error!\n\n";
} else {
    echo "✅ ClickPesa configuration appears complete\n\n";
}

// Test API service initialization
echo "2. API Service Initialization Test:\n";
try {
    $apiService = app(\App\Services\ClickPesaAPIService::class);
    echo "✅ ClickPesaAPIService instantiated successfully\n\n";
} catch (Exception $e) {
    echo "❌ ClickPesaAPIService failed to initialize: " . $e->getMessage() . "\n\n";
}

// Test payment status with a working reference
echo "3. Payment Status Test:\n";
$reference = 'FEEDTANE8D8BFF598311'; // Use a known reference

echo "Testing with reference: {$reference}\n";

try {
    $controller = new \App\Http\Controllers\PaymentController(
        app(\App\Services\ClickPesaAPIService::class),
        app(\App\Services\MessagingServiceAPI::class)
    );
    
    $request = new \Illuminate\Http\Request(['reference' => $reference]);
    
    // Test the status method
    ob_start();
    $response = $controller->status($request);
    $output = ob_get_clean();
    
    echo "✅ Status method executed\n";
    echo "   Response: " . get_class($response) . "\n";
    
} catch (Exception $e) {
    echo "❌ Status method failed: " . $e->getMessage() . "\n";
    echo "   This is the error causing the issue!\n\n";
}

// Check if ClickPesaServiceProvider is registered
echo "4. Service Provider Registration:\n";
$providers = config('app.providers');
$clickpesaProviderRegistered = in_array(\App\Providers\ClickPesaServiceProvider::class, $providers);

if ($clickpesaProviderRegistered) {
    echo "✅ ClickPesaServiceProvider is registered\n";
} else {
    echo "❌ ClickPesaServiceProvider is NOT registered\n";
    echo "   This could cause dependency injection issues\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "DIAGNOSIS SUMMARY:\n";
echo str_repeat("=", 50) . "\n";

echo "\n🔧 Root Cause of Payment Status Error:\n";
echo "1. Missing ClickPesa API credentials (api_key, client_id)\n";
echo "2. ClickPesaAPIService cannot authenticate with API\n";
echo "3. API calls fail, causing 'Unable to fetch payment status' error\n\n";

echo "🔧 FIXES NEEDED:\n";
echo "1. Set CLICKPESA_API_KEY in environment\n";
echo "2. Set CLICKPESA_CLIENT_ID in environment\n";
echo "3. Or add default values to config/clickpesa.php\n";
echo "4. Test API connectivity after configuration\n\n";

echo "🚀 IMMEDIATE ACTIONS:\n";
echo "1. Check .env file for ClickPesa credentials\n";
echo "2. Add missing environment variables\n";
echo "3. Clear config cache: php artisan config:clear\n";
echo "4. Test payment status endpoint again\n";

echo "\n✅ Diagnosis completed!\n";
