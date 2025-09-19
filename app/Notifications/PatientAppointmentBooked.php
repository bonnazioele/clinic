<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PatientAppointmentBooked extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via($notifiable) { return ['database','broadcast']; }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => "Appointment (#{$this->appointment->id}) confirmed for {$this->appointment->appointment_date} at {$this->appointment->appointment_time}.",
            'appointment_id' => $this->appointment->id,
            'role' => 'patient'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "Appointment (#{$this->appointment->id}) confirmed for {$this->appointment->appointment_date} at {$this->appointment->appointment_time}.",
            'appointment_id' => $this->appointment->id,
            'role' => 'patient'
        ];
    }

    public function broadcastOn(): array
    {
        // Use the appointment's owning user for channel scoping
        return [new \Illuminate\Broadcasting\PrivateChannel('user.notifications.' . $this->appointment->user_id)];
    }
}
