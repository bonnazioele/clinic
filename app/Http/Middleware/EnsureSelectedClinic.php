<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSelectedClinic
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user || ! $user->is_secretary) {
            return $next($request);
        }

        $session = $request->session();
        $currentId = $session->get('active_clinic_id');

        if ($currentId && ! $user->secretaryClinics()->where('clinics.id',$currentId)->exists()) {
            $currentId = null;
            $session->forget('active_clinic_id');
        }

        if (! $currentId) {
            $first = $user->secretaryClinics()->orderBy('name')->first();
            if ($first) {
                $currentId = $first->id;
                $session->put('active_clinic_id', $currentId);
            } else {
                if (! $session->has('_no_clinic_warned')) {
                    $session->flash('warning', 'You have no assigned clinics. Please contact an administrator.');
                    $session->put('_no_clinic_warned', true);
                }
            }
        }

        $routeClinic = $request->route('clinic');
        if ($routeClinic) {
            $routeClinicId = is_object($routeClinic) ? ($routeClinic->id ?? null) : (int)$routeClinic;
            if ($currentId && $routeClinicId && $routeClinicId !== (int)$currentId) {
                abort(403, 'Clinic mismatch with active clinic context.');
            }
        }

        if ($currentId) {
            view()->share('activeClinic', $user->secretaryClinics()->where('clinics.id',$currentId)->first());
            view()->share('activeClinicId', $currentId);
        } else {
            view()->share('activeClinic', null);
            view()->share('activeClinicId', null);
        }

        return $next($request);
    }
}
