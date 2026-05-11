<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicOperationalHoursConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! ($user->is_secretary ?? false)) {
            return $next($request);
        }

        if ($request->routeIs('secretary.onboarding.*') || $request->routeIs('secretary.auth.password.force.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        $clinicId = session('active_clinic_id')
            ?? session('selected_clinic_id')
            ?? $request->query('clinic_id');

        $clinicQuery = Clinic::query()
            ->whereHas('secretaries', function ($secretaryQuery) use ($user) {
                $secretaryQuery->where('users.id', $user->id);
            });

        $clinic = $clinicId
            ? (clone $clinicQuery)->where('clinics.id', $clinicId)->with('operationalHours')->first()
            : $clinicQuery->with('operationalHours')->orderBy('clinics.name')->first();

        if (! $clinic) {
            return $next($request);
        }

        session(['active_clinic_id' => $clinic->id]);

        if (! $clinic->hasConfiguredOperationalHours()) {
            return redirect()->route('secretary.onboarding.operational-hours');
        }

        if (! $clinic->setup_completed_at) {
            return redirect()->route('secretary.onboarding.ready');
        }

        return $next($request);
    }
}
