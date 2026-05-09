<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'doctor_id' => User::factory(),
            'clinic_id' => Clinic::factory(),
            'service_id' => Service::factory(),
            'appointment_date' => $this->faker->dateTimeBetween('-30 days', '+30 days'),
            'appointment_time' => $this->faker->time('H:i'),
            'status' => $this->faker->randomElement(['scheduled', 'completed', 'cancelled', 'no_show']),
            'notes' => $this->faker->optional()->text(),
        ];
    }
}
