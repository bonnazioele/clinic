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

    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', $database)
            ->where('table_name', $table)
            ->where('constraint_name', $foreignKey)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }

    public function up(): void
    {
        if (! Schema::hasTable('doctor_service')) {
            return;
        }

        /*
         * Add normal indexes only if they do not already exist.
         * This prevents duplicate index errors when the migration is retried.
         */
        Schema::table('doctor_service', function (Blueprint $table) {
            if (! $this->indexExists('doctor_service', 'doctor_service_doctor_id_index')) {
                $table->index('doctor_id', 'doctor_service_doctor_id_index');
            }

            if (! $this->indexExists('doctor_service', 'doctor_service_service_id_index')) {
                $table->index('service_id', 'doctor_service_service_id_index');
            }

            if (! $this->indexExists('doctor_service', 'doctor_service_clinic_id_index')) {
                $table->index('clinic_id', 'doctor_service_clinic_id_index');
            }
        });

        /*
         * Drop the old wrong unique index only if it exists.
         * Your error happened because this index was missing.
         */
        if ($this->indexExists('doctor_service', 'doctor_service_doctor_id_service_id_unique')) {
            Schema::table('doctor_service', function (Blueprint $table) {
                $table->dropUnique('doctor_service_doctor_id_service_id_unique');
            });
        }

        /*
         * Add the correct unique index for:
         * one doctor + one service + one clinic.
         *
         * This allows the same doctor/service pair in different clinics.
         */
        if (! $this->indexExists('doctor_service', 'doctor_service_doctor_service_clinic_unique')) {
            Schema::table('doctor_service', function (Blueprint $table) {
                $table->unique(
                    ['doctor_id', 'service_id', 'clinic_id'],
                    'doctor_service_doctor_service_clinic_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('doctor_service')) {
            return;
        }

        if ($this->indexExists('doctor_service', 'doctor_service_doctor_service_clinic_unique')) {
            Schema::table('doctor_service', function (Blueprint $table) {
                $table->dropUnique('doctor_service_doctor_service_clinic_unique');
            });
        }

        if (! $this->indexExists('doctor_service', 'doctor_service_doctor_id_service_id_unique')) {
            Schema::table('doctor_service', function (Blueprint $table) {
                $table->unique(
                    ['doctor_id', 'service_id'],
                    'doctor_service_doctor_id_service_id_unique'
                );
            });
        }

        Schema::table('doctor_service', function (Blueprint $table) {
            if ($this->indexExists('doctor_service', 'doctor_service_doctor_id_index')) {
                $table->dropIndex('doctor_service_doctor_id_index');
            }

            if ($this->indexExists('doctor_service', 'doctor_service_service_id_index')) {
                $table->dropIndex('doctor_service_service_id_index');
            }

            if ($this->indexExists('doctor_service', 'doctor_service_clinic_id_index')) {
                $table->dropIndex('doctor_service_clinic_id_index');
            }
        });
    }
};