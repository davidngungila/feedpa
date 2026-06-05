<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get list of indexes for user_sessions table
        $indexes = DB::select("SHOW INDEX FROM user_sessions WHERE Key_name = 'user_sessions_user_id_unique'");
        
        if (!empty($indexes)) {
            // Drop unique index directly without touching foreign key
            DB::statement("ALTER TABLE user_sessions DROP INDEX user_sessions_user_id_unique");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if index doesn't exist before adding
        $indexes = DB::select("SHOW INDEX FROM user_sessions WHERE Key_name = 'user_sessions_user_id_unique'");
        
        if (empty($indexes)) {
            DB::statement("ALTER TABLE user_sessions ADD UNIQUE INDEX user_sessions_user_id_unique (user_id)");
        }
    }
};
