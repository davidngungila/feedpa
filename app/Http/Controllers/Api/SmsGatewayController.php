<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsDevice;
use App\Models\SmsMessage;
use App\Models\SmsProvider;
use App\Models\SmsTransaction;
use App\Models\SmsReconciliation;
use App\Models\SmsSyncLog;
use App\Models\SmsDeviceHeartbeat;
use App\Services\SmsParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SmsGatewayController extends Controller
{
    // -------------------------------------------------------------
    // Device registration & activation (admin creates device, gateway activates)
    // -------------------------------------------------------------

    /**
     * POST /api/v1/auth/device/register (ADMIN, web auth) is in SmsDeviceController
     * POST /api/v1/auth/device/activate  (public, device side)
     * Body: { activation_code: "849215" }
     */
    public function activate(Request $request)
    {
        $request->validate([
            'activation_code' => 'required|string|size:6',
            'device_info' => 'nullable|array',
        ]);

        $device = SmsDevice::where('activation_code', $request->activation_code)->first();
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Invalid activation code.'], 404);
        }
        if ($device->activation_expires_at && $device->activation_expires_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'Activation code expired. Request a new one.'], 422);
        }
        if ($device->status === 'REVOKED' || $device->status === 'SUSPENDED') {
            return response()->json(['success' => false, 'message' => 'Device is '.$device->status], 403);
        }

        // Activate
        $device->update([
            'status' => 'ACTIVE',
            'activation_code' => null,
            'activation_expires_at' => null,
            'android_version' => $request->input('device_info.android_version', $device->android_version),
            'app_version' => $request->input('device_info.app_version', $device->app_version),
        ]);

        // optional revoke old tokens
        $device->tokens()->where('is_revoked', false)->update(['is_revoked' => true]);

        $tokenModel = $device->createAuthToken();
        return response()->json([
            'success' => true,
            'message' => 'Device activated successfully.',
            'data' => [
                'device_id' => $device->device_code,
                'device_db_id' => $device->id,
                'device_name' => $device->name,
                'location' => $device->location?->name,
                'status' => $device->status,
                'token' => $tokenModel->plain_token,
                'token_hint' => $tokenModel->plain_hint,
            ]
        ]);
    }

    /**
     * POST /api/v1/auth/device/login  (if device already activated but needs new token)
     * Header: X-Device-Token OR body device_token + device_code
     * Actually for re-login we require activation_code again? We'll support token refresh via existing token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'device_code' => 'required|string',
            'activation_code' => 'required|string',
        ]);
        $device = SmsDevice::where('device_code', $request->device_code)->where('activation_code', $request->activation_code)->first();
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Invalid device code or activation code.'], 401);
        }
        $device->update(['status' => 'ACTIVE', 'activation_code' => null, 'activation_expires_at' => null]);
        $device->tokens()->where('is_revoked', false)->update(['is_revoked' => true]);
        $tokenModel = $device->createAuthToken();
        return response()->json([
            'success' => true,
            'data' => [
                'device_id' => $device->device_code,
                'token' => $tokenModel->plain_token,
            ]
        ]);
    }

    // -------------------------------------------------------------
    // Device authenticated endpoints
    // -------------------------------------------------------------

    /**
     * GET /api/v1/device/config  -> returns remote config
     */
    public function config(Request $request)
    {
        /** @var SmsDevice $device */
        $device = $request->attributes->get('device');
        $defaultConfig = [
            'auto_sync' => true,
            'retry_interval_seconds' => 30,
            'heartbeat_interval_seconds' => 60,
            'max_queue' => 10000,
            'notifications' => true,
            'batch_size' => 50,
        ];
        $config = array_merge($defaultConfig, $device->config ?? []);
        return response()->json([
            'success' => true,
            'data' => [
                'device_id' => $device->device_code,
                'status' => $device->status,
                'config' => $config,
                'server_time' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * POST /api/v1/device/heartbeat
     * Body: { battery:82, network:"4G", pending_sms:0, app_version:"1.0.0", android_version:"14", signal:"Good" }
     */
    public function heartbeat(Request $request)
    {
        $request->validate([
            'battery' => 'nullable|integer|min:0|max:100',
            'network' => 'nullable|string|max:20',
            'pending_sms' => 'nullable|integer|min:0',
            'app_version' => 'nullable|string|max:20',
            'android_version' => 'nullable|string|max:20',
            'signal' => 'nullable|string|max:20',
        ]);
        /** @var SmsDevice $device */
        $device = $request->attributes->get('device');
        $battery = $request->input('battery');
        $network = $request->input('network');
        $pending = $request->input('pending_sms', 0);

        $device->update([
            'battery_level' => $battery ?? $device->battery_level,
            'network_type' => $network ?? $device->network_type,
            'signal_strength' => $request->input('signal', $device->signal_strength),
            'app_version' => $request->input('app_version', $device->app_version),
            'android_version' => $request->input('android_version', $device->android_version),
            'last_heartbeat_at' => now(),
        ]);

        SmsDeviceHeartbeat::create([
            'device_id' => $device->id,
            'battery_level' => $battery,
            'network_type' => $network,
            'pending_sms' => $pending,
            'app_version' => $request->input('app_version'),
            'android_version' => $request->input('android_version'),
            'ip_address' => $request->ip(),
            'meta' => $request->only(['signal']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat recorded.',
            'data' => [
                'device_id' => $device->device_code,
                'server_time' => now()->toIso8601String(),
                'pending_sms_server' => $device->smsMessages()->where('sync_status', 'PENDING')->count(),
            ]
        ]);
    }

    /**
     * GET /api/v1/device/status
     */
    public function status(Request $request)
    {
        /** @var SmsDevice $device */
        $device = $request->attributes->get('device');
        return response()->json([
            'success' => true,
            'data' => [
                'device_id' => $device->device_code,
                'name' => $device->name,
                'status' => $device->status,
                'battery' => $device->battery_level,
                'network' => $device->network_type,
                'last_heartbeat' => $device->last_heartbeat_at,
                'last_sync' => $device->last_sync_at,
                'is_online' => $device->isOnline(),
            ]
        ]);
    }

    /**
     * POST /api/v1/sms/batch  (primary gateway upload)
     * Body: { messages: [ { device_message_id, sender, body, timestamp }, ... ] }
     * timestamp = ISO8601 or "Y-m-d H:i:s" on device
     */
    public function batch(Request $request)
    {
        $request->validate([
            'messages' => 'required|array|min:1|max:100',
            'messages.*.sender' => 'required|string|max:50',
            'messages.*.body' => 'required|string|max:2000',
            'messages.*.timestamp' => 'required|string', // we'll parse
            'messages.*.device_message_id' => 'nullable|string|max:100',
            'messages.*.uuid' => 'nullable|string|max:50',
        ]);

        /** @var SmsDevice $device */
        $device = $request->attributes->get('device');
        $messages = $request->input('messages');
        $results = [];
        $created = 0;
        $duplicates = 0;
        $failed = 0;

        DB::beginTransaction();
        try {
            foreach ($messages as $idx => $msg) {
                $sender = trim($msg['sender']);
                $body = trim($msg['body']);
                $tsRaw = $msg['timestamp'];
                try {
                    $smsTs = Carbon::parse($tsRaw);
                } catch (\Exception $e) {
                    $smsTs = now();
                }
                $deviceMsgId = $msg['device_message_id'] ?? $msg['uuid'] ?? (string) Str::uuid();
                $uuid = $msg['uuid'] ?? (string) Str::uuid();

                // Compute dedup hash: device_code + sender + timestamp (normalized) + body
                // Use sms timestamp normalized to second
                $hash = SmsMessage::computeHash($device->device_code, $sender, $smsTs->format('Y-m-d H:i:s'), $body);

                // Check duplicate
                $existing = SmsMessage::where('hash', $hash)->first();
                if ($existing) {
                    $duplicates++;
                    $results[] = [
                        'index' => $idx,
                        'device_message_id' => $deviceMsgId,
                        'status' => 'DUPLICATE',
                        'server_id' => $existing->id,
                        'hash' => $hash,
                    ];
                    continue;
                }

                // Detect provider
                $parsed = SmsParserService::parse($sender, $body, $smsTs->toDateTimeString());
                $providerId = $parsed['provider_id'];
                $providerCode = $parsed['provider_code'];

                $sms = SmsMessage::create([
                    'uuid' => $uuid,
                    'device_message_id' => $deviceMsgId,
                    'device_id' => $device->id,
                    'provider_id' => $providerId,
                    'sender' => $sender,
                    'body' => $body,
                    'hash' => $hash,
                    'sms_timestamp' => $smsTs,
                    'received_at' => now(),
                    'sync_status' => 'SENT',
                    'processing_status' => 'PROCESSED',
                    'reconciliation_status' => 'UNRECONCILED',
                    'parsed_data' => $parsed,
                ]);

                // Create sms_transaction
                $smsTxn = SmsTransaction::create([
                    'sms_message_id' => $sms->id,
                    'device_id' => $device->id,
                    'provider_id' => $providerId,
                    'provider_code' => $providerCode,
                    'transaction_type' => $parsed['transaction_type'],
                    'amount' => $parsed['amount'],
                    'currency' => $parsed['currency'],
                    'reference' => $parsed['reference'],
                    'counterparty' => $parsed['counterparty'],
                    'counterparty_name' => $parsed['counterparty_name'] ?? null,
                    'balance' => $parsed['balance'],
                    'transaction_at' => $parsed['transaction_at'],
                    'raw_extracted' => $parsed['raw_extracted'],
                ]);

                // Create reconciliation row (auto match attempt)
                $match = $this->attemptAutoReconcile($smsTxn);
                $reconStatus = $match ? 'MATCHED' : 'UNRECONCILED';
                SmsReconciliation::create([
                    'sms_transaction_id' => $smsTxn->id,
                    'sms_message_id' => $sms->id,
                    'matched_transaction_id' => $match['transaction_id'] ?? null,
                    'matched_payout_id' => $match['payout_id'] ?? null,
                    'status' => $reconStatus,
                    'match_meta' => $match,
                ]);

                $sms->update(['reconciliation_status' => $reconStatus]);

                $created++;
                $results[] = [
                    'index' => $idx,
                    'device_message_id' => $deviceMsgId,
                    'status' => 'SENT',
                    'server_id' => $sms->id,
                    'uuid' => $sms->uuid,
                    'hash' => $hash,
                    'provider' => $providerCode,
                    'parsed' => [
                        'amount' => $parsed['amount'],
                        'reference' => $parsed['reference'],
                        'type' => $parsed['transaction_type'],
                    ],
                ];
            }

            $device->update(['last_sync_at' => now(), 'last_sms_at' => now()]);

            SmsSyncLog::create([
                'device_id' => $device->id,
                'action' => 'batch_upload',
                'request_payload' => ['count' => count($messages)],
                'response_payload' => ['created' => $created, 'duplicates' => $duplicates],
                'status' => 'success',
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Batch processing failed: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Batch processed: {$created} created, {$duplicates} duplicates.",
            'data' => [
                'created' => $created,
                'duplicates' => $duplicates,
                'failed' => $failed,
                'results' => $results,
            ]
        ]);
    }

    /**
     * POST /api/v1/sms  (single)
     */
    public function single(Request $request)
    {
        $request->validate([
            'sender' => 'required|string|max:50',
            'body' => 'required|string|max:2000',
            'timestamp' => 'required|string',
            'device_message_id' => 'nullable|string|max:100',
            'uuid' => 'nullable|string|max:50',
        ]);
        $request->merge(['messages' => [[
            'sender' => $request->sender,
            'body' => $request->body,
            'timestamp' => $request->timestamp,
            'device_message_id' => $request->device_message_id,
            'uuid' => $request->uuid,
        ]]]);
        return $this->batch($request);
    }

    /**
     * GET /api/v1/sms/{id}/status
     */
    public function smsStatus(Request $request, $id)
    {
        /** @var SmsDevice $device */
        $device = $request->attributes->get('device');
        $sms = SmsMessage::where('id', $id)->where('device_id', $device->id)->first();
        if (!$sms) return response()->json(['success' => false, 'message' => 'SMS not found'], 404);
        return response()->json(['success' => true, 'data' => $sms->load(['smsTransaction', 'reconciliation'])]);
    }

    /**
     * Simple auto-reconcile: try to match by reference or amount+counterparty within 24h
     */
    private function attemptAutoReconcile(SmsTransaction $smsTxn): ?array
    {
        if (!$smsTxn->reference && !$smsTxn->amount) return null;

        // Try exact reference match in transactions table
        if ($smsTxn->reference) {
            $txn = \App\Models\Transaction::where('order_reference', $smsTxn->reference)
                ->orWhere('transaction_id', $smsTxn->reference)->first();
            if ($txn) {
                return ['transaction_id' => $txn->id, 'matched_by' => 'reference', 'confidence' => 'high'];
            }
            $payout = \App\Models\Payout::where('order_reference', $smsTxn->reference)->first();
            if ($payout) {
                return ['payout_id' => $payout->id, 'matched_by' => 'reference', 'confidence' => 'high'];
            }
        }

        // Try amount + phone near transaction time
        if ($smsTxn->amount && $smsTxn->counterparty) {
            $phone = preg_replace('/\D/', '', $smsTxn->counterparty);
            $txn = \App\Models\Transaction::where('amount', $smsTxn->amount)
                ->where(function ($q) use ($phone) {
                    $q->where('phone', 'like', '%'.substr($phone, -9).'%');
                })
                ->whereBetween('created_at', [Carbon::parse($smsTxn->transaction_at)->subDay(), Carbon::parse($smsTxn->transaction_at)->addDay()])
                ->first();
            if ($txn) {
                return ['transaction_id' => $txn->id, 'matched_by' => 'amount_phone', 'confidence' => 'medium'];
            }
        }

        return null;
    }
}
