<?php

namespace App\Observers;

use App\Models\DoctorSchedule;

class DoctorScheduleObserver
{
    /**
     * Handle the DoctorSchedule "created" event.
     */
    public function created(DoctorSchedule $doctorSchedule): void
    {
        //
    }

    /**
     * Handle the DoctorSchedule "updated" event.
     */
    public function updated(DoctorSchedule $doctorSchedule): void
    {
        //
    }

    /**
     * Handle the DoctorSchedule "deleted" event.
     */
    public function deleted(DoctorSchedule $doctorSchedule): void
    {
        //
    }

    /**
     * Handle the DoctorSchedule "restored" event.
     */
    public function restored(DoctorSchedule $doctorSchedule): void
    {
        //
    }

    /**
     * Handle the DoctorSchedule "force deleted" event.
     */
    public function forceDeleted(DoctorSchedule $doctorSchedule): void
    {
        //
    }
}