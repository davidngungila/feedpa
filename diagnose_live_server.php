<?php

/**
 * Live Server Diagnostic Script
 * Run this script on your live server to identify issues
 */

echo "=== Live Server Diagnostic Tool ===\n\n";

// Check 1: Laravel Version
echo "1. Laravel Version:\n";
echo "Version: " . app()->version() . "\n\n";

// Check 2: Environment
echo "2. Environment:\n";
echo "Environment: " . env('APP_ENV') . "\n";
echo "Debug Mode: " . (env('APP_DEBUG') ? 'true' : 'false') . "\n";
echo "App URL: " . env('APP_URL') . "\n\n";

// Check 3: Database Connection
echo "3. Database Connection:\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "Database: Connected\n";
    echo "Database Name: " . DB::connection()->getDatabaseName() . "\n";
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Check 4: Required Environment Variables
echo "4. Environment Variables:\n";
$required_vars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
foreach ($required_vars as $var) {
    $value = env($var);
    echo "$var: " . ($value ? 'SET' : 'MISSING') . "\n";
}
echo "\n";

// Check 5: ClickPesa API Configuration
echo "5. ClickPesa API Configuration:\n";
$api_key = env('CLICKPESA_API_KEY');
$client_id = env('CLICKPESA_CLIENT_ID');
echo "API Key: " . ($api_key ? 'SET' : 'MISSING') . "\n";
echo "Client ID: " . ($client_id ? 'SET' : 'MISSING') . "\n";
echo "API Base URL: " . env('CLICKPESA_API_BASE_URL', 'not set') . "\n\n";

// Check 6: Database Tables
echo "6. Database Tables:\n";
try {
    $tables = DB::select('SHOW TABLES');
    $table_names = [];
    foreach ($tables as $table) {
        $table_names[] = array_values((array)$table)[0];
    }
    
    $required_tables = ['transactions', 'users', 'migrations'];
    foreach ($required_tables as $table) {
        echo "$table: " . (in_array($table, $table_names) ? 'EXISTS' : 'MISSING') . "\n";
    }
} catch (Exception $e) {
    echo "Error checking tables: " . $e->getMessage() . "\n";
}
echo "\n";

// Check 7: Transactions Table Structure
echo "7. Transactions Table Structure:\n";
try {
    if (Schema::hasTable('transactions')) {
        $columns = Schema::getColumnListing('transactions');
        $required_columns = ['order_reference', 'phone_number', 'payer_name', 'amount', 'status'];
        foreach ($required_columns as $column) {
            echo "$column: " . (in_array($column, $columns) ? 'EXISTS' : 'MISSING') . "\n";
        }
    } else {
        echo "Transactions table does not exist\n";
    }
} catch (Exception $e) {
    echo "Error checking transactions table: " . $e->getMessage() . "\n";
}
echo "\n";

// Check 8: File Permissions
echo "8. File Permissions:\n";
$paths = ['storage', 'bootstrap/cache', '.env'];
foreach ($paths as $path) {
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = is_writable($path) ? 'WRITABLE' : 'NOT WRITABLE';
        echo "$path: $perms ($writable)\n";
    } else {
        echo "$path: MISSING\n";
    }
}
echo "\n";

// Check 9: PHP Extensions
echo "9. PHP Extensions:\n";
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'json', 'curl'];
foreach ($required_extensions as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? 'LOADED' : 'MISSING') . "\n";
}
echo "\n";

// Check 10: Routes
echo "10. Payment Routes:\n";
try {
    $routes = app('router')->getRoutes();
    $payment_routes = [];
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (strpos($uri, 'payment') !== false) {
            $payment_routes[] = $uri;
        }
    }
    if (empty($payment_routes)) {
        echo "No payment routes found\n";
    } else {
        foreach ($payment_routes as $route) {
            echo "Route: $route\n";
        }
    }
} catch (Exception $e) {
    echo "Error checking routes: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== Diagnostic Complete ===\n";
echo "If you see any MISSING or ERROR items above, those need to be fixed.\n";

?>
