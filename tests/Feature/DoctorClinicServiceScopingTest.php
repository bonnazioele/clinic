<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\DoctorSchedule;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorClinicServiceScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_services_are_scoped_per_clinic(): void
    {
        $doctor = User::factory()->create(['is_doctor' => true]);
        $clinicA = Clinic::factory()->create();
        $clinicB = Clinic::factory()->create();
        $serviceA = Service::factory()->create();
        $serviceB = Service::factory()->create();

        $clinicA->services()->attach($serviceA->id);
        $clinicB->services()->attach($serviceB->id);
        $doctor->clinics()->sync([$clinicA->id, $clinicB->id]);

        $doctor->syncServicesForClinic($clinicA->id, [$serviceA->id]);
        $doctor->syncServicesForClinic($clinicB->id, [$serviceB->id]);

        $this->assertSame([$serviceA->id], $doctor->servicesForClinic($clinicA->id)->pluck('services.id')->all());
        $this->assertSame([$serviceB->id], $doctor->servicesForClinic($clinicB->id)->pluck('services.id')->all());
    }

    public function test_unassigning_doctor_from_one_clinic_cleans_only_that_clinic_data(): void
    {
        $secretary = User::factory()->create([
            'is_secretary' => true,
            'is_initial_login' => false,
        ]);
        $doctor = User::factory()->create(['is_doctor' => true]);
        $clinicA = Clinic::factory()->create();
        $clinicB = Clinic::factory()->create();
        $serviceA = Service::factory()->create();
        $serviceB = Service::factory()->create();

        $secretary->secretaryClinics()->sync([$clinicA->id, $clinicB->id]);
        $clinicA->services()->attach($serviceA->id);
        $clinicB->services()->attach($serviceB->id);
        $doctor->clinics()->sync([$clinicA->id, $clinicB->id]);
        $doctor->syncServicesForClinic($clinicA->id, [$serviceA->id]);
        $doctor->syncServicesForClinic($clinicB->id, [$serviceB->id]);

        DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinicA->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'is_active' => true,
        ]);
        DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinicB->id,
            'day_of_week' => 2,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'is_active' => true,
        ]);

        $this->actingAs($secretary)
            ->withSession(['active_clinic_id' => $clinicA->id])
            ->delete(route('secretary.doctors.destroy', $doctor))
            ->assertRedirect();

        $this->assertFalse($doctor->fresh()->clinics()->whereKey($clinicA->id)->exists());
        $this->assertTrue($doctor->fresh()->clinics()->whereKey($clinicB->id)->exists());
        $this->assertDatabaseMissing('doctor_service', [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinicA->id,
            'service_id' => $serviceA->id,
        ]);
        $this->assertDatabaseHas('doctor_service', [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinicB->id,
            'service_id' => $serviceB->id,
        ]);
        $this->assertDatabaseMissing('doctor_schedules', [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinicA->id,
        ]);
        $this->assertDatabaseHas('doctor_schedules', [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinicB->id,
        ]);
    }
}
