<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicDoctorService extends Model
{
    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'service_id',
        'duration',
        'is_active',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
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
