<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Expand enum to include no_show (and keep existing values)
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled'");
    }

    public function down(): void
    {
        // Revert (may truncate existing no_show rows back to cancelled if any remain)
        DB::statement("UPDATE appointments SET status='cancelled' WHERE status='no_show'");
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled'");
    }
};
