<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Notifications\AppointmentStatusChanged;
use App\Notifications\PatientAppointmentBooked;
use App\Notifications\SecretaryAppointmentBooked;
use App\Notifications\DoctorAppointmentBooked;

class AppointmentController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware(['auth', EnsureSelectedClinic::class]);

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
        $activeClinic = $this->activeClinic($request);
        $activeClinicId = $activeClinic->id;

        $activeClinic->loadMissing(['services', 'doctors.services']);
        $this->applyClinicScopedDoctorServices(collect([$activeClinic]));
        $clinics = collect([$activeClinic]);
        $clinicPatients = $this->buildClinicPatientMap($clinics);

        return view('secretary.appointments.create', compact('clinics', 'clinicPatients', 'activeClinicId', 'activeClinic'));
    }

    public function index(Request $request)
    {
        $activeClinicId = $this->activeClinicId($request);
        $query = Appointment::with('user','clinic','service','doctor')
            ->where('clinic_id', $activeClinicId);

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
            ->whereHas('clinics', function($q) use ($activeClinicId){
                $q->where('clinics.id', $activeClinicId);
            })->get();

        return view('secretary.appointments.index', compact('appointments','doctors'));
    }

    public function edit(Request $request, Appointment $appointment)
    {
        $activeClinic = $this->activeClinic($request);
        $activeClinicId = $activeClinic->id;
        $clinics = collect([$activeClinic]);
        $clinicIds = collect([$activeClinicId]);
        $doctors = User::where('is_doctor', true)
            ->whereHas('clinics', function($q) use ($clinicIds){
                $q->whereIn('clinics.id', $clinicIds);
            })->get();
        $services = $activeClinic->services()->orderBy('name')->get();

        if (! $clinicIds->contains($appointment->clinic_id)) {
            abort(403,'You are not assigned to this clinic.');
        }

        return view('secretary.appointments.edit', compact('appointment','clinics','doctors','services'));
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

        $activeClinicId = $this->activeClinicId($req);
        if ((int) $data['clinic_id'] !== $activeClinicId) {
            return back()->withInput()->withErrors(['clinic_id' => 'You cannot manage appointments for this clinic.']);
        }

        if ($data['status'] === 'scheduled' && $data['doctor_id']) {
            if (! $this->doctorOffersServiceForClinic(
                (int) $data['doctor_id'],
                (int) $data['clinic_id'],
                (int) $data['service_id']
            )) {
                return back()->withInput()->withErrors([
                    'doctor_id' => 'Selected doctor does not offer that service at this clinic.'
                ]);
            }

            $day = \Carbon\Carbon::parse($data['appointment_date'])->dayOfWeek;
            $appointmentDate = \Carbon\Carbon::parse($data['appointment_date'])->toDateString();
            $time = $data['appointment_time'];
            $hasSchedule = \App\Models\DoctorSchedule::where('doctor_id', $data['doctor_id'])
                ->where('clinic_id', $data['clinic_id'])
                ->where('day_of_week', $day)
                ->where('is_active', true)
                ->where(function ($q) use ($appointmentDate) {
                    $q->whereNull('start_date')
                        ->orWhereDate('start_date', '<=', $appointmentDate);
                })
                ->where(function ($q) use ($appointmentDate) {
                    $q->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $appointmentDate);
                })
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
            ->whereIn('status', ['waiting', 'now_serving'])
            ->update(['status' => 'cancelled']);

        $appointment->delete();
        return back()->with('status','Appointment deleted.');
    }

    public function store(Request $request)
    {
        $clinicId = $this->activeClinicId($request);

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

        if (! $this->doctorOffersServiceForClinic(
            (int) $data['doctor_id'],
            (int) $clinicId,
            (int) $data['service_id']
        )) {
            return back()->withInput()->withErrors([
                'doctor_id' => 'Selected doctor does not offer that service at this clinic.'
            ]);
        }

        $time = \Carbon\Carbon::parse($data['appointment_time'])->format('H:i:s');

        $exists = Appointment::where('user_id', $patient->id)
            ->where('clinic_id', $clinicId)
            ->where('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $time)
            ->whereNotIn('status', ['cancelled','no_show'])
            ->exists();
        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['appointment_time' => 'This patient already has an appointment for this timeslot.']);
        }

        $globalConflict = Appointment::where('user_id', $patient->id)
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $time)
            ->whereNotIn('status', ['cancelled','no_show'])
            ->exists();
        if ($globalConflict) {
            return back()
                ->withInput()
                ->withErrors(['appointment_time' => 'Patient already has another appointment at this timeslot.']);
        }

        $day = \Carbon\Carbon::parse($data['appointment_date'])->dayOfWeek;
        $appointmentDate = \Carbon\Carbon::parse($data['appointment_date'])->toDateString();

        $hasSchedule = \App\Models\DoctorSchedule::where('doctor_id', $data['doctor_id'])
            ->where('clinic_id', $clinicId)
            ->where('day_of_week', $day)
            ->where('is_active', true)
            ->where(function ($q) use ($appointmentDate) {
                $q->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $appointmentDate);
            })
            ->where(function ($q) use ($appointmentDate) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $appointmentDate);
            })
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

        try {
            $appointment = Appointment::create([
                'user_id'          => $patient->id,
                'clinic_id'        => $clinicId,
                'service_id'       => $data['service_id'],
                'doctor_id'        => $data['doctor_id'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $time,
                'status'           => 'scheduled',
                'notes'            => $data['notes'] ?? null,
            ]);
        } catch (QueryException $e) {
            $sqlState = (string) ($e->errorInfo[0] ?? '');
            $errorMessage = strtolower((string) $e->getMessage());
            $isDoctorSlotConflict = $sqlState === '23000' && str_contains($errorMessage, 'appointments_doctor_date_time_unique');

            if ($isDoctorSlotConflict) {
                return back()->withInput()->withErrors([
                    'appointment_time' => 'Doctor already booked for that timeslot.',
                ]);
            }

            throw $e;
        }

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

    protected function patientBelongsToClinic(int $userId, int $clinicId): bool
    {
        return (bool) \App\Models\Patient::where('user_id', $userId)
            ->where('clinic_id', $clinicId)
            ->exists();
    }

    protected function doctorOffersServiceForClinic($doctorId, $clinicId, $serviceId): bool
    {
        return (bool) \App\Models\DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->exists()
            && (bool) \App\Models\Service::whereHas('doctors', function ($q) use ($doctorId) {
                $q->where('users.id', $doctorId);
            })->where('id', $serviceId)->exists();
    }

    protected function applyClinicScopedDoctorServices($clinics): void
    {
        foreach ($clinics as $clinic) {
            $clinic->setRelation(
                'doctors',
                $clinic->doctors->map(function ($doctor) use ($clinic) {
                    $doctor->setRelation(
                        'services',
                        $doctor->services()->where('clinic_id', $clinic->id)->get()
                    );
                    return $doctor;
                })
            );
        }
    }
}
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

    private function doctorOffersServiceForClinic(int $doctorId, int $clinicId, int $serviceId): bool
    {
        return DB::table('doctor_service as ds')
            ->join('clinic_doctor as cd', function ($join) {
                $join->on('cd.doctor_id', '=', 'ds.doctor_id')
                    ->on('cd.clinic_id', '=', 'ds.clinic_id');
            })
            ->join('clinic_service as cs', function ($join) {
                $join->on('cs.clinic_id', '=', 'ds.clinic_id')
                    ->on('cs.service_id', '=', 'ds.service_id');
            })
            ->where('ds.doctor_id', $doctorId)
            ->where('ds.clinic_id', $clinicId)
            ->where('ds.service_id', $serviceId)
            ->exists();
    }

    private function applyClinicScopedDoctorServices($clinics): void
    {
        foreach ($clinics as $clinic) {
            if (! $clinic->relationLoaded('doctors')) {
                continue;
            }

            foreach ($clinic->doctors as $doctor) {
                $doctor->setRelation(
                    'services',
                    $doctor->servicesForClinic((int) $clinic->id)
                        ->orderBy('services.name')
                        ->get(['services.id', 'services.name'])
                );
            }
        }
    }
}
