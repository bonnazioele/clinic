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
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'is_active',
        'is_system_admin',
        'age',
        'birthdate',
        'address',
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
            'last_login' => 'datetime',
            'is_active' => 'boolean',
            'is_system_admin' => 'boolean',
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
}
