<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\ClickPesaAPIService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:sync {--days=1 : Number of days to look back}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Sync payment details from ClickPesa API to local database';

    protected ClickPesaAPIService $api;

    public function __construct(ClickPesaAPIService $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $this->info("Syncing payments for the last {$days} day(s)...");
        Log::info("SyncPayments command started", ['days' => $days]);

        try {
            $params = [
                'limit' => 100,
                'orderBy' => 'DESC',
                'sortBy' => 'createdAt',
                'startDate' => Carbon::now()->subDays($days)->format('Y-m-d')
            ];

            $response = $this->api->queryAllPayments($params);
            
            if (!isset($response['data']) || empty($response['data'])) {
                $this->warn("No payments found in the specified range.");
                Log::info("SyncPayments: No payments found.");
                return 0;
            }

            $payments = $response['data'];
            $total = count($payments);
            $synced = 0;
            $created = 0;

            foreach ($payments as $paymentData) {
                $orderReference = $paymentData['orderReference'] ?? $paymentData['id'] ?? null;
                
                if (!$orderReference) {
                    continue;
                }

                $transaction = Transaction::where('order_reference', $orderReference)->first();

                $data = [
                    'order_reference' => $orderReference,
                    'transaction_id' => $paymentData['id'] ?? $paymentData['transaction_id'] ?? null,
                    'status' => $paymentData['status'] ?? 'UNKNOWN',
                    'amount' => $paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 0,
                    'currency' => $paymentData['collectedCurrency'] ?? $paymentData['currency'] ?? 'TZS',
                    'phone' => $paymentData['customer']['customerPhoneNumber'] ?? $paymentData['paymentPhoneNumber'] ?? null,
                    'payer_name' => $paymentData['customer']['customerName'] ?? $paymentData['payer_name'] ?? null,
                    'email' => $paymentData['customer']['customerEmail'] ?? $paymentData['email'] ?? null,
                    'description' => $paymentData['description'] ?? $paymentData['narrative'] ?? null,
                    'payment_method' => $paymentData['channel'] ?? $paymentData['paymentMethod'] ?? null,
                    'updated_at' => isset($paymentData['updatedAt']) ? Carbon::parse($paymentData['updatedAt']) : now(),
                ];

                if ($transaction) {
                    $transaction->update($data);
                    $synced++;
                } else {
                    $data['type'] = 'payment';
                    $data['created_at'] = isset($paymentData['createdAt']) ? Carbon::parse($paymentData['createdAt']) : now();
                    Transaction::create($data);
                    $created++;
                }
            }

            $this->info("Sync complete. Total processed: {$total}. Synced: {$synced}. Created: {$created}.");
            Log::info("SyncPayments command finished", [
                'total' => $total,
                'synced' => $synced,
                'created' => $created
            ]);

            return 0;
        } catch (Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
            Log::error("SyncPayments command failed", ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
