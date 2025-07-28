<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            // Drop the foreign key constraint first (if it exists)
            if (Schema::hasColumn('services', 'clinic_id')) {
                $table->dropForeign(['clinic_id']);
                $table->dropColumn('clinic_id');
            }
        });
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            // Recreate clinic_id if you ever roll back
            $table->foreignId('clinic_id')
                  ->after('id')
                  ->constrained()
                  ->cascadeOnDelete();
        });
    }
};
