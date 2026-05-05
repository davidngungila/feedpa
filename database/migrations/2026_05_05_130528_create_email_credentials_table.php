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
        Schema::create('email_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('email_address')->unique();
            $table->string('password');
            $table->string('smtp_host');
            $table->integer('smtp_port');
            $table->string('encryption');
            $table->string('from_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_credentials');
    }
};
