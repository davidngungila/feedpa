<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\BillPayNumber;
use App\Services\ClickPesaAPIService;
use Illuminate\Support\Facades\Auth;

#[Signature('app:sync-bills-from-api')]
#[Description('Sync bills from ClickPesa API to local database')]
class SyncBillsFromApi extends Command
{
    public function __construct(private ClickPesaAPIService $api)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting bill sync...');
        $synced = 0;
        $failed = 0;

        try {
            $response = $this->api->queryAllBillPayNumbers(['limit' => 1000, 'orderBy' => 'DESC']);

            if (isset($response['data'])) {
                $bills = $response['data'];

                foreach ($bills as $apiBill) {
                    $billPayNumber = $apiBill['billPayNumber'] ?? null;
                    if (!$billPayNumber) continue;

                    try {
                        // Determine bill type from API response
                        $billType = 'order';
                        if (isset($apiBill['customerName'])) {
                            $billType = 'customer';
                        }

                        BillPayNumber::updateOrCreate(
                            ['bill_pay_number' => $billPayNumber],
                            [
                                'bill_description' => $apiBill['billDescription'] ?? null,
                                'bill_amount' => $apiBill['billAmount'] ?? null,
                                'bill_currency' => $apiBill['currency'] ?? 'TZS',
                                'bill_payment_mode' => $apiBill['billPaymentMode'] ?? 'ALLOW_PARTIAL_AND_OVER_PAYMENT',
                                'bill_status' => $apiBill['billStatus'] ?? 'ACTIVE',
                                'bill_type' => $billType,
                                'customer_name' => $apiBill['customerName'] ?? null,
                                'customer_email' => $apiBill['customerEmail'] ?? null,
                                'customer_phone' => $apiBill['customerPhone'] ?? null,
                                'bill_reference' => $apiBill['billReference'] ?? null,
                                'total_paid' => $apiBill['totalPaid'] ?? $apiBill['collectedAmount'] ?? 0,
                                'last_payment_at' => isset($apiBill['lastPaymentAt']) ? \Illuminate\Support\Carbon::parse($apiBill['lastPaymentAt']) : null,
                            ]
                        );

                        $synced++;
                        $this->line("Synced bill: {$billPayNumber}");
                    } catch (\Exception $e) {
                        $failed++;
                        $this->error("Failed to sync {$billPayNumber}: " . $e->getMessage());
                    }
                }
            }

            $this->info("Sync complete! Synced {$synced} bills. Failed: {$failed}");

            // Store last bill sync status
            cache()->put('api_bills_last_sync', now()->timestamp, now()->addHours(1));

        } catch (\Exception $e) {
            $this->error('Failed to sync bills: ' . $e->getMessage());
            $failed++;
        }

        return Command::SUCCESS;
    }
}
