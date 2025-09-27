<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Only create if not already present (guards against partial previous runs / manual index addition)
        $driver = Schema::getConnection()->getDriverName();
        $exists = false;
        try {
            if ($driver === 'mysql') {
                $indexes = collect(DB::select('SHOW INDEX FROM `'.DB::getTablePrefix().'appointments`'))->pluck('Key_name');
                $exists = $indexes->contains('appointments_doctor_date_time_unique');
            } elseif ($driver === 'sqlite') {
                $indexes = collect(DB::select('PRAGMA index_list(appointments)'));
                $exists = $indexes->contains(function($row){ return isset($row->name) && $row->name === 'appointments_doctor_date_time_unique'; });
            }
    } catch (\Throwable $e) { /* inspection fail -> assume not exists */ }

        if (! $exists) {
            Schema::table('appointments', function (Blueprint $table) {
                // Prevent two non-cancelled appointments for same doctor/date/time.
                $table->unique(['doctor_id','appointment_date','appointment_time'], 'appointments_doctor_date_time_unique');
            });
        }
    }

    public function down(): void
    {
        // Some engines or later migrations might add FKs referencing the indexed columns;
        // attempt a guarded drop that won't explode on refresh.
        $connection = Schema::getConnection()->getDriverName();
        if ($connection === 'mysql') {
            // Inspect indexes
            $indexes = collect(DB::select('SHOW INDEX FROM `'.DB::getTablePrefix().'appointments`'))
                ->pluck('Key_name')
                ->unique();
            if ($indexes->contains('appointments_doctor_date_time_unique')) {
                try {
                    DB::statement('ALTER TABLE `'.DB::getTablePrefix().'appointments` DROP INDEX `appointments_doctor_date_time_unique`');
                } catch (Throwable $e) {
                    // swallow - index in use by FK or already removed
                }
            }
            return;
        }
        if ($connection === 'sqlite') {
            try { DB::statement('DROP INDEX IF EXISTS appointments_doctor_date_time_unique'); } catch (Throwable $e) {}
            return;
        }
        // Fallback: silent
    }
};
