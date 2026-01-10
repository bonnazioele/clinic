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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_number')->unique()->comment('Auto-generated patient ID');

            // Patient Personal Information
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->enum('sex', ['Male', 'Female'])->comment('Patient sex');
            $table->date('date_of_birth');

            // Contact Information
            $table->string('mobile_number', 15);
            $table->string('email_address')->nullable();
            $table->text('complete_address');

            // Emergency Contact
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_relationship', 50);
            $table->string('emergency_contact_number', 15);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for search performance
            $table->index(['last_name', 'first_name']);
            $table->index('mobile_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
