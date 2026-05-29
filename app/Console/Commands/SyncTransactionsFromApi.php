<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Services\ClickPesaAPIService;
use Illuminate\Support\Str;

#[Signature('app:sync-transactions-from-api')]
#[Description('Sync transactions from ClickPesa API to local database')]
class SyncTransactionsFromApi extends Command
{
    public function __construct(private ClickPesaAPIService $api)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting transaction sync...');
        $synced = 0;
        $failed = 0;

        try {
            $response = $this->api->queryAllPayments(['limit' => 1000, 'orderBy' => 'DESC']);

            if (isset($response['data'])) {
                $payments = $response['data'];

                foreach ($payments as $payment) {
                    $orderRef = $payment['orderReference'] ?? null;
                    if (!$orderRef) continue;

                    try {
                        Transaction::updateOrCreate(
                            ['order_reference' => $orderRef],
                            [
                                'id' => (string) Str::uuid(),
                                'transaction_id' => $payment['transactionId'] ?? $payment['id'] ?? null,
                                'status' => $payment['status'] ?? 'PENDING',
                                'amount' => $payment['amount'] ?? $payment['collectedAmount'] ?? 0,
                                'currency' => $payment['currency'] ?? 'TZS',
                                'phone' => $payment['customer_phone'] ?? $payment['customerPhone'] ?? null,
                                'email' => $payment['customer_email'] ?? $payment['customerEmail'] ?? null,
                                'description' => $payment['customer_name'] ?? $payment['customerName'] ?? 'Payment',
                                'type' => 'payment',
                                'payment_method' => $payment['paymentMethod'] ?? $payment['channel'] ?? null,
                                'callback_data' => $payment
                            ]
                        );

                        $synced++;
                        $this->line("Synced transaction: {$orderRef}");
                    } catch (\Exception $e) {
                        $failed++;
                        $this->error("Failed to sync {$orderRef}: " . $e->getMessage());
                    }
                }
            }

            $this->info("Sync complete! Synced {$synced} transactions. Failed: {$failed}");

            // Store last sync status as timestamp
            cache()->put('api_last_sync', now()->timestamp, now()->addHours(1));
            cache()->put('api_status', 'connected', now()->addMinutes(5));

        } catch (\Exception $e) {
            $this->error('Failed to sync: ' . $e->getMessage());
            cache()->put('api_status', 'disconnected', now()->addMinutes(5));
            $failed++;
        }

        return Command::SUCCESS;
    }
}
