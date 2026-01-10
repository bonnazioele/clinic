<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_number',
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
    ];

    protected $appends = ['age', 'full_name'];

    /**
     * Generate patient number on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->patient_number)) {
                $patient->patient_number = self::generatePatientNumber();
            }
        });
    }

    /**
     * Generate unique patient number (format: PAT-YYYYMMDD-XXXX)
     */
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

    /**
     * Get the patient's age
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : null;
    }

    /**
     * Get the patient's full name
     */
    public function getFullNameAttribute(): string
    {
        $middleInitial = $this->middle_name ? strtoupper(substr($this->middle_name, 0, 1)) . '.' : '';
        return trim("{$this->first_name} {$middleInitial} {$this->last_name}");
    }

    /**
     * Relationships
     */
    public function visits()
    {
        return $this->hasMany(PatientVisit::class);
    }

    public function visitsForClinic(int $clinicId)
    {
        return $this->hasMany(PatientVisit::class)->where('clinic_id', $clinicId);
    }

    /**
     * Get the latest visit
     */
    public function latestVisit()
    {
        return $this->hasOne(PatientVisit::class)->latest();
    }

    /**
     * Search patients by name, mobile, or patient number
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('patient_number', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('mobile_number', 'like', "%{$search}%");
        });
    }
}
