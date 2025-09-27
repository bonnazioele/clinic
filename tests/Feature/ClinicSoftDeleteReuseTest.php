<?php

namespace Tests\Feature;

use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicSoftDeleteReuseTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_reuse_email_and_branch_code_after_soft_delete(): void
    {
        $clinic = Clinic::factory()->create([
            'email' => 'reuse@example.com',
            'branch_code' => 'REUSE-CODE-1',
        ]);

        $clinic->delete();

        $payload = [
            'clinic_name' => 'New Clinic Reuse',
            'clinic_address' => 'Address',
            'clinic_contact' => '12345',
            'branch_code' => 'REUSE-CODE-1',
            'clinic_email' => 'reuse@example.com',
        ];

        $response = $this->post('/apply/clinic', $payload);
        $response->assertRedirect(route('owner.apply.thanks'));
        $this->assertDatabaseHas('clinics', [
            'email' => 'reuse@example.com',
            'branch_code' => 'REUSE-CODE-1',
            'status' => 'pending',
            'deleted_at' => null,
        ]);
    }
}
