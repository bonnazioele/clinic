<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void {
        Schema::create('patient_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // patient
            $table->string('clinic_name');
            $table->string('diagnosis')->nullable();
            $table->string('treatment')->nullable();
            $table->string('doctor')->nullable();
            $table->string('document_path')->nullable(); // link to uploaded record
            $table->date('date_of_visit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('patient_histories');
    }
};
