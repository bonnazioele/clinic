<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_service', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration_minutes')->default(30)->after('clinic_id');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_service', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });
    }
};