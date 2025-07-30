<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicType extends Model
{
    use HasFactory;
    
    protected $fillable = ['type_name', 'description'];

    /**
     * Get all clinics of this type.
     */
    public function clinics(): HasMany
    {
        return $this->hasMany(Clinic::class, 'type_id');
    }
}
