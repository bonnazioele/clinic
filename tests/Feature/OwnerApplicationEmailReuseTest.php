<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerApplicationEmailReuseTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_email_can_be_reused_if_only_linked_to_deleted_clinic(): void
    {
        config()->set('clinic_permits.types', []);
        Storage::fake('public');

        $owner = User::factory()->create([
            'email' => 'owner-reapply@example.com',
            'is_secretary' => true,
            'is_active' => false,
        ]);

        $clinic = Clinic::factory()->create([
            'created_by_user_id' => $owner->id,
            'contact_person_email' => $owner->email,
            'email' => 'clinic-deleted@example.com',
            'branch_code' => 'DEL-001',
            'status' => 'approved',
        ]);
        $clinic->secretaries()->syncWithoutDetaching([$owner->id]);
        $clinic->delete();

        $payload = [
            'clinic_name' => 'Reapply Clinic',
            'clinic_address' => 'Address 123',
            'clinic_contact' => '09171234567',
            'branch_code' => 'DEL-001',
            'clinic_email' => 'clinic-deleted@example.com',
            'contact_first_name' => 'Owner',
            'contact_last_name' => 'Again',
            'contact_person_email' => 'owner-reapply@example.com',
            'latitude' => '14.5995',
            'longitude' => '120.9842',
            'logo' => UploadedFile::fake()->image('logo.jpg'),
        ];

        $response = $this->post(route('owner.apply.store'), $payload);

        $response->assertRedirect(route('owner.apply.thanks'));
        $this->assertDatabaseHas('clinics', [
            'name' => 'Reapply Clinic',
            'contact_person_email' => 'owner-reapply@example.com',
            'status' => 'pending',
            'deleted_at' => null,
        ]);
    }

    public function test_contact_email_still_blocked_for_active_non_secretary_account(): void
    {
        config()->set('clinic_permits.types', []);

        User::factory()->create([
            'email' => 'existing-patient@example.com',
            'is_secretary' => false,
        ]);

        $payload = [
            'clinic_name' => 'Another Clinic',
            'clinic_address' => 'Address 456',
            'clinic_contact' => '09987654321',
            'branch_code' => 'NEW-002',
            'clinic_email' => 'another@example.com',
            'contact_first_name' => 'Jane',
            'contact_last_name' => 'Doe',
            'contact_person_email' => 'existing-patient@example.com',
            'latitude' => '14.5995',
            'longitude' => '120.9842',
        ];

        $response = $this->from(route('owner.apply'))->post(route('owner.apply.store'), $payload);

        $response->assertRedirect(route('owner.apply'));
        $response->assertSessionHasErrors('contact_person_email');
    }
}
