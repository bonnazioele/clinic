<?php

namespace App\Events;

use App\Models\QueueEntry;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public QueueEntry $entry, public string $action)
    {
        // $action: created|served|cancelled
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('clinic.queue.'.$this->entry->clinic_id)];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->entry->id,
            'clinic_id' => $this->entry->clinic_id,
            'queue_number' => $this->entry->queue_number,
            'status' => $this->entry->status,
            'action' => $this->action,
        ];
    }
}
