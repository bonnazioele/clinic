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
    { $formatted = $this->formatDateTime(); return [
        'message' => "New patient (".$this->appointment->user?->name.") booked for {$formatted}.",
        'appointment_id' => $this->appointment->id,
        'role' => 'doctor'
      ]; }
    public function toBroadcast($notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray($notifiable): array
    { $formatted = $this->formatDateTime(); return [
        'message' => "New patient (".$this->appointment->user?->name.") booked for {$formatted}.",
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

  protected function formatDateTime(): string
  {
    $date = \Carbon\Carbon::parse($this->appointment->appointment_date);
    $time = substr($this->appointment->appointment_time,0,5);
    $start = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d')." ".$time);
    return $start->format('g:i A') . ' on ' . $date->format('M d, Y');
  }
}
