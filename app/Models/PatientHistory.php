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
    'doctor',  // Changed from 'doctor_name'
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
}
