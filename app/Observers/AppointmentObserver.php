<?php

namespace App\Observers;

use App\Models\Appointment;

class AppointmentObserver
{
    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment)
{
    if ($appointment->isDirty('status') && $appointment->status === 'completed') {
        $appointment->user->patientHistories()->create([
            'clinic_name'   => $appointment->clinic->name,
            'doctor_name'   => $appointment->doctor->name ?? null,
            'diagnosis'     => $appointment->service->name,
            'treatment'     => null, // fill in if you have a treatment field on appointments
            'date_of_visit' => $appointment->appointment_date,
            'document_path' => null,
        ]);
    }
}

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "restored" event.
     */
    public function restored(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "force deleted" event.
     */
    public function forceDeleted(Appointment $appointment): void
    {
        //
    }
}
