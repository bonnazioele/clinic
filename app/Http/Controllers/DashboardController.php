<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the welcome page.
     */
    public function welcome()
    {
        return view('welcome');
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        // Admin users go to admin dashboard
        if ($user->is_system_admin) {
            return view('admin.dashboard');
        }
        
        // Set clinic context for role-based users
        $clinicRole = $user->clinicUserRoles()
            ->where('is_active', true)
            ->with(['clinic', 'role'])
            ->first();
            
        if ($clinicRole) {
            // Set session data for clinic context
            Session::put('current_clinic_id', $clinicRole->clinic_id);
            Session::put('current_user_role', $clinicRole->role->role_name);
            Session::put('current_clinic_name', $clinicRole->clinic->name);
            
            // Route based on role
            if ($clinicRole->role->role_name === 'secretary') {
                return redirect()->route('secretary.dashboard');
            } elseif ($clinicRole->role->role_name === 'staff') {
                return redirect()->route('staff.queue.index'); // Assuming staff route exists
            }
        }
        
        // Default patient dashboard
        return view('dashboard');
    }
}
