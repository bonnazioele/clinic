<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\QueueEntry;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        // Prefer the restored clinic used by the signed-in doctor.
        $clinic = Clinic::find(1) ?? Clinic::first();
        if (! $clinic) {
            $clinic = Clinic::factory()->create();
        }

        // Create or get a doctor user
        $doctor = User::firstOrCreate(
            ['email' => 'doctor@example.com'],
            [
                'name' => 'Dr. Smith',
                'password' => bcrypt('password'),
                'is_active' => true,
                'is_doctor' => true,
            ]
        );

        // Assign doctor to clinic
        if (!$doctor->clinics()->where('clinic_id', $clinic->id)->exists()) {
            $doctor->clinics()->attach($clinic->id, ['assigned_by' => 1]);
        }

        // Create or get services
        $services = Service::limit(3)->get();
        if ($services->isEmpty()) {
            $services = collect([
                Service::create(['name' => 'General Checkup', 'description' => 'General medical checkup']),
                Service::create(['name' => 'Consultation', 'description' => 'Doctor consultation']),
                Service::create(['name' => 'Follow-up', 'description' => 'Follow-up appointment']),
            ]);
        }

        if ($clinic->services()->count() === 0) {
            $clinic->services()->syncWithoutDetaching(
                $services->pluck('id')->mapWithKeys(fn ($serviceId) => [(int) $serviceId => ['duration_minutes' => 30]])->all()
            );
        }

        // Create or reuse dedicated test patients for reports/export.
        $patients = collect();
        for ($i = 1; $i <= 5; $i++) {
            $patients->push(
                User::updateOrCreate(
                    ['email' => 'report-patient' . $i . '@test.com'],
                    [
                        'name' => 'Report Patient ' . $i,
                        'password' => bcrypt('password'),
                        'is_active' => true,
                    ]
                )
            );
        }

        // Create appointments for the past 30 days to today
        $statuses = ['scheduled', 'completed', 'cancelled', 'no_show'];
        $times = ['09:00', '10:30', '13:00', '14:30', '15:00', '16:00', '11:00', '12:00', '17:00', '08:30'];

        $appointmentCount = 0;
        for ($i = 0; $i < 25; $i++) {
            $appointmentDate = Carbon::now()->subDays(rand(0, 30))->toDateString();
            $timeIndex = $appointmentCount % count($times);
            
            try {
                Appointment::create([
                    'user_id' => $patients->random()->id,
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $clinic->id,
                    'service_id' => $services->random()->id,
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => $times[$timeIndex],
                    'status' => $statuses[array_rand($statuses)],
                    'notes' => fake()->optional(0.6)->sentence(),
                ]);
                $appointmentCount++;
            } catch (\Exception $e) {
                // Skip duplicate unique constraint violations
                continue;
            }
        }

        $today = Carbon::now()->toDateString();

        User::where('is_doctor', true)
            ->with('clinics')
            ->get()
            ->each(function (User $doctor) use ($services, $patients, $today) {
                $doctor->clinics->each(function (Clinic $clinic, int $clinicIndex) use ($doctor, $services, $patients, $today) {
                    $this->seedQueueReportFixtures($doctor, $clinic, $services, $patients, $today, $clinicIndex);
                });
            });
    }

    private function seedQueueReportFixtures(User $doctor, Clinic $clinic, $fallbackServices, $patients, string $today, int $clinicIndex = 0): void
    {
        if ($patients->isEmpty()) {
            return;
        }

        $services = $doctor->servicesForClinic($clinic->id)->get();

        if ($services->isEmpty()) {
            $services = $clinic->services()->get();
        }

        if ($services->isEmpty()) {
            $services = collect($fallbackServices);
        }

        if ($services->isEmpty()) {
            return;
        }

        // Guaranteed rows for today's export and queue reports.
        $queueFixtures = [
            ['time' => '06:00', 'appointment_status' => 'completed', 'queue_status' => 'served', 'called_after' => 5, 'served_after' => 16],
            ['time' => '06:30', 'appointment_status' => 'completed', 'queue_status' => 'served', 'called_after' => 4, 'served_after' => 15],
            ['time' => '07:00', 'appointment_status' => 'scheduled', 'queue_status' => 'waiting'],
            ['time' => '07:30', 'appointment_status' => 'scheduled', 'queue_status' => 'waiting'],
            ['time' => '08:00', 'appointment_status' => 'no_show', 'queue_status' => 'no_show'],
            ['time' => '08:30', 'appointment_status' => 'cancelled', 'queue_status' => 'cancelled'],
        ];

        foreach ($queueFixtures as $index => $fixture) {
            $patient = User::updateOrCreate(
                ['email' => 'report-patient-c' . $clinic->id . '-d' . $doctor->id . '-' . ($index + 1) . '@test.com'],
                [
                    'name' => 'Report Patient ' . ($index + 1),
                    'password' => bcrypt('password'),
                    'is_active' => true,
                ]
            );
            $appointmentAt = Carbon::parse($today . ' ' . $fixture['time'])
                ->addMinutes($clinicIndex * 5);
            $time = $appointmentAt->format('H:i');

            $appointment = Appointment::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'appointment_date' => $today,
                    'appointment_time' => $time,
                ],
                [
                    'clinic_id' => $clinic->id,
                    'user_id' => $patient->id,
                    'service_id' => $services->random()->id,
                    'status' => $fixture['appointment_status'],
                    'notes' => fake()->optional(0.6)->sentence(),
                ]
            );

            $calledAt = isset($fixture['called_after'])
                ? $appointmentAt->copy()->addMinutes($fixture['called_after'])
                : null;
            $servedAt = isset($fixture['served_after'])
                ? $appointmentAt->copy()->addMinutes($fixture['served_after'])
                : null;

            QueueEntry::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'clinic_id' => $clinic->id,
                    'user_id' => $patient->id,
                    'patient_id' => null,
                    'doctor_id' => $doctor->id,
                    'queue_number' => $index + 1,
                    'status' => $fixture['queue_status'],
                    'called_at' => $calledAt,
                    'served_at' => $servedAt,
                    'created_at' => $appointmentAt->copy()->subMinutes(10),
                    'updated_at' => $servedAt ?? $calledAt ?? now(),
                ]
            );
        }
    }
}
