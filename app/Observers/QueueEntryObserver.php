<?php

namespace App\Observers;

use App\Models\QueueEntry;

class QueueEntryObserver
{
    /**
     * Handle the QueueEntry "created" event.
     */
    public function created(QueueEntry $queueEntry): void
    {
        //
    }

    /**
     * Handle the QueueEntry "updated" event.
     */
    public function updated(QueueEntry $queueEntry): void
    {
        //
    }

    /**
     * Handle the QueueEntry "deleted" event.
     */
    public function deleted(QueueEntry $queueEntry): void
    {
        //
    }

    /**
     * Handle the QueueEntry "restored" event.
     */
    public function restored(QueueEntry $queueEntry): void
    {
        //
    }

    /**
     * Handle the QueueEntry "force deleted" event.
     */
    public function forceDeleted(QueueEntry $queueEntry): void
    {
        //
    }
}