<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\QueueEntry;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\DoctorMiddleware::class]);
    }

    public function index(Request $request)
    {
        $doctor = Auth::user();
        $today = now()->toDateString();

        $clinics = $doctor->clinics()->get();

        $activeClinicId = (int) $request->input('clinic_id');

        if (!$activeClinicId || !$clinics->pluck('id')->contains($activeClinicId)) {
            $activeClinicId = (int) optional($clinics->first())->id;
        }

        $appointments = collect();
        $queue = collect();

        if ($activeClinicId) {
            $appointments = Appointment::with(['clinic', 'user', 'service'])
                ->where('doctor_id', $doctor->id)
                ->where('clinic_id', $activeClinicId)
                ->whereDate('appointment_date', $today)
                ->orderBy('appointment_time')
                ->get();

            $queue = QueueEntry::with(['appointment.user', 'appointment.clinic', 'clinic'])
                ->where('clinic_id', $activeClinicId)
                ->whereIn('status', ['waiting', 'now_serving'])
                ->orderByRaw("CASE WHEN status = 'now_serving' THEN 0 ELSE 1 END")
                ->orderBy('created_at')
                ->get();
        }

        return view('doctor.dashboard', compact(
            'doctor',
            'appointments',
            'queue',
            'clinics',
            'activeClinicId'
        ));
    }
}