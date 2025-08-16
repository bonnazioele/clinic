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
        'gps_latitude',
        'gps_longitude',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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
                    ->withPivot(['duration_minutes'])
                    ->withTimestamps();
    }

    /**
     * Get all appointments for the clinic.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'clinic_id');
    }

    /**
     * Get all queue entries for the clinic.
     */
    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class, 'clinic_id');
    }

    /**
     * Get all doctors associated with the clinic.
     */
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clinic_doctor', 'clinic_id', 'doctor_id')
                    ->where('is_doctor', true)
                    ->withTimestamps();
    }

    public function scopeStatus($query, string $status)
    {
        //usage: Clinic::status('approved')->get();
        return $query->where('status', $status);
    }

    public function scopeWithService($query, int $serviceId)
    {
        //usage: Clinic::withService(1)->get();
        return $query->whereHas('services', function ($q) use ($serviceId) {
            $q->where('services.id', $serviceId);
        });
    }
}

