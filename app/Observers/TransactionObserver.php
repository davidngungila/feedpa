<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\SystemSetting;
use App\Services\EmailConfigService;
use App\Services\MessagingServiceAPI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransactionObserver
{
    protected $messagingService;

    public function __construct(MessagingServiceAPI $messagingService)
    {
        $this->messagingService = $messagingService;
    }

    public function created(Transaction $transaction)
    {
        $this->trySendEmail($transaction);
        $this->trySendSMS($transaction);
    }

    public function updated(Transaction $transaction)
    {
        $this->trySendEmail($transaction);
        $this->trySendSMS($transaction);
    }

    private function trySendEmail(Transaction $transaction)
    {
        // Check if notifications are enabled
        if (!SystemSetting::get('payment_notifications_enabled', true)) {
            return;
        }

        // Check if email already sent
        if ($transaction->email_sent) {
            return;
        }

        // Check if transaction is settled (or completed)
        $status = strtolower($transaction->status ?? '');
        $allowedStatuses = ['settled', 'completed', 'success', 'successful'];
        if (!in_array($status, $allowedStatuses)) {
            Log::info('Transaction status not eligible for email alert', [
                'transaction_id' => $transaction->id,
                'status' => $status
            ]);
            return;
        }

        try {
            $this->sendTransactionAlertToOfficers($transaction);
        } catch (\Exception $e) {
            Log::error('Failed to send transaction alert: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'exception' => $e
            ]);
        }
    }

    private function trySendSMS(Transaction $transaction)
    {
        // Check if SMS already sent
        if ($transaction->sms_sent) {
            return;
        }

        // Check if transaction is settled (or completed)
        $status = strtolower($transaction->status ?? '');
        $allowedStatuses = ['settled', 'completed', 'success', 'successful'];
        if (!in_array($status, $allowedStatuses)) {
            Log::info('Transaction status not eligible for SMS alert', [
                'transaction_id' => $transaction->id,
                'status' => $status
            ]);
            return;
        }

        // Check if phone number exists
        if (!$transaction->phone) {
            Log::info('No phone number for transaction', [
                'transaction_id' => $transaction->id
            ]);
            return;
        }

        try {
            $paymentData = [
                'orderReference' => $transaction->order_reference,
                'id' => $transaction->transaction_id,
                'collectedAmount' => $transaction->collected_amount ?? $transaction->amount,
                'collectedCurrency' => $transaction->currency,
                'paymentPhoneNumber' => $transaction->phone,
                'customer' => [
                    'customerName' => $transaction->customer_name ?? $transaction->payer_name,
                ],
                'customer_name' => $transaction->customer_name ?? $transaction->payer_name,
                'payer_name' => $transaction->payer_name,
                'createdAt' => $transaction->created_at,
            ];

            $this->messagingService->sendPaymentConfirmation($transaction->phone, $paymentData);

            $transaction->update([
                'sms_sent' => true,
                'sms_message' => $this->messagingService->buildPaymentConfirmationMessage($paymentData),
                'sms_sent_at' => now(),
                'sms_error' => null,
            ]);

            Log::info('SMS confirmation sent', [
                'transaction_id' => $transaction->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send SMS confirmation: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'exception' => $e
            ]);

            $transaction->update([
                'sms_sent' => false,
                'sms_error' => $e->getMessage(),
            ]);
        }
    }

    private function sendTransactionAlertToOfficers(Transaction $transaction)
    {
        // Get officer users (users who can log in - adjust query as needed)
        $officers = \App\Models\User::whereNotNull('email')->get();
        
        if ($officers->isEmpty()) {
            Log::info('No officer users found to send transaction alerts');
            return;
        }
        
        // Configure email from database
        $emailConfigService = new EmailConfigService();
        $emailConfigService->configureMail();
        
        // Prepare admin email and CC list
        $adminEmail = 'feedtan15@gmail.com';
        $ccEmails = [];
        $recipients = [$adminEmail];
        foreach ($officers as $officer) {
            if (strtolower($officer->email) !== strtolower($adminEmail)) {
                $ccEmails[] = $officer->email;
                $recipients[] = $officer->email;
            }
        }
        
        // Build email template
        $emailTemplate = $this->buildTransactionEmailTemplate($transaction, (object)['name' => 'Officer']);
        
        try {
            Mail::html($emailTemplate['html'], function ($message) use ($emailTemplate, $adminEmail, $ccEmails, $emailConfigService) {
                $config = $emailConfigService->getEmailConfig();
                $message->to($adminEmail, 'FeedTan Admin')
                        ->subject($emailTemplate['subject'])
                        ->from($config['from_address'], $config['from_name']);
                
                if (!empty($ccEmails)) {
                    $message->cc($ccEmails);
                }
            });
            
            // Update transaction email tracking
            $transaction->update([
                'email_sent' => true,
                'email_message' => implode(', ', $recipients),
                'email_sent_at' => now(),
                'email_error' => null,
            ]);
            
            Log::info('Transaction alert sent', [
                'to' => $adminEmail,
                'cc' => $ccEmails,
                'transaction_id' => $transaction->id
            ]);
        } catch (\Exception $e) {
            // Update transaction email tracking with error
            $transaction->update([
                'email_sent' => false,
                'email_error' => $e->getMessage(),
            ]);
            
            Log::error('Failed to send transaction alert', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ]);
        }
    }

    private function buildTransactionEmailTemplate(Transaction $transaction, $officer): array
    {
        $subject = "🔔 Arifa ya Malipo Mapya - {$transaction->order_reference}";
        
        $memberName = $transaction->customer_name ?? 'Haujulikani';
        $actualPayer = $transaction->payer_name ?? $memberName;
        $phone = $transaction->phone ?? 'Haiyupo';
        $amount = number_format($transaction->collected_amount ?? $transaction->amount ?? 0, 0);
        $currency = $transaction->currency ?? 'TZS';
        $status = $transaction->status ?? 'HAIJUIKANI';
        $reference = $transaction->order_reference ?? 'Haiyupo';
        $transactionId = $transaction->transaction_id ?? 'Haiyupo';
        $paymentMethod = $transaction->payment_method ?? 'Haujulikani';
        $date = $transaction->created_at ? $transaction->created_at->format('d M, Y H:i:s') : now()->format('d M, Y H:i:s');
        $description = $transaction->description ?? $transaction->resolved_description ?? 'Malipo yamepokelewa';
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
    <style>
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background: #f3f4f6;
        }
        .container {
            max-width: 650px;
            margin: 30px auto;
            padding: 0;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 16px 16px 0 0;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .status-badge {
            display: inline-block;
            padding: 10px 24px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-settled, .status-completed {
            background-color: rgba(255,255,255,0.2);
            border: 2px solid white;
        }
        .content {
            padding: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 3px solid #10b981;
        }
        .section-title:first-child {
            margin-top: 0;
        }
        .details-grid {
            display: grid;
            gap: 15px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 16px;
            background: #f9fafb;
            border-radius: 10px;
        }
        .detail-label {
            font-weight: 600;
            color: #4b5563;
            font-size: 14px;
        }
        .detail-value {
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
        }
        .alert {
            background: #fef3c7;
            border-left: 5px solid #f59e0b;
            padding: 20px;
            border-radius: 0 12px 12px 0;
            margin: 25px 0;
        }
        .alert strong {
            color: #92400e;
        }
        .button {
            display: block;
            width: 100%;
            text-align: center;
            padding: 16px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            margin: 25px 0;
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
        }
        .footer {
            text-align: center;
            padding: 25px;
            background: #f9fafb;
            color: #6b7280;
            font-size: 13px;
            border-radius: 0 0 16px 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Malipo Mapya Yamepokelewa!</h1>
            <div class="status-badge status-{$this->getCssStatus($status)}">{$status}</div>
        </div>
        
        <div class="content">
            <p style="font-size: 16px; color: #374151;">Mambo vema Afisa,</p>
            <p style="font-size: 16px; color: #374151;">Malipo mapya yamefanikiwa. Tafadhali ingia kwenye mfumo ili kurekodi muamala huu kwenye rekodi zetu.</p>
            
            <div class="section-title">📊 Maelezo ya Malipo</div>
            <div class="details-grid">
                <div class="detail-row">
                    <span class="detail-label">Rejea</span>
                    <span class="detail-value">{$reference}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Kiasi</span>
                    <span class="detail-value">{$currency} {$amount}</span>
                </div>
            </div>
            
            <div class="section-title">👤 Maelezo ya Mwanachama</div>
            <div class="details-grid">
                <div class="detail-row">
                    <span class="detail-label">Jina la Mwanachama</span>
                    <span class="detail-value">{$memberName}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Mwenyeji wa Malipo</span>
                    <span class="detail-value">{$actualPayer}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Simu</span>
                    <span class="detail-value">{$phone}</span>
                </div>
            </div>
            
            <div class="section-title">📝 Maelezo ya Muamala</div>
            <div class="details-grid">
                <div class="detail-row">
                    <span class="detail-label">ID ya Muamala</span>
                    <span class="detail-value">{$transactionId}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Njia ya Malipo</span>
                    <span class="detail-value">{$paymentMethod}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tarehe & Muda</span>
                    <span class="detail-value">{$date}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Nia / Maelezo</span>
                    <span class="detail-value">{$description}</span>
                </div>
            </div>
            
            <div class="alert">
                <strong>⚠️ Hatua Inahitajika:</strong> Tafadhali ingia kwenye mfumo ili kurekodi muamala huu wa malipo kwenye rekodi zetu.
            </div>
            
            <a href="{$this->getLoginUrl()}" class="button">🔑 Ingia kwenye Mfumo</a>
        </div>
        
        <div class="footer">
            <p><strong>FeedTan Community Microfinance Group</strong><br>
            "Tufanye Kazi Pamoja"</p>
        </div>
    </div>
</body>
</html>
HTML;

        return ['html' => $html, 'subject' => $subject];
    }

    private function getCssStatus(string $status): string
    {
        $statusLower = strtolower($status);
        if (str_contains($statusLower, 'complete')) return 'completed';
        if (str_contains($statusLower, 'fail')) return 'failed';
        return 'pending';
    }

    private function getLoginUrl(): string
    {
        return 'https://pay.feedtancmg.org/login';
    }
}
