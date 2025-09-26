<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Purpose: After introducing secretary-only forced password changes, ensure
     * existing non-secretary users are not incorrectly flagged for initial login.
     */
    public function up(): void
    {
        // Set is_initial_login = false for users who are NOT secretaries but currently true/null.
        DB::table('users')
            ->where(function($q){
                $q->whereNull('is_initial_login')
                  ->orWhere('is_initial_login', true);
            })
            ->where(function($q){
                $q->whereNull('is_secretary')
                  ->orWhere('is_secretary', false);
            })
            ->update(['is_initial_login' => false]);
    }

    public function down(): void
    {
        // No rollback action (data correction). Leaving empty intentionally.
    }
};
