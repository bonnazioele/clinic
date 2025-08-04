<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SecretarySeeder extends Seeder
{
    public function run()
    {
        $secretaries = [
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'secretary1@cliniq.com',
                'password' => Hash::make('admin123'),
                'phone' => '+1234567890',
                'age' => 28,
                'birthdate' => '1996-05-15',
                'address' => '123 Main St, City Center',
                'is_active' => true,
                'is_secretary' => true,
                'is_doctor' => false,
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'email' => 'secretary2@cliniq.com',
                'password' => Hash::make('admin123'),
                'phone' => '+1234567891',
                'age' => 32,
                'birthdate' => '1992-08-22',
                'address' => '456 Oak Ave, Downtown',
                'is_active' => true,
                'is_secretary' => true,
                'is_doctor' => false,
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Jennifer',
                'last_name' => 'Chen',
                'email' => 'secretary3@cliniq.com',
                'password' => Hash::make('admin123'),
                'phone' => '+1234567892',
                'age' => 26,
                'birthdate' => '1998-12-10',
                'address' => '789 Pine Rd, Uptown',
                'is_active' => true,
                'is_secretary' => true,
                'is_doctor' => false,
                'is_system_admin' => false,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($secretaries as $secretaryData) {
            User::firstOrCreate(
                ['email' => $secretaryData['email']], // Check if user exists by email
                $secretaryData // Create with this data if not found
            );
        }

        $this->command->info('Created 3 secretary users with password: admin123');
        $this->command->info('Secretary emails: secretary1@cliniq.com, secretary2@cliniq.com, secretary3@cliniq.com');
    }
}
