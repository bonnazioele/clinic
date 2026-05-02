<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('doctor_schedules', 'service_id')) {
            Schema::table('doctor_schedules', function (Blueprint $table) {
                $table->foreignId('service_id')->nullable()->constrained('services')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('doctor_schedules', 'service_id')) {
            Schema::table('doctor_schedules', function (Blueprint $table) {
                $table->dropForeignKey(['service_id']);
                $table->dropColumn('service_id');
            });
        }
    }
};
