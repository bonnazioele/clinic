<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SecretarySeeder extends Seeder
{
    public function run()
    {
        User::where('email','secretary@cliniq.com')   // or ->where('id',3)
            ->update(['is_secretary'=>true]);
    }
}
