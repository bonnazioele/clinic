<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Service;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
     protected $fillable = [
        'name','first_name','last_name','email','password',
        'phone','address','medical_document',
        'is_admin',  'is_secretary', 'is_doctor',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin'          => 'boolean',
            'is_secretary'      => 'boolean',
            'is_doctor'         => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
 * Get all appointments for this user.
 */
public function appointments()
{
    return $this->hasMany(Appointment::class);
}

public function queueEntries()
{
    return $this->hasMany(\App\Models\QueueEntry::class);
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

    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'doctor_service',
            'doctor_id',
            'service_id'
        );
    }

    /**
     * Get the user's full name.
     */
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

    /**
     * Set the user's name and update first_name/last_name accordingly.
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;

        // If name is set, always update first_name and last_name
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

    /**
     * Boot method to handle automatic name splitting
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Handle name splitting
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

            // Set default values for boolean fields
            if (!isset($user->is_active)) {
                $user->is_active = true;
            }
            if (!isset($user->is_admin)) {
                $user->is_admin = false;
            }
            if (!isset($user->is_secretary)) {
                $user->is_secretary = false;
            }
            if (!isset($user->is_doctor)) {
                $user->is_doctor = false;
            }
        });

        static::updating(function ($user) {
            // Handle name splitting on updates
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
}
