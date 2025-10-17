<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    protected $fillable = [
        'user_id','clinic_id','service_id','doctor_id',
    'appointment_date','appointment_time','status','notes','medical_document'
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function clinic() { return $this->belongsTo(Clinic::class); }
    public function service() { return $this->belongsTo(Service::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function doctor()
{
    return $this->belongsTo(User::class,'doctor_id');
}

    public function isPast()
    {
        return $this->appointment_date instanceof \Carbon\Carbon
            ? $this->appointment_date->lt(now()->startOfDay())
            : Carbon::parse($this->appointment_date)->lt(now()->startOfDay());
    }

    public function isUpcoming()
    {
    return ($this->appointment_date instanceof \Carbon\Carbon
        ? $this->appointment_date->gte(now()->startOfDay())
        : Carbon::parse($this->appointment_date)->gte(now()->startOfDay()))
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

    public function getAppointmentTimeAttribute($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        $formats = ['H:i:s', 'H:i'];
        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $value);
            } catch (\Exception $e) {
            }
        }
        return Carbon::parse($value);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'Scheduled',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show'   => 'No Show',
            default     => ucfirst(str_replace('_',' ',$this->status ?? 'Unknown')),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'bg-warning text-dark',
            'completed' => 'bg-success',
            'cancelled' => 'bg-secondary',
            'no_show'   => 'bg-secondary',
            default     => 'bg-light text-dark',
        };
    }
}

