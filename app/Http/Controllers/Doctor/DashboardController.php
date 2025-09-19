<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Clinic;
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

        $appointments = Appointment::with('clinic','user','service')
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time')
            ->get();

        $clinics = $doctor->clinics()->get();

        $queue = QueueEntry::with('appointment.user','clinic')
            ->whereIn('clinic_id', $clinics->pluck('id'))
            ->where('status','waiting')
            ->orderBy('created_at')
            ->get();

        return view('doctor.dashboard', compact('doctor','appointments','queue','clinics'));
    }
}
