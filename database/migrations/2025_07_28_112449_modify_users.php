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
        Schema::table('users', function (Blueprint $table) {
            // Drop 'name' if exists
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Modify existing columns
            if (Schema::hasColumn('users', 'email')) {
                $table->string('email', 100)->change();
            }

            if (Schema::hasColumn('users', 'password')) {
                $table->string('password', 255)->change()->after('email');
            }

            // Add new columns only if they don't exist
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name', 50)->after('id');
            }

            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 50)->after('first_name');
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->after('last_name');
            }

            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('phone');
            }

            if (!Schema::hasColumn('users', 'is_system_admin')) {
                $table->boolean('is_system_admin')->default(false)->after('is_active');
            }

            if (!Schema::hasColumn('users', 'last_login')) {
                $table->timestamp('last_login')->nullable()->after('email_verified_at');
            }

            if (Schema::hasColumn('users', 'medical_document')) {
                $table->dropColumn('medical_document');
            }

            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }

            if (!Schema::hasColumn('users', 'age')) {
                $table->unsignedTinyInteger('age')->after('last_name');
            }

            if (!Schema::hasColumn('users', 'birthdate')) {
                $table->date('birthdate')->nullable()->after('age');
            }

            $table->index('email', 'idx_email');
            $table->index('is_active', 'idx_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes safely
            if (Schema::hasColumn('users', 'email')) {
                $table->dropIndex('idx_email');
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropIndex('idx_active');
            }

            // Drop new columns safely
            foreach (['first_name', 'last_name', 'phone', 'is_active', 'is_system_admin', 'last_login', 'age', 'birthdate'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Revert column modifications
            if (Schema::hasColumn('users', 'email')) {
                $table->string('email')->unique()->change();
            }

            if (Schema::hasColumn('users', 'password')) {
                $table->string('password')->change();
            }

            // Add back 'name' column if missing
            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->after('id');
            }

        });
    }
};
