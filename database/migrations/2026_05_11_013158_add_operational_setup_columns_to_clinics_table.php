<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (! Schema::hasColumn('clinics', 'operational_hours_configured')) {
                $table->boolean('operational_hours_configured')->default(false)->after('queue_mode');
            }

            if (! Schema::hasColumn('clinics', 'setup_completed_at')) {
                $table->timestamp('setup_completed_at')->nullable()->after('operational_hours_configured');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (Schema::hasColumn('clinics', 'setup_completed_at')) {
                $table->dropColumn('setup_completed_at');
            }

            if (Schema::hasColumn('clinics', 'operational_hours_configured')) {
                $table->dropColumn('operational_hours_configured');
            }
        });
    }
};
