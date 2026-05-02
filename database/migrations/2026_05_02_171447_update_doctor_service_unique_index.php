<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    private function indexExists(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }

    public function up(): void
    {
        /*
         * Add normal indexes first so foreign keys no longer depend
         * on the old doctor_id + service_id unique index.
         */
        Schema::table('doctor_service', function (Blueprint $table) {
            $table->index('doctor_id', 'doctor_service_doctor_id_index');
            $table->index('service_id', 'doctor_service_service_id_index');
            $table->index('clinic_id', 'doctor_service_clinic_id_index');
        });

        /*
         * Now it is safe to drop the old wrong unique index.
         */
        Schema::table('doctor_service', function (Blueprint $table) {
            $table->dropUnique('doctor_service_doctor_id_service_id_unique');
        });

        /*
         * Add the correct unique index for multi-clinic doctors.
         */
        Schema::table('doctor_service', function (Blueprint $table) {
            $table->unique(
                ['doctor_id', 'service_id', 'clinic_id'],
                'doctor_service_doctor_service_clinic_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('doctor_service', function (Blueprint $table) {
            $table->dropUnique('doctor_service_doctor_service_clinic_unique');
        });

        Schema::table('doctor_service', function (Blueprint $table) {
            $table->unique(
                ['doctor_id', 'service_id'],
                'doctor_service_doctor_id_service_id_unique'
            );
        });

        Schema::table('doctor_service', function (Blueprint $table) {
            $table->dropIndex('doctor_service_doctor_id_index');
            $table->dropIndex('doctor_service_service_id_index');
            $table->dropIndex('doctor_service_clinic_id_index');
        });
    }
};
