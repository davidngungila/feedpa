<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->string('workflow_stage')->default('INITIATION_OTP')->after('status');
            $table->foreignId('initiated_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('initiated_at')->nullable()->after('initiated_by');
            $table->foreignId('initiation_verified_by')->nullable()->after('initiated_at')->constrained('users')->nullOnDelete();
            $table->timestamp('initiation_verified_at')->nullable()->after('initiation_verified_by');
            $table->foreignId('approved_by')->nullable()->after('initiation_verified_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            $table->foreignId('payment_otp_requested_by')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('payment_otp_requested_at')->nullable()->after('payment_otp_requested_by');
            $table->foreignId('payment_authorized_by')->nullable()->after('payment_otp_requested_at')->constrained('users')->nullOnDelete();
            $table->timestamp('payment_authorized_at')->nullable()->after('payment_authorized_by');
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_authorized_by');
            $table->dropColumn('payment_authorized_at');
            $table->dropConstrainedForeignId('payment_otp_requested_by');
            $table->dropColumn('payment_otp_requested_at');
            $table->dropColumn('rejection_reason');
            $table->dropColumn('rejected_at');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn('approved_at');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('initiation_verified_at');
            $table->dropConstrainedForeignId('initiation_verified_by');
            $table->dropColumn('initiated_at');
            $table->dropConstrainedForeignId('initiated_by');
            $table->dropColumn('workflow_stage');
        });
    }
};
