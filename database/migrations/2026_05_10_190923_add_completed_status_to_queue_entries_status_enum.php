<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE queue_entries
            MODIFY status ENUM(
                'waiting',
                'called',
                'in_progress',
                'now_serving',
                'rescheduled',
                'no_show',
                'cancelled',
                'served',
                'completed'
            ) NOT NULL DEFAULT 'waiting'
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE queue_entries
            SET status = 'served'
            WHERE status = 'completed'
        ");

        DB::statement("
            ALTER TABLE queue_entries
            MODIFY status ENUM(
                'waiting',
                'called',
                'in_progress',
                'now_serving',
                'rescheduled',
                'no_show',
                'cancelled',
                'served'
            ) NOT NULL DEFAULT 'waiting'
        ");
    }
};