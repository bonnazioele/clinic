<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('queue_entries', 'original_scheduled_slot_date')) {
                $table->date('original_scheduled_slot_date')->nullable()->after('scheduled_slot_time');
            }

            if (! Schema::hasColumn('queue_entries', 'original_scheduled_slot_time')) {
                $table->time('original_scheduled_slot_time')->nullable()->after('original_scheduled_slot_date');
            }

            if (! Schema::hasColumn('queue_entries', 'priority_marked_at')) {
                $table->timestamp('priority_marked_at')->nullable()->after('delay_notice_reason');
            }

            if (! Schema::hasColumn('queue_entries', 'priority_marked_by')) {
                $table->foreignId('priority_marked_by')
                    ->nullable()
                    ->after('priority_marked_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (Schema::hasColumn('queue_entries', 'priority_marked_by')) {
                $table->dropConstrainedForeignId('priority_marked_by');
            }

            if (Schema::hasColumn('queue_entries', 'priority_marked_at')) {
                $table->dropColumn('priority_marked_at');
            }

            if (Schema::hasColumn('queue_entries', 'original_scheduled_slot_time')) {
                $table->dropColumn('original_scheduled_slot_time');
            }

            if (Schema::hasColumn('queue_entries', 'original_scheduled_slot_date')) {
                $table->dropColumn('original_scheduled_slot_date');
            }
        });
    }
};
