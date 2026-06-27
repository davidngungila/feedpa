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
            $this->sendEmailWithCC($paymentData);
            
            Log::info('Payment success emails sent successfully', [
                'payment_reference' => $paymentData['orderReference'] ?? 'N/A',
                'amount' => $paymentData['collectedAmount'] ?? 0
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
     * Send email with CC to specified addresses
     */
    private function sendEmailWithCC(array $paymentData): void
    {
        // Configure mail system with database settings
        $emailConfig = new EmailConfigService();
        $emailConfig->configureMail();
        
        $emailTemplate = $this->buildProfessionalEmailTemplate($paymentData);
        
        $config = (new EmailConfigService())->getEmailConfig();
        $customerEmail = $paymentData['customer']['customerEmail'] ?? 'service@feedtancmg.org';
        
        Mail::html($emailTemplate['html'], function ($message) use ($customerEmail, $emailTemplate, $config) {
            $message->to($customerEmail)
                    ->cc(['elulandala@gmail.com', 'davidngungila@gmail.com'])
                    ->subject($emailTemplate['subject'])
                    ->from($config['from_address'], $config['from_name']);
        });
    }
    
    /**
     * Build professional email template for payment notification
     */
    private function buildProfessionalEmailTemplate(array $paymentData): array
    {
        $name = $paymentData['customer']['customerName'] ?? 'Valued Customer';
        $amount = number_format($paymentData['collectedAmount'] ?? 0, 2);
        $customerPhone = $paymentData['customer']['customerPhoneNumber'] ?? '255712345678';
        $paymentMethod = $paymentData['channel'] ?? 'Mobile Money';
        $transactionId = $paymentData['id'] ?? 'FTN-' . date('Ymd') . '-PRO';
        $reference = $paymentData['orderReference'] ?? 'TEST-' . date('YmdHis');
        $date = \Carbon\Carbon::parse($paymentData['createdAt'] ?? now())->format('d M Y, H:i');
        $period = \Carbon\Carbon::parse($paymentData['createdAt'] ?? now())->format('F Y');
        $pdfLink = "https://www.feedtancmg.org/statements/{$transactionId}.pdf";
        
        $subject = "Payment Confirmation - {$name} - {$period}";
        
        $htmlBody = "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Your Payment Confirmation - FeedTan CMG</title>
    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap\" rel=\"stylesheet\">
    <style>
        body { margin: 0; padding: 0; background-color: #f3f7f5; font-family: 'Poppins', sans-serif; color: #1f2937; line-height: 1.6; }
        .email-container { max-width: 680px; margin: 30px auto; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08); border: 1px solid #d8e8df; }
        .header { background: linear-gradient(135deg, #064e3b 0%, #0f766e 100%); padding: 34px 28px; color: white; }
        .header .eyebrow { font-size: 12px; letter-spacing: 0.18em; text-transform: uppercase; opacity: 0.85; }
        .header .title { font-size: 28px; font-weight: 700; margin: 10px 0 6px; }
        .header .sub-title { font-size: 14px; opacity: 0.86; max-width: 520px; }
        .content { padding: 30px 28px; }
        .greeting { font-size: 20px; font-weight: 600; color: #0f172a; margin-bottom: 14px; }
        .lead { font-size: 14px; color: #475569; margin-bottom: 22px; }
        .highlight-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 24px; }
        .highlight-card { background: #f8fcfa; border: 1px solid #e3f1ea; border-radius: 14px; padding: 18px; }
        .highlight-label { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #64748b; }
        .highlight-value { margin-top: 8px; font-size: 20px; font-weight: 700; color: #0f172a; }
        .card { background-color: #ffffff; border: 1px solid #e5efe9; border-radius: 14px; padding: 22px; margin-bottom: 22px; }
        .card-header { display: flex; align-items: center; margin-bottom: 15px; }
        .card-header .icon { font-size: 24px; margin-right: 12px; color: #059669; }
        .card-header h4 { margin: 0; font-size: 16px; font-weight: 600; color: #2d3748; }

        .button-container { text-align: center; margin: 30px 0; }
        .download-button { display: inline-block; padding: 14px 24px; background: linear-gradient(135deg, #059669 0%, #0f766e 100%); color: white !important; font-weight: 700; border-radius: 10px; text-decoration: none; }
        
        .special-section { background-color: #f8fcfa; border: 1px solid #dceee3; padding: 24px; border-radius: 14px; margin: 25px 0; }
        .special-section h4 { margin-top: 0; font-size: 18px; display: flex; align-items: center; color: #065f46; font-weight: 700; }
        .special-section .icon { font-size: 24px; margin-right: 10px; color: #065f46; }
        .special-section p { margin: 10px 0; font-size: 14px; }
        
        .invest-button { display: inline-block; padding: 12px 22px; background-color: #065f46; color: white !important; font-weight: 700; border-radius: 10px; text-decoration: none; margin-top: 15px; }

        .signature { margin-top: 40px; font-size: 14px; color: #4a5568; }
        .footer { background-color: #f8fbf9; color: #64748b; text-align: center; padding: 18px; font-size: 12px; letter-spacing: 0.3px; border-top: 1px solid #e6efe9; }
        
        .transaction-details { background-color: #f8fcfa; border: 1px solid #dceee3; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .transaction-details h4 { color: #2f855a; margin-bottom: 15px; font-size: 16px; }
        .transaction-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .transaction-row:last-child { border-bottom: none; }
        .transaction-label { color: #4a5568; font-weight: 500; }
        .transaction-value { color: #2d3748; font-weight: 600; }
        .amount-value { color: #065f46; font-weight: 700; font-size: 16px; }
    </style>
</head>
<body>
    <div class=\"email-container\">
        <div class=\"header\">
            <div class=\"eyebrow\">Payment Confirmation</div>
            <div class=\"title\">Your payment was received successfully</div>
            <div class=\"sub-title\">Thank you for your payment. This message confirms that your transaction has been recorded and processed by FeedTan.</div>
        </div>
        <div class=\"content\">
            <p class=\"greeting\">Hello {$name},</p>
            <p class=\"lead\">We are pleased to confirm that your payment has been completed successfully. A summary of the transaction is provided below for your records.</p>

            <div class=\"highlight-grid\">
                <div class=\"highlight-card\">
                    <div class=\"highlight-label\">Amount</div>
                    <div class=\"highlight-value\">TZS {$amount}</div>
                </div>
                <div class=\"highlight-card\">
                    <div class=\"highlight-label\">Reference</div>
                    <div class=\"highlight-value\" style=\"font-size:16px;\">{$reference}</div>
                </div>
                <div class=\"highlight-card\">
                    <div class=\"highlight-label\">Paid On</div>
                    <div class=\"highlight-value\" style=\"font-size:16px;\">{$date}</div>
                </div>
            </div>

            <div class=\"card\">
                <div class=\"card-header\">
                    <span class=\"icon\">&#x2705;</span>
                    <h4>Payment Summary</h4>
                </div>
                <p style=\"font-size: 14px; color: #4a5568;\">Your payment of <strong>TZS {$amount}</strong> via <strong>{$paymentMethod}</strong> was received and recorded on <strong>{$date}</strong>.</p>
                
                <div class=\"transaction-details\">
                    <h4>&#128196; Transaction Details</h4>
                    <div class=\"transaction-row\">
                        <span class=\"transaction-label\">Transaction ID:</span>
                        <span class=\"transaction-value\">{$transactionId}</span>
                    </div>
                    <div class=\"transaction-row\">
                        <span class=\"transaction-label\">Reference:</span>
                        <span class=\"transaction-value\">{$reference}</span>
                    </div>
                    <div class=\"transaction-row\">
                        <span class=\"transaction-label\">Amount:</span>
                        <span class=\"transaction-value amount-value\">TZS {$amount}</span>
                    </div>
                    <div class=\"transaction-row\">
                        <span class=\"transaction-label\">Payment Method:</span>
                        <span class=\"transaction-value\">{$paymentMethod}</span>
                    </div>
                    <div class=\"transaction-row\">
                        <span class=\"transaction-label\">Date:</span>
                        <span class=\"transaction-value\">{$date}</span>
                    </div>
                    <div class=\"transaction-row\">
                        <span class=\"transaction-label\">Phone Number:</span>
                        <span class=\"transaction-value\">{$customerPhone}</span>
                    </div>
                </div>
                
                <div class=\"button-container\">
                    <a href=\"{$pdfLink}\" class=\"download-button\" target=\"_blank\">Download Payment Receipt</a>
                </div>
            </div>

            <div class=\"savings-tips\" style=\"margin-top: 25px; background-color: #f7fafc; padding: 15px; border-left: 5px solid #38a169; border-radius: 10px;\">
                <h4 style=\"color: #2f855a; margin-bottom: 10px;\">&#128184; Smart Savings Tips</h4>
                <ul style=\"font-size: 14px; color: #4a5568; line-height: 1.6; margin-left: 20px;\">
                    <li>&#128161; Set aside at least <strong>10%</strong> of your income every month.</li>
                    <li>&#128197; Pay yourself first by saving before spending.</li>
                    <li>&#127919; Create clear financial goals for business, education, housing, or investments.</li>
                    <li>&#128201; Limit unnecessary debt and track your cash flow consistently.</li>
                    <li>&#127793; Explore disciplined long-term investment opportunities that match your goals.</li>
                </ul>
                <p style=\"font-size: 13px; color: #2f855a; font-style: italic; margin-top: 10px;\">
                    \"Long-term financial strength begins with disciplined saving.\" &#128181;
                </p>
            </div>

            <div class=\"special-section\">
                <h4><span class=\"icon\">&#128200;</span>Explore Growth Opportunities</h4>
                <p>If you are interested in community-focused investment opportunities, our team can help you learn more about available programs and participation options.</p>
                <a href=\"https://www.feedtancmg.org/invest\" class=\"invest-button\" target=\"_blank\">Learn More</a>
            </div>
            
            <p style=\"font-size: 14px; color: #4a5568;\">If you have any questions about this payment, please contact our support team and keep this email for your records.</p>

            <div class=\"signature\">
                <p>Kind regards,<br><strong>FeedTan CMG Team</strong></p>
                <p style=\"font-weight: 600; color: #006400;\">Let's Grow Together! &#x1F91D;</p>
            </div>
        </div>
        <div class=\"footer\">
            FeedTan CMG Payment System
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
        $customerName = $paymentData['customer']['customerName'] ?? $paymentData['payer_name'] ?? 'Customer';
        $paymentMethod = $paymentData['channel'] ?? 'Mobile Money';
        $date = \Carbon\Carbon::parse($paymentData['createdAt'] ?? now())->format('d M Y, H:i');
        $transactionId = $paymentData['id'] ?? 'N/A';
        $reference = $paymentData['orderReference'] ?? $paymentData['reference'] ?? 'N/A';
        
        return "FEEDTAN COMMUNITY MICROFINANCE GROUP
PAYMENT CONFIRMATION

Your payment was completed successfully. We received TZS {$amount} from {$customerName} via {$paymentMethod} on {$date}. Transaction ID: {$transactionId}. Reference: {$reference}.

========================================
Transaction Details
========================================

Transaction ID: {$transactionId}
Reference: {$reference}
Amount: TZS {$amount}
Payment Method: {$paymentMethod}
Date: {$date}
Customer: {$customerName}

========================================
This is an automated payment confirmation generated by the FeedTan Community Microfinance Group payment system.

For support, please contact: service@feedtancmg.org

========================================
FeedTan Community Microfinance Group
Let's Grow Together
========================================";
    }
}
