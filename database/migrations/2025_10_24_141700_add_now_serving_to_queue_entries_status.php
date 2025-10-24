<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {

        DB::statement("ALTER TABLE queue_entries MODIFY status ENUM('waiting','called','now_serving','rescheduled','no_show','cancelled','served') NOT NULL DEFAULT 'waiting'");
    }


    public function down(): void
    {
        DB::statement("UPDATE queue_entries SET status='called' WHERE status='now_serving'");
        DB::statement("ALTER TABLE queue_entries MODIFY status ENUM('waiting','called','rescheduled','no_show','cancelled','served') NOT NULL DEFAULT 'waiting'");
    }
};
