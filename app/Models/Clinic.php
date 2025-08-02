<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clinic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'type_id',
        'branch_code',
        'address',
        'contact_number',
        'email',
        'logo',
        'gps_latitude',
        'gps_longitude',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all users associated with the clinic through roles.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clinic_user_roles')
                    ->using(ClinicUserRole::class)
                    ->withPivot(['role_id', 'is_active', 'assigned_at'])
                    ->withTimestamps();
    }

    /**
     * Get all roles associated with the clinic through users.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'clinic_user_roles')
                    ->using(ClinicUserRole::class)
                    ->withPivot(['user_id', 'is_active', 'assigned_at'])
                    ->withTimestamps();
    }

    /**
     * Get all clinic-user-role relationships.
     */
    public function clinicUserRoles(): HasMany
    {
        return $this->hasMany(ClinicUserRole::class, 'clinic_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ClinicType::class, 'type_id');
    }

    /**
     * Get the user who submitted this clinic.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all services offered by the clinic.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'clinic_service', 'clinic_id', 'service_id')
                    ->withPivot(['duration_minutes', 'is_active'])
                    ->withTimestamps();
    }

    /**
     * Get all services associated with the clinic through appointments.
     */
    public function servicesThoughAppointments(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'appointments');
    }

    /**
     * Get all doctors associated with the clinic through appointments.
     */
    public function doctors(): HasManyThrough
    {
        return $this->hasManyThrough(Doctor::class, Appointment::class);
    }

    /**
     * Get all appointments for the clinic.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id');
    }

    /**
     * Get all doctors associated with the clinic through walk-ins.
     */
    public function doctorsThroughWalkIns(): HasManyThrough
    {
        return $this->hasManyThrough(Doctor::class, WalkIn::class);
    }

    /**
     * Get all queues for the clinic.
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'clinic_id');
    }

    /**
     * Get all walk-in patients for the clinic.
     */
    public function walkIns(): HasMany
    {
        return $this->hasMany(WalkIn::class);
    }

    public function scopeStatus($query, string $status)
    {
        //usage: Clinic::status('approved')->get();
        return $query->where('status', $status);
    }

    public function scopeOfType($query, int $typeId)
    {
        //usage: Clinic::ofType(1)->get();
        return $query->where('type_id', $typeId);
    }

    public function scopeWithService($query, int $serviceId)
    {
        //usage: Clinic::withService(1)->get();
        return $query->whereHas('services', function ($q) use ($serviceId) {
            $q->where('services.id', $serviceId);
        });
    }


}

    // public function doctors()
    // {
    //     return $this->belongsToMany(User::class, 'clinic_doctor', 'clinic_id', 'doctor_id')
    //                 ->where('is_doctor', true);
    // }

