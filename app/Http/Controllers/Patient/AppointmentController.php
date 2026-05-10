<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Appointment;
use App\Models\QueueEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Notifications\PatientAppointmentBooked;
use App\Notifications\SecretaryAppointmentBooked;
use App\Notifications\DoctorAppointmentBooked;
use App\Services\DoctorScheduleAvailability;
use App\Services\QueueService;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    public function index()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $activeAppointmentStatuses = Appointment::ACTIVE_STATUSES;
        $historyAppointmentStatuses = ['completed', 'cancelled', 'no_show', 'rescheduled'];

        /*
        |--------------------------------------------------------------------------
        | Today's Appointments
        |--------------------------------------------------------------------------
        | Only today's scheduled appointments.
        | Active queue status will be shown from queueEntries.
        */
        $todayAppointments = $user->appointments()
            ->with(['clinic', 'service', 'doctor', 'queueEntries'])
            ->whereDate('appointment_date', $today)
            ->whereIn('status', $activeAppointmentStatuses)
            ->orderBy('appointment_time')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Upcoming Appointments
        |--------------------------------------------------------------------------
        | Future appointments only.
        | This prevents today's appointments/queues from appearing in Upcoming.
        */
        $upcomingAppointments = $user->appointments()
            ->with(['clinic', 'service', 'doctor', 'queueEntries'])
            ->whereDate('appointment_date', '>', $today)
            ->whereIn('status', $activeAppointmentStatuses)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Past / History Appointments
        |--------------------------------------------------------------------------
        | Includes past dates and final statuses.
        | No Show must stay visible here.
        */
        $pastAppointments = $user->appointments()
            ->with(['clinic', 'service', 'doctor', 'queueEntries'])
            ->where(function ($query) use ($today, $historyAppointmentStatuses) {
                $query->whereDate('appointment_date', '<', $today)
                    ->orWhereIn('status', $historyAppointmentStatuses);
            })
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Backward compatibility
        |--------------------------------------------------------------------------
        | If some old blade code still uses $upcoming or $past, this prevents errors.
        */
        $upcoming = $todayAppointments->merge($upcomingAppointments);
        $past = $pastAppointments;

        return view('appointments.index', compact(
            'todayAppointments',
            'upcomingAppointments',
            'pastAppointments',
            'upcoming',
            'past'
        ));
    }

    public function create()
    {
        $clinics = Clinic::with(['services', 'doctors.services'])
            ->where('status', 'active')
            ->get();

        $this->applyClinicScopedDoctorServices($clinics);

        return view('appointments.create', compact('clinics'));
    }

    public function availability(Request $request)
    {
        $data = $request->validate([
            'clinic_id'  => 'required|exists:clinics,id',
            'doctor_id'  => 'required|exists:users,id',
            'date'       => 'required|date|after_or_equal:today',
            'service_id' => 'nullable|exists:services,id',
        ]);

        $date = \Carbon\Carbon::parse($data['date']);

        if (! empty($data['service_id']) && ! $this->doctorOffersServiceForClinic(
            (int) $data['doctor_id'],
            (int) $data['clinic_id'],
            (int) $data['service_id']
        )) {
            return response()->json([
                'date' => $date->toDateString(),
                'weekday' => $date->format('l'),
                'slots' => [],
                'schedule' => [],
                'message' => 'Selected doctor does not offer this service at this clinic.',
            ]);
        }

        $queueService = app(QueueService::class);
        $schedules = $queueService->schedulesForDate(
            (int) $data['clinic_id'],
            (int) $data['doctor_id'],
            $date,
            ! empty($data['service_id']) ? (int) $data['service_id'] : null
        );

        if ($schedules->isEmpty()) {
            return response()->json([
                'date' => $date->toDateString(),
                'weekday' => $date->format('l'),
                'slots' => [],
                'schedule' => [],
                'message' => 'No schedule for this doctor on the selected day.',
            ]);
        }

        $slotMinutes = $queueService->getSlotMinutes(
            (int) $data['clinic_id'],
            ! empty($data['service_id']) ? (int) $data['service_id'] : null
        );

        $slots = $queueService->availableSlots(
            (int) $data['clinic_id'],
            (int) $data['doctor_id'],
            ! empty($data['service_id']) ? (int) $data['service_id'] : null,
            $date
        );

        $slots = collect($slots)
            ->unique(fn ($slot) => $slot['date'].' '.$slot['time'])
            ->sortBy(['date', 'time'])
            ->values();

        return response()->json([
            'date' => $date->toDateString(),
            'weekday' => $date->format('l'),
            'slot_minutes' => $slotMinutes,
            'schedule' => $schedules->map(fn ($schedule) => [
                'start' => substr($schedule->start_time, 0, 5),
                'end' => substr($schedule->end_time, 0, 5),
            ]),
            'booked' => $slots->where('occupied', true)->pluck('time')->values(),
            'slots' => $slots->map(fn (array $slot) => [
                'time' => $slot['time'],
                'display' => $slot['display'],
                'available' => $slot['available'],
                'end_time' => $slot['end_time'],
                'occupied' => $slot['occupied'],
                'expired' => $slot['expired'],
            ])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'clinic_id'        => 'required|exists:clinics,id',
            'service_id'       => 'required|exists:services,id',
            'doctor_id'        => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'medical_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $appointmentDate = \Carbon\Carbon::parse($data['appointment_date'])->toDateString();
        $time = \Carbon\Carbon::parse($data['appointment_time'])->format('H:i:s');

        if (! $this->doctorOffersServiceForClinic(
            (int) $data['doctor_id'],
            (int) $data['clinic_id'],
            (int) $data['service_id']
        )) {
            return back()->withInput()->withErrors([
                'doctor_id' => 'Selected doctor does not offer that service at this clinic.',
            ]);
        }

        if (! app(DoctorScheduleAvailability::class)->doctorHasScheduleAt(
            (int) $data['doctor_id'],
            (int) $data['clinic_id'],
            (int) $data['service_id'],
            $appointmentDate,
            $time
        )) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Selected doctor is not available at that time for this clinic.',
            ]);
        }

        $doctorBusy = Appointment::where('doctor_id', $data['doctor_id'])
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $time)
            ->whereNotIn('status', ['cancelled', 'no_show', 'rescheduled'])
            ->exists();

        if ($doctorBusy) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Doctor is already booked for that timeslot.',
            ]);
        }

        $patientConflict = Appointment::where('user_id', Auth::id())
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $time)
            ->whereNotIn('status', ['cancelled', 'no_show', 'rescheduled'])
            ->exists();

        if ($patientConflict) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'You already have an appointment at this timeslot.',
            ]);
        }

        $sameClinicConflict = Appointment::where('user_id', Auth::id())
            ->where('clinic_id', $data['clinic_id'])
            ->whereDate('appointment_date', $data['appointment_date'])
            ->whereNotIn('status', ['cancelled', 'no_show', 'rescheduled'])
            ->exists();

        if ($sameClinicConflict) {
            return back()->withInput()->withErrors([
                'appointment_date' => 'You already have an appointment in this clinic for that date.',
            ]);
        }

        $queueService = app(QueueService::class);

        if (! $queueService->slotIsAvailable(
            (int) $data['clinic_id'],
            (int) $data['doctor_id'],
            (int) $data['service_id'],
            $appointmentDate,
            $time
        )) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Doctor is already booked for that timeslot.',
            ]);
        }

        try {
            $appointment = Auth::user()
                ->appointments()
                ->create([
                    'clinic_id'        => $data['clinic_id'],
                    'service_id'       => $data['service_id'],
                    'doctor_id'        => $data['doctor_id'],
                    'appointment_date' => $data['appointment_date'],
                    'appointment_time' => $time,
                    'status'           => 'scheduled',
                ]);
        } catch (QueryException $e) {
            $sqlState = (string) ($e->errorInfo[0] ?? '');
            $errorMessage = strtolower((string) $e->getMessage());

            $isDoctorSlotConflict = $sqlState === '23000'
                && str_contains($errorMessage, 'appointments_doctor_date_time_unique');

            if ($isDoctorSlotConflict) {
                return back()->withInput()->withErrors([
                    'appointment_time' => 'Doctor is already booked for that timeslot.',
                ]);
            }

            throw $e;
        }

        if ($request->hasFile('medical_document')) {
            $path = $request->file('medical_document')->store('medical-documents', 'public');
            $appointment->update(['medical_document' => $path]);
        }

        /*
        |--------------------------------------------------------------------------
        | Automatically join queue after booking
        |--------------------------------------------------------------------------
        | This is why the appointment page should show "View Queue Status",
        | not "Join Queue".
        */
        $queueNumber = $queueService->getSlotQueueNumber(
            (int) $data['clinic_id'],
            (int) $data['doctor_id'],
            (int) $data['service_id'],
            $appointmentDate,
            $time
        );

        QueueEntry::updateOrCreate(
            [
                'clinic_id' => $data['clinic_id'],
                'doctor_id' => $data['doctor_id'],
                'scheduled_slot_date' => $appointmentDate,
                'scheduled_slot_time' => $time,
            ],
            [
                'user_id' => Auth::id(),
                'appointment_id' => $appointment->id,
                'patient_id' => null,
                'queue_number' => $queueNumber,
                'status' => 'waiting',
                'served_at' => null,
                'service_started_at' => null,
                'service_ended_at' => null,
            ]
        );

        Auth::user()->notify(new PatientAppointmentBooked($appointment));

        if ($appointment->clinic) {
            $appointment->clinic->secretaries()->each(function ($secretary) use ($appointment) {
                $secretary->notify(new SecretaryAppointmentBooked($appointment));
            });
        }

        if ($appointment->doctor) {
            $appointment->doctor->notify(new DoctorAppointmentBooked($appointment));
        }

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Appointment booked successfully. You have been automatically added to the queue.');
    }

    public function edit(Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id()) {
            abort(403, 'Forbidden');
        }

        if (in_array($appointment->status, ['in_progress', 'completed', 'cancelled', 'no_show', 'rescheduled'], true)) {
            return redirect()
                ->route('appointments.index')
                ->with('warning', 'This appointment can no longer be modified.');
        }

        $appointment->load(['clinic.services', 'doctor', 'service']);

        $clinic = $appointment->clinic;
        $clinic->load(['services', 'doctors']);

        $this->applyClinicScopedDoctorServices(collect([$clinic]));

        return view('appointments.edit', [
            'appointment' => $appointment,
            'clinic' => $clinic,
        ]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id()) {
            abort(403, 'Forbidden');
        }

        if (in_array($appointment->status, ['in_progress', 'completed', 'cancelled', 'no_show', 'rescheduled'], true)) {
            return back()->with('warning', 'This appointment can no longer be modified.');
        }

        $data = $request->validate([
            'service_id'       => 'required|exists:services,id',
            'doctor_id'        => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
        ]);

        $appointmentDate = \Carbon\Carbon::parse($data['appointment_date'])->toDateString();
        $time = \Carbon\Carbon::parse($data['appointment_time'])->format('H:i:s');
        $clinicId = $appointment->clinic_id;

        if (! $this->doctorOffersServiceForClinic(
            (int) $data['doctor_id'],
            (int) $clinicId,
            (int) $data['service_id']
        )) {
            return back()->withInput()->withErrors([
                'doctor_id' => 'Selected doctor does not offer that service at this clinic.',
            ]);
        }

        if (! app(DoctorScheduleAvailability::class)->doctorHasScheduleAt(
            (int) $data['doctor_id'],
            (int) $clinicId,
            (int) $data['service_id'],
            $appointmentDate,
            $time
        )) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Doctor not available at that time.',
            ]);
        }

        $doctorBusy = Appointment::where('doctor_id', $data['doctor_id'])
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $time)
            ->whereNotIn('status', ['cancelled', 'no_show', 'rescheduled'])
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($doctorBusy) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Doctor already booked for that slot.',
            ]);
        }

        $patientConflict = Appointment::where('user_id', Auth::id())
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $time)
            ->whereNotIn('status', ['cancelled', 'no_show', 'rescheduled'])
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($patientConflict) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'You have another appointment at that time.',
            ]);
        }

        $appointment->update([
            'service_id' => $data['service_id'],
            'doctor_id' => $data['doctor_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $time,
            'status' => 'scheduled',
        ]);

        QueueEntry::where('appointment_id', $appointment->id)
            ->whereIn('status', QueueEntry::activePatientStatuses())
            ->update([
                'doctor_id' => $data['doctor_id'],
                'status' => 'rescheduled',
            ]);

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Appointment updated. The old queue entry was marked as rescheduled.');
    }

    public function destroy(Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id()) {
            abort(403, 'Forbidden');
        }

        if (in_array($appointment->status, ['in_progress', 'completed', 'no_show'], true)) {
            return back()->with('error', 'This appointment can no longer be cancelled.');
        }

        /*
        |--------------------------------------------------------------------------
        | Do not delete appointment.
        |--------------------------------------------------------------------------
        | It must stay visible in history.
        */
        $appointment->update([
            'status' => 'cancelled',
        ]);

        QueueEntry::where('appointment_id', $appointment->id)
            ->whereIn('status', QueueEntry::activePatientStatuses())
            ->update([
                'status' => 'cancelled',
            ]);

        return back()->with('status', 'Appointment cancelled and moved to history.');
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
