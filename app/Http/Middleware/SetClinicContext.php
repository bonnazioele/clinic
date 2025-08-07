<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class SetClinicContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Skip for admin users
            if ($user->is_system_admin) {
                return $next($request);
            }
            
            // Only set clinic context if not already set and user has clinic roles
            if (!Session::has('current_clinic_id')) {
                // Get the first active clinic role for this user
                $clinicRole = $user->clinicUserRoles()
                    ->where('is_active', true)
                    ->with(['clinic', 'role'])
                    ->first();
                
                if ($clinicRole) {
                    Session::put('current_clinic_id', $clinicRole->clinic_id);
                    Session::put('current_user_role', $clinicRole->role->role_name);
                    Session::put('current_clinic_name', $clinicRole->clinic->name);
                } else {
                    // User has no clinic assignment, clear any existing session data
                    Session::forget(['current_clinic_id', 'current_user_role', 'current_clinic_name']);
                    
                    // If it's a secretary/staff route but no clinic assignment, redirect with error
                    if ($request->is('secretary/*') || $request->is('staff/*')) {
                        return redirect()->route('dashboard')->with('error', 'You are not assigned to any clinic. Please contact administrator.');
                    }
                }
            }
        }
        
        return $next($request);
    }
}
