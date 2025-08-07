<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //schema to drop the secretary_n_doctor column from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_secretary', 'is_doctor']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_secretary')->default(false);
            $table->boolean('is_doctor')->default(false);
        });
    }
};
