<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SecretaryMiddleware;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', SecretaryMiddleware::class]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $clinicIds = $user->secretaryClinics()->pluck('clinics.id');

        // Default date = today
        $selectedDate = $request->input('date', now()->toDateString());

        // Default tab = pending
        $selectedStatus = $request->input('status', 'pending');

        $availableServices = Service::whereHas('clinics', function ($q) use ($clinicIds) {
            $q->whereIn('clinics.id', $clinicIds);
        })->distinct()->count();

        $activeClinicId = (int) $request->session()->get('active_clinic_id');
        $activeClinic = $activeClinicId && $clinicIds->contains($activeClinicId)
            ? Clinic::find($activeClinicId)
            : ($clinicIds->first() ? Clinic::find($clinicIds->first()) : null);

        $stats = [
            'assignedClinics' => $clinicIds->count(),
            'todayAppts' => Appointment::whereIn('clinic_id', $clinicIds)
                ->whereDate('appointment_date', now()->toDateString())
                ->count(),
            'totalDoctors' => Clinic::whereIn('id', $clinicIds)
                ->withCount('doctors')
                ->get()
                ->sum('doctors_count'),
            'availableServices' => $availableServices,
            'clinicPatients' => $activeClinic
                ? User::where(function ($query) use ($activeClinic) {
                    $query->whereHas('appointments', function ($appointments) use ($activeClinic) {
                        $appointments->where('clinic_id', $activeClinic->id);
                    })->orWhereHas('queueEntries', function ($queues) use ($activeClinic) {
                        $queues->where('clinic_id', $activeClinic->id);
                    });
                })->distinct('users.id')->count('users.id')
                : 0,
        ];

        $clinics = Clinic::whereIn('id', $clinicIds)->get();

        $baseAppointments = Appointment::with(['user', 'clinic', 'service', 'doctor'])
            ->whereIn('clinic_id', $clinicIds)
            ->whereDate('appointment_date', $selectedDate);

        if ($request->filled('patient')) {
            $term = trim((string) $request->input('patient'));
            $baseAppointments->whereHas('user', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");
            });
        }

        $statusCounts = [
            'all' => (clone $baseAppointments)->count(),
            'pending' => (clone $baseAppointments)->where('status', 'pending')->count(),
            'scheduled' => (clone $baseAppointments)->where('status', 'scheduled')->count(),
            'completed' => (clone $baseAppointments)->where('status', 'completed')->count(),
        ];

        if (!empty($selectedStatus) && $selectedStatus !== 'all') {
            $baseAppointments->where('status', $selectedStatus);
        }

        $appointments = $baseAppointments
            ->orderBy('appointment_time')
            ->paginate(15)
            ->withQueryString();

        return view('secretary.dashboard', array_merge(
            $stats,
            compact(
                'clinics',
                'appointments',
                'selectedDate',
                'selectedStatus',
                'statusCounts'
            )
        ));
    }
}