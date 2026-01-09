<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\QueueEntry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (! $request->user()?->is_secretary) {
                abort(403, 'Forbidden');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $activeClinicId = (int) $request->session()->get('active_clinic_id');
        $clinic = $activeClinicId
            ? $user->secretaryClinics()->where('clinics.id', $activeClinicId)->first()
            : null;

        if (! $clinic) {
            return redirect()
                ->route('secretary.dashboard')
                ->with('warning', 'Please select an active clinic to view patients.');
        }

        $search = trim((string) $request->input('q', ''));

        $patientsQuery = User::query()
            ->select('users.*')
            ->where(function ($query) use ($clinic) {
                $query->whereHas('appointments', function ($appointments) use ($clinic) {
                    $appointments->where('clinic_id', $clinic->id);
                })->orWhereHas('queueEntries', function ($queues) use ($clinic) {
                    $queues->where('clinic_id', $clinic->id);
                })->orWhereHas('clinicsAsPatient', function ($patientClinics) use ($clinic) {
                    $patientClinics->where('clinics.id', $clinic->id);
                });
            })
            ->withCount([
                'appointments as clinic_appointments_count' => function ($appointments) use ($clinic) {
                    $appointments->where('clinic_id', $clinic->id);
                },
                'queueEntries as clinic_queue_entries_count' => function ($queues) use ($clinic) {
                    $queues->where('clinic_id', $clinic->id);
                },
            ])
            ->addSelect([
                'last_appointment_date' => Appointment::selectRaw('MAX(appointment_date)')
                    ->whereColumn('appointments.user_id', 'users.id')
                    ->where('appointments.clinic_id', $clinic->id),
                'last_queue_activity' => QueueEntry::selectRaw('MAX(created_at)')
                    ->whereColumn('queue_entries.user_id', 'users.id')
                    ->where('queue_entries.clinic_id', $clinic->id),
            ]);

        if ($search !== '') {
            $patientsQuery->where(function ($query) use ($search) {
                $query->where('users.name', 'like', "%{$search}%")
                      ->orWhere('users.email', 'like', "%{$search}%")
                      ->orWhere('users.phone', 'like', "%{$search}%");
            });
        }

        $patients = $patientsQuery
            ->orderBy('users.name')
            ->paginate(12)
            ->withQueryString();

        return view('secretary.patients.index', [
            'clinic' => $clinic,
            'patients' => $patients,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('secretary.patients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:500',
            'password'     => 'nullable|string|min:8|confirmed',
        ]);

        $plainPassword = $data['password'] ?: Str::random(12);

        $patient = User::create([
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'name'         => trim($data['first_name'].' '.$data['last_name']),
            'email'        => $data['email'],
            'phone'        => $data['phone'] ?? null,
            'address'      => $data['address'] ?? null,
            'password'     => Hash::make($plainPassword),
            'is_admin'     => false,
            'is_secretary' => false,
            'is_doctor'    => false,
        ]);

        session()->flash('generated_password', $plainPassword);

        $activeClinicId = (int) $request->session()->get('active_clinic_id');
        if ($activeClinicId > 0 && $request->user()->secretaryClinics()->where('clinics.id', $activeClinicId)->exists()) {
            $patient->clinicsAsPatient()->syncWithoutDetaching([
                $activeClinicId => ['registered_by' => $request->user()->id],
            ]);
        }

        return redirect()
            ->route('secretary.patients.create')
            ->with('status', 'Patient account registered successfully for '.$patient->name.'.');
    }
}
