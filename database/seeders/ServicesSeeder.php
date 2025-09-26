<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesSeeder extends Seeder
{
    public function run()
    {
        $services = [
            'General Consultation',
            'Outpatient Department (OPD)',
            'Emergency Room Services',
            'Pediatrics / Neonatal Care',
            'Internal Medicine',
            'Surgery / Orthopedics',
            'OB-Gynecology',
            'Ophthalmology',
            'Dental Services',
            'Laboratory Tests (CBC, Chemistry, etc.)',
            'Imaging / Diagnostics (X-ray, Ultrasound, Mammography)',
            'Health Check-Up Packages',
            'Physical Therapy & Rehabilitation',
            'Wound Care / Diabetic Foot Clinic',
            'Casting / Splinting',
            'Musculoskeletal Ultrasound',
            'Vision / Optometric Services',
            'Preventive Care & School Exams',
        ];

        foreach ($services as $svcName) {
            DB::table('services')->updateOrInsert(
                ['name' => $svcName],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
