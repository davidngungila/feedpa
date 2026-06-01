<?php

namespace App\Support;

use App\Models\Transaction;

class TransactionFieldResolver
{
    private const GENERIC_DESCRIPTIONS = [
        'payment transaction',
        'malipo ya feedtan',
        'bill payment',
        'payment',
        'n/a',
        'na',
        '-',
    ];

    public static function hasMeaningfulText(?string $value): bool
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' && strtoupper($value) !== 'N/A';
    }

    public static function isGenericDescription(?string $value): bool
    {
        $normalized = strtolower(trim((string) ($value ?? '')));

        if ($normalized === '') {
            return true;
        }

        return in_array($normalized, self::GENERIC_DESCRIPTIONS, true);
    }

    public static function formPurposeFromCallback(?array $callbackData): ?string
    {
        if (!is_array($callbackData)) {
            return null;
        }

        foreach (['form_description', 'member_purpose', 'purpose', 'payment_purpose'] as $key) {
            $value = $callbackData[$key] ?? null;
            if (self::hasMeaningfulText($value) && !self::isGenericDescription($value)) {
                return trim((string) $value);
            }
        }

        return null;
    }

    public static function mergeCallbackData(?array $existing, array $incoming): array
    {
        $existing = is_array($existing) ? $existing : [];
        $merged = array_merge($existing, $incoming);

        foreach (['form_description', 'member_purpose', 'purpose', 'payment_purpose', 'submitted_via'] as $key) {
            if (!empty($existing[$key])) {
                $merged[$key] = $existing[$key];
            }
        }

        return $merged;
    }

    public static function initialCallbackSnapshot(string $description, string $source = 'public_form'): array
    {
        $description = trim($description);

        return [
            'form_description' => $description,
            'member_purpose' => $description,
            'submitted_via' => $source,
            'submitted_at' => now()->toIso8601String(),
        ];
    }

    public static function description(?string $local, ?string $remote = null, ?string $default = 'Malipo ya FEEDTAN', ?array $callbackData = null): ?string
    {
        $formPurpose = self::formPurposeFromCallback($callbackData);

        foreach ([$local, $formPurpose] as $candidate) {
            if (self::hasMeaningfulText($candidate) && !self::isGenericDescription($candidate)) {
                return trim((string) $candidate);
            }
        }

        if (self::hasMeaningfulText($remote) && !self::isGenericDescription($remote)) {
            return trim((string) $remote);
        }

        if (self::hasMeaningfulText($formPurpose)) {
            return trim((string) $formPurpose);
        }

        if (self::hasMeaningfulText($local)) {
            return trim((string) $local);
        }

        if (self::hasMeaningfulText($remote)) {
            return trim((string) $remote);
        }

        // Ensure we never return N/A, use the default
        return $default;
    }

    public static function resolveForTransaction(Transaction $transaction, ?string $remote = null, ?string $default = 'Malipo ya FEEDTAN'): string
    {
        $callback = is_array($transaction->callback_data) ? $transaction->callback_data : [];

        return self::description(
            $transaction->description,
            $remote ?? ($callback['description'] ?? $callback['narrative'] ?? null),
            $default,
            $callback
        ) ?? $default;
    }

    public static function looksLikePhone(?string $value): bool
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' && ctype_digit($value) && strlen($value) >= 9;
    }

    public static function memberName(?string $localMember, ?string $remoteName = null): ?string
    {
        if (self::hasMeaningfulText($localMember) && !self::looksLikePhone($localMember)) {
            return trim($localMember);
        }

        if (self::hasMeaningfulText($remoteName) && !self::looksLikePhone($remoteName)) {
            return trim($remoteName);
        }

        return self::hasMeaningfulText($localMember) ? trim($localMember) : null;
    }

    public static function payerName(?string $localPayer, ?string $remoteName = null): ?string
    {
        if (self::hasMeaningfulText($remoteName) && !self::looksLikePhone($remoteName)) {
            return trim($remoteName);
        }

        if (self::hasMeaningfulText($localPayer) && !self::looksLikePhone($localPayer)) {
            return trim($localPayer);
        }

        return self::hasMeaningfulText($remoteName) ? trim($remoteName) : null;
    }
}
