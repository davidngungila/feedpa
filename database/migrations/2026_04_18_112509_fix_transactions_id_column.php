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
            // First, drop the existing id column if it's not properly set up
            if (Schema::hasColumn('transactions', 'id')) {
                $table->dropColumn('id');
            }
            
            // Add proper UUID column
            $table->uuid('id')->primary()->first();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('id');
            // Add back a regular string id for rollback
            $table->string('id')->primary()->first();
        });
    }
};
