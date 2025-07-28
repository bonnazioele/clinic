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
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->enum('day_of_week', [0,1,2,3,4,5,6]); // 0=Sunday
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('max_patients')->nullable(); // NULL = no limit
            $table->boolean('is_recurring')->default(true);
            $table->date('valid_from')->default(now());
            $table->date('valid_to')->nullable(); // NULL = indefinitely
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};
