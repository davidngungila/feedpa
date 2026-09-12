<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -----------------------------------------------------------------
        // Locations (Moshi, Tabora, Arusha ...)
        // -----------------------------------------------------------------
        Schema::create('sms_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Moshi
            $table->string('code')->unique(); // MOSHI
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // -----------------------------------------------------------------
        // SMS Providers (M-Pesa, Airtel Money, Mixx, HaloPesa ...)
        // -----------------------------------------------------------------
        Schema::create('sms_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // M-Pesa
            $table->string('code')->unique(); // MPESA
            $table->string('sender_ids')->nullable(); // comma separated sender names/numbers for detection e.g. "MPesa,AirtelMoney"
            $table->text('detection_keywords')->nullable(); // JSON array for auto detection
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // -----------------------------------------------------------------
        // Devices (MOSHI-01, TABORA-01)
        // -----------------------------------------------------------------
        Schema::create('sms_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_code')->unique(); // MOSHI-01
            $table->string('name'); // Moshi Main Phone
            $table->foreignId('location_id')->nullable()->constrained('sms_locations')->nullOnDelete();
            $table->string('phone_number')->nullable(); // with SIM
            $table->string('sim_slot')->nullable(); // SIM1 / SIM2
            $table->string('sim_operator')->nullable();
            $table->string('activation_code', 20)->nullable()->unique(); // 6 digit code for pairing
            $table->timestamp('activation_expires_at')->nullable();
            $table->enum('status', ['PENDING', 'ACTIVE', 'SUSPENDED', 'REVOKED'])->default('PENDING');
            $table->string('android_version')->nullable();
            $table->string('app_version')->nullable();
            $table->integer('battery_level')->nullable(); // 0..100
            $table->string('network_type')->nullable(); // 4G, WiFi
            $table->string('signal_strength')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_sms_at')->nullable();
            $table->json('config')->nullable(); // remote config JSON
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'location_id']);
        });

        // Device auth tokens (per device, revocable)
        Schema::create('sms_device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('sms_devices')->cascadeOnDelete();
            $table->string('token', 80)->unique(); // sha256 hash
            $table->string('plain_hint', 8)->nullable(); // last 4 chars for display
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->timestamps();
            $table->index('device_id');
        });

        // Device heartbeats history
        Schema::create('sms_device_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('sms_devices')->cascadeOnDelete();
            $table->integer('battery_level')->nullable();
            $table->string('network_type')->nullable();
            $table->integer('pending_sms')->default(0);
            $table->string('app_version')->nullable();
            $table->string('android_version')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['device_id', 'created_at']);
        });

        // Device logs
        Schema::create('sms_device_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('sms_devices')->cascadeOnDelete();
            $table->string('level')->default('info'); // info, warn, error
            $table->string('message');
            $table->json('context')->nullable();
            $table->timestamps();
        });

        // -----------------------------------------------------------------
        // SMS Messages - central store
        // -----------------------------------------------------------------
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique(); // client generated id or server uuid
            $table->string('device_message_id')->nullable(); // id from device local DB
            $table->foreignId('device_id')->constrained('sms_devices')->cascadeOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('sms_providers')->nullOnDelete();
            $table->string('sender'); // 074xxxx or MPesa
            $table->text('body');
            $table->string('hash', 64)->unique(); // sha256(device_id+sender+timestamp+body) for dedup
            $table->timestamp('sms_timestamp'); // original sms time on device
            $table->timestamp('received_at'); // when server received
            $table->enum('sync_status', ['RECEIVED', 'PENDING', 'PROCESSING', 'SENT', 'FAILED'])->default('RECEIVED');
            $table->enum('processing_status', ['UNPROCESSED', 'PROCESSING', 'PROCESSED', 'FAILED', 'DUPLICATE', 'IGNORED'])->default('UNPROCESSED');
            $table->enum('reconciliation_status', ['UNRECONCILED', 'MATCHED', 'RECONCILED', 'DUPLICATE', 'REVERSED', 'FAILED'])->default('UNRECONCILED');
            $table->json('parsed_data')->nullable(); // extracted amount, reference, etc
            $table->string('failure_reason')->nullable();
            $table->timestamps();
            $table->index(['device_id', 'sender', 'sms_timestamp']);
            $table->index(['sync_status', 'processing_status']);
            $table->index('hash');
        });

        // SMS sync attempts log
        Schema::create('sms_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_message_id')->nullable()->constrained('sms_messages')->nullOnDelete();
            $table->foreignId('device_id')->constrained('sms_devices')->cascadeOnDelete();
            $table->string('action'); // batch_upload, single_upload, retry
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        // -----------------------------------------------------------------
        // SMS Parsing Rules (Rules Engine)
        // -----------------------------------------------------------------
        Schema::create('sms_parsing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->nullable()->constrained('sms_providers')->nullOnDelete();
            $table->string('name');
            $table->string('field')->nullable(); // amount, reference, sender, type etc
            $table->string('pattern'); // regex or contains string
            $table->enum('pattern_type', ['contains', 'regex', 'starts_with', 'ends_with'])->default('contains');
            $table->integer('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // -----------------------------------------------------------------
        // SMS Transactions (extracted financial transaction from SMS)
        // -----------------------------------------------------------------
        Schema::create('sms_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_message_id')->unique()->constrained('sms_messages')->cascadeOnDelete();
            $table->foreignId('device_id')->constrained('sms_devices')->cascadeOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('sms_providers')->nullOnDelete();
            $table->string('provider_code')->nullable(); // denormalized
            $table->enum('transaction_type', ['PAYMENT', 'DEPOSIT', 'WITHDRAWAL', 'TRANSFER', 'REVERSAL', 'BALANCE', 'FAILED', 'OTHER'])->default('PAYMENT');
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('currency', 10)->default('TZS');
            $table->string('reference')->nullable(); // MPX827362 - index defined below once
            $table->string('counterparty')->nullable(); // 074XXXXXXX sender of money
            $table->string('counterparty_name')->nullable();
            $table->decimal('balance', 15, 2)->nullable();
            $table->timestamp('transaction_at')->nullable();
            $table->json('raw_extracted')->nullable();
            $table->timestamps();
            $table->index(['provider_id', 'transaction_type']);
            $table->index('reference');
        });

        // -----------------------------------------------------------------
        // Reconciliations
        // -----------------------------------------------------------------
        Schema::create('sms_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_transaction_id')->constrained('sms_transactions')->cascadeOnDelete();
            $table->foreignId('sms_message_id')->constrained('sms_messages')->cascadeOnDelete();
            // transactions.id is UUID (char 36) per 2024_01_01 migration, so store as uuid string without FK
            $table->uuid('matched_transaction_id')->nullable()->index();
            $table->foreignId('matched_payout_id')->nullable()->constrained('payouts')->nullOnDelete();
            $table->enum('status', ['UNRECONCILED', 'MATCHED', 'RECONCILED', 'DUPLICATE', 'REVERSED', 'FAILED', 'MANUAL'])->default('UNRECONCILED');
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reconciled_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('match_meta')->nullable(); // confidence, rule used
            $table->timestamps();
            $table->index('status');
        });

        // -----------------------------------------------------------------
        // Indexes for performance done above
        // -----------------------------------------------------------------
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_reconciliations');
        Schema::dropIfExists('sms_transactions');
        Schema::dropIfExists('sms_parsing_rules');
        Schema::dropIfExists('sms_sync_logs');
        Schema::dropIfExists('sms_messages');
        Schema::dropIfExists('sms_device_logs');
        Schema::dropIfExists('sms_device_heartbeats');
        Schema::dropIfExists('sms_device_tokens');
        Schema::dropIfExists('sms_devices');
        Schema::dropIfExists('sms_providers');
        Schema::dropIfExists('sms_locations');
    }
};
