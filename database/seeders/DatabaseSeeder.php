<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run comprehensive seeder that creates all required data
        $this->call([
            ComprehensiveSeeder::class,
        ]);

        $this->command->info('Database seeding completed!');
    }
}
