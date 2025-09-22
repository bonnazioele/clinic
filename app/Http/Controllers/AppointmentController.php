<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PatientAppointmentBooked;
use App\Notifications\SecretaryAppointmentBooked;
use App\Notifications\DoctorAppointmentBooked;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    public function index()
    {
        $user = Auth::user();

        $upcoming = $user->appointments()
                         ->where('appointment_date', '>=', now()->toDateString())
                         ->where('status', 'scheduled')
                         ->orderBy('appointment_date')
                         ->orderBy('appointment_time')
                         ->with('clinic', 'service', 'doctor')
                         ->get();

        $past = $user->appointments()
                     ->where(function($query) {
                         $query->where('appointment_date', '<', now()->toDateString())
                               ->orWhereIn('status', ['completed', 'cancelled']);
                     })
                     ->orderBy('appointment_date', 'desc')
                     ->with('clinic', 'service', 'doctor')
                     ->get();

        return view('appointments.index', compact('upcoming', 'past'));
    }

    public function create()
    {
        $clinics = Clinic::with(['services','doctors.services'])->get();
        return view('appointments.create', compact('clinics'));
    }

    /**
     * Return availability (time slots) for a doctor at a clinic on a given date.
     */
    public function availability(Request $request)
    {
        $data = $request->validate([
            'clinic_id'  => 'required|exists:clinics,id',
            'doctor_id'  => 'required|exists:users,id',
            'date'       => 'required|date|after_or_equal:today',
            'service_id' => 'nullable|exists:services,id',
        ]);

        $date      = \Carbon\Carbon::parse($data['date']);
        $dayOfWeek = $date->dayOfWeek; // 0 (Sun) .. 6 (Sat)

        // Fetch all active schedule blocks for that doctor/clinic/day
        $schedules = \App\Models\DoctorSchedule::where('doctor_id', $data['doctor_id'])
            ->where('clinic_id', $data['clinic_id'])
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get(['id','start_time','end_time']);

        if ($schedules->isEmpty()) {
            return response()->json([
                'date' => $date->toDateString(),
                'weekday' => $date->format('l'),
                'slots' => [],
                'schedule' => [],
                'message' => 'No schedule for this doctor on the selected day.'
            ]);
        }

        // Determine slot length (service duration pivot) or fallback to 30 minutes
        $slotMinutes = 30;
        if (!empty($data['service_id'])) {
            $dur = \DB::table('clinic_service')
                ->where('clinic_id', $data['clinic_id'])
                ->where('service_id', $data['service_id'])
                ->value('duration_minutes');
            if ($dur && is_numeric($dur) && $dur > 0 && $dur <= 480) {
                $slotMinutes = (int) $dur;
            }
        }

        // Gather already booked times (non-cancelled) ensuring we normalize to HH:MM.
        // pluck() invokes the accessor which returns a Carbon instance; calling substr on that produced incorrect values (e.g. '2025-').
        $booked = Appointment::where('doctor_id', $data['doctor_id'])
            ->whereDate('appointment_date', $date->toDateString())
            ->where('status', '!=', 'cancelled')
            ->get(['appointment_time'])
            ->map(function($a){
                $raw = $a->getRawOriginal('appointment_time'); // 'HH:MM:SS'
                if (is_string($raw) && strlen($raw) >= 5) {
                    return substr($raw,0,5); // HH:MM
                }
                $val = $a->appointment_time; // accessor (Carbon) fallback
                return $val instanceof \Carbon\Carbon ? $val->format('H:i') : (string) $val;
            })
            ->filter()
            ->unique()
            ->values();

        $slots = [];
        foreach ($schedules as $sch) {
            $start = \Carbon\Carbon::createFromFormat('H:i:s', $sch->start_time, $date->timezone)
                ->setDate($date->year, $date->month, $date->day);
            $end   = \Carbon\Carbon::createFromFormat('H:i:s', $sch->end_time, $date->timezone)
                ->setDate($date->year, $date->month, $date->day);

            // Generate slots inside [start, end) ensuring slot fits fully before end
            $cursor = $start->copy();
            while ($cursor->copy()->addMinutes($slotMinutes) <= $end) {
                $timeLabel = $cursor->format('H:i');
                $slots[] = [
                    'time'      => $timeLabel,            // underlying 24h value to submit
                    'display'   => $cursor->format('g:i A'), // user-facing label
                    'available' => !$booked->contains($timeLabel),
                ];
                $cursor->addMinutes($slotMinutes);
            }
        }

        return response()->json([
            'date' => $date->toDateString(),
            'weekday' => $date->format('l'),
            'slot_minutes' => $slotMinutes,
            'schedule' => $schedules->map(fn($s) => [
                'start' => substr($s->start_time,0,5),
                'end'   => substr($s->end_time,0,5),
            ]),
            'booked' => $booked,
            'slots'  => $slots,
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
            return back()->withInput()->withErrors([
                'appointment_time' => 'Selected doctor is not available at that time for this clinic.'
            ]);
        }

        $doctorBusy = Appointment::where('doctor_id', $data['doctor_id'])
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $time)
            ->where('status', '!=', 'cancelled')
            ->exists();
        if ($doctorBusy) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'Doctor is already booked for that timeslot.'
            ]);
        }

        $patientConflict = Appointment::where('user_id', Auth::id())
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $time)
            ->where('status', '!=', 'cancelled')
            ->exists();
        if ($patientConflict) {
            return back()->withInput()->withErrors([
                'appointment_time' => 'You already have an appointment at this timeslot.'
            ]);
        }

        $appointment = Auth::user()
                           ->appointments()
                           ->create([
                               'clinic_id'        => $data['clinic_id'],
                               'service_id'       => $data['service_id'],
                               'doctor_id'        => $data['doctor_id'],
                               'appointment_date' => $data['appointment_date'],
                               'appointment_time' => $data['appointment_time'],
                               'status'           => 'scheduled',
                           ]);

        // Store optional medical document
        if ($request->hasFile('medical_document')) {
            $path = $request->file('medical_document')->store('medical-documents', 'public');
            $appointment->update(['medical_document' => $path]);
        }

        $queueService = app(\App\Services\QueueService::class);
        $queueNumber = $queueService->getNextNumber($data['clinic_id']);

        \App\Models\QueueEntry::create([
            'clinic_id'     => $data['clinic_id'],
            'user_id'       => Auth::id(),
            'appointment_id'=> $appointment->id,
            'queue_number'  => $queueNumber,
            'status'        => 'waiting',
        ]);

    Auth::user()->notify(new PatientAppointmentBooked($appointment));

        if ($appointment->clinic) {
            $appointment->clinic->secretaries()->each(function($sec) use ($appointment) {
                $sec->notify(new SecretaryAppointmentBooked($appointment));
            });
        }

        if ($appointment->doctor) {
            $appointment->doctor->notify(new DoctorAppointmentBooked($appointment));
        }

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Appointment booked successfully.');
    }

    public function destroy(Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id()) {
            abort(403, 'Forbidden');
        }

        if ($appointment->status === 'completed') {
            return back()->with('error', 'Completed appointments cannot be cancelled.');
        }

        // Cancel any waiting queue entries tied to this appointment
        \App\Models\QueueEntry::where('appointment_id', $appointment->id)
            ->where('status', 'waiting')
            ->update(['status' => 'cancelled']);

        // Hard delete to free the doctor timeslot (unique index enforcement)
        $appointment->delete();

        return back()->with('status', 'Appointment cancelled and slot freed.');
    }
}
