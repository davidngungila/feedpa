<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // WhatsApp Phone-Assisted Gateway Messages (spec section 7)
        // Reuses sms_devices as gateway devices (MOSHI-01 etc) - no new device table needed
        // If you need separate WhatsApp-only devices, create whatsapp_gateway_devices instead
        if (!Schema::hasTable('whatsapp_messages')) {
            Schema::create('whatsapp_messages', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('device_id')->nullable()->constrained('sms_devices')->nullOnDelete(); // gateway device
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('recipient_phone', 20);
                $table->text('message')->nullable();
                // Attachment (private storage, not public)
                $table->string('attachment_path')->nullable(); // storage path like whatsapp_attachments/{uuid}/file.pdf
                $table->string('attachment_name')->nullable(); // original filename
                $table->string('attachment_mime')->nullable();
                $table->unsignedBigInteger('attachment_size')->nullable();
                $table->enum('status', [
                    'PENDING',
                    'QUEUED',
                    'DELIVERED_TO_DEVICE',
                    'OPENING',
                    'OPENED',
                    'USER_ACTION_REQUIRED',
                    'SENT',
                    'FAILED',
                    'CANCELLED',
                    'EXPIRED'
                ])->default('PENDING')->index();
                $table->timestamp('requested_at')->useCurrent();
                $table->timestamp('received_by_device_at')->nullable();
                $table->timestamp('opened_whatsapp_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->text('failure_reason')->nullable();
                $table->timestamps();
                $table->index(['device_id', 'status']);
                $table->index('recipient_phone');
            });
        }

        // Logs for audit (spec section 25)
        if (!Schema::hasTable('whatsapp_message_logs')) {
            Schema::create('whatsapp_message_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('whatsapp_message_id')->constrained('whatsapp_messages')->cascadeOnDelete();
                $table->foreignId('device_id')->nullable()->constrained('sms_devices')->nullOnDelete();
                $table->string('action'); // Request Created, Received by device, Attachment Downloaded, WhatsApp Open Requested, Opened, User Marked Sent, Failed, etc.
                $table->text('details')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->index(['whatsapp_message_id', 'action']);
            });
        }

        // Templates (spec section 19)
        if (!Schema::hasTable('whatsapp_templates')) {
            Schema::create('whatsapp_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->text('content');
                $table->json('variables')->nullable(); // e.g. ["customer_name","amount","reference"]
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_message_logs');
        Schema::dropIfExists('whatsapp_templates');
        Schema::dropIfExists('whatsapp_messages');
    }
};
