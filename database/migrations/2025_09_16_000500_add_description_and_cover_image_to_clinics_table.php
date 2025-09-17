<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('clinics','description')) {
                $table->text('description')->nullable()->after('address');
            }
            if (!Schema::hasColumn('clinics','logo')) {
                $table->string('logo')->nullable()->after('description');
            }
            if (!Schema::hasColumn('clinics','cover_image')) {
                $table->string('cover_image')->nullable()->after('logo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (Schema::hasColumn('clinics','cover_image')) {
                $table->dropColumn('cover_image');
            }
            if (Schema::hasColumn('clinics','logo')) {
                $table->dropColumn('logo');
            }
            if (Schema::hasColumn('clinics','description')) {
                $table->dropColumn('description');
            }
        });
    }
};
