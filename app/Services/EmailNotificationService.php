<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class EmailNotificationService
{
    /**
     * Send payment success notification email
     */
    public function sendPaymentSuccessNotification(array $paymentData): bool
    {
        try {
            $recipients = $this->getNotificationRecipients();
            
            foreach ($recipients as $recipient) {
                $this->sendEmailToRecipient($recipient, $paymentData);
            }
            
            Log::info('Payment success emails sent successfully', [
                'payment_reference' => $paymentData['orderReference'] ?? 'N/A',
                'amount' => $paymentData['collectedAmount'] ?? 0,
                'recipients_count' => count($recipients)
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Log::error('Failed to send payment success emails', [
                'payment_data' => $paymentData,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get list of email recipients for payment notifications
     */
    private function getNotificationRecipients(): array
    {
        return [
            'davidngungila@gmail.com',
            'ecolishe@gmail.com', 
            'feedtanbackup@gmail.com',
            'feedtan15@gmail.com'
        ];
    }
    
    /**
     * Send email to individual recipient
     */
    private function sendEmailToRecipient(string $recipient, array $paymentData): void
    {
        // Configure mail system with database settings
        $emailConfig = new EmailConfigService();
        $emailConfig->configureMail();
        
        $emailTemplate = $this->buildProfessionalEmailTemplate($paymentData);
        
        Mail::html($emailTemplate['html'], function ($message) use ($recipient, $emailTemplate) {
            $config = (new EmailConfigService())->getEmailConfig();
            $message->to($recipient)
                    ->subject($emailTemplate['subject'])
                    ->from($config['from_address'], $config['from_name']);
        });
    }
    
    /**
     * Build professional email template for payment notification
     */
    private function buildProfessionalEmailTemplate(array $paymentData): array
    {
        $name = $paymentData['customer']['customerName'] ?? 'Mteja Mwenye Heshima';
        $amount = number_format($paymentData['collectedAmount'] ?? 0, 2);
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
    
    /**
     * Build email content for payment notification (legacy)
     */
    private function buildEmailContent(array $paymentData): string
    {
        $amount = number_format($paymentData['collectedAmount'] ?? 0, 2);
        $customerName = $paymentData['customer']['customerName'] ?? $paymentData['payer_name'] ?? '[Jina la Mteja]';
        $paymentMethod = $paymentData['channel'] ?? 'Mobile Money';
        $date = \Carbon\Carbon::parse($paymentData['createdAt'] ?? now())->format('d M Y, H:i');
        $transactionId = $paymentData['id'] ?? 'N/A';
        $reference = $paymentData['orderReference'] ?? $paymentData['reference'] ?? 'N/A';
        
        return "FEEDTAN COMMUNITY MICROFINANCE GROUP
TAARIFA YA MALIPO

Malipo yamefanikiwa. Tumepokea kiasi cha TZS {$amount} kutoka kwa {$customerName} kupitia {$paymentMethod} tarehe {$date}. Kumbukumbu ya muamala: {$transactionId}, Rejea: {$reference}. Asante kwa kutumia huduma zetu.

========================================
Maelezo ya Muamala:
========================================

Kumbukumbu ya Muamala: {$transactionId}
Namba ya Rejea: {$reference}
Kiasi: TZS {$amount}
Njia ya Malipo: {$paymentMethod}
Tarehe: {$date}
Mteja: {$customerName}

========================================
Hii ni taarifa ya otomatiki ya malipo yaliyopokelewa kupitia mfumo wa malipo wa FeedTan Community Microfinance Group.

Kwa maelezo zaidi, tafadhali wasiliana nasi: feedtan15@gmail.com

========================================
FeedTan Community Microfinance Group
Let's Grow Together
Pamoja Tunakua
========================================";
    }
}
