<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Clinic;
use App\Models\User;

class MigrateOwnersToSecretaries extends Command
{
    protected $signature = 'owners:migrate-secretaries {--dry-run : Show what would change without persisting}';
    protected $description = 'Promote legacy owners with approved clinics to secretaries (idempotent) before dropping is_owner column';

    public function handle(): int
    {
        $dry = $this->option('dry-run');

        $hasIsOwner = false;
        try {
            $hasIsOwner = \Schema::hasColumn('users', 'is_owner');
        } catch (\Throwable $e) {
            $this->warn('Could not determine if is_owner column exists: '.$e->getMessage());
        }

        if (! $hasIsOwner) {
            $this->info('is_owner column not present; nothing to migrate.');
            return self::SUCCESS;
        }

        $query = User::query()->where('is_owner', true);
        $totalOwners = $query->count();
        if ($totalOwners === 0) {
            $this->info('No legacy owner accounts found.');
            return self::SUCCESS;
        }

        $this->info("Found {$totalOwners} legacy owner user(s). Scanning clinics...");

        $promoted = 0; $pending = 0; $skipped = 0;
        $query->chunkById(100, function($users) use (&$promoted, &$pending, &$skipped, $dry) {
            foreach ($users as $user) {
                $clinic = Clinic::where('created_by_user_id', $user->id)
                    ->orderByRaw("CASE WHEN status IN ('approved','active') THEN 0 ELSE 1 END")
                    ->orderByDesc('id')
                    ->first();
                if (! $clinic) { $skipped++; continue; }
                if ($clinic->isApprovedLike()) {
                    if (! $user->is_secretary) { $user->is_secretary = true; }
                    $user->is_owner = false; // clear flag
                    if (! $dry) { $user->save(); }
                    $promoted++;
                } else {
                    $pending++;
                }
            }
        });

        $this->info("Promoted: {$promoted}, Pending clinic approval: {$pending}, Skipped (no clinic): {$skipped}");
        if ($dry) { $this->comment('Dry run complete. Re-run without --dry-run to persist.'); }
        return self::SUCCESS;
    }
}
