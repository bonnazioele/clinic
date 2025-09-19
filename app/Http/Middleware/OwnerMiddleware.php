<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Clinic;

class OwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !$user->is_owner) {
            return redirect()->route('login')->with('error', 'Unauthorized.');
        }

        // Prefer an approved clinic if multiple exist (e.g., duplicate applications)
        $clinic = Clinic::where('user_id', $user->id)
            ->orderByRaw("CASE WHEN status IN ('approved','active') THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->first();
        if (!$clinic) {
            // Don't force logout; redirect to a neutral page to avoid loops
            return redirect()->route('welcome')->with('error', 'No clinic found for your account.');
        }
        if (!$clinic->isApprovedLike()) {
            return redirect()->route('welcome')->with('warning', 'Your clinic application is pending review.');
        }

        // Share the owner's clinic in request for convenience
        $request->attributes->set('ownerClinic', $clinic);
        return $next($request);
    }
}
