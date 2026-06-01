<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\EmailConfigService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransactionObserver
{
    public function created(Transaction $transaction)
    {
        // Only send email if transaction is completed (or any status you want)
        // Or send for all new transactions
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
        
        // Build email template
        $emailTemplate = $this->buildTransactionEmailTemplate($transaction);
        
        // Send emails to all officers
        foreach ($officers as $officer) {
            try {
                Mail::html($emailTemplate['html'], function ($message) use ($emailTemplate, $officer, $emailConfigService) {
                    $config = $emailConfigService->getEmailConfig();
                    $message->to($officer->email, $officer->name)
                            ->subject($emailTemplate['subject'])
                            ->from($config['from_address'], $config['from_name']);
                });
                
                Log::info('Transaction alert sent to officer', [
                    'officer_email' => $officer->email,
                    'transaction_id' => $transaction->id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send alert to officer', [
                    'officer_email' => $officer->email,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function buildTransactionEmailTemplate(Transaction $transaction): array
    {
        $subject = "🔔 New Payment Notification - {$transaction->order_reference}";
        
        $customerName = $transaction->customer_name ?? $transaction->payer_name ?? 'Unknown';
        $customerPhone = $transaction->phone ?? 'N/A';
        $amount = $transaction->collected_amount ?? $transaction->amount ?? 0;
        $currency = $transaction->currency ?? 'TZS';
        $status = $transaction->status ?? 'UNKNOWN';
        $reference = $transaction->order_reference ?? 'N/A';
        $date = $transaction->created_at ? $transaction->created_at->toDateTimeString() : now()->toDateTimeString();
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
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #10b981;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #10b981;
            margin: 0;
            font-size: 24px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
        }
        .status-completed {
            background-color: #d1fae5;
            color: #059669;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #d97706;
        }
        .status-failed {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .details {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-label {
            font-weight: 600;
            color: #4b5563;
        }
        .detail-value {
            color: #1f2937;
            font-weight: 500;
        }
        .alert {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 0 8px 8px 0;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #10b981;
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 New Payment Received!</h1>
            <div class="status-badge status-{$this->getCssStatus($status)}">{$status}</div>
        </div>
        
        <p>Hi Officer,</p>
        <p>A new payment has been successfully made. Please login to record this transaction in the system.</p>
        
        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Order Reference:</span>
                <span class="detail-value">{$reference}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Customer Name:</span>
                <span class="detail-value">{$customerName}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone Number:</span>
                <span class="detail-value">{$customerPhone}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount Paid:</span>
                <span class="detail-value">{$amount} {$currency}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date & Time:</span>
                <span class="detail-value">{$date}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Description:</span>
                <span class="detail-value">{$description}</span>
            </div>
        </div>
        
        <div class="alert">
            <strong>⚠️ Action Required:</strong> Please login to the system to record this payment transaction in our records.
        </div>
        
        <p style="text-align: center;">
            <a href="{$this->getLoginUrl()}" class="button">🔑 Login to System</a>
        </p>
        
        <div class="footer">
            <p>FeedTan Community Microfinance Group<br>
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
        return config('app.url') . '/login';
    }
}
