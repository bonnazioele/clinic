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
        Schema::create('patient_visits', function (Blueprint $table) {
            $table->id();

            // System-Generated Fields
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('visit_number')->unique()->comment('Auto-generated visit ID');
            $table->date('date_of_visit');
            $table->timestamp('time_in');
            $table->foreignId('registration_staff_id')->constrained('users');

            // Visit Information
            $table->enum('visit_type', ['Walk-In', 'Follow-Up'])->default('Walk-In');
            $table->text('reason_for_visit')->comment('Chief complaint');
            $table->enum('requested_service', [
                'Consultation',
                'Check-up',
                'Medical Certificate',
                'Laboratory',
                'Vaccination'
            ]);
            $table->string('assigned_department')->nullable();

            // Patient Classification
            $table->enum('patient_type', ['New', 'Returning'])->default('New');
            $table->enum('priority_level', ['Normal', 'Urgent', 'Emergency'])->default('Normal');

            // Consent & Verification
            $table->boolean('consent_to_data_collection')->default(false);
            $table->text('patient_signature')->nullable()->comment('Base64 encoded signature or signature path');
            $table->date('date_signed')->nullable();

            // Additional tracking
            $table->enum('status', ['Registered', 'In Progress', 'Completed', 'Cancelled'])->default('Registered');
            $table->timestamp('time_out')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['date_of_visit', 'status']);
            $table->index('priority_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_visits');
    }
};
