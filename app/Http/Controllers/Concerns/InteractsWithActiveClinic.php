<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Clinic;
use Illuminate\Http\Request;

trait InteractsWithActiveClinic
{
    protected function activeClinicId(Request $request): int
    {
        $id = (int) $request->attributes->get('active_clinic_id');

        abort_if($id <= 0, 403, 'Active clinic context is required.');
        return $id;
    }

    protected function activeClinic(Request $request): Clinic
    {
        $activeClinic = $request->attributes->get('active_clinic');

        abort_if(! $activeClinic instanceof Clinic, 403, 'Active clinic context is required.');

        return $activeClinic;
    }

    protected function withActiveClinic(Request $request, array $data): array
    {
        $data['clinic_id'] = $this->activeClinicId($request);
        return $data;
    }

}