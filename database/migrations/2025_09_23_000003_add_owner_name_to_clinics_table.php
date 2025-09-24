<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('clinics', 'owner_first_name')) {
                $table->string('owner_first_name', 255)->nullable()->after('email');
            }
            if (!Schema::hasColumn('clinics', 'owner_last_name')) {
                $table->string('owner_last_name', 255)->nullable()->after('owner_first_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (Schema::hasColumn('clinics', 'owner_last_name')) {
                $table->dropColumn('owner_last_name');
            }
            if (Schema::hasColumn('clinics', 'owner_first_name')) {
                $table->dropColumn('owner_first_name');
            }
        });
    }
};
