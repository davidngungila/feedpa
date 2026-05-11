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
            $table->boolean('sms_sent')->default(false)->after('callback_received_at');
            $table->text('sms_message')->nullable()->after('sms_sent');
            $table->timestamp('sms_sent_at')->nullable()->after('sms_message');
            $table->text('sms_error')->nullable()->after('sms_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['sms_sent', 'sms_message', 'sms_sent_at', 'sms_error']);
        });
    }
};
