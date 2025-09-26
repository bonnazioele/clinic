<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'name' => 'System Admin One',
                'email' => 'admin@cliniq.com',
                'phone' => '09170000001',
                'address' => 'HQ - Floor 1',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'System Admin Two',
                'email' => 'admin2@cliniq.com',
                'phone' => '09170000002',
                'address' => 'HQ - Floor 2',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'System Admin Three',
                'email' => 'admin3@cliniq.com',
                'phone' => '09170000003',
                'address' => 'HQ - Floor 3',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($admins as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                $data + ['is_admin' => true, 'is_active' => true]
            );
        }
    }
}
