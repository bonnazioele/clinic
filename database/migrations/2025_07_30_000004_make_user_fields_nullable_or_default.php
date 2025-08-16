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
        Schema::table('users', function (Blueprint $table) {
            // Make fields nullable or provide defaults
            $table->integer('age')->nullable()->change();
            $table->date('birthdate')->nullable()->change();
            $table->boolean('is_active')->default(true)->change();
            $table->boolean('is_admin')->default(false)->change();
            $table->boolean('is_secretary')->default(false)->change();
            $table->timestamp('last_login')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert changes
            $table->integer('age')->nullable(false)->change();
            $table->date('birthdate')->nullable(false)->change();
            $table->boolean('is_active')->default(null)->change();
            $table->boolean('is_admin')->default(null)->change();
            $table->boolean('is_secretary')->default(null)->change();
            $table->timestamp('last_login')->nullable(false)->change();
        });
    }
};
