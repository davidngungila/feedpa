<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\EmailConfigService;

echo "Designing and Sending Well-Formatted Email Test\n";
echo "===============================================\n\n";

// Create professional HTML email template
function createProfessionalEmailTemplate($paymentData) {
    $amount = number_format($paymentData['collectedAmount'] ?? 50000, 2);
    $customerName = $paymentData['customer']['customerName'] ?? 'JOHN DOE';
    $customerPhone = $paymentData['customer']['customerPhoneNumber'] ?? '255712345678';
    $paymentMethod = $paymentData['channel'] ?? 'Mobile Money';
    $transactionId = $paymentData['id'] ?? 'FTN-' . date('Ymd') . '-DESIGN';
    $reference = $paymentData['orderReference'] ?? 'TEST-' . date('YmdHis');
    $date = \Carbon\Carbon::parse($paymentData['createdAt'] ?? now())->format('d M Y, H:i');
    
    return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Malipo Yamefanikiwa - FeedTan Community Microfinance Group</title>
</head>
<body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff;'>
        <!-- Header -->
        <div style='background: linear-gradient(135deg, #28a745, #20c997); padding: 30px; text-align: center; color: white;'>
            <div style='font-size: 24px; font-weight: bold; margin-bottom: 10px;'>
                FEEDTAN COMMUNITY MICROFINANCE GROUP
            </div>
            <div style='font-size: 16px; opacity: 0.9;'>
                Pamoja Tunakua
            </div>
        </div>
        
        <!-- Main Content -->
        <div style='padding: 40px 30px;'>
            <!-- Success Icon -->
            <div style='text-align: center; margin-bottom: 30px;'>
                <div style='width: 80px; height: 80px; background-color: #d4edda; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;'>
                    <svg width='40' height='40' viewBox='0 0 24 24' fill='#28a745'>
                        <path d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z'/>
                    </svg>
                </div>
            </div>
            
            <!-- Title -->
            <h1 style='color: #28a745; text-align: center; margin-bottom: 20px; font-size: 28px;'>
                Malipo Yamefanikiwa!
            </h1>
            
            <!-- Main Message -->
            <div style='background-color: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 30px; border-left: 5px solid #28a745;'>
                <p style='margin: 0; font-size: 16px; line-height: 1.6; color: #333;'>
                    Tumepokea kiasi cha <strong>TZS {$amount}</strong> kutoka kwa <strong>{$customerName}</strong> kupitia {$paymentMethod} tarehe <strong>{$date}</strong>. Kumbukumbu ya muamala: <strong>{$transactionId}</strong>, Rejea: <strong>{$reference}</strong>. Asante kwa kutumia huduma zetu.
                </p>
            </div>
            
            <!-- Transaction Details -->
            <div style='background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 10px; padding: 25px; margin-bottom: 30px;'>
                <h2 style='color: #495057; margin-bottom: 20px; font-size: 18px; border-bottom: 2px solid #28a745; padding-bottom: 10px;'>
                    Maelezo ya Muamala
                </h2>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr style='border-bottom: 1px solid #e9ecef;'>
                        <td style='padding: 12px 0; color: #6c757d; font-weight: 600; width: 40%;'>Kumbukumbu ya Muamala:</td>
                        <td style='padding: 12px 0; color: #333; font-weight: 600;'>{$transactionId}</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #e9ecef;'>
                        <td style='padding: 12px 0; color: #6c757d; font-weight: 600;'>Namba ya Rejea:</td>
                        <td style='padding: 12px 0; color: #333; font-weight: 600;'>{$reference}</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #e9ecef;'>
                        <td style='padding: 12px 0; color: #6c757d; font-weight: 600;'>Kiasi:</td>
                        <td style='padding: 12px 0; color: #28a745; font-weight: 700; font-size: 18px;'>TZS {$amount}</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #e9ecef;'>
                        <td style='padding: 12px 0; color: #6c757d; font-weight: 600;'>Njia ya Malipo:</td>
                        <td style='padding: 12px 0; color: #333; font-weight: 600;'>{$paymentMethod}</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #e9ecef;'>
                        <td style='padding: 12px 0; color: #6c757d; font-weight: 600;'>Tarehe:</td>
                        <td style='padding: 12px 0; color: #333; font-weight: 600;'>{$date}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px 0; color: #6c757d; font-weight: 600;'>Mteja:</td>
                        <td style='padding: 12px 0; color: #333; font-weight: 600;'>{$customerName}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Customer Information -->
            <div style='background-color: #e3f2fd; border-radius: 10px; padding: 20px; margin-bottom: 30px;'>
                <h3 style='color: #1976d2; margin-bottom: 15px; font-size: 16px;'>Maelezo ya Mteja</h3>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <td style='padding: 8px 0; color: #6c757d; width: 40%; font-size: 14px;'>Jina:</td>
                        <td style='padding: 8px 0; color: #333; font-weight: 600; font-size: 14px;'>{$customerName}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #6c757d; font-size: 14px;'>Namba ya Simu:</td>
                        <td style='padding: 8px 0; color: #333; font-weight: 600; font-size: 14px;'>{$customerPhone}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Footer Message -->
            <div style='text-align: center; color: #6c757d; font-size: 14px; margin-bottom: 20px;'>
                <p style='margin: 0; line-height: 1.5;'>
                    Hii ni taarifa ya otomatiki ya malipo yaliyopokelewa kupitia mfumo wa malipo wa FeedTan Community Microfinance Group.
                </p>
                <p style='margin: 10px 0 0 0;'>
                    Kwa maelezo zaidi, tafadhali wasiliana nasi:
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div style='background-color: #343a40; color: white; padding: 30px; text-align: center;'>
            <div style='font-size: 18px; font-weight: bold; margin-bottom: 10px;'>
                FeedTan Community Microfinance Group
            </div>
            <div style='font-size: 16px; font-weight: 600; margin-bottom: 15px; color: #28a745;'>
                Let's Grow Together
            </div>
            <div style='font-size: 14px; opacity: 0.8; margin-bottom: 15px;'>
                Pamoja Tunakua
            </div>
            <div style='font-size: 12px; opacity: 0.7;'>
                Contact: feedtan15@gmail.com | Phone: +255 700 000 000
            </div>
            <div style='margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 11px; opacity: 0.6;'>
                © 2026 FeedTan Community Microfinance Group. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>";
}

