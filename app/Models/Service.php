<?php

// app/Models/Service.php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    // Using default primary key 'id'
    // protected $primaryKey = 'service_id';

    protected $fillable = [
        'service_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships (with explicit foreign keys)
    |--------------------------------------------------------------------------
    */

    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class, 'clinic_service', 'service_id', 'clinic_id')
                    ->withPivot(['duration_minutes'])
                    ->withTimestamps();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'service_id', 'id');
    }

    public function walkIns(): HasMany
    {
        return $this->hasMany(WalkIn::class, 'service_id', 'id');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'service_id', 'id');
    }

    public function scopeActive($query): mixed
    {
        return $query->where('is_active', true);
    }
}
