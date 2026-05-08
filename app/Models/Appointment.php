<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    public const ACTIVE_STATUSES = [
        'scheduled',
        'in_progress',
    ];

    public const HISTORY_STATUSES = [
        'completed',
        'cancelled',
        'no_show',
        'rescheduled',
    ];

    public const FINAL_STATUSES = [
        'completed',
        'cancelled',
        'no_show',
        'rescheduled',
    ];

    protected $fillable = [
        'user_id',
        'clinic_id',
        'service_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
        'medical_document',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function queueEntries()
    {
        return $this->hasMany(QueueEntry::class);
    }

    public function latestQueueEntry()
    {
        return $this->hasOne(QueueEntry::class)->latestOfMany();
    }

    public function isPast()
    {
        return $this->appointment_date instanceof Carbon
            ? $this->appointment_date->lt(now()->startOfDay())
            : Carbon::parse($this->appointment_date)->lt(now()->startOfDay());
    }

    public function isToday(): bool
    {
        return $this->appointment_date instanceof Carbon
            ? $this->appointment_date->isToday()
            : Carbon::parse($this->appointment_date)->isToday();
    }

    public function isUpcoming()
    {
        $date = $this->appointment_date instanceof Carbon
            ? $this->appointment_date
            : Carbon::parse($this->appointment_date);

        return $date->gt(now()->startOfDay())
            && $this->status === 'scheduled';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isNoShow()
    {
        return $this->status === 'no_show';
    }

    public function isRescheduled()
    {
        return $this->status === 'rescheduled';
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL_STATUSES, true);
    }

    public function shouldAppearInHistory(): bool
    {
        return $this->isPast() || in_array($this->status, self::HISTORY_STATUSES, true);
    }

    public function getAppointmentTimeAttribute($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $formats = ['H:i:s', 'H:i'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Exception $e) {
                //
            }
        }

        return Carbon::parse($value);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'rescheduled' => 'Rescheduled',
            default => ucfirst(str_replace('_', ' ', $this->status ?? 'Unknown')),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'bg-primary',
            'in_progress' => 'bg-info text-dark',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            'no_show' => 'bg-dark',
            'rescheduled' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }
}
