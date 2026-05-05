<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name','description'];

    public function getIdentifierAttribute()
    {
        return $this->id;
    }

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
        )->where('is_doctor',true)
            ->withPivot('clinic_id');
    }

    public function scopeForClinics($query, $clinicIds)
    {
        return $query->whereHas('clinics', function ($clinicQuery) use ($clinicIds) {
            $clinicQuery->whereIn('clinics.id', $clinicIds);
        });
    }
}
