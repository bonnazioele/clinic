<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Session;
use App\Models\Clinic;

trait ClinicContext
{
    /**
     * Get the current clinic ID from session
     */
    protected function getCurrentClinicId(): int
    {
        return Session::get('current_clinic_id');
    }

    /**
     * Get the current clinic name from session
     */
    protected function getCurrentClinicName(): string
    {
        return Session::get('current_clinic_name');
    }

    /**
     * Get the current user role from session
     */
    protected function getCurrentUserRole(): string
    {
        return Session::get('current_user_role');
    }

    /**
     * Get the current clinic model
     */
    protected function getCurrentClinic(): Clinic
    {
        return Clinic::findOrFail($this->getCurrentClinicId());
    }

    /**
     * Check if the given model belongs to the current clinic
     */
    protected function belongsToCurrentClinic($model, $clinicIdField = 'clinic_id'): bool
    {
        return $model->{$clinicIdField} === $this->getCurrentClinicId();
    }

    /**
     * Ensure the given model belongs to the current clinic, abort if not
     */
    protected function ensureBelongsToCurrentClinic($model, $clinicIdField = 'clinic_id', $message = 'Resource does not belong to your clinic.'): void
    {
        if (!$this->belongsToCurrentClinic($model, $clinicIdField)) {
            abort(403, $message);
        }
    }
}
