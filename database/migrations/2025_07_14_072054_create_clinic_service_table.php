<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
    public function down(): void
    {
        Schema::dropIfExists('clinic_service');
    }
};
