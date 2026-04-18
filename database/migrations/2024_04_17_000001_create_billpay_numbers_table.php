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
        Schema::create('billpay_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('bill_pay_number')->unique();
            $table->string('bill_description');
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->string('bill_currency', 3)->default('TZS');
            $table->enum('bill_payment_mode', ['ALLOW_PARTIAL_AND_OVER_PAYMENT', 'EXACT'])->default('ALLOW_PARTIAL_AND_OVER_PAYMENT');
            $table->enum('bill_status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->enum('bill_type', ['order', 'customer'])->default('order');
            
            // Customer information (for customer type)
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            
            // Additional fields
            $table->string('bill_reference')->nullable();
            $table->text('notes')->nullable();
            
            // Metadata
            $table->string('created_by')->nullable();
            $table->timestamp('last_payment_at')->nullable();
            $table->decimal('total_paid', 10, 2)->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('bill_status');
            $table->index('bill_type');
            $table->index('created_at');
            $table->index('customer_email');
            $table->index('customer_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billpay_numbers');
    }
};
