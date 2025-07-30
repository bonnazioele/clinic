<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('doctor_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('service_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['doctor_id','service_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_service');
    }
};
