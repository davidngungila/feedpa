<?php

namespace App\Console\Commands;

use App\Models\Payout;
use App\Services\AppNotificationService;
use App\Services\ClickPesaAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncClickPesaPayouts extends Command
{
    protected $signature = 'app:sync-click-pesa-payouts';
    protected $description = 'Sync all ClickPesa payouts to local database';
    protected ClickPesaAPIService $api;
    protected AppNotificationService $notifications;

    public function __construct(ClickPesaAPIService $api, AppNotificationService $notifications)
    {
        parent::__construct();
        $this->api = $api;
        $this->notifications = $notifications;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting ClickPesa payout sync...');

        try {
            $apiPayouts = $this->api->queryAllPayouts(['limit' => 20, 'orderBy' => 'DESC']);
            $payoutsData = $apiPayouts['data'] ?? $apiPayouts['payouts'] ?? [];
            
            if (!is_array($payoutsData)) {
                $this->error('Invalid response from ClickPesa API');
                return 1;
            }

            $syncedCount = 0;
            foreach ($payoutsData as $apiPayout) {
                $orderRef = $apiPayout['order_reference'] ?? $apiPayout['orderReference'] ?? $apiPayout['id'] ?? null;
                if (!$orderRef) continue;

                $existingPayout = Payout::where('order_reference', $orderRef)->first();
                $previousStatus = $existingPayout?->status;
                $beneficiary = $apiPayout['beneficiary'] ?? [];
                $payoutType = ($apiPayout['channel'] ?? '') === 'BANK TRANSFER' ? 'BANK' : 'MOBILE MONEY';
                $syncedStatus = $apiPayout['status'] ?? 'UNKNOWN';
                $syncedWorkflowStage = in_array($syncedStatus, ['SUCCESS', 'SETTLED'], true)
                    ? 'COMPLETED'
                    : (in_array($syncedStatus, ['FAILED', 'ERROR', 'CANCELLED'], true) ? 'FAILED' : 'PROCESSING');

                $payout = Payout::updateOrCreate(
                    ['order_reference' => $orderRef],
                    [
                        'clickpesa_payout_id' => $apiPayout['id'] ?? null,
                        'transaction_id' => $apiPayout['id'] ?? $apiPayout['transaction_id'] ?? null,
                        'status' => $syncedStatus,
                        'workflow_stage' => $syncedWorkflowStage,
                        'amount' => $apiPayout['amount'] ?? 0,
                        'currency' => $apiPayout['currency'] ?? 'TZS',
                        'fee' => $apiPayout['fee'] ?? 0,
                        'payout_type' => $payoutType,
                        'recipient_name' => $beneficiary['accountName'] ?? $apiPayout['recipient_name'] ?? $apiPayout['customerName'] ?? 'N/A',
                        'recipient_phone' => $beneficiary['beneficiaryMobileNumber'] ?? $apiPayout['recipient_phone'] ?? $apiPayout['phoneNumber'] ?? null,
                        'bank_name' => $apiPayout['bank_name'] ?? null,
                        'bank_account_number' => $beneficiary['accountNumber'] ?? $apiPayout['bank_account_number'] ?? null,
                        'bic' => $beneficiary['bic'] ?? $apiPayout['bic'] ?? null,
                        'channel' => $apiPayout['channel'] ?? null,
                        'channel_provider' => $apiPayout['channelProvider'] ?? null,
                        'transfer_type' => $apiPayout['transferType'] ?? null,
                        'beneficiary_account_number' => $beneficiary['accountNumber'] ?? null,
                        'beneficiary_account_name' => $beneficiary['accountName'] ?? null,
                        'beneficiary_mobile' => $beneficiary['beneficiaryMobileNumber'] ?? null,
                        'beneficiary_email' => $beneficiary['beneficiaryEmail'] ?? null,
                        'notes' => $apiPayout['notes'] ?? null,
                        'created_at' => isset($apiPayout['createdAt']) ? \Carbon\Carbon::parse($apiPayout['createdAt'])->toDateTimeString() : now(),
                        'updated_at' => isset($apiPayout['updatedAt']) ? \Carbon\Carbon::parse($apiPayout['updatedAt'])->toDateTimeString() : now(),
                        'callback_data' => $apiPayout,
                        'user_id' => null
                    ]
                );

                if ($previousStatus !== $syncedStatus && in_array($syncedStatus, ['SUCCESS', 'SETTLED'], true)) {
                    $this->notifications->sendPayoutSuccessEmail($payout);
                }

                $syncedCount++;
            }

            $this->info("Successfully synced {$syncedCount} payouts!");
            Log::info("Successfully synced {$syncedCount} ClickPesa payouts");

            return 0;
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            Log::error('ClickPesa payout sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
