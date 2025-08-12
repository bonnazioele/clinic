<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Http\Traits\ClinicContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Service;
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

        // Get active doctors count (users with doctor profiles assigned to this clinic)
        $activeDoctors = User::whereHas('doctor', function($query) use ($clinicId) {
            $query->whereHas('clinics', function($clinicQuery) use ($clinicId) {
                $clinicQuery->where('clinic_doctor.clinic_id', $clinicId)
                           ->where('clinic_doctor.is_active', true);
            });
        })
        ->where('is_active', true)
        ->count();

        // Get services count (active services assigned to this clinic)
        $servicesCount = Service::whereHas('clinics', function($query) use ($clinicId) {
            $query->where('clinic_service.clinic_id', $clinicId)
                  ->where('clinic_service.is_active', true);
        })
        ->where('is_active', true)
        ->count();

        // Get recent appointments (last 10) with proper relationships
        $recentAppointments = Appointment::where('clinic_id', $clinicId)
            ->with(['user', 'doctor.user', 'service'])
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
