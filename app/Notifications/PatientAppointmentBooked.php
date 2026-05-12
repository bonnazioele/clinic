<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Carbon\Carbon;

class PatientAppointmentBooked extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message'        => "Your appointment is confirmed for {$this->formatDateTime()}.",
            'appointment_id' => $this->appointment->id,
            'role'           => 'patient'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray($notifiable)
    {
        return [
            'message'        => "Your appointment is confirmed for {$this->formatDateTime()}.",
            'appointment_id' => $this->appointment->id,
            'role'           => 'patient'
        ];
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

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.notifications.' . $this->appointment->user_id)];
    }
}
