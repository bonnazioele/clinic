<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Service;

class ServiceSyncCommand extends Command
{
    protected $signature = 'service:sync {--dry-run : Show actions without making changes} {--prune : Remove services not in catalog}';
    protected $description = 'Synchronize the services table with the configured catalog list.';

    public function handle(): int
    {
        $catalog = collect(config('services_catalog.services', []))
            ->filter(fn($v)=>is_string($v) && trim($v) !== '')
            ->unique()
            ->values();

        if ($catalog->isEmpty()) {
            $this->warn('No services defined in catalog (config/services_catalog.php).');
            return self::SUCCESS;
        }

        $existing = Service::query()->pluck('id','name'); // name => id
        $dry = $this->option('dry-run');
        $prune = $this->option('prune');

        $toInsert = [];
        $keptNames = [];

        foreach ($catalog as $name) {
            $keptNames[] = $name;
            if (!$existing->has($name)) {
                $toInsert[] = ['name' => $name, 'description' => null, 'created_at'=>now(), 'updated_at'=>now()];
            }
        }

        $insertCount = count($toInsert);
        if ($insertCount) {
            $this->info("Will add $insertCount missing service(s).");
            if (!$dry) {
                DB::table('services')->insert($toInsert);
                $this->info('Inserted: '.implode(', ', array_column($toInsert,'name')));
            }
        } else {
            $this->info('No new services to add.');
        }

        $removed = [];
        if ($prune) {
            $orphans = $existing->keys()->diff($keptNames);
            if ($orphans->count()) {
                $this->warn('Will prune services not in catalog: '. $orphans->implode(', '));
                if (!$dry) {
                    Service::whereIn('name', $orphans)->delete();
                    $removed = $orphans->values()->all();
                }
            } else {
                $this->info('No services to prune.');
            }
        }

        // Summary
        $summary = [
            'added' => $insertCount,
            'pruned' => count($removed),
            'total_after' => Service::count(),
            'dry_run' => $dry,
        ];
        $this->line('Summary: '. json_encode($summary));

        if ($dry) {
            $this->comment('Dry run complete. Re-run without --dry-run to apply changes.');
        }

        return self::SUCCESS;
    }
}
