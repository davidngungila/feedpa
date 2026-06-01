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
        Schema::table('audits', function (Blueprint $table) {
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('timezone')->nullable();
            $table->string('device_type')->nullable(); // e.g., 'desktop', 'mobile', 'tablet'
            $table->string('device_browser')->nullable(); // e.g., 'Chrome', 'Firefox'
            $table->string('device_platform')->nullable(); // e.g., 'Windows', 'iOS'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropColumn([
                'country',
                'city',
                'timezone',
                'device_type',
                'device_browser',
                'device_platform'
            ]);
        });
    }
};
