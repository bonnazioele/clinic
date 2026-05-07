<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'queue_entries')
            ->where('index_name', $index)
            ->exists();
    }

    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('queue_entries', 'doctor_id')) {
                $table->foreignId('doctor_id')
                    ->nullable()
                    ->after('patient_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        if (! $this->indexExists('queue_entries_doctor_clinic_status_index')) {
            Schema::table('queue_entries', function (Blueprint $table) {
                $table->index(
                    ['doctor_id', 'clinic_id', 'status'],
                    'queue_entries_doctor_clinic_status_index'
                );
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('queue_entries_doctor_clinic_status_index')) {
            Schema::table('queue_entries', function (Blueprint $table) {
                $table->dropIndex('queue_entries_doctor_clinic_status_index');
            });
        }

        Schema::table('queue_entries', function (Blueprint $table) {
            if (Schema::hasColumn('queue_entries', 'doctor_id')) {
                $table->dropConstrainedForeignId('doctor_id');
            }
        });
    }
};
