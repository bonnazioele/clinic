<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_operational_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('day_of_week', 20);
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->boolean('is_open')->default(false);
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->timestamps();

            $table->unique(['clinic_id', 'day_of_week'], 'clinic_hours_clinic_day_unique');
            $table->index(['clinic_id', 'sort_order'], 'clinic_hours_clinic_sort_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_operational_hours');
    }
};
