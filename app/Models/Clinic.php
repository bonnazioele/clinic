<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'created_by_user_id',
        'name',
        'branch_code',
        'address',
        'contact_number',
        'email',
        'contact_first_name',
        'contact_last_name',
        'contact_person_email',
        'logo',
        'cover_image',
        'description',
        'gps_latitude',
        'gps_longitude',
        'status',
        'operational_hours_configured',
        'setup_completed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'operational_hours_configured' => 'boolean',
        'setup_completed_at' => 'datetime',
        'gps_latitude' => 'decimal:8',
        'gps_longitude' => 'decimal:8',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'clinic_service', 'clinic_id', 'service_id')
            ->withPivot(['duration_minutes'])
            ->withTimestamps();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id');
    }

    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class, 'clinic_id');
    }

    public function permits(): HasMany
    {
        return $this->hasMany(ClinicPermit::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ClinicStatusLog::class);
    }

    public function operationalHours(): HasMany
    {
        return $this->hasMany(ClinicOperationalHour::class, 'clinic_id')
            ->orderBy('sort_order');
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clinic_doctor', 'clinic_id', 'doctor_id')
            ->where('is_doctor', true)
            ->withTimestamps();
    }

    public function secretaries(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clinic_secretary', 'clinic_id', 'secretary_id')
            ->where('is_secretary', true)
            ->withTimestamps();
    }

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clinic_patients', 'clinic_id', 'patient_id')
            ->withPivot(['registered_by'])
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeWithService($query, int $serviceId)
    {
        return $query->whereHas('services', function ($q) use ($serviceId) {
            $q->where('services.id', $serviceId);
        });
    }

    public function scopeForIds($query, $clinicIds)
    {
        return $query->whereIn('id', $clinicIds);
    }

    public function scopeWithDashboardDoctorLaneRelations($query, ?int $clinicId = null)
    {
        return $query->with([
            'doctors' => function ($doctorQuery) use ($clinicId) {
                $doctorQuery->select('users.id', 'users.name')
                    ->with(['services' => function ($serviceQuery) use ($clinicId) {
                        $serviceQuery->select('services.id', 'services.name');

                        if ($clinicId !== null) {
                            $serviceQuery->wherePivot('clinic_id', $clinicId);
                        }
                    }])
                    ->orderBy('users.name');
            },
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isApprovedLike(): bool
    {
        return in_array(strtolower((string) $this->status), ['approved', 'active'], true);
    }

    public function queueModeIs(string $mode): bool
    {
        return strtolower($this->queue_mode ?? 'fcfs') === strtolower($mode);
    }

    public function hasConfiguredOperationalHours(): bool
    {
        if ((bool) ($this->operational_hours_configured ?? false)) {
            return true;
        }

        return $this->operationalHours()
            ->where('is_open', true)
            ->exists();
    }

    public function isSetupReady(): bool
    {
        return $this->hasConfiguredOperationalHours()
            && ! is_null($this->setup_completed_at);
    }

    public function markOperationalHoursConfigured(): void
    {
        $this->forceFill([
            'operational_hours_configured' => true,
            'setup_completed_at' => $this->setup_completed_at ?? now(),
        ])->save();
    }

    public function markSetupReady(): void
    {
        $this->forceFill([
            'operational_hours_configured' => true,
            'setup_completed_at' => now(),
        ])->save();
    }
}