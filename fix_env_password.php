<?php

echo "Fixing .env Gmail Password Issue\n";
echo "=================================\n\n";

$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    echo "❌ .env file not found\n";
    exit(1);
}

echo "1. Reading current .env file...\n";
$envContent = file_get_contents($envFile);

// Find the problematic line
$pattern = '/^(MAIL_PASSWORD\s*=\s*)([^"\n\r]+)(.*)$/m';
$replacement = '${1}"xgbs yqgn kmjy buqn"${3}';

echo "2. Fixing Gmail password line...\n";

if (preg_match($pattern, $envContent)) {
    $newContent = preg_replace($pattern, $replacement, $envContent);
    
    // Backup original file
    $backupFile = $envFile . '.backup.' . date('Y-m-d_H-i-s');
    file_put_contents($backupFile, $envContent);
    echo "✅ Backup created: {$backupFile}\n";
    
    // Write fixed content
    file_put_contents($envFile, $newContent);
    echo "✅ .env file fixed\n";
    
    // Verify the fix
    echo "\n3. Verifying fix...\n";
    $fixedContent = file_get_contents($envFile);
    if (strpos($fixedContent, 'MAIL_PASSWORD="xgbs yqgn kmjy buqn"') !== false) {
        echo "✅ Gmail password is now properly quoted\n";
    } else {
        echo "❌ Fix verification failed\n";
    }
    
    echo "\n4. Clearing Laravel cache...\n";
    $configCache = __DIR__ . '/bootstrap/cache/config.php';
    if (file_exists($configCache)) {
        unlink($configCache);
        echo "✅ Config cache cleared\n";
    }
    
    echo "\n🎯 NEXT STEPS:\n";
    echo "1. Run: php artisan serve\n";
    echo "2. Test: /payments/status?reference=FEEDTAN4252413898864\n";
    echo "3. Verify: SETTLED SMS notifications work\n";
    
} else {
    echo "❌ Could not find problematic MAIL_PASSWORD line\n";
    echo "   The line may already be fixed or have different format\n";
    
    // Show current MAIL_PASSWORD line
    $lines = explode("\n", $envContent);
    foreach ($lines as $line) {
        if (strpos($line, 'MAIL_PASSWORD=') !== false) {
            echo "   Current line: " . trim($line) . "\n";
            break;
        }
    }
}

echo "\n✅ .env fix process completed!\n";
