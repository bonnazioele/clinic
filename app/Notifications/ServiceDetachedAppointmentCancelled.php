<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;

class ServiceDetachedAppointmentCancelled extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public Clinic $clinic,
        public Service $service
    ) {
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage($this->payload());
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    public function toArray($notifiable): array
    {
        return $this->payload();
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.notifications.' . $this->appointment->user_id)];
    }

    private function payload(): array
    {
        return [
            'message' => "Your appointment at {$this->clinic->name} has been cancelled. Clinic administrator has discontinued '{$this->service->name}'. Please contact the clinic for more details.",
            'appointment_id' => $this->appointment->id,
            'clinic_id' => $this->clinic->id,
            'service_id' => $this->service->id,
            'role' => 'system',
            'type' => 'appointment_cancelled_service_detached',
        ];
    }
}
