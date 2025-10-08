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

        $clinic = Clinic::where('created_by_user_id', $user->id)
            ->orderByRaw("CASE WHEN status IN ('approved','active') THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->first();
        if (!$clinic) {
            return redirect()->route('welcome')->with('error', 'No clinic found for your account.');
        }
        if (!$clinic->isApprovedLike()) {
            return redirect()->route('welcome')->with('warning', 'Your clinic application is pending review.');
        }

        $request->attributes->set('ownerClinic', $clinic);
        return $next($request);
    }
}
