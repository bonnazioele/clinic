<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AppointmentStatusChanged;
use App\Notifications\PatientAppointmentBooked;
use App\Notifications\SecretaryAppointmentBooked;
use App\Notifications\DoctorAppointmentBooked;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function($req, $next) {
            if (! Auth::user()?->is_secretary) {
                abort(403, 'Forbidden');
            }
            return $next($req);
        });
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $clinics = $user->secretaryClinics()
            ->whereIn('status', ['active','approved'])
            ->with(['services','doctors.services'])
            ->get();

        $clinicPatients = $this->buildClinicPatientMap($clinics);
        $activeClinicId = (int) $request->session()->get('active_clinic_id');

        if (! $activeClinicId && $clinics->isNotEmpty()) {
            $activeClinicId = (int) $clinics->first()->id;
            $request->session()->put('active_clinic_id', $activeClinicId);
        }

        if ($activeClinicId && ! $clinics->contains('id', $activeClinicId)) {
            $activeClinicId = (int) ($clinics->first()->id ?? 0);
            if ($activeClinicId) {
                $request->session()->put('active_clinic_id', $activeClinicId);
            } else {
                $request->session()->forget('active_clinic_id');
            }
        }

        $activeClinic = $clinics->firstWhere('id', $activeClinicId) ?? null;

        return view('secretary.appointments.create', compact('clinics', 'clinicPatients', 'activeClinicId', 'activeClinic'));
    }

    public function index(Request $request)
    {
        $secretaryClinicIds = Auth::user()->secretaryClinics()->pluck('clinics.id');
        $query = Appointment::with('user','clinic','service','doctor')
            ->whereIn('clinic_id', $secretaryClinicIds);

        if ($request->filled('patient')) {
            $term = trim((string) $request->input('patient'));
            $query->whereHas('user', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }
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

        $doctors = User::where('is_doctor', true)
            ->whereHas('clinics', function($q) use ($secretaryClinicIds){
                $q->whereIn('clinics.id', $secretaryClinicIds);
            })->get();

        return view('secretary.appointments.index', compact('appointments','doctors'));
    }

    public function edit(Appointment $appointment)
    {
        $clinics = Auth::user()->secretaryClinics()->get();
        $clinicIds = $clinics->pluck('id');
        $doctors = User::where('is_doctor', true)
            ->whereHas('clinics', function($q) use ($clinicIds){
                $q->whereIn('clinics.id', $clinicIds);
            })->get();

        if (! $clinicIds->contains($appointment->clinic_id)) {
            abort(403,'You are not assigned to this clinic.');
        }

        return view('secretary.appointments.edit', compact('appointment','clinics','doctors'));
    }

    public function update(Request $req, Appointment $appointment)
    {
        $data = $req->validate([
            'clinic_id'        => 'required|exists:clinics,id',
            'service_id'       => 'required|exists:services,id',
            'doctor_id'        => 'nullable|exists:users,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'status'           => 'required|in:scheduled,completed,cancelled,no_show',
        ]);

        $allowedClinicIds = Auth::user()->secretaryClinics()->pluck('clinics.id');
        if (! $allowedClinicIds->contains($data['clinic_id'])) {
            return back()->withInput()->withErrors(['clinic_id' => 'You cannot manage appointments for this clinic.']);
        }

        if ($data['status'] === 'scheduled' && $data['doctor_id']) {
            $day = \Carbon\Carbon::parse($data['appointment_date'])->dayOfWeek;
            $time = $data['appointment_time'];
            $hasSchedule = \App\Models\DoctorSchedule::where('doctor_id', $data['doctor_id'])
                ->where('clinic_id', $data['clinic_id'])
                ->where('day_of_week', $day)
                ->where('is_active', true)
                ->where('start_time', '<=', $time)
                ->where('end_time', '>', $time)
                ->exists();
            if (! $hasSchedule) {
                return back()->withInput()->withErrors(['appointment_time' => 'Doctor not available for that time.']);
            }
            $doctorBusy = Appointment::where('doctor_id', $data['doctor_id'])
                ->whereDate('appointment_date', $data['appointment_date'])
                ->where('appointment_time', $time)
                ->whereNotIn('status', ['cancelled','no_show'])
                ->where('id', '!=', $appointment->id)
                ->exists();
            if ($doctorBusy) {
                return back()->withInput()->withErrors(['appointment_time' => 'Doctor already booked for that timeslot.']);
            }

            $patientConflict = Appointment::where('user_id', $appointment->user_id)
                ->whereDate('appointment_date', $data['appointment_date'])
                ->where('appointment_time', $data['appointment_time'])
                ->whereNotIn('status', ['cancelled','no_show'])
                ->where('id','!=',$appointment->id)
                ->exists();
            if ($patientConflict) {
                return back()->withInput()->withErrors(['appointment_time' => 'Patient already has another appointment at this timeslot.']);
            }
        }

        $appointment->update($data);

        $appointment->user->notify(new AppointmentStatusChanged($appointment));

        return redirect()
            ->route('secretary.appointments.index')
            ->with('status','Appointment updated.');
    }

    public function destroy(Appointment $appointment)
    {
        \App\Models\QueueEntry::where('appointment_id', $appointment->id)
            ->where('status', 'waiting')
            ->update(['status' => 'cancelled']);

        $appointment->delete();
        return back()->with('status','Appointment deleted.');
    }

    public function store(Request $request)
    {
        $allowedClinicIds = Auth::user()->secretaryClinics()->pluck('clinics.id');
        $clinicId = (int) $request->session()->get('active_clinic_id');

        if (! $clinicId || ! $allowedClinicIds->contains($clinicId)) {
            return back()
                ->withInput()
                ->withErrors(['active_clinic' => 'Please select one of your clinics before creating appointments.']);
        }

        $data = $request->validate([
            'patient_id'      => 'required|exists:users,id',
            'service_id'       => 'required|exists:services,id',
            'doctor_id'        => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'notes'            => 'nullable|string|max:500',
            'medical_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $patient = User::findOrFail($data['patient_id']);

        if (! $this->patientBelongsToClinic($patient->id, $clinicId)) {
            return back()
                ->withInput()
                ->withErrors(['patient_id' => 'Selected patient is not registered for this clinic.']);
        }

        $doctor = User::where('id', $data['doctor_id'])->where('is_doctor', true)
            ->whereHas('clinics', function($q) use ($clinicId){ $q->where('clinics.id', $clinicId); })
            ->first();
        if (! $doctor) {
            return back()->withInput()->withErrors(['doctor_id' => 'Doctor not assigned to this clinic.']);
        }

        $exists = Appointment::where('user_id', $patient->id)
            ->where('clinic_id', $clinicId)
            ->where('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $data['appointment_time'])
            ->whereNotIn('status', ['cancelled','no_show'])
            ->exists();
        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['appointment_time' => 'This patient already has an appointment for this timeslot.']);
        }

        $globalConflict = Appointment::where('user_id', $patient->id)
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $data['appointment_time'])
            ->whereNotIn('status', ['cancelled','no_show'])
            ->exists();
        if ($globalConflict) {
            return back()
                ->withInput()
                ->withErrors(['appointment_time' => 'Patient already has another appointment at this timeslot.']);
        }

        $day = \Carbon\Carbon::parse($data['appointment_date'])->dayOfWeek;
        $time = $data['appointment_time'];
        $hasSchedule = \App\Models\DoctorSchedule::where('doctor_id', $data['doctor_id'])
            ->where('clinic_id', $clinicId)
            ->where('day_of_week', $day)
            ->where('is_active', true)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>', $time)
            ->exists();
        if (! $hasSchedule) {
            return back()->withInput()->withErrors(['appointment_time' => 'Doctor not available for that time.']);
        }
        $doctorBusy = Appointment::where('doctor_id', $data['doctor_id'])
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $time)
            ->whereNotIn('status', ['cancelled','no_show'])
            ->exists();
        if ($doctorBusy) {
            return back()->withInput()->withErrors(['appointment_time' => 'Doctor already booked for that timeslot.']);
        }

        $appointment = Appointment::create([
            'user_id'          => $patient->id,
            'clinic_id'        => $clinicId,
            'service_id'       => $data['service_id'],
            'doctor_id'        => $data['doctor_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'status'           => 'scheduled',
            'notes'            => $data['notes'] ?? null,
        ]);

        if ($request->hasFile('medical_document')) {
            $path = $request->file('medical_document')->store('medical-documents', 'public');
            $appointment->update(['medical_document' => $path]);
        }

        $queueService = app(\App\Services\QueueService::class);
        $queueNumber = $queueService->getNextNumber($clinicId);

        \App\Models\QueueEntry::create([
            'clinic_id'     => $clinicId,
            'user_id'       => $patient->id,
            'appointment_id'=> $appointment->id,
            'queue_number'  => $queueNumber,
            'status'        => 'waiting',
        ]);

        $appointment->user->notify(new PatientAppointmentBooked($appointment));

        if ($appointment->clinic) {
            $appointment->clinic->secretaries()->each(function($sec) use ($appointment) {
                $sec->notify(new SecretaryAppointmentBooked($appointment));
            });
        }

        if ($appointment->doctor) {
            $appointment->doctor->notify(new DoctorAppointmentBooked($appointment));
        }

        return redirect()
            ->route('secretary.appointments.index')
            ->with('status', 'Appointment created successfully for patient.');
    }

    public function show(Appointment $a) { return redirect()->route('secretary.appointments.index'); }

    protected function buildClinicPatientMap($clinics): array
    {
        $map = [];

        foreach ($clinics as $clinic) {
            $patients = User::query()
                ->select('users.id', 'users.name', 'users.email')
                ->where(function ($query) use ($clinic) {
                    $query->whereHas('appointments', function ($appointments) use ($clinic) {
                        $appointments->where('clinic_id', $clinic->id);
                    })->orWhereHas('queueEntries', function ($queues) use ($clinic) {
                        $queues->where('clinic_id', $clinic->id);
                    })->orWhereHas('clinicsAsPatient', function ($patientClinics) use ($clinic) {
                        $patientClinics->where('clinics.id', $clinic->id);
                    });
                })
                ->distinct('users.id')
                ->orderBy('users.name')
                ->get();

            $map[$clinic->id] = $patients->map(fn ($patient) => [
                'id' => $patient->id,
                'name' => $patient->name,
                'email' => $patient->email,
            ]);
        }

        return $map;
    }

    protected function patientBelongsToClinic(int $patientId, int $clinicId): bool
    {
        return User::where('id', $patientId)
            ->where(function ($query) use ($clinicId) {
                $query->whereHas('appointments', function ($appointments) use ($clinicId) {
                    $appointments->where('clinic_id', $clinicId);
                })->orWhereHas('queueEntries', function ($queues) use ($clinicId) {
                    $queues->where('clinic_id', $clinicId);
                })->orWhereHas('clinicsAsPatient', function ($patientClinics) use ($clinicId) {
                    $patientClinics->where('clinics.id', $clinicId);
                });
            })
            ->exists();
    }
}
