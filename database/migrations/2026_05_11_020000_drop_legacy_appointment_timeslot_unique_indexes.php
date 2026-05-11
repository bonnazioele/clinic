<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $this->addIndexIfMissing('appointments_user_id_index', ['user_id']);
        $this->addIndexIfMissing('appointments_doctor_id_index', ['doctor_id']);

        $this->dropIndexIfExists('appointments_user_date_time_unique');
        $this->dropIndexIfExists('appointments_doctor_date_time_unique');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        try {
            if (! $this->indexExists('appointments_user_date_time_unique')) {
                DB::statement('ALTER TABLE `appointments` ADD UNIQUE INDEX `appointments_user_date_time_unique` (`user_id`, `appointment_date`, `appointment_time`)');
            }

            if (! $this->indexExists('appointments_doctor_date_time_unique')) {
                DB::statement('ALTER TABLE `appointments` ADD UNIQUE INDEX `appointments_doctor_date_time_unique` (`doctor_id`, `appointment_date`, `appointment_time`)');
            }
        } catch (\Throwable $e) {
            // Terminal historical rows can duplicate old unconditional slot keys.
        }
    }

    private function addIndexIfMissing(string $index, array $columns): void
    {
        if ($this->indexExists($index)) {
            return;
        }

        $columns = collect($columns)
            ->map(fn (string $column) => "`{$column}`")
            ->join(', ');

        DB::statement("ALTER TABLE `appointments` ADD INDEX `{$index}` ({$columns})");
    }

    private function dropIndexIfExists(string $index): void
    {
        if (! $this->indexExists($index)) {
            return;
        }

        DB::statement("ALTER TABLE `appointments` DROP INDEX `{$index}`");
    }

    private function indexExists(string $index): bool
    {
        return collect(DB::select('SHOW INDEX FROM `appointments`'))
            ->pluck('Key_name')
            ->contains($index);
    }
};
