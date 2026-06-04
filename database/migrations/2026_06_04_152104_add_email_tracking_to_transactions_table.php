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
            $table->boolean('email_sent')->default(false)->after('sms_error');
            $table->text('email_message')->nullable()->after('email_sent');
            $table->timestamp('email_sent_at')->nullable()->after('email_message');
            $table->text('email_error')->nullable()->after('email_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['email_sent', 'email_message', 'email_sent_at', 'email_error']);
        });
    }
};
