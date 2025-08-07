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

    protected $hidden = [
        'password',
        'remember_token',
    ];

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
     * Get the user's full name.
     */
    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
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

    public function hasClinicRole(string $roleName, $clinicId = null): bool
    {
        return $this->clinicUserRoles()
            ->whereHas('role', function ($query) use ($roleName) {
                $query->where('role_name', $roleName);
            })
            ->when($clinicId, function ($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId);
            })
            ->where('is_active', true)
            ->exists();
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }
}
