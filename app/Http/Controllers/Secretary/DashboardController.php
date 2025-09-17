<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SecretaryMiddleware;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Clinic;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', SecretaryMiddleware::class]);
    }

    public function index()
    {
        $user = Auth::user();
        $clinicIds = $user->secretaryClinics()->pluck('clinics.id');
        $today = now()->toDateString();

        $stats = [
            'assignedClinics' => $clinicIds->count(),
            'todayAppts' => Appointment::whereIn('clinic_id', $clinicIds)
                ->whereDate('appointment_date', $today)->count(),
            'totalDoctors' => Clinic::whereIn('id', $clinicIds)
                ->withCount('doctors')->get()->sum('doctors_count'),
        ];

        return view('secretary.dashboard', $stats);
    }
}
