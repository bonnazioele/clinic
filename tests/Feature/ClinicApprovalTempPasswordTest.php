<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Mail\ClinicApprovedMail;

class ClinicApprovalTempPasswordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function approving_clinic_generates_temp_password_for_new_owner()
    {
        Mail::fake();

        $clinic = Clinic::factory()->create([
            'status' => 'pending',
            'email' => 'newowner@example.test',
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->post(route('admin.clinics.approve', $clinic));

        Mail::assertSent(ClinicApprovedMail::class, function($mail) use ($clinic) {
            return $mail->email === $clinic->email && !empty($mail->password);
        });

        $owner = User::where('email', $clinic->email)->first();
        $this->assertNotNull($owner);
        $this->assertTrue($owner->is_initial_login, 'Owner should be forced to change password');
    }

    /** @test */
    public function approving_clinic_resets_password_for_existing_owner()
    {
        Mail::fake();

        $owner = User::factory()->create([
            'email' => 'existing@example.test',
            'password' => 'OldPassword!123',
            'is_secretary' => false,
            'is_initial_login' => false,
        ]);

        $clinic = Clinic::factory()->create([
            'status' => 'pending',
            'email' => $owner->email,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->post(route('admin.clinics.approve', $clinic));

        Mail::assertSent(ClinicApprovedMail::class, function($mail) use ($clinic) {
            return $mail->email === $clinic->email && !empty($mail->password);
        });

        $owner->refresh();
        $this->assertTrue($owner->is_secretary, 'Existing owner should be marked secretary for login flow');
        $this->assertTrue($owner->is_initial_login, 'Existing owner should now be forced to change password');
    }
}
