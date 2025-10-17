<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DoctorMarkedAppointmentDone extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'message' => 'Doctor marked appointment #' . $this->appointment->id . ' for ' . $this->appointment->user->name . ' as done and awaits finalization.',
        ];
    }
}
