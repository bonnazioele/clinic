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
        Schema::table('clinics', function (Blueprint $table) {
            // Rename owner fields to contact fields
            $table->renameColumn('owner_first_name', 'contact_first_name');
            $table->renameColumn('owner_last_name', 'contact_last_name');
            
            // Rename user_id to created_by_user_id
            $table->renameColumn('user_id', 'created_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Rename back to original column names
            $table->renameColumn('created_by_user_id', 'user_id');
            $table->renameColumn('contact_first_name', 'owner_first_name');
            $table->renameColumn('contact_last_name', 'owner_last_name');
        });
    }
};
