<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicUserRole extends Model
{
    protected $table = 'clinic_user_roles';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
