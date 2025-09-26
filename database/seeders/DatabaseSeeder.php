<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Database\Seeders\AdminSeeder;
use Database\Seeders\ServicesSeeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );

        $this->call([
            AdminSeeder::class,
            ServicesSeeder::class,
        ]);
    }
}
