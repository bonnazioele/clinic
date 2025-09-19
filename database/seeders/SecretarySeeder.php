<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SecretarySeeder extends Seeder
{
    public function run()
    {
        User::where('email','secretary@cliniq.com')  
            ->update(['is_secretary'=>true]);
    }
}
