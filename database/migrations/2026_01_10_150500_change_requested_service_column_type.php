<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE patient_visits MODIFY requested_service VARCHAR(255) NOT NULL"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE patient_visits MODIFY requested_service ENUM('Consultation','Check-up','Medical Certificate','Laboratory','Vaccination') NOT NULL"
        );
    }
};
