<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Only proceed if column exists
        if (!Schema::hasColumn('users','last_login')) {
            return; // earlier migration should have added it; stay idempotent
        }

        // Backfill any NULL last_login with created_at (fallback to now()) in manageable batches to avoid large locks
        DB::table('users')
            ->whereNull('last_login')
            ->orderBy('id')
            ->chunkById(500, function($users){
                foreach ($users as $u) {
                    DB::table('users')
                        ->where('id', $u->id)
                        ->update(['last_login' => $u->created_at ?? now()]);
                }
            });
    }

    public function down(): void
    {
        // Down does nothing (data backfill is irreversible safely). Intentionally left blank.
    }
};
