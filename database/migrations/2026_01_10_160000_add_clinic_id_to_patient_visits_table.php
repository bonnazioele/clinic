<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_visits', function (Blueprint $table) {
            if (! Schema::hasColumn('patient_visits', 'clinic_id')) {
                $table->foreignId('clinic_id')
                    ->nullable()
                    ->after('patient_id')
                    ->constrained()
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_visits', function (Blueprint $table) {
            if (Schema::hasColumn('patient_visits', 'clinic_id')) {
                $table->dropConstrainedForeignId('clinic_id');
            }
        });
    }
};
