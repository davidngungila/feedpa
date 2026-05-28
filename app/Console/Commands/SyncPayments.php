<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\ClickPesaAPIService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\MessagingServiceAPI;

class SyncPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:sync {--days=1 : Number of days to look back} {--force-sms : Force sending SMS for all synced transactions}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Sync payment details from ClickPesa API to local database and notify customers';

    protected ClickPesaAPIService $api;
    protected MessagingServiceAPI $messaging;

    public function __construct(ClickPesaAPIService $api, MessagingServiceAPI $messaging)
    {
        parent::__construct();
        $this->api = $api;
        $this->messaging = $messaging;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $forceSms = (bool) $this->option('force-sms');
        $this->info("Syncing payments for the last {$days} day(s)...");
        Log::info("SyncPayments command started", ['days' => $days, 'force_sms' => $forceSms]);

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
            $smsSent = 0;

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

                $isNew = false;
                if ($transaction) {
                    $transaction->update($data);
                    $synced++;
                } else {
                    $data['type'] = 'payment';
                    $data['created_at'] = isset($paymentData['createdAt']) ? Carbon::parse($paymentData['createdAt']) : now();
                    $transaction = Transaction::create($data);
                    $created++;
                    $isNew = true;
                }

                // Send SMS if it's a new transaction or force-sms is enabled
                // AND the status is SUCCESS or SETTLED
                if (($isNew || $forceSms) && in_array($transaction->status, ['SUCCESS', 'SETTLED'])) {
                    if ($this->sendPaymentSms($transaction, $paymentData)) {
                        $smsSent++;
                    }
                }
            }

            $this->info("Sync complete. Total processed: {$total}. Synced: {$synced}. Created: {$created}. SMS Sent: {$smsSent}");
            Log::info("SyncPayments command finished", [
                'total' => $total,
                'synced' => $synced,
                'created' => $created,
                'sms_sent' => $smsSent
            ]);

            return 0;
        } catch (Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
            Log::error("SyncPayments command failed", ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Send payment confirmation SMS
     */
    private function sendPaymentSms(Transaction $transaction, array $paymentData): bool
    {
        try {
            if (!config('messaging.enabled') || !config('messaging.notifications.payment_confirmation')) {
                return false;
            }

            $phoneNumber = $transaction->phone;
            if (!$phoneNumber) {
                Log::warning("Cannot send SMS for transaction {$transaction->order_reference}: No phone number.");
                return false;
            }

            // Prepare payment data for MessagingService
            $smsData = [
                'orderReference' => $transaction->order_reference,
                'id' => $transaction->transaction_id,
                'status' => $transaction->status,
                'collectedAmount' => $transaction->amount,
                'collectedCurrency' => $transaction->currency,
                'paymentPhoneNumber' => $phoneNumber,
                'channel' => $transaction->payment_method ?? 'Mobile Money',
                'customer' => [
                    'customerName' => $transaction->payer_name,
                    'customerPhoneNumber' => $phoneNumber
                ],
                'createdAt' => $transaction->created_at
            ];

            $this->messaging->sendPaymentConfirmation($phoneNumber, $smsData);
            
            $transaction->update([
                'sms_sent' => true,
                'sms_sent_at' => now(),
                'sms_error' => null
            ]);

            return true;
        } catch (Exception $e) {
            Log::error("SMS failed for synced transaction {$transaction->order_reference}: " . $e->getMessage());
            $transaction->update([
                'sms_sent' => false,
                'sms_error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
