<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\QueueEntry;

class DoctorServedQueue extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public QueueEntry $servedEntry, public ?QueueEntry $nextEntry)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database','broadcast'];
    }

    public function broadcastOn(): array
    {
        return [new \Illuminate\Broadcasting\PrivateChannel('user.notifications.'.$this->servedEntry->clinic->id.'-'.$notifiable->id)];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $served = $this->servedEntry;
        $next = $this->nextEntry;
        $message = 'Doctor served queue #' . $served->queue_number . ' (' . ($served->user?->name ?? 'Patient') . ') at ' . $served->clinic?->name . '.';
        if ($next) {
            $message .= ' Next: #' . $next->queue_number . ' - ' . ($next->user?->name ?? 'Patient');
        } else {
            $message .= ' No more waiting patients.';
        }
        return new DatabaseMessage([
            'message' => $message,
            'clinic_id' => $served->clinic_id,
            'served_queue_id' => $served->id,
            'next_queue_id' => $next?->id,
        ]);
    }
}
