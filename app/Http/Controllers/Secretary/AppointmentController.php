<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\Appointment;
use App\Models\QueueEntry;
use App\Models\User;
use App\Notifications\AppointmentStatusChanged;
use App\Notifications\DoctorAppointmentBooked;
use App\Notifications\PatientAppointmentBooked;
use App\Notifications\SecretaryAppointmentBooked;
use App\Services\DoctorScheduleAvailability;
use App\Services\MoceanSmsService;
use App\Services\QueueService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware(['auth', EnsureSelectedClinic::class]);

        $this->middleware(function ($req, $next) {
            if (! Auth::user()?->is_secretary) {
                abort(403, 'Forbidden');
            }

            return $next($req);
        });
    }

    public function create(Request $request)
    {
        $activeClinic = $this->activeClinic($request);
        $activeClinicId = $activeClinic->id;

        $activeClinic->loadMissing(['services', 'doctors.services']);
        $this->applyClinicScopedDoctorServices(collect([$activeClinic]));

        $clinics = collect([$activeClinic]);
        $clinicPatients = $this->buildClinicPatientMap($clinics);

        return view('secretary.appointments.create', compact(
            'clinics',
            'clinicPatients',
            'activeClinicId',
            'activeClinic'
        ));
    }

    public function index(Request $request)
{
    $activeClinic = $this->activeClinic($request);
    $activeClinicId = $activeClinic->id;

    $query = Appointment::with('user', 'clinic', 'service', 'doctor')
        ->where('clinic_id', $activeClinicId);

    if ($request->filled('patient')) {
        $term = trim((string) $request->input('patient'));

        $query->whereHas('user', function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    if ($request->filled('status')) {
        $query->where('status', (string) $request->input('status'));
    }

    if ($request->filled('date')) {
        $query->whereDate('appointment_date', (string) $request->input('date'));
    }

    if ($request->filled('doctor_id')) {
        $query->where('doctor_id', (int) $request->input('doctor_id'));
    }

    if ($request->filled('service_id')) {
        $query->where('service_id', (int) $request->input('service_id'));
    }

    $appointments = $query
        ->orderBy('appointment_date')
        ->orderBy('appointment_time')
        ->paginate(15)
        ->appends($request->except(['page', 'export']));

    $doctors = User::where('is_doctor', true)
        ->whereHas('clinics', function ($q) use ($activeClinicId) {
            $q->where('clinics.id', $activeClinicId);
        })
        ->orderBy('name')
        ->get();

    $services = $activeClinic->services()
        ->orderBy('name')
        ->get();

    return view('secretary.appointments.index', compact(
        'appointments',
        'doctors',
        'services',
        'activeClinic',
        'activeClinicId'
    ));
}

    public function edit(Request $request, Appointment $appointment)
    {
        $activeClinic = $this->activeClinic($request);
        $activeClinicId = $activeClinic->id;

        $clinics = collect([$activeClinic]);
        $clinicIds = collect([$activeClinicId]);

        $doctors = User::where('is_doctor', true)
            ->whereHas('clinics', function ($q) use ($clinicIds) {
                $q->whereIn('clinics.id', $clinicIds);
            })
            ->get();

        $services = $activeClinic->services()->orderBy('name')->get();

        if (! $clinicIds->contains($appointment->clinic_id)) {
            abort(403, 'You are not assigned to this clinic.');
        }

        return view('secretary.appointments.edit', compact(
            'appointment',
            'clinics',
            'doctors',
            'services'
        ));
    }

    public function update(Request $req, Appointment $appointment, MoceanSmsService $sms)
    {
        $oldStatus = $appointment->status;

        $data = $req->validate([
            'clinic_id'        => 'required|exists:clinics,id',
            'service_id'       => 'required|exists:services,id',
            'doctor_id'        => 'nullable|exists:users,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'status'           => 'required|in:scheduled,in_progress,completed,cancelled,no_show,rescheduled',
        ]);

        $activeClinicId = $this->activeClinicId($req);

        if ((int) $data['clinic_id'] !== $activeClinicId) {
            return back()
                ->withInput()
                ->withErrors(['clinic_id' => 'You cannot manage appointments for this clinic.']);
        }

        if ($data['status'] === 'scheduled' && $data['doctor_id']) {
            if (! $this->doctorOffersServiceForClinic(
                (int) $data['doctor_id'],
                (int) $data['clinic_id'],
                (int) $data['service_id']
            )) {
                return back()->withInput()->withErrors([
                    'doctor_id' => 'Selected doctor does not offer that service at this clinic.',
                ]);
            }

            $appointmentDate = \Carbon\Carbon::parse($data['appointment_date'])->toDateString();
            $time = \Carbon\Carbon::parse($data['appointment_time'])->format('H:i:s');

            $hasSchedule = app(DoctorScheduleAvailability::class)->doctorHasScheduleAt(
                (int) $data['doctor_id'],
                (int) $data['clinic_id'],
                (int) $data['service_id'],
                $appointmentDate,
                $time
            );

            if (! $hasSchedule) {
                return back()->withInput()->withErrors([
                    'appointment_time' => 'Doctor not available for that time.',
                ]);
            }

            $doctorBusy = Appointment::where('doctor_id', $data['doctor_id'])
                ->whereDate('appointment_date', $appointmentDate)
                ->where('appointment_time', $time)
                ->whereNotIn('status', Appointment::FINAL_STATUSES)
                ->where('id', '!=', $appointment->id)
                ->exists();

            if ($doctorBusy) {
                return back()->withInput()->withErrors([
                    'appointment_time' => 'Doctor already booked for that timeslot.',
                ]);
            }

            $patientConflict = Appointment::where('user_id', $appointment->user_id)
                ->whereDate('appointment_date', $appointmentDate)
                ->where('appointment_time', $time)
                ->whereNotIn('status', Appointment::FINAL_STATUSES)
                ->where('id', '!=', $appointment->id)
                ->exists();

            if ($patientConflict) {
                return back()->withInput()->withErrors([
                    'appointment_time' => 'Patient already has another appointment at this timeslot.',
                ]);
            }
        }

        $data['appointment_time'] = \Carbon\Carbon::parse($data['appointment_time'])->format('H:i:s');

        $appointment->update($data);
        $appointment->refresh()->loadMissing(['user', 'clinic', 'service', 'doctor']);

        $appointment->user?->notify(new AppointmentStatusChanged($appointment));

        if ($oldStatus !== $appointment->status) {
            $this->sendAppointmentStatusSms($sms, $appointment);
        }

        return redirect()
            ->route('secretary.appointments.index')
            ->with('status', 'Appointment updated.');
    }

    public function destroy(Appointment $appointment, MoceanSmsService $sms)
    {
        $appointment->loadMissing(['user', 'clinic']);

        QueueEntry::where('appointment_id', $appointment->id)
            ->where('status', 'waiting')
            ->update(['status' => 'cancelled']);

        $this->sendAppointmentSms(
            $sms,
            $appointment,
            'CliniQ: Your appointment has been deleted/cancelled by the clinic. Please contact the clinic for more details.'
        );

        $appointment->delete();

        return back()->with('status', 'Appointment deleted.');
    }

    public function store(Request $request, MoceanSmsService $sms)
    {
        $clinicId = $this->activeClinicId($request);

        $data = $request->validate([
            'patient_id'       => 'required|exists:users,id',
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

        $doctor = User::where('id', $data['doctor_id'])
            ->where('is_doctor', true)
            ->whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinics.id', $clinicId);
            })
            ->first();

        if (! $doctor) {
            return back()->withInput()->withErrors([
                'doctor_id' => 'Doctor not assigned to this clinic.',
            ]);
        }

        if (! $this->doctorOffersServiceForClinic(
            (int) $data['doctor_id'],
            (int) $clinicId,
            (int) $data['service_id']
        )) {
            return back()->withInput()->withErrors([
                'doctor_id' => 'Selected doctor does not offer that service at this clinic.',
            ]);
        }

        $appointmentDate = \Carbon\Carbon::parse($data['appointment_date'])->toDateString();
        $time = \Carbon\Carbon::parse($data['appointment_time'])->format('H:i:s');

        $exists = Appointment::where('user_id', $patient->id)
            ->where('clinic_id', $clinicId)
            ->whereDate('appointment_date', $appointmentDate)
            ->where('appointment_time', $time)
            ->whereNotIn('status', Appointment::FINAL_STATUSES)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['appointment_time' => 'This patient already has an appointment for this timeslot.']);
        }

        $globalConflict = Appointment::where('user_id', $patient->id)
            ->whereDate('appointment_date', $appointmentDate)
            ->where('appointment_time', $time)
            ->whereNotIn('status', Appointment::FINAL_STATUSES)
            ->exists();

        if ($globalConflict) {
            return back()
                ->withInput()
                ->withErrors(['appointment_time' => 'Patient already has another appointment at this timeslot.']);
        }

        $hasSchedule = app(DoctorScheduleAvailability::class)->doctorHasScheduleAt(
            (int) $data['doctor_id'],
            (int) $clinicId,
            (int) $data['service_id'],
            $appointmentDate,
            $time
        );

        if (! $hasSchedule) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Doctor not available for that time.',
            ]);
        }

        $doctorBusy = Appointment::where('doctor_id', $data['doctor_id'])
            ->whereDate('appointment_date', $appointmentDate)
            ->where('appointment_time', $time)
            ->whereNotIn('status', Appointment::FINAL_STATUSES)
            ->exists();

        if ($doctorBusy) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Doctor already booked for that timeslot.',
            ]);
        }

        $queueService = app(QueueService::class);

        if (! $queueService->slotIsAvailable(
            (int) $clinicId,
            (int) $data['doctor_id'],
            (int) $data['service_id'],
            $appointmentDate,
            $time
        )) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Doctor already booked for that timeslot.',
            ]);
        }

        try {
            $appointment = Appointment::create([
                'user_id'          => $patient->id,
                'clinic_id'        => $clinicId,
                'service_id'       => $data['service_id'],
                'doctor_id'        => $data['doctor_id'],
                'appointment_date' => $appointmentDate,
                'appointment_time' => $time,
                'status'           => 'scheduled',
                'notes'            => $data['notes'] ?? null,
            ]);
        } catch (QueryException $e) {
            $sqlState = (string) ($e->errorInfo[0] ?? '');
            $errorMessage = strtolower((string) $e->getMessage());

            $isDoctorSlotConflict = $sqlState === '23000'
                && (
                    str_contains($errorMessage, 'appointments_doctor_date_time_unique')
                    || str_contains($errorMessage, 'appointments_active_doctor_slot_unique')
                );

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

        $queueNumber = $queueService->getSlotQueueNumber(
            (int) $clinicId,
            (int) $data['doctor_id'],
            (int) $data['service_id'],
            $appointmentDate,
            $time
        );

        $queueService->createOrReuseSlotEntry(
            (int) $clinicId,
            (int) $data['doctor_id'],
            $appointmentDate,
            $time,
            [
                'user_id' => $patient->id,
                'appointment_id' => $appointment->id,
                'patient_id' => null,
                'queue_number' => $queueNumber,
                'status' => 'waiting',
                'served_at' => null,
                'service_started_at' => null,
                'service_ended_at' => null,
            ]
        );

        $appointment->loadMissing(['user', 'clinic', 'service', 'doctor']);

        $appointment->user?->notify(new PatientAppointmentBooked($appointment));

        if ($appointment->clinic) {
            $appointment->clinic->secretaries()->each(function ($sec) use ($appointment) {
                $sec->notify(new SecretaryAppointmentBooked($appointment));
            });
        }

        if ($appointment->doctor) {
            $appointment->doctor->notify(new DoctorAppointmentBooked($appointment));
        }

        $clinicName = $appointment->clinic?->name ?? 'the clinic';
        $dateText = \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y');
        $timeText = \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A');

        $this->sendAppointmentSms(
            $sms,
            $appointment,
            "CliniQ: Your appointment at {$clinicName} has been booked for {$dateText} at {$timeText}. Your queue number is {$queueNumber}."
        );

        return redirect()
            ->route('secretary.appointments.index')
            ->with('status', 'Appointment created successfully for patient.');
    }

    public function show(Appointment $a)
    {
        return redirect()->route('secretary.appointments.index');
    }

    private function filteredAppointmentQuery(Request $request, int $activeClinicId, bool $includeStatus = true, ?string $period = null)
    {
        $query = Appointment::with('user', 'clinic', 'service', 'doctor')
            ->where('clinic_id', $activeClinicId);

        $today = now()->toDateString();
        $period = $period ?: $this->appointmentPeriod($request);

        if ($period === 'upcoming') {
            $query->whereDate('appointment_date', '>', $today);
        } else {
            $query->whereDate('appointment_date', $today);
        }

        if ($request->filled('patient')) {
            $term = trim((string) $request->input('patient'));

            $query->whereHas('user', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', (int) $request->input('doctor_id'));
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', (int) $request->input('service_id'));
        }

        if ($includeStatus && $request->filled('status')) {
            $status = (string) $request->input('status');

            if (in_array($status, ['scheduled', 'completed', 'cancelled', 'no_show', 'rescheduled'], true)) {
                $query->where('status', $status);
            }
        }

        return $query;
    }

    private function appointmentPeriod(Request $request): string
    {
        return $request->query('period') === 'upcoming' ? 'upcoming' : 'today';
    }

    private function exportAppointments($query, int $clinicId)
    {
        $appointments = (clone $query)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $filename = 'secretary-appointments-clinic-' . $clinicId . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($appointments) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Appointment ID',
                'Patient Name',
                'Patient Email',
                'Patient Phone',
                'Service',
                'Doctor Name',
                'Doctor Email',
                'Appointment Date',
                'Appointment Time',
                'Status',
            ]);

            foreach ($appointments as $appointment) {
                fputcsv($handle, [
                    $appointment->id,
                    $appointment->user?->name ?? '',
                    $appointment->user?->email ?? '',
                    $appointment->user?->phone ?? '',
                    $appointment->service?->name ?? '',
                    $appointment->doctor?->name ? 'Dr. ' . $appointment->doctor->name : '',
                    $appointment->doctor?->email ?? '',
                    $appointment->appointment_date?->format('Y-m-d') ?? '',
                    $appointment->appointment_time?->format('H:i') ?? '',
                    $appointment->status_label,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

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

    private function sendAppointmentStatusSms(MoceanSmsService $sms, Appointment $appointment): void
    {
        $clinicName = $appointment->clinic?->name ?? 'the clinic';

        $dateText = $appointment->appointment_date
            ? \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y')
            : '';

        $timeText = $appointment->appointment_time
            ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A')
            : '';

        $message = match ($appointment->status) {
            'scheduled' => "CliniQ: Your appointment at {$clinicName} is scheduled for {$dateText} at {$timeText}.",
            'in_progress' => "CliniQ: Your appointment at {$clinicName} is now in progress.",
            'completed' => "CliniQ: Your appointment at {$clinicName} has been completed. Thank you.",
            'cancelled' => "CliniQ: Your appointment at {$clinicName} has been cancelled.",
            'no_show' => "CliniQ: You have been marked as no-show for your appointment at {$clinicName}.",
            'rescheduled' => "CliniQ: Your appointment at {$clinicName} has been rescheduled.",
            default => "CliniQ: Your appointment status at {$clinicName} has been updated to {$appointment->status}.",
        };

        $this->sendAppointmentSms($sms, $appointment, $message);
    }

    private function sendAppointmentSms(MoceanSmsService $sms, Appointment $appointment, string $message): void
    {
        $appointment->loadMissing(['user']);

        $phone = $this->extractPhoneNumber($appointment->user);

        $sms->send($phone, $message);
    }

    private function extractPhoneNumber($model): ?string
    {
        if (! $model) {
            return null;
        }

        foreach ([
            'phone',
            'phone_number',
            'mobile',
            'mobile_number',
            'contact_number',
            'contact',
            'cellphone',
            'cellphone_number',
        ] as $field) {
            if (isset($model->{$field}) && filled($model->{$field})) {
                return (string) $model->{$field};
            }
        }

        return null;
    }
}
