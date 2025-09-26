<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Clinic;

class SecretaryClinicAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_mismatch_clinic_param_is_forbidden()
    {
        $secretary = User::factory()->create(['is_secretary' => true]);
        $clinicA = Clinic::factory()->create();
        $clinicB = Clinic::factory()->create();
        $secretary->secretaryClinics()->sync([$clinicA->id, $clinicB->id]);

        // Hit dashboard to set active clinic (auto selects first by name order)
        $this->actingAs($secretary)->get('/secretary/dashboard')->assertStatus(200);
        $activeId = session('active_clinic_id');
        $otherId = $activeId === $clinicA->id ? $clinicB->id : $clinicA->id;

        // Calling a route with mismatched clinic param should 403
        $this->get("/secretary/clinics/{$otherId}/queue")
            ->assertStatus(403);
    }
}
