<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->boolean('is_recorded')->default(false)->after('reconciliation_status')->index();
            $table->timestamp('recorded_at')->nullable()->after('is_recorded');
            $table->foreignId('recorded_by')->nullable()->after('recorded_at')->constrained('users')->nullOnDelete();
            $table->text('admin_comment')->nullable()->after('recorded_by');
            $table->foreignId('comment_by')->nullable()->after('admin_comment')->constrained('users')->nullOnDelete();
            $table->timestamp('commented_at')->nullable()->after('comment_by');
        });
    }

    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropForeign(['recorded_by']);
            $table->dropForeign(['comment_by']);
            $table->dropColumn(['is_recorded','recorded_at','recorded_by','admin_comment','comment_by','commented_at']);
        });
    }
};
