<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('queue_entries', 'doctor_completed_at')) {
                $table->timestamp('doctor_completed_at')->nullable()->after('service_ended_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (Schema::hasColumn('queue_entries', 'doctor_completed_at')) {
                $table->dropColumn('doctor_completed_at');
            }
        });
    }
};
