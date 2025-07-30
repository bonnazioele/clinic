<?php
// app/Notifications/AppointmentBooked.php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class AppointmentBooked extends Notification
{
    use Queueable;

    protected Appointment $appt;

    public function __construct(Appointment $appt)
    {
        $this->appt = $appt;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message'   => "Your appointment (#{$this->appt->id}) on {$this->appt->appointment_date} at {$this->appt->appointment_time} was booked.",
            'appointment_id' => $this->appt->id,
        ];
    }
}
