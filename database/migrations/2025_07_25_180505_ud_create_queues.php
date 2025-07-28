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
        Schema::create('queues', function (Blueprint $table) {
            $table->id();

            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('queue_number', 20)->nullable()->index();
            $table->unique(['clinic_id', 'queue_number']);
            $table->enum('type', ['walk-in', 'appointment', 'emergency'])->default('walk-in');
            $table->enum('status', ['waiting', 'serving', 'completed'])->default('waiting');
            $table->enum('priority', ['normal', 'high', 'emergency'])->default('normal');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('walk_in_id')->nullable()->constrained('walk_ins')->nullOnDelete();

            $table->string('dynamic_group_id')->index();
            $table->timestamp('checkin_time')->useCurrent();
            $table->timestamp('called_time')->nullable();
            $table->timestamp('completed_time')->nullable();
            $table->integer('estimated_wait_minutes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
