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
        $counterpartyName = self::extractCounterpartyName($body);
        $balance = self::extractBalance($body);
        $type = self::detectType($body);
        // normalize currency: TSh is same as TZS in TZ context
        $hasUSD = stripos($body, 'USD') !== false;
        $currency = $hasUSD ? 'USD' : 'TZS';

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

        $allRefs = self::extractAllReferences($body);
        return [
            'provider_code' => $providerCode,
            'provider_id' => $providerId,
            'transaction_type' => $type,
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $reference,
            'all_references' => $allRefs,
            'counterparty' => $counterparty,
            'counterparty_name' => $counterpartyName,
            'balance' => $balance,
            'transaction_at' => $smsTimestamp ? \Carbon\Carbon::parse($smsTimestamp) : now(),
            'raw_extracted' => [
                'sender' => $sender,
                'body' => $body,
                'detected_provider' => $providerCode,
                'all_references' => $allRefs,
            ],
        ];
    }

    public static function extractAmount(string $body): ?float
    {
        // Matches: TZS/TSh 150,000 or TZS150,000.00 or 150,000 TZS/TSh (supports TSh variant)
        if (preg_match('/TZS\s*([0-9,]+\.?[0-9]*)/i', $body, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }
        if (preg_match('/TSh\s*([0-9,]+\.?[0-9]*)/i', $body, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }
        if (preg_match('/([0-9,]+\.?[0-9]*)\s*TZS/i', $body, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }
        if (preg_match('/([0-9,]+\.?[0-9]*)\s*TSh/i', $body, $m)) {
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
            // Swahili primary IDs — Namba ya muamala, Risiti, Kumbukumbu
            '/Namba ya muamala\s*[:\s]*([A-Za-z0-9\-]{5,30})/iu',
            '/Risiti\s*[:\s]*([A-Za-z0-9\-\.]{5,30})/iu',
            // Swahili Kumbukumbu (Mixx by Yas) — e.g. "Kumbukumbu no.: 26452292369821" or "Kumbukumbu: 264522..."
            '/Kumbukumbu\s*(?:no\.?|namba)?\s*[:\.\s]*([A-Za-z0-9\-]{5,30})/iu',
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
            if (preg_match($p, $body, $m)) return trim($m[1], " .\t\n\r\0\x0B-");
        }
        return null;
    }

    public static function extractAllReferences(string $body): array
    {
        $out = [];
        if (preg_match('/Namba ya muamala\s*[:\s]*([A-Za-z0-9\-]{5,30})/iu', $body, $m)) $out['namba_ya_muamala'] = trim($m[1], " .");
        if (preg_match('/Risiti\s*[:\s]*([A-Za-z0-9\-\.]{5,30})/iu', $body, $m)) $out['risiti'] = trim($m[1], " .");
        if (preg_match('/Kumbukumbu\s*(?:no\.?|namba)?\s*[:\.\s]*([A-Za-z0-9\-]{5,30})/iu', $body, $m)) $out['kumbukumbu'] = trim($m[1], " .");
        // also capture generic reference for fallback display
        $ref = self::extractReference($body);
        if ($ref) $out['reference'] = $ref;
        return $out;
    }

    public static function extractCounterparty(string $body): ?string
    {
        // Swahili: kwenda kwa 255..., kutoka kwa 255..., kwa 255..., English from/to — capture full number with prefix
        if (preg_match('/kwenda kwa\s+((?:\+?255|0)?[67]\d{8,9})/iu', $body, $m)) return $m[1];
        if (preg_match('/kutoka kwa\s+((?:\+?255|0)?[67]\d{8,9})/iu', $body, $m)) return $m[1];
        if (preg_match('/\bkwa\s+((?:\+?255|0)?[67]\d{8,9})/iu', $body, $m)) return $m[1];
        if (preg_match('/from\s+((?:\+?255|0)?[67]\d{8,9})/i', $body, $m)) return $m[1];
        if (preg_match('/to\s+((?:\+?255|0)?[67]\d{8,9})/i', $body, $m)) return $m[1];
        // fallback: any TZ mobile number
        if (preg_match('/((?:\+?255|0)?[67]\d{8,9})/', $body, $m)) {
            return $m[1];
        }
        if (preg_match('/from\s+([A-Za-z0-9 \.]{3,30})/i', $body, $m)) return trim($m[1]);
        return null;
    }

    public static function extractCounterpartyName(string $body): ?string
    {
        // e.g. "kwenda kwa 255717358865 - EMMANUEL LULANDALA."  -> capture until first dot
        if (preg_match('/(?:kwenda kwa|kutoka kwa|from|to)\s+(?:\+?255|0)?[67]\d{8,9}\s*[-–]\s*([^.\n]+?)\s*\./iu', $body, $m)) {
            $name = trim($m[1]);
            $name = rtrim($name, '. ,');
            // limit to first 40 chars and title case
            return mb_substr($name, 0, 40);
        }
        // fallback without dot: capture 2-4 uppercase words
        if (preg_match('/(?:kwenda kwa|kutoka kwa|kwa)\s+(?:\+?255|0)?[67]\d{8,9}\s*[-–]\s*([A-Z][A-Z]+\s+[A-Z][A-Z]+(?:\s+[A-Z]+)?)/u', $body, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    public static function extractBalance(string $body): ?float
    {
        if (preg_match('/Balance[:\s]*TZS\s*([0-9,]+\.?[0-9]*)/i', $body, $m)) return (float) str_replace(',', '', $m[1]);
        if (preg_match('/New balance[^0-9]*([0-9,]+\.?[0-9]*)/i', $body, $m)) return (float) str_replace(',', '', $m[1]);
        // Swahili: Salio lako jipya ni TSh 3,613  or Salio: TZS 1,200
        if (preg_match('/Salio(?:\s*lako)?(?:\s*jipya)?\s*(?:ni)?\s*[:\s]*TSh\s*([0-9,]+\.?[0-9]*)/iu', $body, $m)) return (float) str_replace(',', '', $m[1]);
        if (preg_match('/Salio[^0-9]*TZS?\s*([0-9,]+\.?[0-9]*)/iu', $body, $m)) return (float) str_replace(',', '', $m[1]);
        if (preg_match('/Salio[^0-9]*([0-9,]+\.?[0-9]*)/iu', $body, $m)) {
            // fallback: capture last number after Salio if previous failed (heuristic)
            // ensure we don't pick wrong, only if TSh/TZS nearby
            if (preg_match('/Salio.*?(TSh|TZS)\s*([0-9,]+\.?[0-9]*)/iu', $body, $mm)) return (float) str_replace(',', '', $mm[2]);
        }
        return null;
    }

    public static function detectType(string $body): string
    {
        $lower = mb_strtolower($body, 'UTF-8');
        // normalize spaces
        if (str_contains($lower, 'reversed') || str_contains($lower, 'reversal') || str_contains($lower, 'rudishwa') || str_contains($lower, 'umerudishiwa')) return 'REVERSAL';
        if (str_contains($lower, 'received') || str_contains($lower, 'umepokea') || str_contains($lower, 'ume pokea') || str_contains($lower, 'umepokea') || str_contains($lower, 'payment received') || str_contains($lower, 'pesa zimeingia')) return 'PAYMENT';
        if (str_contains($lower, 'withdrawn') || str_contains($lower, 'umetua') || str_contains($lower, 'umetoa') || str_contains($lower, 'kutoa') || str_contains($lower, 'utoaji')) return 'WITHDRAWAL';
        if (str_contains($lower, 'deposit') || str_contains($lower, 'umeweka') || str_contains($lower, 'kuweka')) return 'DEPOSIT';
        if (str_contains($lower, 'transferred') || str_contains($lower, 'umetuma') || str_contains($lower, 'umelipia') || str_contains($lower, 'kulipia') || str_contains($lower, 'kutuma') || str_contains($lower, 'umethibitisha') || str_contains($lower, 'transfer') || str_contains($lower, 'kwenda kwa')) return 'TRANSFER';
        if (str_contains($lower, 'failed') || str_contains($lower, 'imeshindwa') || str_contains($lower, 'haikufanikiwa')) return 'FAILED';
        if (str_contains($lower, 'balance') || str_contains($lower, 'salio')) return 'BALANCE';
        // fallback: if body contains "umetuma" with kwenda kwa, treat as TRANSFER even if BALANCE keyword present
        if (str_contains($lower, 'umetuma')) return 'TRANSFER';
        return 'OTHER';
    }
}
