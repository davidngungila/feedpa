<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Support\TransactionFieldResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncTransactionsFromApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:sync {--limit=100 : Number of transactions to fetch from API}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync missing transactions from ClickPesa API to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('==============================================');
        $this->info('Starting Transaction Sync from ClickPesa API');
        $this->info('==============================================');
        $this->newLine();

        // Step 1: Get existing references
        $this->info('Step 1: Fetching existing transactions from database...');
        $existingReferences = Transaction::pluck('order_reference')->filter()->unique()->toArray();
        $this->info("✅ Found " . count($existingReferences) . " existing transactions");
        $this->newLine();

        // Step 2: Query API
        $this->info('Step 2: Querying ClickPesa API...');
        try {
            $api = app('App\Services\ClickPesaAPIService');
            $limit = $this->option('limit');
            $allPayments = $api->queryAllPayments([
                'limit' => $limit,
                'status' => 'all'
            ]);
            $this->info("✅ API responded successfully");
        } catch (\Exception $e) {
            $this->error("❌ Error querying API: " . $e->getMessage());
            return 1;
        }

        // Parse payments data
        $paymentsData = [];
        if (isset($allPayments['data']) && is_array($allPayments['data'])) {
            $paymentsData = $allPayments['data'];
        } elseif (is_array($allPayments)) {
            $paymentsData = $allPayments;
        }

        $this->info("📊 Found " . count($paymentsData) . " payments from API");
        $this->newLine();

        // Step 3: Process each payment
        $this->info('Step 3: Processing payments...');
        $syncedCount = 0;
        $skippedCount = 0;

        foreach ($paymentsData as $payment) {
            $orderRef = $payment['orderReference'] ?? $payment['order_reference'] ?? null;

            if (!$orderRef) {
                $this->warn("⚠️ Skipping payment with no order reference");
                continue;
            }

            if (in_array($orderRef, $existingReferences)) {
                $skippedCount++;
                continue;
            }

            // Extract data
            $transactionId = $payment['id'] ?? $payment['transaction_id'] ?? null;
            $status = $payment['status'] ?? 'UNKNOWN';
            $amount = $payment['collectedAmount'] ?? $payment['amount'] ?? 0;
            $currency = $payment['collectedCurrency'] ?? $payment['currency'] ?? 'TZS';
            $phone = $payment['customer']['customerPhoneNumber'] ?? $payment['paymentPhoneNumber'] ?? $payment['phone'] ?? null;
            $payerName = $payment['customer']['customerName'] ?? $payment['payer_name'] ?? $payment['customerName'] ?? 'Unknown';
            $description = $payment['description'] ?? $payment['narrative'] ?? 'Payment';
            $paymentMethod = $payment['channel'] ?? $payment['paymentMethod'] ?? $payment['payment_method'] ?? null;
            $createdAt = $payment['createdAt'] ?? now();
            $updatedAt = $payment['updatedAt'] ?? now();

            // Create transaction
            try {
                Transaction::create([
                    'id' => (string) Str::uuid(),
                    'order_reference' => $orderRef,
                    'transaction_id' => $transactionId,
                    'status' => $status,
                    'amount' => $amount,
                    'currency' => $currency,
                    'phone' => $phone,
                    'payer_name' => $payerName,
                    'customer_name' => $payerName,
                    'description' => $description,
                    'payment_method' => $paymentMethod,
                    'callback_data' => $payment,
                    'callback_received_at' => now(),
                    'type' => 'payment',
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt
                ]);

                $syncedCount++;
                $this->info("✅ Synced: {$orderRef} | {$status} | {$amount} {$currency}");

            } catch (\Exception $e) {
                $this->error("❌ Failed to sync {$orderRef}: " . $e->getMessage());
            }
        }

        // Summary
        $this->newLine();
        $this->info('==============================================');
        $this->info('Sync Complete!');
        $this->info('==============================================');
        $this->info("✅ Synced: {$syncedCount} new transactions");
        $this->info("⚠️ Skipped: {$skippedCount} existing transactions");
        $this->info("Total processed: " . ($syncedCount + $skippedCount));
        $this->info('==============================================');

        return 0;
    }
}
