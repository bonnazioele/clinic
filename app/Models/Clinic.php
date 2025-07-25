<?php

// app/Models/Clinic.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $fillable = ['name','address','latitude','longitude'];

    public function services()
    {
        return $this->belongsToMany(Service::class)
                    ->withPivot('duration_minutes');
    }
}

