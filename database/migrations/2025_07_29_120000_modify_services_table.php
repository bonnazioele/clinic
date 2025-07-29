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
        Schema::table('services', function (Blueprint $table) {
            // Rename 'name' to 'service_name' if needed
            if (Schema::hasColumn('services', 'name') && !Schema::hasColumn('services', 'service_name')) {
                $table->renameColumn('name', 'service_name');
            }
            // Make 'service_name' unique and max 100 chars
            if (Schema::hasColumn('services', 'service_name')) {
                $table->string('service_name', 100)->unique()->change();
            }
            // Add 'description' if missing
            if (!Schema::hasColumn('services', 'description')) {
                $table->text('description')->nullable()->after('service_name');
            }
            // Add 'is_active' if missing
            if (!Schema::hasColumn('services', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Revert 'service_name' to 'name' if needed
            if (Schema::hasColumn('services', 'service_name') && !Schema::hasColumn('services', 'name')) {
                $table->renameColumn('service_name', 'name');
            }
            // Remove 'description' if exists
            if (Schema::hasColumn('services', 'description')) {
                $table->dropColumn('description');
            }
            // Remove 'is_active' if exists
            if (Schema::hasColumn('services', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
