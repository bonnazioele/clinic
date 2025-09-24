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
        if ($user && ($user->is_initial_login ?? false)) {
            // Allow only the forced password change routes and logout
            if ($request->routeIs('auth.password.force.*') || $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('auth.password.force.show');
        }

        return $next($request);
    }
}
