<?php

// app/Models/Appointment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id','clinic_id','service_id',
        'appointment_date','appointment_time','status'
    ];

    public function clinic() { return $this->belongsTo(Clinic::class); }
    public function service() { return $this->belongsTo(Service::class); }
    public function user()    { return $this->belongsTo(User::class); }
}

