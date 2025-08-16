<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id','user_id','appointment_id','queue_number','status','served_at'
    ];

    protected $casts = [
        'served_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Scope to get waiting entries
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Check if this entry is next in line
     */
    public function isNextInLine()
    {
        return $this->status === 'waiting' &&
               $this->queue_number === $this->clinic->queueEntries()
                   ->waiting()
                   ->min('queue_number');
    }

    /**
     * Get estimated wait time in minutes
     */
    public function getEstimatedWaitTime()
    {
        if ($this->status !== 'waiting') {
            return 0;
        }

        $ahead = $this->clinic->queueEntries()
            ->waiting()
            ->where('queue_number', '<', $this->queue_number)
            ->count();

        return $ahead * 15; // 15 minutes per person
    }

    /**
     * Get formatted created time
     */
    public function getFormattedCreatedTimeAttribute()
    {
        return \Carbon\Carbon::parse($this->created_at)->format('g:i A');
    }

    /**
     * Get formatted served time
     */
    public function getFormattedServedTimeAttribute()
    {
        if (!$this->served_at) {
            return null;
        }
        return \Carbon\Carbon::parse($this->served_at)->format('g:i A');
    }
}
