<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE queue_entries MODIFY status ENUM('waiting','called','in_progress','now_serving','rescheduled','no_show','cancelled','served') NOT NULL DEFAULT 'waiting'");
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled','in_progress','completed','cancelled','no_show','rescheduled') NOT NULL DEFAULT 'scheduled'");
    }

    public function down(): void
    {
        DB::statement("UPDATE queue_entries SET status='now_serving' WHERE status='in_progress'");
        DB::statement("ALTER TABLE queue_entries MODIFY status ENUM('waiting','called','now_serving','rescheduled','no_show','cancelled','served') NOT NULL DEFAULT 'waiting'");

        DB::statement("UPDATE appointments SET status='scheduled' WHERE status='in_progress'");
        DB::statement("UPDATE appointments SET status='cancelled' WHERE status='rescheduled'");
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled'");
    }
};
