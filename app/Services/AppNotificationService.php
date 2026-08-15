<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Payout;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AppNotificationService
{
    protected MessagingServiceAPI $sms;
    protected WhatsAppService $whatsapp;

    public function __construct(MessagingServiceAPI $sms, WhatsAppService $whatsapp)
    {
        $this->sms = $sms;
        $this->whatsapp = $whatsapp;
    }

    public function notifyUsers(iterable $users, string $type, string $title, string $message, ?string $link = null, array $data = [], ?string $eventKey = null): void
    {
        foreach ($users as $user) {
            if (!$user instanceof User) {
                continue;
            }

            $attributes = [
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'data' => $data,
                'is_read' => false,
                'read_at' => null,
            ];

            if ($eventKey) {
                AppNotification::updateOrCreate(
                    ['user_id' => $user->id, 'event_key' => $eventKey],
                    $attributes
                );

                continue;
            }

            AppNotification::create(array_merge($attributes, [
                'user_id' => $user->id,
            ]));
        }
    }

    public function payoutOfficers(): Collection
    {
        return User::query()
            ->where(function ($query) {
                $query->where('can_create_payouts', true)
                    ->orWhere('is_admin', true);
            })
            ->orderBy('name')
            ->get();
    }

    public function notifyPayoutOfficers(string $type, string $title, string $message, ?string $link = null, array $data = [], ?string $eventKey = null): void
    {
        $this->notifyUsers($this->payoutOfficers(), $type, $title, $message, $link, $data, $eventKey);
    }

    public function notifyPaymentOfficers(Transaction $transaction): void
    {
        $amount = number_format((float) ($transaction->collected_amount ?? $transaction->amount ?? 0), 2);
        $payer = $transaction->customer_name ?? $transaction->payer_name ?? 'Customer';
        $message = "New payment received: {$transaction->currency} {$amount} from {$payer}. Reference {$transaction->order_reference}.";

        $this->notifyPayoutOfficers(
            'payment_success',
            'New Payment Received',
            $message,
            route('payments.history'),
            [
                'transaction_id' => $transaction->id,
                'order_reference' => $transaction->order_reference,
                'amount' => $transaction->collected_amount ?? $transaction->amount,
                'currency' => $transaction->currency,
                'payer_name' => $payer,
                'status' => $transaction->status,
            ],
            'payment:' . $transaction->id . ':success'
        );
    }

    public function sendPayoutOtpEmail(Payout $payout, string $otp, string $purpose, User $actor): void
    {
        $stageTitle = $purpose === 'payment_authorization'
            ? 'Payment Authorization OTP'
            : 'Payout Initiation OTP';

        $subject = "{$stageTitle} - {$payout->order_reference}";
        $description = $purpose === 'payment_authorization'
            ? 'This OTP is required to authorize release of the payout after approval.'
            : 'This OTP is required to verify payout initiation before approval.';

        $html = $this->buildPayoutEmailTemplate(
            $subject,
            $stageTitle,
            [
                'Reference' => $payout->order_reference,
                'Amount' => "{$payout->currency} " . number_format((float) $payout->amount, 2),
                'Recipient' => $payout->recipient_name,
                'Officer' => $actor->name,
                'Reason' => $payout->resolvedDescription() ?: 'N/A',
                'OTP Code' => $otp,
                'Expires' => now()->addMinutes(10)->format('d M Y, H:i'),
            ],
            $description,
            route('payouts.status', $payout->order_reference)
        );

        if (filled($actor->email)) {
            $this->sendHtmlEmail($actor->email, $subject, $html);
        }
    }

    public function sendPayoutSuccessEmail(Payout $payout): void
    {
        $payout->loadMissing(['initiator', 'approver', 'paymentAuthorizer']);

        $subject = "Payout Completed Successfully - {$payout->order_reference}";
        $html = $this->buildPayoutEmailTemplate(
            $subject,
            'Payout Completed Successfully',
            [
                'Reference' => $payout->order_reference,
                'Status' => $payout->status,
                'Amount' => "{$payout->currency} " . number_format((float) $payout->amount, 2),
                'Recipient' => $payout->recipient_name,
                'Initiated By' => $payout->initiator?->name ?? 'N/A',
                'Approved By' => $payout->approver?->name ?? 'N/A',
                'Authorized By' => $payout->paymentAuthorizer?->name ?? 'N/A',
                'Reason' => $payout->resolvedDescription() ?: 'N/A',
            ],
            'The payout has been processed successfully and confirmed by the provider.',
            route('payouts.status', $payout->order_reference)
        );

        $this->emailPayoutOfficers($subject, $html, [
            'payout_id' => $payout->id,
            'order_reference' => $payout->order_reference,
            'status' => $payout->status,
        ]);
    }

    /**
     * Send SMS and email to the payout beneficiary (Mobile Optional / Email Optional)
     * when the payout is initialized or processed.
     */
    public function sendBeneficiaryPayoutNotification(Payout $payout, string $event): void
    {
        $mobile = filled($payout->beneficiary_mobile) ? $payout->beneficiary_mobile : null;
        $email = filled($payout->beneficiary_email) ? $payout->beneficiary_email : null;

        if (!$mobile && !$email) {
            return;
        }

        if ($event === 'completed') {
            $smsText = $this->buildBeneficiarySmsMessage($payout, 'completed');
            $emailSubject = "Payout Completed Successfully - {$payout->order_reference}";
            $emailHtml = $this->buildBeneficiaryEmailTemplate(
                $emailSubject,
                'Payout Completed Successfully',
                'Your payout has been processed successfully and confirmed by the provider. Below are the details of the completed transaction.',
                [
                    'Amount' => "{$payout->currency} " . number_format((float) $payout->amount, 2),
                    'Reference' => $payout->order_reference,
                    'Recipient' => $payout->recipient_name,
                    'Status' => $payout->status,
                    'Date' => $payout->created_at ? $payout->created_at->format('d M Y, H:i') : now()->format('d M Y, H:i'),
                    'Description' => $payout->resolvedDescription() ?: 'N/A',
                ]
            );
        } else {
            $smsText = $this->buildBeneficiarySmsMessage($payout, 'initiated');
            $emailSubject = "Payout Initiated - {$payout->order_reference}";
            $emailHtml = $this->buildBeneficiaryEmailTemplate(
                $emailSubject,
                'Payout Initiated',
                'Your payout has been initiated and is currently being processed. We will notify you once it is completed.',
                [
                    'Amount' => "{$payout->currency} " . number_format((float) $payout->amount, 2),
                    'Reference' => $payout->order_reference,
                    'Recipient' => $payout->recipient_name,
                    'Status' => 'Initiated / Pending Processing',
                    'Date' => $payout->created_at ? $payout->created_at->format('d M Y, H:i') : now()->format('d M Y, H:i'),
                    'Description' => $payout->resolvedDescription() ?: 'N/A',
                ]
            );
        }

        if ($mobile) {
            try {
                $this->sms->sendSMS($mobile, $smsText);
            } catch (\Exception $e) {
                Log::warning('Failed to send payout beneficiary SMS', [
                    'payout_id' => $payout->id,
                    'event' => $event,
                    'mobile' => $mobile,
                    'error' => $e->getMessage(),
                ]);
            }

            if (SystemSetting::get('whatsapp_enabled', false)) {
                try {
                    $this->whatsapp->sendText($mobile, $smsText);
                } catch (\Exception $e) {
                    Log::warning('Failed to send payout beneficiary WhatsApp', [
                        'payout_id' => $payout->id,
                        'event' => $event,
                        'mobile' => $mobile,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($email) {
            $this->sendHtmlEmail($email, $emailSubject, $emailHtml);
        }
    }

    protected function buildBeneficiarySmsMessage(Payout $payout, string $event): string
    {
        $heading = $event === 'completed'
            ? 'FEEDTAN PAYOUT COMPLETED'
            : 'FEEDTAN PAYOUT INITIATED';

        $statusLine = $event === 'completed'
            ? "Status: {$payout->status}\n"
            : "Status: INITIATED / PENDING PROCESSING\n";

        return "{$heading}\n"
            . "Reference: {$payout->order_reference}\n"
            . "Amount: {$payout->amount} {$payout->currency}\n"
            . "Recipient: {$payout->recipient_name}\n"
            . $statusLine
            . "Thank you for using FEEDTAN services.";
    }

    protected function sendHtmlEmail(string $email, string $subject, string $html): void
    {
        $emailConfigService = new EmailConfigService();

        try {
            $emailConfigService->configureMail();
            $config = $emailConfigService->getEmailConfig();

            Mail::html($html, function ($message) use ($email, $subject, $config) {
                $message->to($email)
                    ->subject($subject)
                    ->from($config['from_address'], $config['from_name']);
            });

            Log::info('Payout HTML email sent', [
                'to' => $email,
                'subject' => $subject,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to email payout recipient', [
                'to' => $email,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function emailPayoutOfficers(string $subject, string $html, array $context = []): void
    {
        $recipients = $this->payoutOfficers()
            ->pluck('email')
            ->filter(fn ($email) => filled($email))
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        $emailConfigService = new EmailConfigService();

        try {
            $emailConfigService->configureMail();
            $config = $emailConfigService->getEmailConfig();
            $primaryEmail = $recipients->first();
            $ccEmails = $recipients->slice(1)->all();

            Mail::html($html, function ($message) use ($subject, $config, $primaryEmail, $ccEmails) {
                $message->to($primaryEmail)
                    ->subject($subject)
                    ->from($config['from_address'], $config['from_name']);

                if (!empty($ccEmails)) {
                    $message->cc($ccEmails);
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to email payout officers', [
                'subject' => $subject,
                'context' => $context,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function buildPayoutEmailTemplate(string $subject, string $title, array $details, string $description, ?string $actionUrl = null): string
    {
        $isOtp = str_contains(strtolower($subject), 'otp');

        $summaryKeys = array_slice(array_keys($details), 0, 3);
        $summaryCards = [];
        foreach ($summaryKeys as $key) {
            $summaryCards[] = ['label' => $key, 'value' => (string) $details[$key]];
        }

        $detailRows = [];
        foreach ($details as $label => $value) {
            $detailRows[] = ['label' => $label, 'value' => (string) $value];
        }

        $chipText = $isOtp ? 'OTP Verification Required' : 'Payout Update';

        $intro = $isOtp
            ? 'Hello, a payout workflow requires your attention. The OTP below is required to complete the current step. The key details are summarized below for immediate action.'
            : 'Hello, a payout workflow update has been recorded. The key details are summarized below for immediate action.';

        $notice = $isOtp
            ? 'This OTP is sensitive and valid for 10 minutes only. Please enter it in the payout verification screen to complete the required step and do not share this code with anyone.'
            : 'Please review this payout in the officer portal and confirm it is properly reflected in the system records and downstream reporting.';

        return $this->renderPayoutEmailHtml(
            $subject,
            'FeedTan Payout Alert',
            $title,
            $description,
            $chipText,
            $intro,
            $summaryCards,
            $detailRows,
            $notice,
            'Open Payout Details',
            $actionUrl
        );
    }

    protected function buildBeneficiaryEmailTemplate(string $subject, string $title, string $description, array $details): string
    {
        $summaryKeys = array_slice(array_keys($details), 0, 3);
        $summaryCards = [];
        foreach ($summaryKeys as $key) {
            $summaryCards[] = ['label' => $key, 'value' => (string) $details[$key]];
        }

        $detailRows = [];
        foreach ($details as $label => $value) {
            $detailRows[] = ['label' => $label, 'value' => (string) $value];
        }

        $isComplete = str_contains(strtolower($title), 'completed');

        $chipText = $isComplete ? 'Payout Completed' : 'Payout Initiated';

        $intro = $isComplete
            ? 'Your payout has been processed successfully. Below is a summary of the completed transaction.'
            : 'Your payout has been initiated and is currently being processed. Below is a summary of the transaction.';

        $notice = 'Thank you for using FeedTan Community Microfinance Group services. For any questions about this payout, please contact our support team and keep this notification for your records.';

        return $this->renderPayoutEmailHtml(
            $subject,
            'FeedTan Payout Notification',
            $title,
            $description,
            $chipText,
            $intro,
            $summaryCards,
            $detailRows,
            $notice,
            null,
            null
        );
    }

    protected function renderPayoutEmailHtml(
        string $subject,
        string $eyebrow,
        string $title,
        string $description,
        string $chipText,
        string $intro,
        array $summaryCards,
        array $detailRows,
        string $notice,
        ?string $buttonLabel = null,
        ?string $buttonUrl = null
    ): string {
        $safeSubject = e($subject);
        $safeEyebrow = e($eyebrow);
        $safeTitle = e($title);
        $safeDescription = e($description);
        $safeChip = e($chipText);
        $safeIntro = e($intro);
        $safeNotice = e($notice);

        $cards = '';
        foreach ($summaryCards as $card) {
            $label = e((string) ($card['label'] ?? ''));
            $value = e((string) ($card['value'] ?? ''));
            $cards .= "<div class=\"summary-card\"><div class=\"summary-label\">{$label}</div><div class=\"summary-value\">{$value}</div></div>";
        }

        $rows = '';
        foreach ($detailRows as $row) {
            $label = e((string) ($row['label'] ?? ''));
            $value = e((string) ($row['value'] ?? ''));
            $rows .= "<tr><td class=\"details-label\">{$label}</td><td class=\"details-value\">{$value}</td></tr>";
        }

        $button = '';
        if ($buttonLabel && $buttonUrl) {
            $button = '<div class="button-wrap"><a href="' . e($buttonUrl) . '" class="button">' . e($buttonLabel) . '</a></div>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeSubject}</title>
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
            word-break: break-all;
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
            <div class="eyebrow">{$safeEyebrow}</div>
            <h1>{$safeTitle}</h1>
            <p>{$safeDescription}</p>
            <div class="status-chip">Status: {$safeChip}</div>
        </div>

        <div class="content">
            <p class="intro">{$safeIntro}</p>

            <div class="summary-grid">
                {$cards}
            </div>

            <div class="section">
                <h2 class="section-title">Details</h2>
                <table class="details-table">
                    {$rows}
                </table>
            </div>

            <div class="notice">
                {$safeNotice}
            </div>

            {$button}
        </div>

        <div class="footer">
            <strong>FeedTan Community Microfinance Group</strong><br>
            Automated payout notification.
        </div>
    </div>
</body>
</html>
HTML;
    }
}
