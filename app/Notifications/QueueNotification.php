<?php


namespace App\Notifications;

use App\Models\QueueEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class QueueNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    protected $entry;

    public function __construct(QueueEntry $entry)
    {
        $this->entry = $entry;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "It's your turn! Please head to {$this->entry->clinic->name} now.",
            'role'    => 'patient',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => "It's your turn! Please head to {$this->entry->clinic->name} now.",
            'role'    => 'patient',
        ]);
    }
}

