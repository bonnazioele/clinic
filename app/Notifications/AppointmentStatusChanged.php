<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class AppointmentStatusChanged extends Notification
{
    use Queueable;

    protected Appointment $appointment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification’s delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation for the database channel.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'message' => "Your appointment (#{$this->appointment->id}) status changed to “{$this->appointment->status}.”",
            'appointment_id' => $this->appointment->id,
        ];
    }
}
