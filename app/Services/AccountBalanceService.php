<?php

namespace App\Services;

use App\Models\AccountBalance;
use Exception;
use Illuminate\Support\Facades\Log;

class AccountBalanceService
{
    public function __construct(
        protected ClickPesaAPIService $api
    ) {}

    /**
     * Fetch live balance from ClickPesa and persist when changed.
     */
    public function syncFromApi(string $currency = 'TZS'): ?AccountBalance
    {
        $response = $this->api->getAccountBalance($currency);
        
        Log::info('ClickPesa getAccountBalance raw response', [
            'currency' => $currency,
            'response' => $response
        ]);
        
        $parsed = $this->parseBalanceResponse($response, $currency);

        if (! $parsed) {
            Log::warning('ClickPesa getAccountBalance failed to parse response', [
                'currency' => $currency,
                'response' => $response
            ]);
            return null;
        }

        $existing = AccountBalance::where('currency', $parsed['currency'])->first();
        $changed = ! $existing || (float) $existing->balance !== (float) $parsed['balance'];

        $record = AccountBalance::updateOrCreate(
            ['currency' => $parsed['currency']],
            [
                'balance' => $parsed['balance'],
                'synced_at' => now(),
            ]
        );

        if ($changed) {
            Log::info('Account balance updated from API', [
                'currency' => $parsed['currency'],
                'balance' => $parsed['balance'],
                'previous' => $existing?->balance,
            ]);
        }

        return $record;
    }

    /**
     * Get TZS balance: live API first, database fallback on failure.
     */
    public function getTzsBalance(bool $refresh = true): array
    {
        $currency = 'TZS';

        if ($refresh) {
            try {
                $synced = $this->syncFromApi($currency);
                if ($synced) {
                    return $this->formatBalancePayload($synced, live: true);
                }
            } catch (Exception $e) {
                Log::warning('Live account balance fetch failed, using database', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $cached = AccountBalance::where('currency', $currency)->first();
        if ($cached) {
            return $this->formatBalancePayload($cached, live: false);
        }

        return [
            'balance' => 0,
            'currency' => $currency,
            'synced_at' => null,
            'live' => false,
        ];
    }

    private function formatBalancePayload(AccountBalance $record, bool $live): array
    {
        return [
            'balance' => (float) $record->balance,
            'currency' => $record->currency,
            'synced_at' => $record->synced_at,
            'live' => $live,
        ];
    }

    private function parseBalanceResponse(mixed $response, string $defaultCurrency = 'TZS'): ?array
    {
        if (empty($response) || ! is_array($response)) {
            return null;
        }

        // Check if response has a top-level 'balances' key
        if (isset($response['balances']) && is_array($response['balances'])) {
            foreach ($response['balances'] as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $itemCurrency = strtoupper($item['currency'] ?? $defaultCurrency);
                if ($itemCurrency === strtoupper($defaultCurrency)) {
                    return [
                        'currency' => $itemCurrency,
                        'balance' => (float) ($item['balance'] ?? 0),
                    ];
                }
            }

            // Fallback to first balance in list
            $first = $response['balances'][0] ?? null;
            if (is_array($first) && isset($first['balance'])) {
                return [
                    'currency' => strtoupper($first['currency'] ?? $defaultCurrency),
                    'balance' => (float) $first['balance'],
                ];
            }
        }

        // Original logic for backward compatibility
        if (isset($response['balance'])) {
            return [
                'currency' => strtoupper($response['currency'] ?? $defaultCurrency),
                'balance' => (float) $response['balance'],
            ];
        }

        if (array_is_list($response)) {
            foreach ($response as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $itemCurrency = strtoupper($item['currency'] ?? $defaultCurrency);
                if ($itemCurrency === strtoupper($defaultCurrency)) {
                    return [
                        'currency' => $itemCurrency,
                        'balance' => (float) ($item['balance'] ?? 0),
                    ];
                }
            }

            $first = $response[0] ?? null;
            if (is_array($first) && isset($first['balance'])) {
                return [
                    'currency' => strtoupper($first['currency'] ?? $defaultCurrency),
                    'balance' => (float) $first['balance'],
                ];
            }
        }

        return null;
    }
}
