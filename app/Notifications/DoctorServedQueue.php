<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class DoctorServedQueue extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
        $this->appointment->loadMissing([
            'doctor',
            'service',
            'patient',
            'user',
        ]);
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return $this->payload();
    }

    public function toArray($notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    private function payload(): array
    {
        $serviceId = $this->appointment->service_id
            ?? $this->appointment->service?->id
            ?? null;

        $doctorId = $this->appointment->doctor_id
            ?? $this->appointment->doctor?->id
            ?? null;

        $patientName = $this->appointment->patient?->name
            ?? $this->appointment->user?->name
            ?? 'The patient';

        $doctorName = $this->appointment->doctor?->name
            ?? trim(($this->appointment->doctor?->first_name ?? '') . ' ' . ($this->appointment->doctor?->last_name ?? ''))
            ?: 'the doctor';

        $queueUrl = null;

        if ($serviceId && $doctorId) {
            $queueUrl = Route::has('secretary.services.doctors.queue')
                ? route('secretary.services.doctors.queue', [
                    'service_id' => $serviceId,
                    'doctor_id' => $doctorId,
                ])
                : url("/secretary/services/{$serviceId}/doctors/{$doctorId}/queue");
        }

        return [
            'role' => 'secretary',
            'type' => 'doctor_served',

            'message' => "{$patientName} was marked as served by Dr. {$doctorName}. Please click Done & Next.",

            'appointment_id' => $this->appointment->id,
            'clinic_id' => $this->appointment->clinic_id,
            'service_id' => $serviceId,
            'doctor_id' => $doctorId,

            'url' => $queueUrl,
            'queue_url' => $queueUrl,
            'redirect_url' => $queueUrl,
        ];
    }
}