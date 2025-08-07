<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Http\Traits\ClinicContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use ClinicContext;

    public function __construct()
    {
        $this->middleware(['auth', 'can:access-secretary-panel', 'clinic.context']);
    }

    /**
     * Display the secretary dashboard.
     */
    public function index()
    {
        $clinicId = $this->getCurrentClinicId();
        
        if (!$clinicId) {
            return redirect()->route('dashboard')->with('error', 'No clinic context available.');
        }

        // Get today's appointments count
        $todayAppointments = Appointment::where('clinic_id', $clinicId)
            ->whereDate('appointment_date', Carbon::today())
            ->count();

        // Get queue count (for now, just scheduled appointments for today)
        $queueCount = Appointment::where('clinic_id', $clinicId)
            ->whereDate('appointment_date', Carbon::today())
            ->where('status', 'scheduled')
            ->count();

        // Get active doctors count (users with doctor role at this clinic)
        $activeDoctors = User::whereHas('clinicUserRoles', function($query) use ($clinicId) {
            $query->where('clinic_id', $clinicId)
                  ->where('is_active', true)
                  ->whereHas('role', function($roleQuery) {
                      $roleQuery->where('role_name', 'doctor');
                  });
        })->count();

        // Get services count (placeholder for now)
        $servicesCount = 0; // Will be implemented when services management is added

        // Get recent appointments (last 10)
        $recentAppointments = Appointment::where('clinic_id', $clinicId)
            ->with(['user', 'doctor', 'service'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('secretary.dashboard', compact(
            'todayAppointments',
            'queueCount',
            'activeDoctors',
            'servicesCount',
            'recentAppointments'
        ));
    }
}
