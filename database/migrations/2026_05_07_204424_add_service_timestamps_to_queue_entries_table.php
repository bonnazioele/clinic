<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('queue_entries', 'service_started_at')) {
                $table->timestamp('service_started_at')->nullable()->after('served_at');
            }

            if (! Schema::hasColumn('queue_entries', 'service_ended_at')) {
                $table->timestamp('service_ended_at')->nullable()->after('service_started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (Schema::hasColumn('queue_entries', 'service_ended_at')) {
                $table->dropColumn('service_ended_at');
            }

            if (Schema::hasColumn('queue_entries', 'service_started_at')) {
                $table->dropColumn('service_started_at');
            }
        });
    }
};