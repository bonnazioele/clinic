<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('doctor_schedules')) {
            return;
        }

        Schema::table('doctor_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('doctor_schedules', 'start_date')) {
                $table->date('start_date')->nullable()->after('day_of_week');
            }

            if (!Schema::hasColumn('doctor_schedules', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });

        try {
            Schema::table('doctor_schedules', function (Blueprint $table) {
                $table->dropUnique('doctor_schedule_unique');
            });
        } catch (\Throwable $e) {
            // Index may already be missing in some environments.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('doctor_schedules')) {
            return;
        }

        Schema::table('doctor_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('doctor_schedules', 'end_date')) {
                $table->dropColumn('end_date');
            }

            if (Schema::hasColumn('doctor_schedules', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });

        try {
            Schema::table('doctor_schedules', function (Blueprint $table) {
                $table->unique(['doctor_id', 'clinic_id', 'day_of_week', 'start_time', 'end_time'], 'doctor_schedule_unique');
            });
        } catch (\Throwable $e) {
            // Recreating the original unique index may fail if duplicates exist.
        }
    }
};
