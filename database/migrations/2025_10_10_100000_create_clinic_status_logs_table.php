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
        Schema::create('clinic_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->enum('action', [
                'submitted',    // Initial application submitted (pending status)
                'approved',     // Application approved by admin
                'rejected',     // Application rejected by admin
                'activated',    // Clinic activated/reactivated
                'suspended',    // Clinic suspended by admin
                'deleted'       // Clinic deleted by admin
            ]);
            $table->text('reason')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('performed_at');
            $table->timestamps();
            
            // Add indexes for better query performance
            $table->index(['clinic_id', 'performed_at']);
            $table->index(['performed_by']);
            $table->index(['action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_status_logs');
    }
};