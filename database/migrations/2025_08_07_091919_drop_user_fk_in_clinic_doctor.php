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
        Schema::table('clinic_doctor', function (Blueprint $table) {
            // 1. Drop the existing foreign key (if any)
            $table->dropForeign(['doctor_id']); 

            // 2. Modify the existing column's foreign key (without recreating it)
            $table->unsignedBigInteger('doctor_id')->change(); // Ensure correct type
            
            // 3. Add the new foreign key constraint
            $table->foreign('doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_doctor', function (Blueprint $table) {
            // 1. Drop the new foreign key
            $table->dropForeign(['doctor_id']); 

            // 2. Revert to the original foreign key (pointing to `users`)
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
