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
        Schema::table('clinics', function (Blueprint $table) {
            // Change status from string to enum with all possible values
            $table->enum('status', [
                'pending',      // Initial application status
                'approved',     // Approved by admin
                'active',       // Currently active (default for existing)
                'rejected',     // Application rejected
                'suspended',    // Temporarily suspended
                'deleted'       // Soft deleted/permanently removed
            ])->default('active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Revert back to string
            $table->string('status')->default('active')->change();
        });
    }
};