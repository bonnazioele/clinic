<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SecretaryAppointmentBooked extends Notification implements ShouldBroadcast
{
    use Queueable;
    public function __construct(public Appointment $appointment) {}
    public function via($n){ return ['database','broadcast']; }
    public function toDatabase($n): array
    { return [
        'message' => "New appointment (#{$this->appointment->id}) booked for {$this->appointment->appointment_date} at {$this->appointment->appointment_time} (Patient: ".$this->appointment->user?->name.").",
        'appointment_id' => $this->appointment->id,
        'role' => 'secretary'
      ]; }
  public function toBroadcast($notifiable)
  {
    return new \Illuminate\Notifications\Messages\BroadcastMessage($this->toArray($notifiable));
  }

  public function toArray($notifiable)
  {
    return [
      'message' => "New appointment (#{$this->appointment->id}) booked for {$this->appointment->appointment_date} at {$this->appointment->appointment_time} (Patient: ".$this->appointment->user?->name.").",
      'appointment_id' => $this->appointment->id,
      'role' => 'secretary'
    ];
  }

  public function broadcastOn(): array
  {
    // Secretary notifications typically go to the patient user who owns the appointment
    // Adjust if later you want to broadcast to a secretary-specific channel.
    return [new \Illuminate\Broadcasting\PrivateChannel('user.notifications.' . $this->appointment->user_id)];
  }
}
