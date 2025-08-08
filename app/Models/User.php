<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\ClinicUserRole;


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
            'clinic_doctor_services',  // Updated table name
            'doctor_id',
            'service_id'
        )->withPivot('clinic_id', 'duration', 'is_active')
         ->withTimestamps();
    }

    /**
     * Get services for a specific clinic
     */
    public function servicesForClinic($clinicId)
    {
        return $this->services()
            ->wherePivot('clinic_id', $clinicId)
            ->select('services.*'); // Explicitly select from services table to avoid ambiguous 'id'
    }

    /**
     * Get all clinics where this user is assigned (any role)
     */
    public function assignedClinics()
    {
        return $this->belongsToMany(
            Clinic::class,
            'clinic_user_roles',
            'user_id',
            'clinic_id'
        )->withPivot('role_id', 'is_active')
         ->wherePivot('is_active', true)
         ->with('type');
    }

    /**
     * Get clinics where this user is a secretary
     */
    public function secretaryClinics()
    {
        return $this->belongsToMany(
            Clinic::class,
            'clinic_user_roles',
            'user_id',
            'clinic_id'
        )->withPivot('role_id', 'is_active')
         ->wherePivot('is_active', true)
         ->whereHas('pivotParent', function($q) {
             $q->whereHas('role', function($roleQ) {
                 $roleQ->where('role_name', 'secretary');
             });
         });
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

    /**
     * Get all clinic-user-role relationships for this user.
     */
    public function clinicUserRoles()
    {
        return $this->hasMany(ClinicUserRole::class, 'user_id');
    }

    /**
     * Check if user is secretary at any clinic or specific clinic.
     */
    public function isSecretaryAt($clinicId): bool
    {
        return $this->clinicUserRoles()
            ->where('clinic_id', $clinicId)
            ->whereHas('role', fn($q) => $q->where('role_name', 'secretary'))
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if user is secretary at any clinic.
     */
    public function isSecretary(): bool
    {
        return $this->clinicUserRoles()
            ->whereHas('role', fn($q) => $q->where('role_name', 'secretary'))
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if user is staff at any clinic or specific clinic.
     */
    public function isStaffAt($clinicId = null): bool
    {
        return $this->hasClinicRole('staff', $clinicId);
    }

    /**
     * Check if user is doctor at any clinic or specific clinic.
     */
    public function isDoctorAt($clinicId = null): bool
    {
        return $this->hasClinicRole('doctor', $clinicId);
    }

    /**
     * Scope to get users who are doctors at any clinic.
     */
    public function scopeDoctors($query)
    {
        return $query->whereHas('clinicUserRoles', function($q) {
            $q->whereHas('role', function($roleQuery) {
                $roleQuery->where('role_name', 'doctor');
            })->where('is_active', true);
        });
    }

    /**
     * Scope to get users who are secretaries at any clinic.
     */
    public function scopeSecretaries($query)
    {
        return $query->whereHas('clinicUserRoles', function($q) {
            $q->whereHas('role', function($roleQuery) {
                $roleQuery->where('role_name', 'secretary');
            })->where('is_active', true);
        });
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }
}
