<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('queue_entries', function (Blueprint $table) {
        $table->enum('status', [
            'waiting',
            'called',
            'rescheduled',
            'cancelled'
        ])->default('waiting')->change();
    });
}

public function down(): void
{
    Schema::table('queue_entries', function (Blueprint $table) {
        $table->enum('status', ['waiting'])->default('waiting')->change();
    });
}
};
