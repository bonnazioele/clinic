<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'specialization',
        'clinic_id',
        'contact_number',
        'email',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
