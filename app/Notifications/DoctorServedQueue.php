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
        // We still want per-user notification channels; for broadcast queue events we notify
        // secretaries of the clinic and possibly the served user separately when dispatching.
        // Here, fallback to served user channel for compatibility.
        $userId = $this->servedEntry->user_id ?? null;
        if (!$userId && $this->nextEntry) {
            $userId = $this->nextEntry->user_id;
        }
        if ($userId) {
            return [new \Illuminate\Broadcasting\PrivateChannel('user.notifications.' . $userId)];
        }
        // Fallback generic channel (could be listened to by admins/secretaries)
        return [new \Illuminate\Broadcasting\PrivateChannel('queue.updates')];
    }

    public function toBroadcast($notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray($notifiable): array
    {
        $served = $this->servedEntry;
        $next = $this->nextEntry;
        $message = 'Doctor served queue #' . $served->queue_number . ' (' . ($served->user?->name ?? 'Patient') . ') at ' . $served->clinic?->name . '.';
        if ($next) {
            $message .= ' Next: #' . $next->queue_number . ' - ' . ($next->user?->name ?? 'Patient');
        } else {
            $message .= ' No more waiting patients.';
        }
        return [
            'message' => $message,
            'clinic_id' => $served->clinic_id,
            'served_queue_id' => $served->id,
            'next_queue_id' => $next?->id,
        ];
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
