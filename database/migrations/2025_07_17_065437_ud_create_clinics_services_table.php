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
        Schema::create('clinic_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->integer('duration_minutes')->default(30);
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['Approved', 'Rejected', 'Pending'])->default('Pending');
            $table->timestamps(); // includes created_at and updated_at

            $table->unique(['clinic_id', 'service_id'], 'unique_clinic_service');

            // Indexes
            $table->index('clinic_id', 'idx_clinic_id');
            $table->index('service_id', 'idx_service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics_services');
    }
};
