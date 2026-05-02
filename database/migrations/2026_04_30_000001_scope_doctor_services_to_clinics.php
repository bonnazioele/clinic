<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doctor_service')) {
            return;
        }

        Schema::table('doctor_service', function (Blueprint $table) {
            if (! Schema::hasColumn('doctor_service', 'clinic_id')) {
                $table->foreignId('clinic_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('clinics')
                    ->cascadeOnDelete();
            }
        });

        /*
         * Important:
         * The old unique index doctor_id + service_id blocks the same doctor/service
         * from existing in multiple clinics. We must remove it before inserting
         * clinic-scoped rows.
         */

        $this->ensurePlainIndexExists('doctor_service', ['doctor_id'], 'doctor_service_doctor_id_plain_index');
        $this->ensurePlainIndexExists('doctor_service', ['service_id'], 'doctor_service_service_id_plain_index');

        $this->dropIndexIfExists('doctor_service', 'doctor_service_doctor_id_service_id_unique');

        $rows = DB::table('doctor_service')
            ->select('id', 'doctor_id', 'service_id', 'created_at', 'updated_at')
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $clinicIds = DB::table('clinic_doctor as cd')
                ->join('clinic_service as cs', function ($join) use ($row) {
                    $join->on('cs.clinic_id', '=', 'cd.clinic_id')
                        ->where('cs.service_id', '=', $row->service_id);
                })
                ->where('cd.doctor_id', $row->doctor_id)
                ->orderBy('cd.clinic_id')
                ->pluck('cd.clinic_id')
                ->unique()
                ->values();

            if ($clinicIds->isEmpty()) {
                DB::table('doctor_service')
                    ->where('id', $row->id)
                    ->delete();

                continue;
            }

            $firstClinicId = (int) $clinicIds->shift();

            DB::table('doctor_service')
                ->where('id', $row->id)
                ->update([
                    'clinic_id' => $firstClinicId,
                    'updated_at' => $row->updated_at ?? now(),
                ]);

            foreach ($clinicIds as $clinicId) {
                $exists = DB::table('doctor_service')
                    ->where('clinic_id', (int) $clinicId)
                    ->where('doctor_id', $row->doctor_id)
                    ->where('service_id', $row->service_id)
                    ->exists();

                if (! $exists) {
                    DB::table('doctor_service')->insert([
                        'clinic_id' => (int) $clinicId,
                        'doctor_id' => $row->doctor_id,
                        'service_id' => $row->service_id,
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => $row->updated_at ?? now(),
                    ]);
                }
            }
        }

        DB::table('doctor_service')
            ->whereNull('clinic_id')
            ->delete();

        $this->dropIndexIfExists('doctor_service', 'doctor_service_clinic_doctor_service_unique');

        Schema::table('doctor_service', function (Blueprint $table) {
            $table->unique(
                ['clinic_id', 'doctor_id', 'service_id'],
                'doctor_service_clinic_doctor_service_unique'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('doctor_service')) {
            return;
        }

        $this->dropIndexIfExists('doctor_service', 'doctor_service_clinic_doctor_service_unique');

        if (Schema::hasColumn('doctor_service', 'clinic_id')) {
            $seen = [];

            DB::table('doctor_service')
                ->select('id', 'doctor_id', 'service_id')
                ->orderBy('id')
                ->get()
                ->each(function ($row) use (&$seen) {
                    $key = $row->doctor_id . ':' . $row->service_id;

                    if (isset($seen[$key])) {
                        DB::table('doctor_service')
                            ->where('id', $row->id)
                            ->delete();

                        return;
                    }

                    $seen[$key] = true;
                });

            Schema::table('doctor_service', function (Blueprint $table) {
                $table->dropConstrainedForeignId('clinic_id');
            });
        }

        $this->dropIndexIfExists('doctor_service', 'doctor_service_doctor_id_service_id_unique');

        Schema::table('doctor_service', function (Blueprint $table) {
            $table->unique(
                ['doctor_id', 'service_id'],
                'doctor_service_doctor_id_service_id_unique'
            );
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $exists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();

        if ($exists) {
            DB::statement("ALTER TABLE `$table` DROP INDEX `$indexName`");
        }
    }

    private function ensurePlainIndexExists(string $table, array $columns, string $indexName): void
    {
        $exists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();

        if (! $exists) {
            $columnList = collect($columns)
                ->map(fn ($column) => "`{$column}`")
                ->implode(', ');

            DB::statement("ALTER TABLE `$table` ADD INDEX `$indexName` ($columnList)");
        }
    }
};