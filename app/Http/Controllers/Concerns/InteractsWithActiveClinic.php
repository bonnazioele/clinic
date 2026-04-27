<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Clinic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

trait InteractsWithActiveClinic
{
    protected function activeClinicId(Request $request): int
    {
        $id = (int) $request->attributes->get('active_clinic_id');

        abort_if($id <= 0, 403, 'Active clinic context is required.');
        return $id;
    }

    protected function withActiveClinic(Request $request, array $data): array
    {
        $data['clinic_id'] = $this->activeClinicId($request);
        return $data;
    }

}