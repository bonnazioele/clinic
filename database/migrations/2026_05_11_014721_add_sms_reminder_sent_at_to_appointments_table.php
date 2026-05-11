<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'sms_reminder_sent_at')) {
                $table->timestamp('sms_reminder_sent_at')->nullable()->after('medical_document');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'sms_reminder_sent_at')) {
                $table->dropColumn('sms_reminder_sent_at');
            }
        });
    }
};