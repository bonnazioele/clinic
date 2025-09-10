<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('doctor_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('doctor_schedules','is_active')) {
                $table->boolean('is_active')->default(true)->after('end_time');
            }
        });
    }
    public function down(): void
    {
        Schema::table('doctor_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('doctor_schedules','is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
