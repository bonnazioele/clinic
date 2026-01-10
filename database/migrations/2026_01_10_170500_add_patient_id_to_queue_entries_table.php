<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('queue_entries', 'user_id')) {
            Schema::table('queue_entries', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            DB::statement('ALTER TABLE queue_entries MODIFY user_id BIGINT UNSIGNED NULL');

            Schema::table('queue_entries', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        Schema::table('queue_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('queue_entries', 'patient_id')) {
                $table->foreignId('patient_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('patients')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (Schema::hasColumn('queue_entries', 'patient_id')) {
                $table->dropConstrainedForeignId('patient_id');
            }
        });

        if (Schema::hasColumn('queue_entries', 'user_id')) {
            Schema::table('queue_entries', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            DB::statement('ALTER TABLE queue_entries MODIFY user_id BIGINT UNSIGNED NOT NULL');

            Schema::table('queue_entries', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }
};
