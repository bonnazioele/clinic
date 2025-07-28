<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        // Next 5 upcoming appointments
        $upcoming = $user->appointments()
                         ->where('appointment_date', '>=', now()->toDateString())
                         ->where('status','scheduled')
                         ->orderBy('appointment_date')
                         ->orderBy('appointment_time')
                         ->take(5)
                         ->with('clinic','service')
                         ->get();

        // Last 5 past appointments
        $past = $user->appointments()
                     ->where('appointment_date','<', now()->toDateString())
                     ->orderBy('appointment_date','desc')
                     ->take(5)
                     ->with('clinic','service')
                     ->get();

        return view('dashboard.index', compact('upcoming','past'));
    }
}
