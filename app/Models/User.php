<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Service;


class User extends Authenticatable
{
    use HasFactory, Notifiable;
    
     protected $fillable = [
        'name','first_name','last_name','email','password',
        'phone','address','medical_document',
        'is_admin','is_secretary','is_doctor','is_initial_login','is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin'          => 'boolean',
            'is_secretary'      => 'boolean',
            'is_doctor'         => 'boolean',
            'password' => 'hashed',
            'is_initial_login' => 'boolean',
        ];
    }

public function appointments()
{
    return $this->hasMany(Appointment::class);
}

public function queueEntries()
{
    return $this->hasMany(\App\Models\QueueEntry::class);
}

 public function clinicsAsDoctor()
    {
        return $this->belongsToMany(
            Clinic::class,
            'clinic_doctor',
            'doctor_id',
            'clinic_id'
        );
    }

    public function clinicsAsSecretary()
    {
        return $this->belongsToMany(
            Clinic::class,
            'clinic_secretary',
            'secretary_id',
            'clinic_id'
        );
    }

    public function secretaryClinics()
    {
        return $this->belongsToMany(
            Clinic::class,
            'clinic_secretary',
            'secretary_id',
            'clinic_id'
        );
    }

    public function clinics()
    {
        return $this->belongsToMany(
            Clinic::class,
            'clinic_doctor',
            'doctor_id',
            'clinic_id'
        );
    }

    public function doctorSchedules()
    {
        return $this->hasMany(\App\Models\DoctorSchedule::class, 'doctor_id');
    }

    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'doctor_service',
            'doctor_id',
            'service_id'
        );
    }

    public function getNameAttribute($value)
    {
        if ($value) {
            return $value;
        }

        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }

        return $this->first_name ?: $this->last_name ?: '';
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;

        if ($value) {
            $parts = explode(' ', trim($value), 2);
            if (count($parts) >= 2) {
                $this->attributes['first_name'] = $parts[0];
                $this->attributes['last_name'] = $parts[1];
            } else {
                $this->attributes['first_name'] = $value;
                $this->attributes['last_name'] = null;
            }
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if ($user->name && !$user->first_name && !$user->last_name) {
                $parts = explode(' ', trim($user->name), 2);
                if (count($parts) >= 2) {
                    $user->first_name = $parts[0];
                    $user->last_name = $parts[1];
                } else {
                    $user->first_name = $user->name;
                    $user->last_name = null;
                }
            }

            
            if (!array_key_exists('is_active', $user->attributes)) {
                $user->is_active = true;
            }
            if (!array_key_exists('is_admin', $user->attributes)) {
                $user->is_admin = false;
            }
            if (!array_key_exists('is_secretary', $user->attributes)) {
                $user->is_secretary = false;
            }
            if (!array_key_exists('is_doctor', $user->attributes)) {
                $user->is_doctor = false;
            }
        });

        static::updating(function ($user) {
            if ($user->isDirty('name') && !$user->isDirty('first_name') && !$user->isDirty('last_name')) {
                $parts = explode(' ', trim($user->name), 2);
                if (count($parts) >= 2) {
                    $user->first_name = $parts[0];
                    $user->last_name = $parts[1];
                } else {
                    $user->first_name = $user->name;
                    $user->last_name = null;
                }
            }
        });
    }

    public function patientHistories()
{
    return $this->hasMany(PatientHistory::class);
}
}
