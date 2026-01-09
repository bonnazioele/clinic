<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clinic_patients')) {
            Schema::create('clinic_patients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['clinic_id', 'patient_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_patients');
    }
};
