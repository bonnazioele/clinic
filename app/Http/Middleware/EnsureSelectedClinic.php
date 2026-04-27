<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Clinic;

class EnsureSelectedClinic
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Enforce for secretaries and doctors only.
        if (! $user || (! $user->is_secretary && ! $user->is_doctor)) {
            return $next($request);
        }


        $session = $request->session();

        // Fetch assigned clinic IDs once (single query)
        $clinicIds = $user->is_secretary ? $user->secretaryClinics()->pluck('clinics.id')->map(fn ($id) => (int) $id) 
            : $user->clinics()->pluck('clinics.id')->map(fn ($id) => (int) $id);

        $request->attributes->set('assigned_clinic_ids', $clinicIds);

        $activeClinicId = (int) $session->get('active_clinic_id');

        // If session active clinic is not in assigned clinics, clear it
        if ($activeClinicId && ! $clinicIds->contains($activeClinicId)) {
            $session->forget('active_clinic_id');
            $activeClinicId = 0;
        }

        // If no active clinic yet, auto-select or redirect to chooser
        if (! $activeClinicId) {
            if ($clinicIds->count() === 1) {
                $activeClinicId = (int) $clinicIds->first();
                $session->put('active_clinic_id', $activeClinicId);
            } else {
                // Allow chooser routes through to avoid redirect loops
                if ($user->is_secretary) {
                    if (! $request->routeIs('secretary.choose-clinic') &&
                        ! $request->routeIs('secretary.choose-clinic.select')) {
                        return redirect()->route('secretary.choose-clinic');
                    }
                } else {
                    if (! $request->routeIs('doctor.choose-clinic') &&
                        ! $request->routeIs('doctor.choose-clinic.select')) {
                        return redirect()->route('doctor.choose-clinic');
                    }
                }

            }
        }

        // Attach active clinic id for controllers/services to reuse
        $request->attributes->set('active_clinic_id', $activeClinicId);

        // Load active clinic model safely (only from assigned clinics)
        $activeClinic = null;

        if ($activeClinicId) {
            $activeClinic = $user->is_secretary
                ? $user->secretaryClinics()->whereKey($activeClinicId)->first()
                : $user->clinics()->whereKey($activeClinicId)->first();
        }

        $request->attributes->set('active_clinic', $activeClinic);
        View::share('activeClinic', $activeClinic);

        return $next($request);
    }
}