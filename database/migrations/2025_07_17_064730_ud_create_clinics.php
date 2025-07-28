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
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('type_id')->nullable()->constrained('clinic_types')->nullOnDelete();
            $table->string('branch_code', 50)->unique();
            $table->text('address');
            $table->string('contact_number', 50);
            $table->string('email', 100)->unique();
            $table->string('logo', 255)->nullable();
            $table->enum('status', ['Approved', 'Rejected', 'Pending'])->default('Pending');
            $table->decimal('gps_latitude', 10, 7)->nullable();
            $table->decimal('gps_longitude', 10, 7)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Indexes
            $table->index('status', 'idx_status');
            $table->index('type_id', 'idx_type_id');
            $table->index(['gps_latitude', 'gps_longitude'], 'idx_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
