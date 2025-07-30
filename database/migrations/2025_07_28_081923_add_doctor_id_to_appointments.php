<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up() {
    Schema::table('appointments', function (Blueprint $t) {
      $t->foreignId('doctor_id')
        ->nullable()
        ->after('user_id')
        ->constrained('users')
        ->nullOnDelete();
    });
  }
  public function down() {
    Schema::table('appointments', function (Blueprint $t) {
      $t->dropForeign(['doctor_id']);
      $t->dropColumn('doctor_id');
    });
  }
};
