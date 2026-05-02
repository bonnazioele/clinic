<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\QueueEntry;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\DoctorMiddleware::class, EnsureSelectedClinic::class]);
    }

    public function index(Request $request)
    {
        $doctor = Auth::user();
        $activeClinicId = $this->activeClinicId($request);
        $activeClinic = $this->activeClinic($request);
        $today = now()->toDateString();

        $appointments = Appointment::with('clinic','user','service')
            ->where('doctor_id', $doctor->id)
            ->where('clinic_id', $activeClinicId)
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time')
            ->get();

        $clinics = collect([$activeClinic]);

        $queue = QueueEntry::with('appointment.user','clinic')
            ->where('clinic_id', $activeClinicId)
            ->whereHas('appointment', function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id);
            })
            ->whereIn('status', ['waiting', 'now_serving'])
            ->orderBy('created_at')
            ->get();

        $servicesOfferedCount = $doctor->servicesForClinic($activeClinicId)
            ->distinct('services.id')
            ->count('services.id');

        return view('doctor.dashboard', compact('doctor','appointments','queue','clinics','activeClinic','servicesOfferedCount'));
    }
}
