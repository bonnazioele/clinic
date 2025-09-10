<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Global uniqueness for patient per date+time (ignore cancelled via application logic)
            $table->unique(['user_id','appointment_date','appointment_time'], 'appointments_user_date_time_unique');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appointments_user_date_time_unique');
        });
    }
};
