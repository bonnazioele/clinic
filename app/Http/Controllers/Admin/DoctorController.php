<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('is_doctor', true)
            ->with(['clinics:id,name']);

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('clinic_id')) {
            $clinicId = (int) $request->input('clinic_id');
            $query->whereHas('clinics', fn($q) => $q->where('clinics.id', $clinicId));
        }

        $doctors = $query->orderBy('name')->paginate(15)->withQueryString();
        $clinics = Clinic::orderBy('name')->get(['id','name']);

        return view('admin.doctors.index', compact('doctors','clinics'));
    }

    public function show(User $doctor)
    {
        abort_unless($doctor->is_doctor, 404);

        $doctor->load(['clinics:id,name']);

        $recentAppointments = \App\Models\Appointment::where('doctor_id', $doctor->id)
            ->with(['clinic:id,name', 'user:id,name'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $queueByClinic = [];
        foreach ($doctor->clinics as $clinic) {
            $waiting = \App\Models\QueueEntry::where('clinic_id', $clinic->id)
                ->whereIn('status', ['waiting', 'now_serving'])->count();
            $servedToday = \App\Models\QueueEntry::where('clinic_id', $clinic->id)
                ->where('status','served')
                ->whereDate('served_at', today())->count();
            $queueByClinic[$clinic->id] = [
                'clinic' => $clinic,
                'waiting' => $waiting,
                'servedToday' => $servedToday,
            ];
        }

        return view('admin.doctors.show', [
            'doctor' => $doctor,
            'queueByClinic' => $queueByClinic,
            'recentAppointments' => $recentAppointments,
        ]);
    }
}
