<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (Schema::hasColumn('queue_entries', 'patient_disposition')) {
                $table->dropColumn('patient_disposition');
            }

            if (Schema::hasColumn('queue_entries', 'doctor_notes')) {
                $table->dropColumn('doctor_notes');
            }

            if (Schema::hasColumn('queue_entries', 'prescription')) {
                $table->dropColumn('prescription');
            }

            if (Schema::hasColumn('queue_entries', 'follow_up_at')) {
                $table->dropColumn('follow_up_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('queue_entries', 'patient_disposition')) {
                $table->string('patient_disposition')->nullable()->after('status');
            }

            if (! Schema::hasColumn('queue_entries', 'doctor_notes')) {
                $table->text('doctor_notes')->nullable()->after('patient_disposition');
            }

            if (! Schema::hasColumn('queue_entries', 'prescription')) {
                $table->text('prescription')->nullable()->after('doctor_notes');
            }

            if (! Schema::hasColumn('queue_entries', 'follow_up_at')) {
                $table->timestamp('follow_up_at')->nullable()->after('prescription');
            }
        });
    }
};