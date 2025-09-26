<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesSeeder extends Seeder
{
    public function run()
    {
        $services = config('services_catalog.services', []);

        foreach ($services as $svcName) {
            DB::table('services')->updateOrInsert(
                ['name' => $svcName],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
