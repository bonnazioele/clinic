<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Service;

class ServiceSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_sync_inserts_missing_catalog_entries()
    {
        // Ensure table starts empty
        $this->assertSame(0, Service::count());

        $this->artisan('service:sync')
            ->expectsOutputToContain('Summary:')
            ->assertExitCode(0);

        $catalog = config('services_catalog.services');
        $this->assertGreaterThan(0, count($catalog));
        $this->assertSame(count($catalog), Service::count());
    }

    public function test_service_sync_prune_removes_non_catalog_entries()
    {
        // Seed one bogus service not in catalog
        Service::create(['name' => 'Bogus Custom', 'description' => null]);
        $catalogCount = count(config('services_catalog.services'));

        $this->artisan('service:sync --prune')
            ->expectsOutputToContain('Summary:')
            ->assertExitCode(0);

        $this->assertSame($catalogCount, Service::count());
        $this->assertDatabaseMissing('services', ['name' => 'Bogus Custom']);
    }

    public function test_service_sync_dry_run_does_not_change_database()
    {
        $this->assertSame(0, Service::count());
        $this->artisan('service:sync --dry-run')
            ->expectsOutputToContain('Dry run complete')
            ->assertExitCode(0);
        $this->assertSame(0, Service::count());
    }
}
