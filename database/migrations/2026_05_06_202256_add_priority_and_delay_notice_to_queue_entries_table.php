<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('queue_entries', 'priority_level')) {
                $table->string('priority_level')->default('regular')->after('status');
            }

            if (! Schema::hasColumn('queue_entries', 'priority_rank')) {
                $table->unsignedTinyInteger('priority_rank')->default(5)->after('priority_level');
            }

            if (! Schema::hasColumn('queue_entries', 'delay_notice_at')) {
                $table->timestamp('delay_notice_at')->nullable()->after('priority_rank');
            }

            if (! Schema::hasColumn('queue_entries', 'delay_notice_reason')) {
                $table->string('delay_notice_reason')->nullable()->after('delay_notice_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (Schema::hasColumn('queue_entries', 'delay_notice_reason')) {
                $table->dropColumn('delay_notice_reason');
            }

            if (Schema::hasColumn('queue_entries', 'delay_notice_at')) {
                $table->dropColumn('delay_notice_at');
            }

            if (Schema::hasColumn('queue_entries', 'priority_rank')) {
                $table->dropColumn('priority_rank');
            }

            if (Schema::hasColumn('queue_entries', 'priority_level')) {
                $table->dropColumn('priority_level');
            }
        });
    }
};
