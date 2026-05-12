<?php

namespace App\Notifications;

use App\Models\QueueEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;

class QueueNextUp extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public QueueEntry $entry) {}

    public function via(object $notifiable): array
    {
        return ['database','broadcast'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $clinic = $this->entry->clinic?->name;
        return new DatabaseMessage([
            'message' => "You're next! Please proceed to the clinic.",
            'queue_entry_id' => $this->entry->id,
            'clinic_id' => $this->entry->clinic_id,
            'type' => 'queue_next_up'
        ]);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $clinic = $this->entry->clinic?->name;
        return new BroadcastMessage([
            'message' => "You're next! Please proceed to the clinic.",
            'queue_entry_id' => $this->entry->id,
            'clinic_id' => $this->entry->clinic_id,
            'type' => 'queue_next_up'
        ]);
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.notifications.' . $this->entry->user_id)];
    }
}
