<?php

namespace App\Observers;

use App\Models\AppNotification;
use App\Models\Transaction;
use App\Models\SystemSetting;
use App\Services\EmailConfigService;
use App\Services\AppNotificationService;
use App\Services\MessagingServiceAPI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransactionObserver
{
    protected $messagingService;
    protected $notificationService;

    public function __construct(MessagingServiceAPI $messagingService, AppNotificationService $notificationService)
    {
        $this->messagingService = $messagingService;
        $this->notificationService = $notificationService;
    }

    public function created(Transaction $transaction)
    {
        $this->trySendEmail($transaction);
        $this->trySendSMS($transaction);
        $this->tryCreateInAppNotification($transaction);
    }

    public function updated(Transaction $transaction)
    {
        $this->trySendEmail($transaction);
        $this->trySendSMS($transaction);
        $this->tryCreateInAppNotification($transaction);
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
        // Restrict to only specified email recipients
        $allowedEmails = [
            'feedtan15@gmail.com',
            'elulandala@gmail.com',
            'davidngungila@gmail.com'
        ];
        
        // Configure email from database
        $emailConfigService = new EmailConfigService();
        $emailConfigService->configureMail();
        
        // Prepare admin email and CC list
        $adminEmail = 'feedtan15@gmail.com';
        $ccEmails = array_filter($allowedEmails, function($email) use ($adminEmail) {
            return strtolower($email) !== strtolower($adminEmail);
        });
        $recipients = $allowedEmails;
        
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

    private function tryCreateInAppNotification(Transaction $transaction): void
    {
        $status = strtolower($transaction->status ?? '');
        $allowedStatuses = ['settled', 'completed', 'success', 'successful'];

        if (!in_array($status, $allowedStatuses, true)) {
            return;
        }

        if (AppNotification::query()->where('event_key', 'payment:' . $transaction->id . ':success')->exists()) {
            return;
        }

        $this->notificationService->notifyPaymentOfficers($transaction);
    }

    private function buildTransactionEmailTemplate(Transaction $transaction, $officer): array
    {
        $subject = "New Transaction Received - {$transaction->order_reference}";

        $memberName = $transaction->customer_name ?? 'Unknown customer';
        $actualPayer = $transaction->payer_name ?? $memberName;
        $phone = $transaction->phone ?? 'Not provided';
        $amount = number_format($transaction->collected_amount ?? $transaction->amount ?? 0, 0);
        $currency = $transaction->currency ?? 'TZS';
        $status = strtoupper($transaction->status ?? 'UNKNOWN');
        $reference = $transaction->order_reference ?? 'N/A';
        $transactionId = $transaction->transaction_id ?? 'N/A';
        $paymentMethod = $transaction->payment_method ?? 'Not specified';
        $date = $transaction->created_at ? $transaction->created_at->format('d M, Y H:i:s') : now()->format('d M, Y H:i:s');
        $description = $transaction->description ?? $transaction->resolved_description ?? 'Payment successfully received';
        $safeDescription = e($description);
        $statusColor = match (strtolower($status)) {
            'settled', 'completed', 'success', 'successful' => '#047857',
            'failed', 'error', 'cancelled' => '#b91c1c',
            default => '#b45309',
        };

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background: #f3f7f5;
        }
        .container {
            max-width: 760px;
            margin: 28px auto;
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #d8e8df;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }
        .header {
            padding: 28px 32px;
            background: linear-gradient(135deg, #064e3b 0%, #0f766e 100%);
            color: white;
        }
        .eyebrow {
            font-size: 12px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            opacity: 0.88;
        }
        .header h1 {
            margin: 10px 0 0;
            font-size: 28px;
            line-height: 1.25;
        }
        .header p {
            margin: 10px 0 0;
            max-width: 560px;
            color: rgba(255,255,255,0.86);
            font-size: 14px;
        }
        .status-chip {
            display: inline-block;
            margin-top: 18px;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            background: rgba(255,255,255,0.16);
            border: 1px solid rgba(255,255,255,0.24);
        }
        .content {
            padding: 32px;
        }
        .intro {
            margin: 0 0 24px;
            font-size: 15px;
            color: #475569;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }
        .summary-card {
            border: 1px solid #e2efe8;
            border-radius: 14px;
            background: #f8fcfa;
            padding: 18px;
        }
        .summary-label {
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
        }
        .summary-value {
            margin-top: 8px;
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
        }
        .section {
            margin-top: 24px;
        }
        .section-title {
            margin: 0 0 14px;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border: 1px solid #e2efe8;
            border-radius: 14px;
            background: #ffffff;
        }
        .details-table td {
            padding: 13px 16px;
            border-bottom: 1px solid #edf4ef;
            vertical-align: top;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .details-label {
            width: 210px;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
        }
        .details-value {
            color: #0f172a;
            font-size: 14px;
            font-weight: 600;
        }
        .notice {
            margin-top: 24px;
            padding: 18px 20px;
            border-radius: 14px;
            background: #effaf5;
            border: 1px solid #d7efe2;
            color: #14532d;
        }
        .button-wrap {
            margin-top: 26px;
            text-align: center;
        }
        .button {
            display: inline-block;
            padding: 14px 22px;
            background: linear-gradient(135deg, #059669 0%, #0f766e 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 800;
        }
        .footer {
            padding: 22px 32px 28px;
            color: #64748b;
            font-size: 12px;
            background: #f8fbf9;
            border-top: 1px solid #e5efe9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="eyebrow">FeedTan Officer Alert</div>
            <h1>New Transaction Received</h1>
            <p>A new payment has been captured successfully and is ready for officer review, reconciliation, and internal follow-up.</p>
            <div class="status-chip" style="color:#ffffff;border-color:rgba(255,255,255,0.2);">Status: {$status}</div>
        </div>

        <div class="content">
            <p class="intro">Hello {$officer->name}, a newly received transaction requires visibility inside the platform. The key details are summarized below for immediate action.</p>

            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-label">Amount</div>
                    <div class="summary-value">{$currency} {$amount}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Reference</div>
                    <div class="summary-value" style="font-size:16px;">{$reference}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Recorded At</div>
                    <div class="summary-value" style="font-size:16px;">{$date}</div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">Transaction Summary</h2>
                <table class="details-table">
                    <tr>
                        <td class="details-label">Transaction ID</td>
                        <td class="details-value">{$transactionId}</td>
                    </tr>
                    <tr>
                        <td class="details-label">Payment Method</td>
                        <td class="details-value">{$paymentMethod}</td>
                    </tr>
                    <tr>
                        <td class="details-label">Status</td>
                        <td class="details-value" style="color: {$statusColor};">{$status}</td>
                    </tr>
                    <tr>
                        <td class="details-label">Description</td>
                        <td class="details-value">{$safeDescription}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <h2 class="section-title">Payer Information</h2>
                <table class="details-table">
                    <tr>
                        <td class="details-label">Customer Name</td>
                        <td class="details-value">{$memberName}</td>
                    </tr>
                    <tr>
                        <td class="details-label">Actual Payer</td>
                        <td class="details-value">{$actualPayer}</td>
                    </tr>
                    <tr>
                        <td class="details-label">Phone Number</td>
                        <td class="details-value">{$phone}</td>
                    </tr>
                </table>
            </div>

            <div class="notice">
                Please review this transaction in the officer portal and confirm it is properly reflected in the system records and downstream reporting.
            </div>

            <div class="button-wrap">
                <a href="{$this->getLoginUrl()}" class="button">Open Officer Portal</a>
            </div>
        </div>

        <div class="footer">
            <strong>FeedTan Community Microfinance Group</strong><br>
            Automated transaction alert for authorized officers only.
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
        return 'https://pay.feedtancmg.org/entry';
    }
}
