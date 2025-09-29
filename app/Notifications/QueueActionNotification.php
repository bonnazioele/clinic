<?php

namespace App\Notifications;

use App\Models\QueueEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;

class QueueActionNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public QueueEntry $entry,
        public string $action // "call", "resched", "done"
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage($this->getPayload());
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->getPayload());
    }

    protected function getPayload(): array
    {
        $clinic = $this->entry->clinic?->name;

        $messages = [
            'call'   => "You are being called for your turn (#{$this->entry->queue_number}) at {$clinic}.",
            'resched'=> "Your queue (#{$this->entry->queue_number}) at {$clinic} has been rescheduled.",
            'done'   => "Your consultation at {$clinic} is marked as done. Thank you!",
        ];

        return [
            'message' => $messages[$this->action] ?? 'Queue update received.',
            'queue_entry_id' => $this->entry->id,
            'clinic_id' => $this->entry->clinic_id,
            'action' => $this->action,
            'type' => 'queue_action'
        ];
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.notifications.' . $this->entry->user_id)];
    }
}
