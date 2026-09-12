<?php

namespace App\Services;

use App\Models\SmsProvider;

class SmsParserService
{
    /**
     * Parse SMS body and return extracted fields.
     * Returns: [provider_code, provider_id, transaction_type, amount, currency, reference, counterparty, balance, transaction_at, raw_extracted]
     */
    public static function parse(string $sender, string $body, ?string $smsTimestamp = null): array
    {
        $provider = SmsProvider::detectProvider($sender, $body);
        $providerCode = $provider?->code;
        $providerId = $provider?->id;

        $amount = self::extractAmount($body);
        $reference = self::extractReference($body);
        $counterparty = self::extractCounterparty($body);
        $balance = self::extractBalance($body);
        $type = self::detectType($body);
        $currency = str_contains($body, 'TZS') ? 'TZS' : (str_contains($body, 'USD') ? 'USD' : 'TZS');

        // Provider-specific overrides
        if ($providerCode === 'MPESA') {
            // M-Pesa example: "You have received TZS 150,000 from 074XXXXXXX. Transaction ID MPX827362."
            if (!$reference) {
                if (preg_match('/(?:Transaction ID|TxnID|Ref)[:\s]*([A-Za-z0-9\-]{5,20})/i', $body, $m)) $reference = $m[1];
            }
        }
        if ($providerCode === 'AIRTEL') {
            if (!$reference) {
                if (preg_match('/(?:TxnId|ID)[:\s]*([A-Za-z0-9]{6,20})/i', $body, $m)) $reference = $m[1];
            }
        }
        if ($providerCode === 'MIXX' || $providerCode === 'YAS') {
            if (!$reference) {
                if (preg_match('/(?:Ref|Transaction)[:\s]*([A-Za-z0-9\-]{5,20})/i', $body, $m)) $reference = $m[1];
            }
        }
        if ($providerCode === 'HALOPESA') {
            if (!$reference) {
                if (preg_match('/(?:Ref|ID)[:\s]*([A-Za-z0-9]{6,20})/i', $body, $m)) $reference = $m[1];
            }
        }

        return [
            'provider_code' => $providerCode,
            'provider_id' => $providerId,
            'transaction_type' => $type,
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $reference,
            'counterparty' => $counterparty,
            'balance' => $balance,
            'transaction_at' => $smsTimestamp ? \Carbon\Carbon::parse($smsTimestamp) : now(),
            'raw_extracted' => [
                'sender' => $sender,
                'body' => $body,
                'detected_provider' => $providerCode,
            ],
        ];
    }

    public static function extractAmount(string $body): ?float
    {
        // Matches: TZS 150,000 or TZS150,000.00 or 150,000 TZS
        if (preg_match('/TZS\s*([0-9,]+\.?[0-9]*)/i', $body, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }
        if (preg_match('/([0-9,]+\.?[0-9]*)\s*TZS/i', $body, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }
        if (preg_match('/Amount[:\s]*([0-9,]+\.?[0-9]*)/i', $body, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }
        return null;
    }

    public static function extractReference(string $body): ?string
    {
        $patterns = [
            '/Transaction ID[:\s]*([A-Za-z0-9\-]{5,30})/i',
            '/TxnID[:\s]*([A-Za-z0-9\-]{5,30})/i',
            '/Txn ID[:\s]*([A-Za-z0-9\-]{5,30})/i',
            '/Reference[:\s]*([A-Za-z0-9\-]{5,30})/i',
            '/Ref[:\s]*([A-Za-z0-9\-]{5,30})/i',
            '/ID[:\s]*([A-Za-z0-9\-]{6,30})/i',
            '/\b(MP[A-Za-z0-9]{5,15})\b/',
            '/\b([A-Z]{2,4}[0-9]{6,12})\b/',
        ];
        foreach ($patterns as $p) {
            if (preg_match($p, $body, $m)) return trim($m[1]);
        }
        return null;
    }

    public static function extractCounterparty(string $body): ?string
    {
        if (preg_match('/from\s+(?:\+?255|0)?([67]\d{8,9})/i', $body, $m)) return $m[1];
        if (preg_match('/(?:\+?255|0)?([67]\d{8,9})/', $body, $m)) return $m[0];
        if (preg_match('/from\s+([A-Za-z0-9 \.]{3,30})/i', $body, $m)) return trim($m[1]);
        return null;
    }

    public static function extractBalance(string $body): ?float
    {
        if (preg_match('/Balance[:\s]*TZS\s*([0-9,]+\.?[0-9]*)/i', $body, $m)) return (float) str_replace(',', '', $m[1]);
        if (preg_match('/New balance[^0-9]*([0-9,]+\.?[0-9]*)/i', $body, $m)) return (float) str_replace(',', '', $m[1]);
        return null;
    }

    public static function detectType(string $body): string
    {
        $lower = strtolower($body);
        if (str_contains($lower, 'reversed') || str_contains($lower, 'reversal')) return 'REVERSAL';
        if (str_contains($lower, 'received') || str_contains($lower, 'ume pokea') || str_contains($lower, 'payment received')) return 'PAYMENT';
        if (str_contains($lower, 'withdrawn') || str_contains($lower, 'kutoa')) return 'WITHDRAWAL';
        if (str_contains($lower, 'deposit') || str_contains($lower, 'umeweka')) return 'DEPOSIT';
        if (str_contains($lower, 'transferred') || str_contains($lower, 'umethibitisha') || str_contains($lower, 'transfer')) return 'TRANSFER';
        if (str_contains($lower, 'failed') || str_contains($lower, 'imeshindwa')) return 'FAILED';
        if (str_contains($lower, 'balance')) return 'BALANCE';
        return 'OTHER';
    }
}
