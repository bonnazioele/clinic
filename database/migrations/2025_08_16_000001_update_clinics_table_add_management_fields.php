<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {

        if (!Schema::hasColumn('clinics', 'branch_code')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->string('branch_code')->unique()->nullable()->after('name');
            });
        }
        if (!Schema::hasColumn('clinics', 'contact_number')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->string('contact_number')->nullable()->after('address');
            });
        }
        if (!Schema::hasColumn('clinics', 'email')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->string('email')->unique()->nullable()->after('contact_number');
            });
        }
        if (!Schema::hasColumn('clinics', 'logo')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->string('logo')->nullable()->after('email');
            });
        }

        if (!Schema::hasColumn('clinics', 'gps_latitude')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->decimal('gps_latitude', 10, 7)->nullable()->after('logo');
            });
        }
        if (!Schema::hasColumn('clinics', 'gps_longitude')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->decimal('gps_longitude', 10, 7)->nullable()->after('gps_latitude');
            });
        }

        if (!Schema::hasColumn('clinics', 'status')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->string('status')->default('active')->after('gps_longitude');
            });
        }
        if (!Schema::hasColumn('clinics', 'user_id')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('status');
            });
        }

        if (!Schema::hasColumn('clinics', 'deleted_at')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasColumn('clinics', 'latitude') && Schema::hasColumn('clinics', 'longitude')) {
            DB::statement('UPDATE clinics SET gps_latitude = COALESCE(gps_latitude, latitude), gps_longitude = COALESCE(gps_longitude, longitude)');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('clinics', 'user_id')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }
        $columns = [
            'branch_code',
            'contact_number',
            'email',
            'logo',
            'gps_latitude',
            'gps_longitude',
            'status',
            'user_id',
            'deleted_at',
        ];
        foreach ($columns as $col) {
            if (Schema::hasColumn('clinics', $col)) {
                Schema::table('clinics', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};
