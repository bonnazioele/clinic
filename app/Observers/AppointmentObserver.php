<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\QueueEntry;

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
        if (! $appointment->wasChanged('status')) {
            return;
        }

        if ($appointment->status === 'cancelled') {
            $this->syncQueueStatus($appointment, 'cancelled');
        }

        if ($appointment->status === 'no_show') {
            $this->syncQueueStatus($appointment, 'no_show');
        }

        if ($appointment->status === 'completed') {
            // Only push queue entries that are still in an active state to 'completed'.
            // Entries already at 'served' or 'completed' are managed by the queue
            // system (secretary's Done & Next) and must NOT be touched here.
            // This prevents the observer from interfering with the served → completed flow.
            QueueEntry::query()
                ->where('appointment_id', $appointment->id)
                ->whereNotIn('status', ['served', 'completed', 'cancelled', 'no_show', 'rescheduled'])
                ->update(['status' => 'completed']);

            $doctorName = null;
            if ($appointment->doctor) {
                $doctorName = trim((string) ($appointment->doctor->first_name ?? '') . ' ' . (string) ($appointment->doctor->last_name ?? ''));
                if ($doctorName === '') {
                    $doctorName = null;
                }
            }

            if (! $appointment->user) {
                return;
            }

            $appointment->user->patientHistories()->create([
                'clinic_name'   => $appointment->clinic->name,
                'doctor'        => $doctorName,
                'diagnosis'     => null,
                'treatment'     => null,
                'date_of_visit' => $appointment->appointment_date,
                'document_path' => $appointment->medical_document,
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

    private function syncQueueStatus(Appointment $appointment, string $status): void
    {
        if (! in_array($status, ['cancelled', 'no_show'], true)) {
            return;
        }

        QueueEntry::query()
            ->where('appointment_id', $appointment->id)
            ->whereNotIn('status', ['served', 'completed', 'cancelled', 'no_show', 'rescheduled'])
            ->update(['status' => $status]);
    }
}