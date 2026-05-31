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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_reference')->unique();
            $table->string('transaction_id')->nullable()->unique();
            $table->string('status')->default('PENDING');
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('TZS');
            $table->string('payout_type')->default('MOBILE_MONEY'); // MOBILE_MONEY or BANK
            $table->string('recipient_name');
            $table->string('recipient_phone')->nullable(); // For mobile money
            $table->string('bank_account_number')->nullable(); // For bank
            $table->string('bank_name')->nullable(); // For bank
            $table->string('bic')->nullable(); // For bank
            $table->string('description')->nullable();
            $table->json('callback_data')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
