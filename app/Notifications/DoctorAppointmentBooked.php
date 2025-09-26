<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DoctorAppointmentBooked extends Notification implements ShouldBroadcast
{
    use Queueable;
    public function __construct(public Appointment $appointment) {}
    public function via($n){ return ['database','broadcast']; }
    public function toDatabase($n): array
    { return [
        'message' => "New patient (".$this->appointment->user?->name.") booked for {$this->appointment->appointment_date} at {$this->appointment->appointment_time}.",
        'appointment_id' => $this->appointment->id,
        'role' => 'doctor'
      ]; }
    public function toBroadcast($notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray($notifiable): array
    { return [
        'message' => "New patient (".$this->appointment->user?->name.") booked for {$this->appointment->appointment_date} at {$this->appointment->appointment_time}.",
        'appointment_id' => $this->appointment->id,
        'role' => 'doctor'
      ]; }

  public function broadcastOn(): array
  {
    $doctor = $this->appointment->doctor;
    if ($doctor) {
      return [new \Illuminate\Broadcasting\PrivateChannel('user.notifications.' . $doctor->id)];
    }
    return [new \Illuminate\Broadcasting\PrivateChannel('user.notifications.unknown')];
  }
}
