<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AppointmentStatusChanged extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via($notifiable): array
    {
        return ['database','broadcast'];
    }

    public function broadcastOn(): array
    {
        return [new \Illuminate\Broadcasting\PrivateChannel('user.notifications.'.$this->appointment->user_id)];
    }

    public function toBroadcast($notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => "Your appointment status has been updated to {$this->appointment->status}.",
            'appointment_id' => $this->appointment->id,
        ];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => "Your appointment status has been updated to {$this->appointment->status}.",
            'appointment_id' => $this->appointment->id,
        ];
    }
}
