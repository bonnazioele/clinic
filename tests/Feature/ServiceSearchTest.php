<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Service;

class ServiceSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_matching_services()
    {
        Service::factory()->create(['name' => 'General Consultation']);
        Service::factory()->create(['name' => 'Dental Cleaning']);
        Service::factory()->create(['name' => 'Dermatology']);

        $response = $this->getJson('/services/search?q=dent');
        $response->assertOk()
                 ->assertJsonStructure(['data' => [['id','name']]]);

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertContains('Dental Cleaning', $names);
        $this->assertNotContains('General Consultation', $names); // ensure filter applied
    }

    public function test_search_without_query_limits_results()
    {
        Service::factory()->count(30)->create();
        $response = $this->getJson('/services/search');
        $response->assertOk();
        $this->assertLessThanOrEqual(20, count($response->json('data'))); // default limit 20
    }
}
