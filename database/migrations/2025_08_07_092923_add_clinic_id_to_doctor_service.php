<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('doctor_service') && !Schema::hasTable('clinic_doctor_services')) {
            Schema::rename('doctor_service', 'clinic_doctor_services');
        }

        Schema::table('clinic_doctor_services', function (Blueprint $table) {
            $table->dropForeign('doctor_service_doctor_id_foreign');
            $table->foreign('doctor_id')->references('id')->on('doctors')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->unsignedInteger('duration')->default(30)->comment('Duration in minutes');
            $table->boolean('is_active')->default(true)->comment('Indicates if the service offered is active');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_doctor_services', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_doctor_services', 'clinic_id')) {
                $table->dropForeign(['clinic_id']);
                $table->dropColumn('clinic_id');
            }

            if (Schema::hasColumn('clinic_doctor_services', 'doctor_id')) {
                $table->dropForeign(['doctor_id']);
                $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete(); // Assuming original pointed to users
            }

            $table->dropColumn(['duration', 'is_active']);
        });

        Schema::rename('clinic_doctor_services', 'doctor_service');
    }
};
