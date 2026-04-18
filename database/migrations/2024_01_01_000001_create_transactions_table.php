<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_reference')->unique();
            $table->string('transaction_id')->nullable();
            $table->string('status');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('TZS');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->default('payment'); // payment, payout, billpay
            $table->string('payment_method')->nullable();
            $table->json('callback_data')->nullable();
            $table->timestamp('callback_received_at')->nullable();
            $table->timestamps();

            $table->index(['order_reference']);
            $table->index(['status']);
            $table->index(['type']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
