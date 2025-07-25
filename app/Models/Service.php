<?php

// app/Models/Service.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name','description'];

    public function clinics()
    {
        return $this->belongsToMany(Clinic::class)
                    ->withPivot('duration_minutes');
    }
}

