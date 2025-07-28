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
        Schema::create('patient_files', function (Blueprint $table) {
            $table->id();

            // Required: every file must belong to a user (specifically, a patient)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_name', 255);         // stored filename (e.g. hashed UUID)
            $table->string('original_name', 255);     // original filename during upload
            $table->string('file_type', 100);         // e.g., image/jpeg, application/pdf
            $table->unsignedBigInteger('file_size')->nullable(); // optional size in bytes
            $table->text('description')->nullable();  // optional user-provided notes
            $table->boolean('is_public')->default(false); // if the file can be viewed by clinics
            $table->timestamps();
            $table->index('user_id', 'idx_patient_file_user');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_files');
    }
};
