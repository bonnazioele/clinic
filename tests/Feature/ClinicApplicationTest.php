<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Clinic;

class ClinicApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_clinic_application_with_clinic_name_only()
    {
        $payload = [
            'clinic_name' => 'Test Health Center',
            'clinic_address' => '123 Main St, City',
            'clinic_contact' => '+63 900 111 2222',
            'branch_code' => 'BRANCH-TEST-001',
            'clinic_email' => 'branch@example.com',
        ];

        $response = $this->post('/apply/clinic', $payload);

        $response->assertRedirect(route('owner.apply.thanks'));
        $this->assertDatabaseHas('clinics', [
            'name' => 'Test Health Center',
            'branch_code' => 'BRANCH-TEST-001',
            'email' => 'branch@example.com',
            'status' => 'pending',
        ]);
    }
}
