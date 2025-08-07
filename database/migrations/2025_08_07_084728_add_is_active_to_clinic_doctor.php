<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clinic_doctor', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('doctor_id')->comment('Indicates if the doctor is active in the clinic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_doctor', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
