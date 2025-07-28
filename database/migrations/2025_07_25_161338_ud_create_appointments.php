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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // references users.id
            $table->string('queue_number', 20)->nullable()->index();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services');
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('status', ['confirmed', 'completed', 'cancelled', 'no_show', 'rescheduled'])->default('confirmed');
            $table->text('patient_note')->nullable();
            $table->text('staff_note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['clinic_id', 'doctor_id', 'appointment_date', 'appointment_time'], 'unique_slot_per_doctor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
