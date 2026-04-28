<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\QueueEntry;
use Illuminate\Support\Facades\DB;

class AppointmentObserver
{
    private ?string $completedQueueStatus = null;

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
            $this->syncQueueStatus($appointment, $this->resolveCompletedQueueStatus());

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
                'treatment'     => null, // fill in if you have a treatment field on appointments
                'date_of_visit' => $appointment->appointment_date,
                'document_path' => $appointment->medical_document,
            ]);
        }
    }

    private function syncQueueStatus(Appointment $appointment, string $targetStatus): void
    {
        QueueEntry::query()
            ->where('appointment_id', $appointment->id)
            ->where('status', '!=', $targetStatus)
            ->update(['status' => $targetStatus]);
    }

    private function resolveCompletedQueueStatus(): string
    {
        if ($this->completedQueueStatus !== null) {
            return $this->completedQueueStatus;
        }

        $columnType = DB::selectOne("SELECT COLUMN_TYPE AS column_type FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'queue_entries' AND COLUMN_NAME = 'status'");
        $type = strtolower((string) ($columnType->column_type ?? ''));

        $this->completedQueueStatus = str_contains($type, "'done'") ? 'done' : 'served';

        return $this->completedQueueStatus;
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
