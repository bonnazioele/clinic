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

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function isNextInLine()
    {
        return $this->status === 'waiting' &&
               $this->queue_number === $this->clinic->queueEntries()
                   ->waiting()
                   ->min('queue_number');
    }

    public function getEstimatedWaitTime()
    {
        if ($this->status !== 'waiting') {
            return 0;
        }

        $ahead = $this->clinic->queueEntries()
            ->waiting()
            ->where('queue_number', '<', $this->queue_number)
            ->count();

        return $ahead * 15;
    }

    public function getFormattedCreatedTimeAttribute()
    {
        return \Carbon\Carbon::parse($this->created_at)->format('g:i A');
    }

    public function getFormattedServedTimeAttribute()
    {
        if (!$this->served_at) {
            return null;
        }
        return \Carbon\Carbon::parse($this->served_at)->format('g:i A');
    }


    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'waiting' => 'Waiting',
            'now_serving' => 'Now Serving',
            'called' => 'Called',
            'rescheduled' => 'Rescheduled',
            'no_show' => 'No Show',
            'cancelled' => 'Cancelled',
            'served' => 'Served',
            default => ucfirst(str_replace('_',' ', $this->status ?? 'Unknown')),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'waiting' => 'secondary',
            'now_serving' => 'primary',
            'called' => 'info',
            'rescheduled' => 'warning',
            'no_show' => 'dark',
            'cancelled' => 'danger',
            'served' => 'success',
            default => 'light',
        };
    }
}
