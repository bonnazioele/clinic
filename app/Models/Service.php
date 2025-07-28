<?php

// app/Models/Service.php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name','description'];

    public function clinics()
    {
        return $this->belongsToMany(Clinic::class)
                    ->withPivot('duration_minutes');
    }

    public function doctors()
    {
        return $this->belongsToMany(
            User::class,
            'doctor_service',
            'service_id',
            'doctor_id'
        )->where('is_doctor',true);
    }
}

