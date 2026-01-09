<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('permit_type');
            $table->string('permit_number')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('attachment_path');
            $table->timestamps();

            $table->index(['clinic_id', 'permit_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_permits');
    }
};
