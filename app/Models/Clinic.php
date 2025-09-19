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
        'branch_code',
        'address',
        'contact_number',
        'email',
        'logo',
        'cover_image',
        'description',
        'gps_latitude',
        'gps_longitude',
        'status',
        'queue_mode',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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

    /**
     * Treat legacy / alternate status values as approved equivalents.
     */
    public function isApprovedLike(): bool
    {
        return in_array(strtolower((string)$this->status), ['approved','active'], true);
    }

    /**
     * Check the configured queue mode. Modes: fcfs, priority
     */
    public function queueModeIs(string $mode): bool
    {
        return strtolower($this->queue_mode ?? 'fcfs') === strtolower($mode);
    }
}

