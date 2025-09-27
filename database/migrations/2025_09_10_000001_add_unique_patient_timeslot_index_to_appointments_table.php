<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Guard against duplicate creation if index already exists (e.g., on partial previous run)
        $driver = Schema::getConnection()->getDriverName();
        $exists = false;
        try {
            if ($driver === 'mysql') {
                $indexes = collect(DB::select('SHOW INDEX FROM `'.DB::getTablePrefix().'appointments`'))->pluck('Key_name');
                $exists = $indexes->contains('appointments_user_date_time_unique');
            } elseif ($driver === 'sqlite') {
                // PRAGMA index_list returns list of indexes
                $indexes = collect(DB::select('PRAGMA index_list(appointments)')); // each has name property
                $exists = $indexes->contains(function($row){ return isset($row->name) && $row->name === 'appointments_user_date_time_unique'; });
            }
        } catch (\Throwable $e) {
            // If inspection fails, fall back to attempting creation (will error only if truly duplicate)
        }

        if (! $exists) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->unique(['user_id','appointment_date','appointment_time'], 'appointments_user_date_time_unique');
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            try {
                $indexes = collect(DB::select('SHOW INDEX FROM `'.DB::getTablePrefix().'appointments`'))->pluck('Key_name');
                if ($indexes->contains('appointments_user_date_time_unique')) {
                    DB::statement('ALTER TABLE `'.DB::getTablePrefix().'appointments` DROP INDEX `appointments_user_date_time_unique`');
                }
            } catch (Throwable $e) { /* swallow */ }
            return;
        }
        if ($driver === 'sqlite') {
            try { DB::statement('DROP INDEX IF EXISTS appointments_user_date_time_unique'); } catch (Throwable $e) {}
            return;
        }
    }
};
