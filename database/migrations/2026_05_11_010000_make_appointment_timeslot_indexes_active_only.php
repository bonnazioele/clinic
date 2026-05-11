<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $this->addMysqlPlainIndexIfMissing('appointments_user_id_index', ['user_id']);
            $this->addMysqlPlainIndexIfMissing('appointments_doctor_id_index', ['doctor_id']);
        }

        $this->dropIndexIfExists('appointments_doctor_date_time_unique');
        $this->dropIndexIfExists('appointments_user_date_time_unique');

        if ($driver === 'mysql') {
            $this->addMysqlGeneratedColumnIfMissing(
                'active_slot_enforcer',
                "CASE WHEN `status` NOT IN ('completed','cancelled','no_show','rescheduled') THEN 1 ELSE NULL END"
            );

            $this->addMysqlUniqueIndexIfMissing(
                'appointments_active_doctor_slot_unique',
                ['doctor_id', 'appointment_date', 'appointment_time', 'active_slot_enforcer']
            );

            $this->addMysqlUniqueIndexIfMissing(
                'appointments_active_user_slot_unique',
                ['user_id', 'appointment_date', 'appointment_time', 'active_slot_enforcer']
            );

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement("
                CREATE UNIQUE INDEX IF NOT EXISTS appointments_active_doctor_slot_unique
                ON appointments (doctor_id, appointment_date, appointment_time)
                WHERE status NOT IN ('completed','cancelled','no_show','rescheduled')
            ");

            DB::statement("
                CREATE UNIQUE INDEX IF NOT EXISTS appointments_active_user_slot_unique
                ON appointments (user_id, appointment_date, appointment_time)
                WHERE status NOT IN ('completed','cancelled','no_show','rescheduled')
            ");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        $this->dropIndexIfExists('appointments_active_doctor_slot_unique');
        $this->dropIndexIfExists('appointments_active_user_slot_unique');

        if ($driver === 'mysql') {
            $this->dropMysqlColumnIfExists('active_slot_enforcer');
        }

        try {
            if (! $this->indexExists('appointments_doctor_date_time_unique')) {
                Schema::table('appointments', function ($table) {
                    $table->unique(['doctor_id', 'appointment_date', 'appointment_time'], 'appointments_doctor_date_time_unique');
                });
            }

            if (! $this->indexExists('appointments_user_date_time_unique')) {
                Schema::table('appointments', function ($table) {
                    $table->unique(['user_id', 'appointment_date', 'appointment_time'], 'appointments_user_date_time_unique');
                });
            }
        } catch (\Throwable $e) {
            // Historical duplicate rows can make the old unconditional indexes impossible to restore.
        }
    }

    private function addMysqlGeneratedColumnIfMissing(string $column, string $expression): void
    {
        if ($this->columnExists($column)) {
            return;
        }

        DB::statement("
            ALTER TABLE `appointments`
            ADD COLUMN `{$column}` TINYINT
            GENERATED ALWAYS AS ({$expression}) STORED
        ");
    }

    private function addMysqlUniqueIndexIfMissing(string $index, array $columns): void
    {
        if ($this->indexExists($index)) {
            return;
        }

        $this->addMysqlIndex($index, $columns, true);
    }

    private function addMysqlPlainIndexIfMissing(string $index, array $columns): void
    {
        if ($this->indexExists($index)) {
            return;
        }

        $this->addMysqlIndex($index, $columns, false);
    }

    private function addMysqlIndex(string $index, array $columns, bool $unique): void
    {
        $columns = collect($columns)
            ->map(fn (string $column) => "`{$column}`")
            ->join(', ');

        $type = $unique ? 'UNIQUE INDEX' : 'INDEX';

        DB::statement("ALTER TABLE `appointments` ADD {$type} `{$index}` ({$columns})");
    }

    private function dropIndexIfExists(string $index): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! $this->indexExists($index)) {
            return;
        }

        try {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `appointments` DROP INDEX `{$index}`");
                return;
            }

            if ($driver === 'sqlite') {
                DB::statement("DROP INDEX IF EXISTS {$index}");
            }
        } catch (\Throwable $e) {
            //
        }
    }

    private function dropMysqlColumnIfExists(string $column): void
    {
        if (! $this->columnExists($column)) {
            return;
        }

        DB::statement("ALTER TABLE `appointments` DROP COLUMN `{$column}`");
    }

    private function indexExists(string $index): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        try {
            if ($driver === 'mysql') {
                return collect(DB::select('SHOW INDEX FROM `appointments`'))
                    ->pluck('Key_name')
                    ->contains($index);
            }

            if ($driver === 'sqlite') {
                return collect(DB::select('PRAGMA index_list(appointments)'))
                    ->contains(fn ($row) => isset($row->name) && $row->name === $index);
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    private function columnExists(string $column): bool
    {
        return Schema::hasColumn('appointments', $column);
    }
};
