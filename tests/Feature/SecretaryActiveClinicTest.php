<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Clinic;

class SecretaryActiveClinicTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSecretaryWithClinics(int $count = 2): array
    {
        $secretary = User::factory()->create(['is_secretary' => true]);
        $clinics = Clinic::factory()->count($count)->create();
        $secretary->secretaryClinics()->sync($clinics->pluck('id'));
        return [$secretary, $clinics];
    }

    public function test_active_clinic_auto_selected_and_persisted()
    {
        [$secretary, $clinics] = $this->makeSecretaryWithClinics();
        $this->actingAs($secretary)
            ->get('/secretary/dashboard')
            ->assertStatus(200);
        $this->assertNotNull(session('active_clinic_id'));
        $this->assertTrue($clinics->pluck('id')->contains(session('active_clinic_id')));
    }

    public function test_switch_active_clinic()
    {
        [$secretary, $clinics] = $this->makeSecretaryWithClinics();
        $this->actingAs($secretary)
            ->post('/secretary/active-clinic', ['clinic_id' => $clinics->last()->id])
            ->assertRedirect();
        $this->assertEquals($clinics->last()->id, session('active_clinic_id'));
    }
}
