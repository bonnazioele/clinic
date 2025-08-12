<?php

// app/Models/Clinic.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $fillable = ['name','address','latitude','longitude'];

    public function services()
{
    return $this->belongsToMany(Service::class);
}

public function doctors()
{
    return $this->belongsToMany(User::class, 'clinic_doctor', 'clinic_id', 'doctor_id')
                ->where('is_doctor', true);
}
}

