<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('clinic_secretary')) {
            Schema::create('clinic_secretary', function (Blueprint $t) {
                $t->id();
                $t->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
                $t->foreignId('secretary_id')->constrained('users')->cascadeOnDelete();
                $t->timestamps();
                $t->unique(['clinic_id','secretary_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_secretary');
    }
};
