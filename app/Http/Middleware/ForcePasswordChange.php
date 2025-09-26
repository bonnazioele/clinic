<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        // Only enforce force password change for secretary users who are on initial login
        if ($user && ($user->is_secretary ?? false) && ($user->is_initial_login ?? false)) {
            if ($request->routeIs('secretary.auth.password.force.*') || $request->routeIs('logout')) {
                return $next($request);
            }

            // Route is now namespaced under secretary group (secretary.auth.password.force.show)
            return redirect()->route('secretary.auth.password.force.show');
        }

        return $next($request);
    }
}
