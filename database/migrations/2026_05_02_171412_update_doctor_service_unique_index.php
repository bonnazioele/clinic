<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_service', function (Blueprint $table) {
            /*
             * Drop old wrong unique index:
             * doctor_id + service_id
             */
            $table->dropUnique('doctor_service_doctor_id_service_id_unique');
        });

        Schema::table('doctor_service', function (Blueprint $table) {
            /*
             * Add correct multi-clinic unique index:
             * doctor_id + service_id + clinic_id
             */
            $table->unique(
                ['doctor_id', 'service_id', 'clinic_id'],
                'doctor_service_doctor_service_clinic_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('doctor_service', function (Blueprint $table) {
            $table->dropUnique('doctor_service_doctor_service_clinic_unique');
        });

        Schema::table('doctor_service', function (Blueprint $table) {
            $table->unique(
                ['doctor_id', 'service_id'],
                'doctor_service_doctor_id_service_id_unique'
            );
        });
    }
};