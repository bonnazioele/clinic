<?php

// app/Models/Appointment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    protected $fillable = [
        'user_id','clinic_id','service_id','doctor_id',
        'appointment_date','appointment_time','status','notes'
    ];

    protected $casts = [
        // Ensure we get Carbon instances
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

    /**
     * Check if appointment is in the past
     */
    public function isPast()
    {
        // Compare as dates (no time component)
        return $this->appointment_date instanceof \Carbon\Carbon
            ? $this->appointment_date->lt(now()->startOfDay())
            : Carbon::parse($this->appointment_date)->lt(now()->startOfDay());
    }

    /**
     * Check if appointment is upcoming
     */
    public function isUpcoming()
    {
    return ($this->appointment_date instanceof \Carbon\Carbon
        ? $this->appointment_date->gte(now()->startOfDay())
        : Carbon::parse($this->appointment_date)->gte(now()->startOfDay()))
        && $this->status === 'scheduled';
    }

    /**
     * Check if appointment is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if appointment is cancelled
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Accessor: return appointment_time as Carbon instance (today's date + time)
     */
    public function getAppointmentTimeAttribute($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        // Handle common DB formats like HH:MM:SS or HH:MM
        $formats = ['H:i:s', 'H:i'];
        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $value);
            } catch (\Exception $e) {
                // try next format
            }
        }
        // Fallback parse
        return Carbon::parse($value);
    }
}

