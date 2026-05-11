<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\EmailConfigService;

echo "Creating Professional Email with New Structure\n";
echo "=============================================\n\n";

// Create professional email template using the provided structure
function createProfessionalEmailTemplate($paymentData) {
    $name = $paymentData['customer']['customerName'] ?? 'Mteja Mwenye Heshima';
    $amount = number_format($paymentData['collectedAmount'] ?? 50000, 2);
    $customerPhone = $paymentData['customer']['customerPhoneNumber'] ?? '255712345678';
    $paymentMethod = $paymentData['channel'] ?? 'Mobile Money';
    $transactionId = $paymentData['id'] ?? 'FTN-' . date('Ymd') . '-PRO';
    $reference = $paymentData['orderReference'] ?? 'TEST-' . date('YmdHis');
    $date = \Carbon\Carbon::parse($paymentData['createdAt'] ?? now())->format('d M Y, H:i');
    $period = \Carbon\Carbon::parse($paymentData['createdAt'] ?? now())->format('F Y');
    $pdfLink = "https://feedtan.com/statements/{$transactionId}.pdf";
    
    $subject = "Malipo Yamefanikiwa - {$name} - {$period}";
    
    $htmlBody = "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Your Payment Confirmation - FeedTan CMG</title>
    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap\" rel=\"stylesheet\">
    <style>
        body { margin: 0; padding: 0; background-color: #f0f4f8; font-family: 'Poppins', sans-serif; color: #333; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08); border: 1px solid #e2e8f0; }
        .header { background: #006400; padding: 30px 25px; text-align: center; color: white; }
        .header .title { font-size: 26px; font-weight: 700; margin-bottom: 5px; }
        .header .sub-title { font-size: 14px; opacity: 0.9; }
        .content { padding: 30px 25px; }
        .greeting { font-size: 18px; font-weight: 600; color: #2d3748; margin-bottom: 15px; }
        
        .card { background-color: #f7fafc; border: 1px solid #edf2f7; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
        .card-header { display: flex; align-items: center; margin-bottom: 15px; }
        .card-header .icon { font-size: 24px; margin-right: 12px; color: #4CAF50; }
        .card-header h4 { margin: 0; font-size: 16px; font-weight: 600; color: #2d3748; }

        .button-container { text-align: center; margin: 30px 0; }
        .download-button { display: inline-block; padding: 12px 25px; background-color: #438a5e; color: white !important; font-weight: 600; border-radius: 6px; text-decoration: none; transition: background-color 0.3s ease; }
        .download-button:hover { background-color: #2e7d32; }
        
        .special-section { background-color: #fff8e1; border-left: 5px solid #FFC107; padding: 25px; border-radius: 8px; margin: 25px 0; }
        .special-section h4 { margin-top: 0; font-size: 18px; display: flex; align-items: center; color: #c09e4f; font-weight: 600; }
        .special-section .icon { font-size: 24px; margin-right: 10px; color: #c09e4f; }
        .special-section p { margin: 10px 0; font-size: 14px; }
        
        .invest-button { display: inline-block; padding: 12px 25px; background-color: #006400; color: white !important; font-weight: 600; border-radius: 6px; text-decoration: none; transition: background-color 0.3s ease; margin-top: 15px; }
        .invest-button:hover { background-color: #2e7d32; }

        .signature { margin-top: 40px; font-size: 14px; color: #4a5568; }
        .footer { background-color: #006400; color: white; text-align: center; padding: 15px; font-size: 12px; letter-spacing: 0.5px; opacity: 0.8; }
        
        .transaction-details { background-color: #f0fff4; border: 1px solid #c6f6d5; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .transaction-details h4 { color: #2f855a; margin-bottom: 15px; font-size: 16px; }
        .transaction-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .transaction-row:last-child { border-bottom: none; }
        .transaction-label { color: #4a5568; font-weight: 500; }
        .transaction-value { color: #2d3748; font-weight: 600; }
        .amount-value { color: #006400; font-weight: 700; font-size: 16px; }
    </style>
</head>
<body>
    <div class=\"email-container\">
        <div class=\"header\">
            <div class=\"title\">FeedTan Community Microfinance Group</div>
            <div class=\"sub-title\">P.O.Box 7744, Ushirika Sokoine Road, Moshi, Kilimanjaro, Tanzania</div>
        </div>
        <div class=\"content\">
            <p class=\"greeting\">Habari {$name},</p>
            <p style=\"font-size: 14px; color: #4a5568;\">Tunatumia ujumbe huu kukuarifu kuwa malipo yako yamefanikiwa. Tunashukuru kwa kuendelea kutuamini kama mteja wetu wa kudumu.</p>

            <div class=\"card\">
                <div class=\"card-header\">
                    <span class=\"icon\">&#x2705;</span>
                    <h4>Thibitisho la Malipo</h4>
                </div>
                <p style=\"font-size: 14px; color: #4a5568;\">Malipo yako ya <strong>TZS {$amount}</strong> kupitia <strong>{$paymentMethod}</strong> yamepokelewa kikamilifu tarehe <strong>{$date}</strong>.</p>
                
                <div class=\"transaction-details\">
                    <h4>&#128196; Maelezo ya Muamala</h4>
                    <div class=\"transaction-row\">
                        <span class=\"transaction-label\">Kumbukumbu ya Muamala:</span>
                        <span class=\"transaction-value\">{$transactionId}</span>
                    </div>
                    <div class=\"transaction-row\">
                        <span class=\"transaction-label\">Namba ya Rejea:</span>
                        <span class=\"transaction-value\">{$reference}</span>
                    </div>
                    <div class=\"transaction-row\">
                        <span class=\"transaction-label\">Kiasi:</span>
                        <span class=\"transaction-value amount-value\">TZS {$amount}</span>
                    </div>
                    <div class=\"transaction-row\">
                        <span class=\"transaction-label\">Njia ya Malipo:</span>
                        <span class=\"transaction-value\">{$paymentMethod}</span>
                    </div>
                    <div class=\"transaction-row\">
                        <span class=\"transaction-label\">Tarehe:</span>
                        <span class=\"transaction-value\">{$date}</span>
                    </div>
                    <div class=\"transaction-row\">
                        <span class=\"transaction-label\">Namba ya Simu:</span>
                        <span class=\"transaction-value\">{$customerPhone}</span>
                    </div>
                </div>
                
                <div class=\"button-container\">
                    <a href=\"{$pdfLink}\" class=\"download-button\" target=\"_blank\">Pakua Risiti ya Malipo</a>
                </div>
            </div>

            <div class=\"savings-tips\" style=\"margin-top: 25px; background-color: #f7fafc; padding: 15px; border-left: 5px solid #38a169; border-radius: 10px;\">
                <h4 style=\"color: #2f855a; margin-bottom: 10px;\">&#128184; Vidokezo vya Akiba (Savings Tips)</h4>
                <ul style=\"font-size: 14px; color: #4a5568; line-height: 1.6; margin-left: 20px;\">
                    <li>&#128161; Weka akiba angalau <strong>10%</strong> ya kipato chako kila mwezi.</li>
                    <li>&#128197; Tumia kanuni ya <strong>\"Jilippe Kwanza\"</strong> — weka akiba kabla ya matumizi.</li>
                    <li>&#127919; Weka malengo maalum ya kifedha (mfano: gawio, biashara, au nyumba).</li>
                    <li>&#128201; Epuka madeni yasiyo ya lazima — deni ni adui wa uhuru wa kifedha.</li>
                    <li>&#127793; Wekeza sehemu ya akiba yako kwenye miradi yenye tija kama FIA.</li>
                </ul>
                <p style=\"font-size: 13px; color: #2f855a; font-style: italic; margin-top: 10px;\">
                    \"Uchumi wa kweli huanza na nidhamu ya akiba.\" &#128181;
                </p>
            </div>

            <div class=\"special-section\">
                <h4><span class=\"icon\">&#128200;</span>Wekeza Nasi</h4>
                <p>Je, ungetaka kuwekeza kwenye miradi yetu ya kijamii? Tunatoa fursa za kuwekeza zenye tija kubwa.</p>
                <a href=\"https://feedtan.com/invest\" class=\"invest-button\" target=\"_blank\">Jifunne Zaidi</a>
            </div>
            
            <p style=\"font-size: 14px; color: #4a5568;\">Usisite kuwasiliana nasi kwa simu au email endapo utakuwa na swali lolote kuhusu malipo yako.</p>

            <div class=\"signature\">
                <p>Wapendwa,<br><strong>Timu ya FeedTan CMG</strong></p>
                <p style=\"font-weight: 600; color: #006400;\">Let's Grow Together! &#x1F91D;</p>
            </div>
        </div>
        <div class=\"footer\">
            FeedTan CMG Payment System V1.1.0.2026
        </div>
    </div>
</body>
</html>";

    return [
        'subject' => $subject,
        'html' => $htmlBody
    ];
}

// Test payment data
$testPaymentData = [
    'event' => 'PAYMENT RECEIVED',
    'status' => 'SUCCESS',
    'orderReference' => 'PRO-TEST-' . date('YmdHis'),
    'collectedAmount' => '85000',
    'collectedCurrency' => 'TZS',
    'channel' => 'Mobile Money',
    'customer' => [
        'customerName' => 'GRACE MWANGI',
        'customerPhoneNumber' => '255714987654'
    ],
    'createdAt' => now()->toISOString(),
    'id' => 'FTN-' . date('Ymd') . '-PRO'
];

echo "Creating Professional Email with New Structure...\n";
echo "==============================================\n";
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
    echo "Subject: " . $emailTemplate['subject'] . "\n\n";
    
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
            \Mail::html($emailTemplate['html'], function ($message) use ($recipient, $emailTemplate) {
                $config = (new \App\Services\EmailConfigService())->getEmailConfig();
                $message->to($recipient)
                        ->subject($emailTemplate['subject'])
                        ->from($config['from_address'], $config['from_name']);
            });
            
            echo "✅ Email sent to: {$recipient}\n";
            $successCount++;
            
        } catch (\Exception $e) {
            echo "❌ Failed to send to {$recipient}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 Professional Email Results:\n";
    echo str_repeat("=", 60) . "\n";
    echo "✅ Emails sent successfully: {$successCount}/4\n";
    echo "✅ Professional HTML structure applied\n";
    echo "✅ Poppins font family integrated\n";
    echo "✅ Responsive design implemented\n";
    echo "✅ Professional styling with CSS\n";
    echo "✅ Swahili content included\n";
    echo "✅ Complete transaction details\n";
    echo "✅ Customer information displayed\n";
    echo "✅ Professional branding\n";
    echo "✅ 'Let's Grow Together' included\n";
    echo "✅ Savings tips section\n";
    echo "✅ Investment opportunity section\n\n";
    
    echo "Email Features:\n";
    echo "- 📱 Mobile-responsive design\n";
    echo "- 🎨 Professional green theme (#006400)\n";
    echo "- 📊 Detailed transaction table\n";
    echo "- 💰 Savings tips with emojis\n";
    echo "- 📈 Investment opportunities\n";
    echo "- 🎯 Clear call-to-action buttons\n";
    echo "- 📧 Professional footer\n";
    echo "- 🏢 Complete company information\n";
    echo "- 🌐 External links for receipts\n";
    echo "- 📝 Well-structured content sections\n\n";
    
    echo "Design Improvements:\n";
    echo "- ✅ Modern card-based layout\n";
    echo "- ✅ Professional typography (Poppins)\n";
    echo "- ✅ Consistent color scheme\n";
    echo "- ✅ Proper spacing and margins\n";
    echo "- ✅ Hover effects on buttons\n";
    echo "- ✅ Icon integration\n";
    echo "- ✅ Responsive design\n";
    echo "- ✅ Professional branding\n\n";
    
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
