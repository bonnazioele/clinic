<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_create_clinic_service_table.php
public function up()
{
    Schema::create('clinic_service', function (Blueprint $table) {
        $table->id();
        $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
        $table->foreignId('service_id')->constrained()->onDelete('cascade');
        $table->integer('duration_minutes')->default(30);
        $table->timestamps();

        $table->unique(['clinic_id','service_id']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_service');
    }
};
