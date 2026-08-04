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
            $table->boolean('whatsapp_sent')->default(false)->after('email_sent');
            $table->text('whatsapp_message')->nullable()->after('whatsapp_sent');
            $table->timestamp('whatsapp_sent_at')->nullable()->after('whatsapp_message');
            $table->text('whatsapp_error')->nullable()->after('whatsapp_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_sent', 'whatsapp_message', 'whatsapp_sent_at', 'whatsapp_error']);
        });
    }
};
