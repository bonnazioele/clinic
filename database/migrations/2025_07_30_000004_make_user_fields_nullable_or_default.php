<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // age: add if missing, otherwise only modify nullability/defaults
            if (!Schema::hasColumn('users', 'age')) {
                $table->unsignedTinyInteger('age')->nullable()->after('last_name');
            } else {
                $table->unsignedTinyInteger('age')->nullable()->change();
            }

            // birthdate: add if missing, otherwise modify
            if (!Schema::hasColumn('users', 'birthdate')) {
                $table->date('birthdate')->nullable()->after('age');
            } else {
                $table->date('birthdate')->nullable()->change();
            }

            // is_active: add if missing with default true, otherwise modify default
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('phone');
            } else {
                $table->boolean('is_active')->default(true)->change();
            }

            // is_admin: add if missing with default false, otherwise modify default
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('password');
            } else {
                $table->boolean('is_admin')->default(false)->change();
            }

            // is_secretary: add if missing with default false, otherwise modify default
            if (!Schema::hasColumn('users', 'is_secretary')) {
                $table->boolean('is_secretary')->default(false)->after('is_admin');
            } else {
                $table->boolean('is_secretary')->default(false)->change();
            }

            // last_login: add if missing as nullable timestamp, otherwise modify
            if (!Schema::hasColumn('users', 'last_login')) {
                $table->timestamp('last_login')->nullable()->after('email_verified_at');
            } else {
                $table->timestamp('last_login')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only revert nullability/defaults if the columns exist
            if (Schema::hasColumn('users', 'last_login')) {
                // Backfill any NULLs first to avoid NOT NULL constraint issues then keep it nullable for safety
                DB::table('users')->whereNull('last_login')->update(['last_login' => now()]);
                // Instead of forcing NOT NULL (caused previous failure), retain nullable to avoid migration refresh breakage
                $table->timestamp('last_login')->nullable()->change();
            }
            if (Schema::hasColumn('users', 'is_secretary')) {
                $table->boolean('is_secretary')->default(null)->change();
            }
            if (Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(null)->change();
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(null)->change();
            }
            if (Schema::hasColumn('users', 'birthdate')) {
                // Keep nullable to avoid failing if existing rows are null
                $table->date('birthdate')->nullable()->change();
            }
            if (Schema::hasColumn('users', 'age')) {
                // Keep nullable for refresh safety
                $table->unsignedTinyInteger('age')->nullable()->change();
            }
        });
    }
};
