<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // MySQL: inspect and drop then add composite indexes
            $existing = collect(DB::select("SHOW INDEX FROM `".DB::getTablePrefix()."clinics`"))
                ->groupBy('Key_name');
            if ($existing->has('clinics_email_unique')) {
                DB::statement('ALTER TABLE `'.DB::getTablePrefix().'clinics` DROP INDEX `clinics_email_unique`');
            }
            if ($existing->has('clinics_branch_code_unique')) {
                DB::statement('ALTER TABLE `'.DB::getTablePrefix().'clinics` DROP INDEX `clinics_branch_code_unique`');
            }
            Schema::table('clinics', function (Blueprint $table) {
                if (!Schema::hasColumn('clinics','deleted_at')) {
                    $table->softDeletes();
                }
                $table->unique(['email','deleted_at'], 'clinics_email_deleted_at_unique');
                $table->unique(['branch_code','deleted_at'], 'clinics_branch_code_deleted_at_unique');
            });
            return; // done for mysql
        }

        if ($driver === 'sqlite') {
            // SQLite: use raw DROP INDEX & create composite
            DB::statement('DROP INDEX IF EXISTS clinics_email_unique');
            DB::statement('DROP INDEX IF EXISTS clinics_branch_code_unique');
            // ensure deleted_at exists
            if (!Schema::hasColumn('clinics','deleted_at')) {
                Schema::table('clinics', function(Blueprint $table){ $table->softDeletes(); });
            }
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS clinics_email_deleted_at_unique ON clinics(email, deleted_at)');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS clinics_branch_code_deleted_at_unique ON clinics(branch_code, deleted_at)');
            return;
        }

        // Other drivers: best effort using schema builder only (may fail silently if not supported)
        try {
            Schema::table('clinics', function (Blueprint $table) {
                if (!Schema::hasColumn('clinics','deleted_at')) {
                    $table->softDeletes();
                }
                $table->unique(['email','deleted_at']);
                $table->unique(['branch_code','deleted_at']);
            });
        } catch (\Throwable $e) {
            // ignore for unsupported drivers
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            $existing = collect(DB::select("SHOW INDEX FROM `".DB::getTablePrefix()."clinics`"))->pluck('Key_name');
            if ($existing->contains('clinics_email_deleted_at_unique')) {
                DB::statement('ALTER TABLE `'.DB::getTablePrefix().'clinics` DROP INDEX `clinics_email_deleted_at_unique`');
            }
            if ($existing->contains('clinics_branch_code_deleted_at_unique')) {
                DB::statement('ALTER TABLE `'.DB::getTablePrefix().'clinics` DROP INDEX `clinics_branch_code_deleted_at_unique`');
            }
            DB::statement('ALTER TABLE `'.DB::getTablePrefix().'clinics` ADD UNIQUE `clinics_email_unique`(`email`)');
            DB::statement('ALTER TABLE `'.DB::getTablePrefix().'clinics` ADD UNIQUE `clinics_branch_code_unique`(`branch_code`)');
            return;
        }
        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS clinics_email_deleted_at_unique');
            DB::statement('DROP INDEX IF EXISTS clinics_branch_code_deleted_at_unique');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS clinics_email_unique ON clinics(email)');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS clinics_branch_code_unique ON clinics(branch_code)');
            return;
        }
    }
};
