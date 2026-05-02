<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('doctor_schedules', 'schedule_type')) {
            Schema::table('doctor_schedules', function (Blueprint $table) {
                $table->enum('schedule_type', ['recurring', 'one_time'])->default('recurring')->after('clinic_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('doctor_schedules', 'schedule_type')) {
            Schema::table('doctor_schedules', function (Blueprint $table) {
                $table->dropColumn('schedule_type');
            });
        }
    }
};
