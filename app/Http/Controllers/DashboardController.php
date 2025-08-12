<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        $user = Auth::user();

        if ($user->is_system_admin) {
            // send admins straight to their panel
            return redirect()->route('admin.clinics.index');
        }

        if ($user->is_secretary) {
            // send secretaries to their manage-appointments
            return redirect()->route('secretary.appointments.index');
        }

        // patient: load the patient dashboard
        $upcoming = $user->appointments()
                         ->where('appointment_date','>=',now()->toDateString())
                         ->where('status','scheduled')
                         ->orderBy('appointment_date')
                         ->orderBy('appointment_time')
                         ->take(5)
                         ->with('clinic','service')
                         ->get();

        $past = $user->appointments()
                     ->where('appointment_date','<', now()->toDateString())
                     ->orderBy('appointment_date','desc')
                     ->take(5)
                     ->with('clinic','service')
                     ->get();

        return view('dashboard.index', compact('upcoming','past'));
    }
}
