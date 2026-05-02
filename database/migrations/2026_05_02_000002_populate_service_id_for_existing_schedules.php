<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use App\Models\DoctorSchedule;

return new class extends Migration {
    public function up(): void
    {
        // Populate service_id for existing schedules that have null service_id
        $schedules = DoctorSchedule::whereNull('service_id')->get();

        foreach ($schedules as $schedule) {
            // Find a service for this doctor in this clinic
            $service = DB::table('doctor_service')
                ->where('doctor_id', $schedule->doctor_id)
                ->where('clinic_id', $schedule->clinic_id)
                ->first();

            if ($service) {
                $schedule->update(['service_id' => $service->service_id]);
            }
        }
    }

    public function down(): void
    {
        // Reset service_id to null for schedules that were populated
        DoctorSchedule::whereNotNull('service_id')->update(['service_id' => null]);
    }
};
