<?php

namespace App\Notifications;

use App\Models\QueueEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QueueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $queueEntry;

    public function __construct(QueueEntry $queueEntry)
    {
        $this->queueEntry = $queueEntry;
    }

    public function via($notifiable)
    {
        // For now: database + mail (if you want) + SMS handled in controller
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "Hi {$notifiable->name}, please prepare to enter. Your queue number is #{$this->queueEntry->queue_number}.",
            'queue_number' => $this->queueEntry->queue_number,
            'clinic_id' => $this->queueEntry->clinic_id,
        ];
    }
}
