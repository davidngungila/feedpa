<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EcommercePaymentSyncService
{
    private const PAID_STATUSES = ['SUCCESS', 'SETTLED'];

    public function syncIfEligible(Transaction $transaction): void
    {
        if ($transaction->type !== 'ecommerce_payment') {
            return;
        }

        $callbackData = is_array($transaction->callback_data) ? $transaction->callback_data : [];
        $callbackUrl = $callbackData['callback_url'] ?? null;

        if (!$callbackUrl || !$this->isPaidStatus($transaction->status)) {
            return;
        }

        $syncState = is_array($callbackData['commerce_sync'] ?? null)
            ? $callbackData['commerce_sync']
            : [];

        if (($syncState['last_success_status'] ?? null) === strtoupper((string) $transaction->status)) {
            return;
        }

        $attempts = (int) ($syncState['attempts'] ?? 0) + 1;
        $syncState['attempts'] = $attempts;
        $syncState['last_attempted_at'] = now()->toIso8601String();
        $syncState['last_attempt_status'] = strtoupper((string) $transaction->status);

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(10)
                ->post($callbackUrl, $this->buildSyncPayload($transaction));

            $syncState['last_http_status'] = $response->status();

            if ($response->successful()) {
                $syncState['last_success_at'] = now()->toIso8601String();
                $syncState['last_success_status'] = strtoupper((string) $transaction->status);
                unset($syncState['last_error']);
            } else {
                $syncState['last_error'] = mb_substr($response->body(), 0, 1000);
            }

            $callbackData['commerce_sync'] = $syncState;
            $transaction->forceFill(['callback_data' => $callbackData])->saveQuietly();

            Log::info('E-commerce payment sync attempted', [
                'order_reference' => $transaction->order_reference,
                'callback_url' => $callbackUrl,
                'http_status' => $response->status(),
                'successful' => $response->successful(),
            ]);
        } catch (\Throwable $e) {
            $syncState['last_error'] = $e->getMessage();
            $callbackData['commerce_sync'] = $syncState;
            $transaction->forceFill(['callback_data' => $callbackData])->saveQuietly();

            Log::error('E-commerce payment sync failed', [
                'order_reference' => $transaction->order_reference,
                'callback_url' => $callbackUrl,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function buildSyncPayload(Transaction $transaction): array
    {
        $callbackData = is_array($transaction->callback_data) ? $transaction->callback_data : [];
        $status = strtoupper((string) $transaction->status);

        return [
            'success' => true,
            'event' => 'payment.updated',
            'sync_ready' => $this->isPaidStatus($status),
            'payment_recorded_in_system' => true,
            'data' => [
                'order_reference' => $transaction->order_reference,
                'transaction_id' => $transaction->transaction_id,
                'status' => $status,
                'is_paid' => $this->isPaidStatus($status),
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'phone_number' => $transaction->phone,
                'payer_name' => $transaction->payer_name,
                'email' => $transaction->email,
                'description' => $transaction->description,
                'payment_method' => $transaction->payment_method,
                'metadata' => $callbackData['metadata'] ?? null,
                'paid_at' => $this->isPaidStatus($status) && $transaction->updated_at
                    ? $transaction->updated_at->toISOString()
                    : null,
                'created_at' => $transaction->created_at?->toISOString(),
                'updated_at' => $transaction->updated_at?->toISOString(),
            ],
        ];
    }

    public function isPaidStatus(?string $status): bool
    {
        return in_array(strtoupper((string) $status), self::PAID_STATUSES, true);
    }
}
