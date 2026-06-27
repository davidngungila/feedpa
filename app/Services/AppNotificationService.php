<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Payout;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AppNotificationService
{
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

        $this->emailPayoutOfficers($subject, $html, [
            'payout_id' => $payout->id,
            'order_reference' => $payout->order_reference,
            'purpose' => $purpose,
        ]);
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
        $detailRows = '';

        foreach ($details as $label => $value) {
            $safeLabel = e((string) $label);
            $safeValue = e((string) $value);
            $detailRows .= "<tr><td style=\"padding:13px 16px;border-bottom:1px solid #e8f1ec;color:#64748b;font-size:13px;font-weight:700;width:220px;\">{$safeLabel}</td><td style=\"padding:13px 16px;border-bottom:1px solid #e8f1ec;color:#0f172a;font-size:14px;font-weight:600;\">{$safeValue}</td></tr>";
        }

        $safeTitle = e($title);
        $safeSubject = e($subject);
        $safeDescription = e($description);
        $accent = str_contains(strtolower($subject), 'otp') ? '#7c3aed' : '#059669';
        $eyebrow = str_contains(strtolower($subject), 'otp') ? 'Payout Verification' : 'Payout Completion';
        $button = $actionUrl
            ? '<div style="margin-top:28px;text-align:center;"><a href="' . e($actionUrl) . '" style="display:inline-block;padding:14px 22px;border-radius:12px;background:' . $accent . ';color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;">Open Payout Details</a></div>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeSubject}</title>
</head>
<body style="margin:0;padding:24px;background:#f4f7f5;font-family:Segoe UI,Arial,sans-serif;color:#0f172a;">
    <div style="max-width:760px;margin:0 auto;background:#ffffff;border:1px solid #dcebe3;border-radius:18px;overflow:hidden;box-shadow:0 18px 44px rgba(15,23,42,0.08);">
        <div style="background:linear-gradient(135deg, {$accent} 0%, #0f172a 100%);color:#ffffff;padding:28px 32px;">
            <div style="font-size:12px;letter-spacing:0.18em;text-transform:uppercase;opacity:0.86;">{$eyebrow}</div>
            <h1 style="margin:12px 0 0;font-size:27px;line-height:1.28;">{$safeTitle}</h1>
            <p style="margin:12px 0 0;max-width:560px;color:rgba(255,255,255,0.84);font-size:14px;line-height:1.7;">{$safeDescription}</p>
        </div>
        <div style="padding:32px;">
            <div style="margin-bottom:20px;padding:18px 20px;border-radius:14px;background:#f8fcfa;border:1px solid #e6f2ec;color:#475569;font-size:14px;line-height:1.7;">
                This message was generated for authorized payout officers. Please review the workflow details below and take any required action promptly.
            </div>
            <table style="width:100%;border-collapse:collapse;background:#ffffff;border:1px solid #e6f2ec;border-radius:14px;overflow:hidden;">
                {$detailRows}
            </table>
            {$button}
        </div>
        <div style="padding:0 32px 28px;color:#64748b;font-size:12px;">
            FeedTan workflow email for authorized internal recipients.
        </div>
    </div>
</body>
</html>
HTML;
    }
}
