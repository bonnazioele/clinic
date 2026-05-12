<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'completed' to the enum.
        // Enum is now: waiting → in_progress → served → completed
        // Side exits: no_show, cancelled, rescheduled
        DB::statement("
            ALTER TABLE queue_entries
            MODIFY COLUMN status ENUM(
                'waiting',
                'called',
                'in_progress',
                'now_serving',
                'served',
                'completed',
                'no_show',
                'cancelled',
                'rescheduled'
            ) NOT NULL DEFAULT 'waiting'
        ");

        // Drop patient_disposition — no longer needed.
        // Doctor now sets status directly to 'served'; secretary sets 'completed'.
        if (Schema::hasColumn('queue_entries', 'patient_disposition')) {
            Schema::table('queue_entries', function (Blueprint $table) {
                $table->dropColumn('patient_disposition');
            });
        }
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
            MODIFY COLUMN status ENUM(
                'waiting',
                'called',
                'in_progress',
                'now_serving',
                'served',
                'no_show',
                'cancelled',
                'rescheduled'
            ) NOT NULL DEFAULT 'waiting'
        ");

        Schema::table('queue_entries', function (Blueprint $table) {
            $table->string('patient_disposition')->nullable()->after('status');
        });
    }
};
