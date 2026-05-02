<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'service_id',
        'clinic_id',
        'schedule_type',
        'day_of_week',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'schedule_type' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
