    public function toBroadcast($notifiable)
    {
        $clinic = $this->entry->clinic?->name;
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'message' => "You're next in the queue (#{$this->entry->queue_number}) at {$clinic}.",
            'queue_entry_id' => $this->entry->id,
            'clinic_id' => $this->entry->clinic_id,
            'type' => 'queue_next_up'
        ]);
    }

    public function toArray($notifiable)
    {
        $clinic = $this->entry->clinic?->name;
        return [
            'message' => "You're next in the queue (#{$this->entry->queue_number}) at {$clinic}.",
            'queue_entry_id' => $this->entry->id,
            'clinic_id' => $this->entry->clinic_id,
            'type' => 'queue_next_up'
        ];
    }
<?php

namespace App\Notifications;

use App\Models\QueueEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class QueueNextUp extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public QueueEntry $entry) {}

    public function via($notifiable): array
    {
        return ['database','broadcast'];
    }

    public function toDatabase($notifiable): DatabaseMessage
    {
        $clinic = $this->entry->clinic?->name;
        return new DatabaseMessage([
            'message' => "You're next in the queue (#{$this->entry->queue_number}) at {$clinic}.",
            'queue_entry_id' => $this->entry->id,
            'clinic_id' => $this->entry->clinic_id,
            'type' => 'queue_next_up'
        ]);
    }

    public function broadcastOn(): array
    {
        return [new \Illuminate\Broadcasting\PrivateChannel('user.notifications.'.$this->entry->user_id)];
    }
}
