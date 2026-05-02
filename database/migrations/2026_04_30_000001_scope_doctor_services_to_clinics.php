<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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

        try {
            Schema::table('doctor_service', function (Blueprint $table) {
                $table->dropUnique('doctor_service_doctor_id_service_id_unique');
            });
        } catch (Throwable $e) {
            // Some environments may already have a different or missing index.
        }

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
                DB::table('doctor_service')->where('id', $row->id)->delete();
                continue;
            }

            $firstClinicId = (int) $clinicIds->shift();
            DB::table('doctor_service')
                ->where('id', $row->id)
                ->update(['clinic_id' => $firstClinicId]);

            foreach ($clinicIds as $clinicId) {
                $exists = DB::table('doctor_service')
                    ->where('clinic_id', $clinicId)
                    ->where('doctor_id', $row->doctor_id)
                    ->where('service_id', $row->service_id)
                    ->exists();

                if (! $exists) {
                    DB::table('doctor_service')->insert([
                        'clinic_id' => (int) $clinicId,
                        'doctor_id' => $row->doctor_id,
                        'service_id' => $row->service_id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                }
            }
        }

        DB::table('doctor_service')->whereNull('clinic_id')->delete();

        try {
            Schema::table('doctor_service', function (Blueprint $table) {
                $table->unique(['clinic_id', 'doctor_id', 'service_id'], 'doctor_service_clinic_doctor_service_unique');
            });
        } catch (Throwable $e) {
            // Leave migration idempotent for partially migrated databases.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('doctor_service') || ! Schema::hasColumn('doctor_service', 'clinic_id')) {
            return;
        }

        try {
            Schema::table('doctor_service', function (Blueprint $table) {
                $table->dropUnique('doctor_service_clinic_doctor_service_unique');
            });
        } catch (Throwable $e) {
            // Index may be absent in a partially rolled-back database.
        }

        $seen = [];
        DB::table('doctor_service')
            ->select('id', 'doctor_id', 'service_id')
            ->orderBy('id')
            ->get()
            ->each(function ($row) use (&$seen) {
                $key = $row->doctor_id.':'.$row->service_id;

                if (isset($seen[$key])) {
                    DB::table('doctor_service')->where('id', $row->id)->delete();
                    return;
                }

                $seen[$key] = true;
            });

        Schema::table('doctor_service', function (Blueprint $table) {
            $table->dropConstrainedForeignId('clinic_id');
        });

        try {
            Schema::table('doctor_service', function (Blueprint $table) {
                $table->unique(['doctor_id', 'service_id']);
            });
        } catch (Throwable $e) {
            // Recreating the legacy index may fail if a database still has duplicates.
        }
    }
};
