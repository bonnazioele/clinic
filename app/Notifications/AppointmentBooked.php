<?php
// app/Notifications/AppointmentBooked.php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AppointmentBooked extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected Appointment $appt;

    public function __construct(Appointment $appt)
    {
        $this->appt = $appt;
    }

    public function via($notifiable)
    {
        return ['database','broadcast'];
    }

    public function broadcastOn(): array
    {
        return [new \Illuminate\Broadcasting\PrivateChannel('user.notifications.'.$this->appt->user_id)];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message'   => "Your appointment (#{$this->appt->id}) on {$this->appt->appointment_date} at {$this->appt->appointment_time} was booked.",
            'appointment_id' => $this->appt->id,
        ];
    }
}
