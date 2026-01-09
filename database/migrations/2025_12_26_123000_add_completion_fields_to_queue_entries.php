<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->string('patient_disposition')->nullable()->after('status');
            $table->text('doctor_notes')->nullable()->after('patient_disposition');
            $table->text('prescription')->nullable()->after('doctor_notes');
            $table->timestamp('follow_up_at')->nullable()->after('served_at');
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->dropColumn(['patient_disposition','doctor_notes','prescription','follow_up_at']);
        });
    }
};
