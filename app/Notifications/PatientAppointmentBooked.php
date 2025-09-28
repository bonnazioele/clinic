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
        $formatted = $this->formatDateTime();
        return [
            'message' => "Appointment (#{$this->appointment->id}) confirmed for {$formatted}.",
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
        $formatted = $this->formatDateTime();
        return [
            'message' => "Appointment (#{$this->appointment->id}) confirmed for {$formatted}.",
            'appointment_id' => $this->appointment->id,
            'role' => 'patient'
        ];
    }

    protected function formatDateTime(): string
    {
        $date = \Carbon\Carbon::parse($this->appointment->appointment_date);
        $time = substr($this->appointment->appointment_time,0,5);
        $start = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d')." ".$time);
        $display = $start->format('g:i A') . ' on ' . $date->format('M d, Y');
        return $display;
    }

    public function broadcastOn(): array
    {
        return [new \Illuminate\Broadcasting\PrivateChannel('user.notifications.' . $this->appointment->user_id)];
    }
}
