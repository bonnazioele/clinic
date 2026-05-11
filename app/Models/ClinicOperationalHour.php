<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicOperationalHour extends Model
{
    use HasFactory;

    public const DAYS = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ];

    public const SORT_ORDER = [
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
        'sunday' => 7,
    ];

    protected $fillable = [
        'clinic_id',
        'day_of_week',
        'sort_order',
        'is_open',
        'is_24_hours',
        'open_time',
        'close_time',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'is_24_hours' => 'boolean',
        'open_time' => 'datetime:H:i',
        'close_time' => 'datetime:H:i',
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function getDisplayHoursAttribute(): string
    {
        if (! $this->is_open) {
            return 'Closed';
        }

        if ($this->is_24_hours) {
            return 'Open 24 hours';
        }

        $open = $this->open_time ? $this->open_time->format('g:i A') : '—';
        $close = $this->close_time ? $this->close_time->format('g:i A') : '—';

        return $open . ' - ' . $close;
    }
}
