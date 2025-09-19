<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('clinics','queue_mode')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->string('queue_mode', 20)->default('fcfs')->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('clinics','queue_mode')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->dropColumn('queue_mode');
            });
        }
    }
};
