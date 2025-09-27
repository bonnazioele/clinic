<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('clinics', 'branch_code')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->string('branch_code')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('clinics', 'branch_code')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->dropColumn('branch_code');
            });
        }
    }
};
