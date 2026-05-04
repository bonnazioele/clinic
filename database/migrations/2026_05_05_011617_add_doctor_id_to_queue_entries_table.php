<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('queue_entries', 'doctor_id')) {
                $table->foreignId('doctor_id')
                    ->nullable()
                    ->after('patient_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        Schema::table('queue_entries', function (Blueprint $table) {
            try {
                $table->index(
                    ['doctor_id', 'clinic_id', 'status'],
                    'queue_entries_doctor_clinic_status_index'
                );
            } catch (\Throwable $e) {
                // Index may already exist. Ignore safely.
            }
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            try {
                $table->dropIndex('queue_entries_doctor_clinic_status_index');
            } catch (\Throwable $e) {
                // Index may not exist. Ignore safely.
            }

            if (Schema::hasColumn('queue_entries', 'doctor_id')) {
                $table->dropConstrainedForeignId('doctor_id');
            }
        });
    }
};