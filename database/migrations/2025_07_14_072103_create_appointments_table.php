<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::create('appointments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
        $table->foreignId('service_id')->constrained()->onDelete('cascade');
        $table->date('appointment_date');
        $table->time('appointment_time');
        $table->enum('status', ['scheduled','completed','cancelled'])->default('scheduled');
        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
