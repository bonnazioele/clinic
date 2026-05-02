<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\DoctorMiddleware::class]);
    }

    public function index(Request $request)
    {
        $doctor = Auth::user();
        $today = now()->toDateString();

        $query = Appointment::with(['user', 'clinic', 'service'])
            ->where('doctor_id', $doctor->id)
            ->where('status', 'scheduled')
            ->whereDate('appointment_date', '>=', $today);

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', (string) $request->input('date'));
        }

        if ($request->filled('clinic_id')) {
            $query->where('clinic_id', (int) $request->input('clinic_id'));
        }

        $appointments = $query
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(15)
            ->withQueryString();

        $clinics = $doctor->clinics()->orderBy('name')->get(['clinics.id', 'clinics.name']);

        return view('doctor.appointments.index', [
            'appointments' => $appointments,
            'clinics' => $clinics,
        ]);
    }
}
