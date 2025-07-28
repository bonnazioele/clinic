<?php

namespace App\Services;

use App\Models\QueueEntry;
use Carbon\Carbon;

class QueueService
{
    /**
     * Get next available queue number for a clinic today.
     */
    public function getNextNumber(int $clinicId): int
    {
        $today = Carbon::today();
        $max   = QueueEntry::where('clinic_id', $clinicId)
                           ->whereDate('created_at', $today)
                           ->max('queue_number');

        return ($max ?? 0) + 1;
    }
}
