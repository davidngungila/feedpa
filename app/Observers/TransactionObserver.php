<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\SystemSetting;
use App\Services\EmailConfigService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransactionObserver
{
    public function created(Transaction $transaction)
    {
        $this->trySendEmail($transaction);
    }

    public function updated(Transaction $transaction)
    {
        $this->trySendEmail($transaction);
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
        $subject = "🔔 New Payment Notification - {$transaction->order_reference}";
        
        $memberName = $transaction->customer_name ?? 'Unknown';
        $actualPayer = $transaction->payer_name ?? $memberName;
        $phone = $transaction->phone ?? 'N/A';
        $amount = number_format($transaction->collected_amount ?? $transaction->amount ?? 0, 2);
        $currency = $transaction->currency ?? 'TZS';
        $status = $transaction->status ?? 'UNKNOWN';
        $reference = $transaction->order_reference ?? 'N/A';
        $transactionId = $transaction->transaction_id ?? 'N/A';
        $paymentMethod = $transaction->payment_method ?? 'Unknown';
        $date = $transaction->created_at ? $transaction->created_at->format('d M, Y H:i:s') : now()->format('d M, Y H:i:s');
        $description = $transaction->description ?? $transaction->resolved_description ?? 'Payment received';
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
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
            <h1>🔔 New Payment Received!</h1>
            <div class="status-badge status-{$this->getCssStatus($status)}">{$status}</div>
        </div>
        
        <div class="content">
            <p style="font-size: 16px; color: #374151;">Hi Officer,</p>
            <p style="font-size: 16px; color: #374151;">A new payment has been successfully made. Please login to record this transaction in the system.</p>
            
            <div class="section-title">📊 Payment Details</div>
            <div class="details-grid">
                <div class="detail-row">
                    <span class="detail-label">Reference</span>
                    <span class="detail-value">{$reference}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount</span>
                    <span class="detail-value">{$currency} {$amount}</span>
                </div>
            </div>
            
            <div class="section-title">👤 Member Information</div>
            <div class="details-grid">
                <div class="detail-row">
                    <span class="detail-label">Member Name</span>
                    <span class="detail-value">{$memberName}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Actual Payer</span>
                    <span class="detail-value">{$actualPayer}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">{$phone}</span>
                </div>
            </div>
            
            <div class="section-title">📝 Transaction Details</div>
            <div class="details-grid">
                <div class="detail-row">
                    <span class="detail-label">Transaction ID</span>
                    <span class="detail-value">{$transactionId}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value">{$paymentMethod}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date & Time</span>
                    <span class="detail-value">{$date}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Purpose / Description</span>
                    <span class="detail-value">{$description}</span>
                </div>
            </div>
            
            <div class="alert">
                <strong>⚠️ Action Required:</strong> Please login to the system to record this payment transaction in our records.
            </div>
            
            <a href="{$this->getLoginUrl()}" class="button">🔑 Login to System</a>
        </div>
        
        <div class="footer">
            <p><strong>FeedTan Community Microfinance Group</strong><br>
            "Let's Grow Together"</p>
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
