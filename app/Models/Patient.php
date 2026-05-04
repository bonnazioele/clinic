<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    public const STATUS_GUEST = 'guest';
    public const STATUS_REGISTERED = 'registered';

    protected $fillable = [
        'user_id',
        'patient_number',
        'status',
        'registration_token',
        'registration_token_expires_at',
        'registration_invited_at',
        'registered_at',

        'last_name',
        'first_name',
        'middle_name',
        'sex',
        'date_of_birth',
        'mobile_number',
        'email_address',
        'complete_address',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_number',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'registration_token_expires_at' => 'datetime',
        'registration_invited_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    protected $appends = [
        'age',
        'full_name',
        'is_guest',
        'is_registered',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->patient_number)) {
                $patient->patient_number = self::generatePatientNumber();
            }

            if (empty($patient->status)) {
                $patient->status = self::STATUS_GUEST;
            }
        });
    }

    public static function generatePatientNumber(): string
    {
        $date = now()->format('Ymd');

        $lastPatient = self::where('patient_number', 'like', "PAT-{$date}-%")
            ->orderBy('patient_number', 'desc')
            ->first();

        if ($lastPatient) {
            $lastNumber = (int) substr($lastPatient->patient_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "PAT-{$date}-{$newNumber}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth
            ? Carbon::parse($this->date_of_birth)->age
            : null;
    }

    public function getFullNameAttribute(): string
    {
        $middleInitial = $this->middle_name
            ? strtoupper(substr($this->middle_name, 0, 1)) . '.'
            : '';

        return trim("{$this->first_name} {$middleInitial} {$this->last_name}");
    }

    public function getIsGuestAttribute(): bool
    {
        return $this->status === self::STATUS_GUEST || $this->user_id === null;
    }

    public function getIsRegisteredAttribute(): bool
    {
        return $this->status === self::STATUS_REGISTERED && $this->user_id !== null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function visits()
    {
        return $this->hasMany(PatientVisit::class);
    }

    public function visitsForClinic(int $clinicId)
    {
        return $this->hasMany(PatientVisit::class)->where('clinic_id', $clinicId);
    }

    public function latestVisit()
    {
        return $this->hasOne(PatientVisit::class)->latestOfMany();
    }

    public function queueEntries()
    {
        return $this->hasMany(QueueEntry::class);
    }

    public function scopeGuest($query)
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_GUEST)
                ->orWhereNull('user_id');
        });
    }

    public function scopeRegistered($query)
    {
        return $query->where('status', self::STATUS_REGISTERED)
            ->whereNotNull('user_id');
    }

    public function scopeSearch($query, $search)
    {
        $search = trim((string) $search);

        return $query->where(function ($q) use ($search) {
            $q->where('patient_number', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email_address', 'like', "%{$search}%")
                ->orWhere('mobile_number', 'like', "%{$search}%");
        });
    }
}