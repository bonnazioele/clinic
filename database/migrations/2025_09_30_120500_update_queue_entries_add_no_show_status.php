<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Consolidate and expand queue status enum to include all runtime states.
     */
    public function up(): void
    {
        // We may currently have enum values from prior migrations: waiting,in_progress,completed,served
        // New target set: waiting, called, rescheduled, no_show, cancelled, served
        // Strategy: (1) Expand enum to superset including legacy + new values.
        //           (2) Remap legacy values (completed -> served, in_progress -> waiting).
        //           (3) Shrink enum to final target list.
        // Some MySQL setups (or earlier errors) can break transactional DDL; run sequentially without an explicit transaction.
        // 1. Expand to superset including legacy + new states
        DB::statement("ALTER TABLE queue_entries MODIFY status ENUM('waiting','in_progress','completed','served','called','rescheduled','no_show','cancelled') NOT NULL DEFAULT 'waiting'");

        // 2. Remap legacy values to target states
        DB::statement("UPDATE queue_entries SET status='served' WHERE status='completed'");
        DB::statement("UPDATE queue_entries SET status='waiting' WHERE status='in_progress'");

        // 3. Shrink to final canonical set
        DB::statement("ALTER TABLE queue_entries MODIFY status ENUM('waiting','called','rescheduled','no_show','cancelled','served') NOT NULL DEFAULT 'waiting'");
    }

    public function down(): void
    {
        // Best-effort rollback: restore legacy superset including in_progress & completed (served kept)
        DB::statement("ALTER TABLE queue_entries MODIFY status ENUM('waiting','in_progress','completed','served') NOT NULL DEFAULT 'waiting'");
    }
};
