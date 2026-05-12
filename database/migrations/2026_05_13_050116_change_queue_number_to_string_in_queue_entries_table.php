<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->string('queue_number')->change();
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->integer('queue_number')->change();
        });
    }
};