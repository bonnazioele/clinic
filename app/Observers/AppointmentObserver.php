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
    public function updated(Appointment $appointment): void
    {
        if (! $appointment->wasChanged('status')) {
            return;
        }

        if ($appointment->status === 'cancelled') {
            $this->syncQueueStatus($appointment, 'cancelled');
            return;
        }

        if ($appointment->status === 'no_show') {
            $this->syncQueueStatus($appointment, 'no_show');
            return;
        }

        if ($appointment->status === 'rescheduled') {
            $this->syncQueueStatus($appointment, 'rescheduled');
            return;
        }

        if ($appointment->status === 'completed') {
            QueueEntry::query()
                ->where('appointment_id', $appointment->id)
                ->whereNotIn('status', [
                    'served',
                    'completed',
                    'cancelled',
                    'no_show',
                    'rescheduled',
                ])
                ->update([
                    'status' => 'completed',
                ]);

            $this->createPatientHistory($appointment);
        }
    }

    /**
     * Sync appointment final statuses to its queue entry.
     */
    private function syncQueueStatus(Appointment $appointment, string $status): void
    {
        if (! in_array($status, ['cancelled', 'no_show', 'rescheduled'], true)) {
            return;
        }

        QueueEntry::query()
            ->where('appointment_id', $appointment->id)
            ->whereNotIn('status', [
                'served',
                'completed',
                'cancelled',
                'no_show',
                'rescheduled',
            ])
            ->update([
                'status' => $status,
            ]);
    }

    /**
     * Create patient history after appointment completion.
     */
    private function createPatientHistory(Appointment $appointment): void
    {
        $appointment->loadMissing([
            'clinic',
            'doctor',
            'user',
        ]);

        if (! $appointment->user) {
            return;
        }

        $doctorName = null;

        if ($appointment->doctor) {
            $doctorName = trim(
                (string) ($appointment->doctor->first_name ?? '') . ' ' .
                (string) ($appointment->doctor->last_name ?? '')
            );

            if ($doctorName === '') {
                $doctorName = $appointment->doctor->name ?? null;
            }
        }

        $appointment->user->patientHistories()->create([
            'clinic_name'   => $appointment->clinic?->name,
            'doctor'        => $doctorName,
            'diagnosis'     => null,
            'treatment'     => null,
            'date_of_visit' => $appointment->appointment_date,
            'document_path' => $appointment->medical_document,
        ]);
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