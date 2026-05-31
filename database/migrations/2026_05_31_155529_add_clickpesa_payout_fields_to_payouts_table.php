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
        Schema::table('payouts', function (Blueprint $table) {
            $table->string('channel')->nullable()->after('payout_type');
            $table->string('channel_provider')->nullable()->after('channel');
            $table->string('transfer_type')->nullable()->after('channel_provider');
            $table->decimal('fee', 15, 2)->default(0)->after('amount');
            $table->string('beneficiary_account_number')->nullable()->after('bic');
            $table->string('beneficiary_account_name')->nullable()->after('beneficiary_account_number');
            $table->string('beneficiary_mobile')->nullable()->after('beneficiary_account_name');
            $table->string('beneficiary_email')->nullable()->after('beneficiary_mobile');
            $table->text('notes')->nullable()->after('description');
            $table->string('clickpesa_payout_id')->nullable()->after('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn([
                'channel',
                'channel_provider',
                'transfer_type',
                'fee',
                'beneficiary_account_number',
                'beneficiary_account_name',
                'beneficiary_mobile',
                'beneficiary_email',
                'notes',
                'clickpesa_payout_id'
            ]);
        });
    }
};
