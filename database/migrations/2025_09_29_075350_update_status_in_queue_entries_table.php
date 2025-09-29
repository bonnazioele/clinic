<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('queue_entries', function (Blueprint $table) {
        $table->enum('status', ['waiting','in_progress','completed','served'])
              ->default('waiting')
              ->change();
    });
}

public function down()
{
    Schema::table('queue_entries', function (Blueprint $table) {
        $table->enum('status', ['waiting','in_progress','completed'])
              ->default('waiting')
              ->change();
    });
}

};