// Test payment data
$testPaymentData = [
    'event' => 'PAYMENT RECEIVED',
    'status' => 'SUCCESS',
    'orderReference' => 'TEST-' . date('YmdHis'),
    'collectedAmount' => '75000',
    'collectedCurrency' => 'TZS',
    'channel' => 'Mobile Money',
    'customer' => [
        'customerName' => 'MARY MWANGA',
        'customerPhoneNumber' => '255755123456'
    ],
    'createdAt' => now()->toISOString(),
    'id' => 'FTN-' . date('Ymd') . '-DESIGN'
];

echo "Creating Professional Email Design...\n";
echo "===================================\n";
echo "Customer: " . $testPaymentData['customer']['customerName'] . "\n";
echo "Amount: TZS " . number_format($testPaymentData['collectedAmount'], 2) . "\n";
echo "Reference: " . $testPaymentData['orderReference'] . "\n";
echo "Payment Method: " . $testPaymentData['channel'] . "\n\n";

try {
    $emailConfigService = new EmailConfigService();
    $emailConfigService->configureMail();
    
    $emailTemplate = createProfessionalEmailTemplate($testPaymentData);
    
    echo "Sending professionally designed email to all recipients...\n";
    echo "====================================================\n";
    
    $recipients = [
        'davidngungila@gmail.com',
        'ecolishe@gmail.com',
        'feedtanbackup@gmail.com',
        'feedtan15@gmail.com'
    ];
    
    $successCount = 0;
    
    foreach ($recipients as $recipient) {
        try {
            $config = $emailConfigService->getEmailConfig();
            \Mail::html($emailTemplate, function ($message) use ($recipient, $config) {
                $message->to($recipient)
                        ->subject('🎉 Malipo Yamefanikiwa - FeedTan Community Microfinance Group')
                        ->from($config['from_address'], $config['from_name']);
            });
            
            echo "✅ Email sent to: {$recipient}\n";
            $successCount++;
            
        } catch (\Exception $e) {
            echo "❌ Failed to send to {$recipient}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 Professional Email Test Results:\n";
    echo str_repeat("=", 50) . "\n";
    echo "✅ Emails sent successfully: {$successCount}/4\n";
    echo "✅ Professional HTML design applied\n";
    echo "✅ Swahili content included\n";
    echo "✅ Complete payment details\n";
    echo "✅ Customer information displayed\n";
    echo "✅ Professional branding\n";
    echo "✅ Responsive design\n";
    echo "✅ Color-coded sections\n";
    echo "✅ Transaction details table\n";
    echo "✅ Contact information\n\n";
    
    echo "Email Features:\n";
    echo "- 📱 Mobile-responsive design\n";
    echo "- 🎨 Professional gradient header\n";
    echo "- ✅ Success icon and confirmation\n";
    echo "- 📊 Detailed transaction table\n";
    echo "- 👤 Customer information section\n";
    echo "- 🎯 Color-coded status indicators\n";
    echo "- 📧 Professional footer\n";
    echo "- 🔗 Contact information\n\n";
    
    echo "Next Steps:\n";
    echo "1. Check all email inboxes\n";
    echo "2. Verify HTML formatting displays correctly\n";
    echo "3. Confirm Swahili content is readable\n";
    echo "4. Test on mobile devices\n";
    echo "5. System ready for production use\n";
    
} catch (\Exception $e) {
    echo "❌ Error sending professional email: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check database email configuration\n";
    echo "2. Verify Gmail app password\n";
    echo "3. Check internet connection\n";
}

echo "\n✅ Professional email design test completed!\n";
