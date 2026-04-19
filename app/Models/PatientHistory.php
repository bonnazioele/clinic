<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'clinic_name',
        'diagnosis',
        'treatment',
        'doctor',
        'document_path',
        'date_of_visit',
    ];

    protected $casts = [
        'date_of_visit' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class);
    }

    public function getDoctorNameAttribute(): ?string
    {
        return $this->attributes['doctor'] ?? null;
    }

    public function setDoctorNameAttribute($value): void
    {
        $this->attributes['doctor'] = $value;
    }
}
