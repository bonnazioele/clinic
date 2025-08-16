<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Only require auth for the index method, not for welcome
        $this->middleware('auth')->only('index');
    }

    /**
     * Public welcome page - no authentication required
     */
    public function welcome()
    {
        return view('welcome');
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->is_admin) {
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

        // Improved past appointments query - include completed, cancelled, and past scheduled appointments
        $past = $user->appointments()
                     ->where(function($query) {
                         $query->where('appointment_date','<', now()->toDateString())
                               ->orWhereIn('status', ['completed', 'cancelled']);
                     })
                     ->orderBy('appointment_date','desc')
                     ->take(10)
                     ->with('clinic','service')
                     ->get();

        // Alternative approach using the model methods
        $allAppointments = $user->appointments()
                               ->with('clinic','service')
                               ->orderBy('appointment_date','desc')
                               ->get();

        $pastAlternative = $allAppointments->filter(function($appointment) {
            return $appointment->isPast() || $appointment->isCompleted() || $appointment->isCancelled();
        })->take(10);

        // Debug information
        \Log::info('Dashboard data for user ' . $user->id, [
            'upcoming_count' => $upcoming->count(),
            'past_count' => $past->count(),
            'past_alternative_count' => $pastAlternative->count(),
            'total_appointments' => $user->appointments()->count(),
            'today' => now()->toDateString(),
            'appointments_debug' => $allAppointments->map(function($a) {
                return [
                    'id' => $a->id,
                    'date' => $a->appointment_date,
                    'status' => $a->status,
                    'is_past' => $a->isPast(),
                    'is_completed' => $a->isCompleted(),
                    'is_cancelled' => $a->isCancelled()
                ];
            })->toArray()
        ]);

        return view('dashboard.index', compact('upcoming','past'));
    }
}
