<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('queue_entries', 'user_id')) {
            try {
                Schema::table('queue_entries', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            } catch (\Throwable $e) {
                // Foreign key may already be missing. Continue safely.
            }

            try {
                DB::statement('ALTER TABLE queue_entries MODIFY user_id BIGINT UNSIGNED NULL');
            } catch (\Throwable $e) {
                try {
                    Schema::table('queue_entries', function (Blueprint $table) {
                        $table->foreignId('user_id')->nullable()->change();
                    });
                } catch (\Throwable $ignored) {
                    // Continue. Some DB engines handle this differently.
                }
            }

            try {
                Schema::table('queue_entries', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // Foreign key may already exist. Continue safely.
            }
        }

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
                $table->index(['doctor_id', 'clinic_id', 'status'], 'queue_entries_doctor_clinic_status_index');
            } catch (\Throwable $e) {
                // Index may already exist.
            }
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            try {
                $table->dropIndex('queue_entries_doctor_clinic_status_index');
            } catch (\Throwable $e) {
                // Index may not exist.
            }

            if (Schema::hasColumn('queue_entries', 'doctor_id')) {
                try {
                    $table->dropConstrainedForeignId('doctor_id');
                } catch (\Throwable $e) {
                    $table->dropColumn('doctor_id');
                }
            }
        });
    }
};