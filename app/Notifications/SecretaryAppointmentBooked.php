<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Carbon\Carbon;

class SecretaryAppointmentBooked extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        $formatted = $this->formatDateTime();

        return [
            'message' => "New appointment (#{$this->appointment->id}) booked for {$formatted} (Patient: ".$this->appointment->user?->name.").",
            'appointment_id' => $this->appointment->id,
            'role' => 'secretary'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray($notifiable)
    {
        $formatted = $this->formatDateTime();

        return [
            'message' => "New appointment (#{$this->appointment->id}) booked for {$formatted} (Patient: ".$this->appointment->user?->name.").",
            'appointment_id' => $this->appointment->id,
            'role' => 'secretary'
        ];
    }

    public function broadcastOn(): array
    {
        return [new \Illuminate\Broadcasting\PrivateChannel('user.notifications.' . $this->appointment->user_id)];
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
