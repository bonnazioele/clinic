<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'visit_number',
        'date_of_visit',
        'time_in',
        'registration_staff_id',
        'visit_type',
        'reason_for_visit',
        'requested_service',
        'assigned_department',
        'patient_type',
        'priority_level',
        'consent_to_data_collection',
        'patient_signature',
        'date_signed',
        'status',
        'time_out',
        'notes',
    ];

    protected $casts = [
        'date_of_visit' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'date_signed' => 'date',
        'consent_to_data_collection' => 'boolean',
    ];

    /**
     * Generate visit number on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($visit) {
            if (empty($visit->visit_number)) {
                $visit->visit_number = self::generateVisitNumber();
            }

            // Set default values if not provided
            if (empty($visit->date_of_visit)) {
                $visit->date_of_visit = now()->toDateString();
            }
            if (empty($visit->time_in)) {
                $visit->time_in = now();
            }
            if (empty($visit->status)) {
                $visit->status = 'Registered';
            }
        });
    }

    /**
     * Generate unique visit number (format: VST-YYYYMMDD-XXXX)
     */
    public static function generateVisitNumber(): string
    {
        $date = now()->format('Ymd');
        $lastVisit = self::where('visit_number', 'like', "VST-{$date}-%")
            ->orderBy('visit_number', 'desc')
            ->first();

        if ($lastVisit) {
            $lastNumber = (int) substr($lastVisit->visit_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "VST-{$date}-{$newNumber}";
    }

    /**
     * Relationships
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function registrationStaff()
    {
        return $this->belongsTo(User::class, 'registration_staff_id');
    }

    /**
     * Scopes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date_of_visit', today());
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority_level', $priority);
    }

    public function scopeByVisitType($query, $type)
    {
        return $query->where('visit_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if patient is a returning patient
     */
    public function isReturningPatient(): bool
    {
        return $this->patient_type === 'Returning';
    }

    /**
     * Check if visit is urgent
     */
    public function isUrgent(): bool
    {
        return in_array($this->priority_level, ['Urgent', 'Emergency']);
    }
}
