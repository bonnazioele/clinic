<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure default password hashing for tests
    }

    private function createSecretary(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'password' => Hash::make('TempPass123!'),
            'is_secretary' => true,
            'is_initial_login' => true,
        ], $overrides));
    }

    private function createPatient(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'password' => Hash::make('TempPass123!'),
            'is_secretary' => false,
            'is_initial_login' => true, // Should NOT trigger redirect for non-secretary now
        ], $overrides));
    }

    public function test_secretary_with_initial_login_flag_is_redirected()
    {
        $user = $this->createSecretary();

    $response = $this->actingAs($user)->get('/secretary/dashboard');

    $response->assertRedirect(route('secretary.auth.password.force.show'));
    }

    public function test_secretary_after_password_change_not_redirected()
    {
        $user = $this->createSecretary(['is_initial_login' => false]);

    $response = $this->actingAs($user)->get('/secretary/dashboard');

        $response->assertOk();
    }

    public function test_non_secretary_not_redirected_even_if_initial_login_true()
    {
        $user = $this->createPatient();

    $response = $this->actingAs($user)->get('/dashboard'); // patient dashboard still accessible

        $response->assertOk();
    }

    public function test_doctor_not_redirected_even_if_initial_login_true()
    {
        $user = User::factory()->create([
            'password' => Hash::make('TempPass123!'),
            'is_doctor' => true,
            'is_secretary' => false,
            'is_initial_login' => true,
        ]);

        // Access doctor dashboard
        $response = $this->actingAs($user)->get('/doctor/dashboard');
        $response->assertOk();
    }

    public function test_admin_not_redirected_even_if_initial_login_true()
    {
        $user = User::factory()->create([
            'password' => Hash::make('TempPass123!'),
            'is_admin' => true,
            'is_secretary' => false,
            'is_initial_login' => true,
        ]);

        // Admin dashboard is /admin (route name admin.dashboard)
        $response = $this->actingAs($user)->get('/admin');
        $response->assertOk();
    }

    public function test_force_password_change_endpoint_reachable_for_secretary()
    {
        $user = $this->createSecretary();

    $response = $this->actingAs($user)->get(route('secretary.auth.password.force.show'));

        $response->assertOk();
    }
}
