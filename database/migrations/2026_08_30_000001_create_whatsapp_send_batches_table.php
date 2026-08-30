<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_send_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('status')->default('processing');
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('sent')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->timestamp('next_available_at')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_send_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id')->index();
            $table->string('recipient_type');
            $table->string('recipient');
            $table->string('recipient_name')->nullable();
            $table->longText('payload');
            $table->string('status')->default('pending')->index();
            $table->text('message')->nullable();
            $table->text('result')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_send_jobs');
        Schema::dropIfExists('whatsapp_send_batches');
    }
};