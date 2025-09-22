<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Prevent two non-cancelled appointments for same doctor/date/time.
            // Simpler unconditional unique; cancelled rows are rare and can be deleted if needed.
            $table->unique(['doctor_id','appointment_date','appointment_time'], 'appointments_doctor_date_time_unique');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appointments_doctor_date_time_unique');
        });
    }
};
