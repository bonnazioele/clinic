<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_operational_hours', function (Blueprint $table) {
            if (! Schema::hasColumn('clinic_operational_hours', 'is_24_hours')) {
                $table->boolean('is_24_hours')->default(false)->after('is_open');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinic_operational_hours', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_operational_hours', 'is_24_hours')) {
                $table->dropColumn('is_24_hours');
            }
        });
    }
};
