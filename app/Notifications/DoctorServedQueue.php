<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DoctorServedQueue extends Notification implements ShouldBroadcast
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
            'message' => "Doctor has finished serving appointment (#{$this->appointment->id}). Secretary may now call the next patient.",
            'appointment_id' => $this->appointment->id,
            'role' => 'secretary'
        ];
    }

    
    public function toBroadcast($notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage($this->toArray($notifiable));
    }

    
    public function toArray($notifiable): array
    {
        return [
            'message' => "Doctor has finished serving appointment (#{$this->appointment->id}). Secretary may now call the next patient.",
            'appointment_id' => $this->appointment->id,
            'role' => 'secretary'
        ];
    }

    
    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('user.notifications.secretary')
        ];
    }
}
