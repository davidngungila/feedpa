<?php

namespace App\Support;

class TransactionFieldResolver
{
    public static function hasMeaningfulText(?string $value): bool
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' && strtoupper($value) !== 'N/A';
    }

    public static function description(?string $local, ?string $remote = null, ?string $default = null): ?string
    {
        if (self::hasMeaningfulText($local)) {
            return trim($local);
        }

        if (self::hasMeaningfulText($remote)) {
            return trim($remote);
        }

        return $default;
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
