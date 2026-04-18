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
        Schema::table('transactions', function (Blueprint $table) {
            // Ensure all required columns exist
            if (!Schema::hasColumn('transactions', 'id')) {
                $table->uuid('id')->primary()->first();
            }
            
            if (!Schema::hasColumn('transactions', 'order_reference')) {
                $table->string('order_reference')->unique();
            }
            
            if (!Schema::hasColumn('transactions', 'transaction_id')) {
                $table->string('transaction_id')->nullable();
            }
            
            if (!Schema::hasColumn('transactions', 'status')) {
                $table->string('status');
            }
            
            if (!Schema::hasColumn('transactions', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable();
            }
            
            if (!Schema::hasColumn('transactions', 'currency')) {
                $table->string('currency', 3)->default('TZS');
            }
            
            if (!Schema::hasColumn('transactions', 'phone')) {
                $table->string('phone')->nullable();
            }
            
            if (!Schema::hasColumn('transactions', 'payer_name')) {
                $table->string('payer_name')->nullable();
            }
            
            if (!Schema::hasColumn('transactions', 'email')) {
                $table->string('email')->nullable();
            }
            
            if (!Schema::hasColumn('transactions', 'description')) {
                $table->text('description')->nullable();
            }
            
            if (!Schema::hasColumn('transactions', 'type')) {
                $table->string('type')->default('payment');
            }
            
            if (!Schema::hasColumn('transactions', 'payment_method')) {
                $table->string('payment_method')->nullable();
            }
            
            if (!Schema::hasColumn('transactions', 'callback_data')) {
                $table->json('callback_data')->nullable();
            }
            
            if (!Schema::hasColumn('transactions', 'callback_received_at')) {
                $table->timestamp('callback_received_at')->nullable();
            }
            
            if (!Schema::hasColumn('transactions', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            
            if (!Schema::hasColumn('transactions', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // This is a safety migration, so we won't drop columns in rollback
        });
    }
};
