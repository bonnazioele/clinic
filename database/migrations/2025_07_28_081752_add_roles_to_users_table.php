<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('is_secretary')->default(false)->after('is_admin');
            $t->boolean('is_doctor')->default(false)->after('is_secretary');
        });
    }
    public function down() {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['is_secretary','is_doctor']);
        });
    }
};
