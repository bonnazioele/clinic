<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClinicType;
use App\Models\Service;
use App\Models\Role;
use App\Models\User;
use App\Models\Clinic;
use App\Models\ClinicUserRole;
use Illuminate\Support\Facades\Hash;

class ComprehensiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create 5 clinic types
        $clinicTypes = [
            ['type_name' => 'General Practice', 'description' => 'Primary healthcare clinic providing general medical services'],
            ['type_name' => 'Dental Clinic', 'description' => 'Specialized clinic for dental and oral health services'],
            ['type_name' => 'Eye Care Center', 'description' => 'Ophthalmology and optometry services'],
            ['type_name' => 'Pediatric Clinic', 'description' => 'Medical care specialized for infants, children, and adolescents'],
            ['type_name' => 'Orthopedic Clinic', 'description' => 'Specialized care for musculoskeletal system']
        ];

        foreach ($clinicTypes as $type) {
            ClinicType::firstOrCreate(['type_name' => $type['type_name']], $type);
        }

        // 2. Create 10 services with active statuses
        $services = [
            ['service_name' => 'General Consultation', 'description' => 'Basic medical consultation and examination', 'is_active' => true],
            ['service_name' => 'Blood Pressure Check', 'description' => 'Blood pressure monitoring and assessment', 'is_active' => true],
            ['service_name' => 'Vaccination', 'description' => 'Immunization services for various diseases', 'is_active' => true],
            ['service_name' => 'Dental Cleaning', 'description' => 'Professional dental cleaning and oral hygiene', 'is_active' => true],
            ['service_name' => 'Eye Examination', 'description' => 'Comprehensive eye health assessment', 'is_active' => true],
            ['service_name' => 'X-Ray Services', 'description' => 'Digital radiography and imaging services', 'is_active' => true],
            ['service_name' => 'Laboratory Tests', 'description' => 'Blood tests and diagnostic laboratory services', 'is_active' => true],
            ['service_name' => 'Physical Therapy', 'description' => 'Rehabilitation and physical therapy services', 'is_active' => true],
            ['service_name' => 'Minor Surgery', 'description' => 'Outpatient surgical procedures', 'is_active' => true],
            ['service_name' => 'Health Screening', 'description' => 'Preventive health checkups and screenings', 'is_active' => true]
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(['service_name' => $service['service_name']], $service);
        }

        // 3. Create 3 roles (secretary, staff, and doctor)
        $roles = [
            ['role_name' => 'secretary', 'description' => 'Administrative staff responsible for clinic management and patient coordination'],
            ['role_name' => 'staff', 'description' => 'General clinic staff providing support services']
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['role_name' => $role['role_name']], $role);
        }

        // 4. Create 3 system administrators
        $admins = [
            [
                'first_name' => 'John',
                'last_name' => 'Admin',
                'email' => 'admin1@clinic.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567890',
                'is_active' => true,
                'is_system_admin' => true,
                'age' => 35,
                'birthdate' => '1989-01-15',
                'address' => '123 Admin Street, City Center'
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'SystemAdmin',
                'email' => 'admin2@clinic.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567891',
                'is_active' => true,
                'is_system_admin' => true,
                'age' => 32,
                'birthdate' => '1992-05-20',
                'address' => '456 Management Ave, Downtown'
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'SuperAdmin',
                'email' => 'admin3@clinic.com',
                'password' => Hash::make('password123'),
                'phone' => '+1234567892',
                'is_active' => true,
                'is_system_admin' => true,
                'age' => 40,
                'birthdate' => '1984-09-10',
                'address' => '789 Executive Blvd, Business District'
            ]
        ];

        foreach ($admins as $admin) {
            User::firstOrCreate(['email' => $admin['email']], $admin);
        }

        // 5. Create 5 clinics with pending and approved statuses
        $createdClinicTypes = ClinicType::all();
        $adminUsers = User::where('is_system_admin', true)->get();

        $clinics = [
            [
                'user_id' => $adminUsers->first()->id,
                'name' => 'Central Medical Clinic',
                'type_id' => $createdClinicTypes->where('type_name', 'General Practice')->first()->id,
                'branch_code' => 'CMC001',
                'address' => '100 Main Street, Central City',
                'contact_number' => '+1555001001',
                'email' => 'info@centralmedical.com',
                'gps_latitude' => 40.7128,
                'gps_longitude' => -74.0060,
                'status' => 'Approved',
                'approved_at' => now()->subDays(5),
                'approved_by' => $adminUsers->first()->id
            ],
            [
                'user_id' => $adminUsers->skip(1)->first()->id,
                'name' => 'Smile Dental Care',
                'type_id' => $createdClinicTypes->where('type_name', 'Dental Clinic')->first()->id,
                'branch_code' => 'SDC002',
                'address' => '200 Dental Plaza, Westside',
                'contact_number' => '+1555002002',
                'email' => 'info@smiledentalcare.com',
                'gps_latitude' => 40.7589,
                'gps_longitude' => -73.9851,
                'status' => 'Approved',
                'approved_at' => now()->subDays(3),
                'approved_by' => $adminUsers->skip(1)->first()->id
            ],
            [
                'user_id' => $adminUsers->last()->id,
                'name' => 'Vision Eye Center',
                'type_id' => $createdClinicTypes->where('type_name', 'Eye Care Center')->first()->id,
                'branch_code' => 'VEC003',
                'address' => '300 Vision Boulevard, Eastside',
                'contact_number' => '+1555003003',
                'email' => 'info@visioneyecenter.com',
                'gps_latitude' => 40.7505,
                'gps_longitude' => -73.9934,
                'status' => 'Pending'
            ],
            [
                'user_id' => $adminUsers->first()->id,
                'name' => 'Little Stars Pediatric',
                'type_id' => $createdClinicTypes->where('type_name', 'Pediatric Clinic')->first()->id,
                'branch_code' => 'LSP004',
                'address' => '400 Children Avenue, Family District',
                'contact_number' => '+1555004004',
                'email' => 'info@littlestars.com',
                'gps_latitude' => 40.7282,
                'gps_longitude' => -74.0776,
                'status' => 'Pending'
            ],
            [
                'user_id' => $adminUsers->skip(1)->first()->id,
                'name' => 'Bone & Joint Specialists',
                'type_id' => $createdClinicTypes->where('type_name', 'Orthopedic Clinic')->first()->id,
                'branch_code' => 'BJS005',
                'address' => '500 Orthopedic Drive, Medical Center',
                'contact_number' => '+1555005005',
                'email' => 'info@boneandjoint.com',
                'gps_latitude' => 40.7614,
                'gps_longitude' => -73.9776,
                'status' => 'Approved',
                'approved_at' => now()->subDays(1),
                'approved_by' => $adminUsers->last()->id
            ]
        ];

        $createdClinics = [];
        foreach ($clinics as $clinic) {
            $createdClinics[] = Clinic::firstOrCreate(
                ['name' => $clinic['name']],
                $clinic
            );
        }

        // 6. Create 2 secretaries and 2 staff for each clinic
        $secretaryRole = Role::where('role_name', 'secretary')->first();
        $staffRole = Role::where('role_name', 'staff')->first();

        $secretaryNames = [
            ['first_name' => 'Emily', 'last_name' => 'Johnson'],
            ['first_name' => 'Jessica', 'last_name' => 'Williams'],
            ['first_name' => 'Amanda', 'last_name' => 'Brown'],
            ['first_name' => 'Michelle', 'last_name' => 'Davis'],
            ['first_name' => 'Lisa', 'last_name' => 'Wilson'],
            ['first_name' => 'Rachel', 'last_name' => 'Garcia'],
            ['first_name' => 'Nicole', 'last_name' => 'Martinez'],
            ['first_name' => 'Ashley', 'last_name' => 'Anderson'],
            ['first_name' => 'Stephanie', 'last_name' => 'Taylor'],
            ['first_name' => 'Jennifer', 'last_name' => 'Thomas']
        ];

        $staffNames = [
            ['first_name' => 'David', 'last_name' => 'Smith'],
            ['first_name' => 'Robert', 'last_name' => 'Johnson'],
            ['first_name' => 'James', 'last_name' => 'Williams'],
            ['first_name' => 'Christopher', 'last_name' => 'Brown'],
            ['first_name' => 'Daniel', 'last_name' => 'Jones'],
            ['first_name' => 'Matthew', 'last_name' => 'Garcia'],
            ['first_name' => 'Anthony', 'last_name' => 'Miller'],
            ['first_name' => 'Mark', 'last_name' => 'Davis'],
            ['first_name' => 'Donald', 'last_name' => 'Rodriguez'],
            ['first_name' => 'Steven', 'last_name' => 'Martinez']
        ];

        $clinicIndex = 0;
        foreach ($createdClinics as $clinic) {
            // Create 2 secretaries for each clinic
            for ($i = 0; $i < 2; $i++) {
                $secretaryData = $secretaryNames[$clinicIndex * 2 + $i];
                $secretary = User::firstOrCreate([
                    'email' => strtolower($secretaryData['first_name'] . '.' . $secretaryData['last_name'] . '.secretary' . ($i + 1) . '@' . str_replace(' ', '', strtolower($clinic->name)) . '.com')
                ], [
                    'first_name' => $secretaryData['first_name'],
                    'last_name' => $secretaryData['last_name'],
                    'password' => Hash::make('password123'),
                    'phone' => '+1555' . str_pad($clinicIndex + 1, 3, '0', STR_PAD_LEFT) . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'is_system_admin' => false,
                    'age' => rand(25, 45),
                    'birthdate' => now()->subYears(rand(25, 45))->format('Y-m-d'),
                    'address' => 'Secretary Address ' . ($i + 1) . ', ' . $clinic->name
                ]);

                // Assign secretary role to clinic
                ClinicUserRole::firstOrCreate([
                    'user_id' => $secretary->id,
                    'clinic_id' => $clinic->id,
                    'role_id' => $secretaryRole->id
                ], [
                    'is_active' => true,
                    'assigned_by' => $adminUsers->first()->id
                ]);
            }

            // Create 2 staff for each clinic
            for ($i = 0; $i < 2; $i++) {
                $staffData = $staffNames[$clinicIndex * 2 + $i];
                $staff = User::firstOrCreate([
                    'email' => strtolower($staffData['first_name'] . '.' . $staffData['last_name'] . '.staff' . ($i + 1) . '@' . str_replace(' ', '', strtolower($clinic->name)) . '.com')
                ], [
                    'first_name' => $staffData['first_name'],
                    'last_name' => $staffData['last_name'],
                    'password' => Hash::make('password123'),
                    'phone' => '+1666' . str_pad($clinicIndex + 1, 3, '0', STR_PAD_LEFT) . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'is_system_admin' => false,
                    'age' => rand(22, 40),
                    'birthdate' => now()->subYears(rand(22, 40))->format('Y-m-d'),
                    'address' => 'Staff Address ' . ($i + 1) . ', ' . $clinic->name
                ]);

                // Assign staff role to clinic
                ClinicUserRole::firstOrCreate([
                    'user_id' => $staff->id,
                    'clinic_id' => $clinic->id,
                    'role_id' => $staffRole->id
                ], [
                    'is_active' => true,
                    'assigned_by' => $adminUsers->first()->id
                ]);
            }

            // Create 1 doctor for each clinic
            $doctorRole = Role::where('role_name', 'doctor')->first();
            $doctorData = [
                ['first_name' => 'Dr. Michael', 'last_name' => 'Thompson'],
                ['first_name' => 'Dr. Sarah', 'last_name' => 'Chen'],
                ['first_name' => 'Dr. Robert', 'last_name' => 'Williams'],
                ['first_name' => 'Dr. Emily', 'last_name' => 'Rodriguez'],
                ['first_name' => 'Dr. David', 'last_name' => 'Park']
            ];

            $doctor = User::firstOrCreate([
                'email' => strtolower(str_replace([' ', '.'], '', $doctorData[$clinicIndex]['first_name']) . '.' . $doctorData[$clinicIndex]['last_name'] . '@' . str_replace(' ', '', strtolower($clinic->name)) . '.com')
            ], [
                'first_name' => $doctorData[$clinicIndex]['first_name'],
                'last_name' => $doctorData[$clinicIndex]['last_name'],
                'password' => Hash::make('password123'),
                'phone' => '+1777' . str_pad($clinicIndex + 1, 3, '0', STR_PAD_LEFT) . '001',
                'is_active' => true,
                'is_system_admin' => false,
                'age' => rand(30, 55),
                'birthdate' => now()->subYears(rand(30, 55))->format('Y-m-d'),
                'address' => 'Doctor Address, ' . $clinic->name
            ]);

            // Assign doctor role to clinic
            ClinicUserRole::firstOrCreate([
                'user_id' => $doctor->id,
                'clinic_id' => $clinic->id,
                'role_id' => $doctorRole->id
            ], [
                'is_active' => true,
                'assigned_by' => $adminUsers->first()->id
            ]);

            $clinicIndex++;
        }

        // 7. Assign 3 services to each clinic
        $allServices = Service::all();
        foreach ($createdClinics as $clinic) {
            // Get 3 random services for each clinic
            $clinicServices = $allServices->random(3);
            
            foreach ($clinicServices as $service) {
                // Use the clinic_service pivot table
                $clinic->services()->syncWithoutDetaching([
                    $service->id => [
                        'duration_minutes' => rand(15, 60), // Random duration between 15-60 minutes
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ]);
            }
        }

        $this->command->info('Comprehensive seeding completed!');
        $this->command->info('Created:');
        $this->command->info('- 5 clinic types');
        $this->command->info('- 10 active services');
        $this->command->info('- 3 roles (secretary, staff, doctor)');
        $this->command->info('- 3 system administrators');
        $this->command->info('- 5 clinics (3 approved, 2 pending)');
        $this->command->info('- 2 secretaries and 2 staff for each clinic (20 users total)');
        $this->command->info('- 1 doctor for each clinic (5 doctors total)');
        $this->command->info('- 3 services assigned to each clinic');
    }
}
