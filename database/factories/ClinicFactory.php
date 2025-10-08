<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClinicFactory extends Factory
{
    protected $model = Clinic::class;

    public function definition(): array
    {
        $name = $this->faker->company().' Clinic';
        return [
            'created_by_user_id' => null,
            'name' => $name,
            'branch_code' => strtoupper(Str::random(6)),
            'address' => $this->faker->address(),
            'contact_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'contact_first_name' => null,
            'contact_last_name' => null,
            'logo' => null,
            'cover_image' => null,
            'description' => $this->faker->sentence(),
            'gps_latitude' => $this->faker->randomFloat(6,-90,90),
            'gps_longitude' => $this->faker->randomFloat(6,-180,180),
            'status' => 'approved',
            'queue_mode' => 'fcfs',
        ];
    }
}
