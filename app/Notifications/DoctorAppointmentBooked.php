<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Carbon\Carbon;

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
        
        if ($this->appointment->appointment_time && strlen($this->appointment->appointment_time) > 5) {
            $start = Carbon::parse($this->appointment->appointment_time);
        } else {
            
            $date = Carbon::parse($this->appointment->appointment_date)->format('Y-m-d');
            $time = $this->appointment->appointment_time ?: '00:00:00';
            $start = Carbon::createFromFormat('Y-m-d H:i:s', "{$date} {$time}");
        }

        return $start->format('g:i A \o\n M d, Y');
    }
}
