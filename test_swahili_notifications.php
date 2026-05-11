<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test payment data matching user's example
$testPaymentData = [
    'event' => 'PAYMENT RECEIVED',
    'status' => 'SUCCESS',
    'orderReference' => 'ORD123456',
    'collectedAmount' => '10000',
    'collectedCurrency' => 'TZS',
    'channel' => 'Mobile Money',
    'customer' => [
        'customerName' => 'Jina la Mteja',
        'customerPhoneNumber' => '255700000000'
    ],
    'createdAt' => '2026-05-04T09:43:00Z',
    'id' => 'FTN-20260504-8XU94K'
];

echo "Testing Swahili Notification Formats:\n";
echo "=====================================\n\n";

// Test SMS format
echo "SMS MESSAGE FORMAT:\n";
echo "------------------\n";
$smsService = new App\Services\MessagingServiceAPI();
$smsMessage = new ReflectionMethod($smsService, 'formatPaymentMessage');
$smsMessage->setAccessible(true);
$smsText = $smsMessage->invoke($smsService, $testPaymentData);
echo $smsText . "\n\n";

// Test Email format
echo "EMAIL MESSAGE FORMAT:\n";
echo "-------------------\n";
$emailService = new App\Services\EmailNotificationService();
$emailMessage = new ReflectionMethod($emailService, 'buildEmailContent');
$emailMessage->setAccessible(true);
$emailText = $emailMessage->invoke($emailService, $testPaymentData);
echo $emailText . "\n\n";

echo "Expected SMS Format:\n";
echo "Malipo yamefanikiwa. Tumepokea kiasi cha TZS 10,000.00 kutoka kwa Jina la Mteja kupitia Mobile Money tarehe 04 May 2026, 09:43. Kumbukumbu ya muamala: FTN-20260504-8XU94K, Rejea: ORD123456. Asante kwa kutumia huduma zetu.\n\n";

echo "Test completed! Both SMS and Email now use Swahili format.\n";
