<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
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
        $clinics = Clinic::with(['services','doctors.services'])
            ->where('status', 'active')
            ->get();
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

        $date      = \Carbon\Carbon::parse($data['date']);
        $dateString = $date->toDateString();
        $dayOfWeek = $date->dayOfWeek;

        $schedules = \App\Models\DoctorSchedule::where('doctor_id', $data['doctor_id'])
            ->where('clinic_id', $data['clinic_id'])
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where(function ($q) use ($dateString) {
                $q->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $dateString);
            })
            ->where(function ($q) use ($dateString) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $dateString);
            })
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

        $booked = Appointment::where('doctor_id', $data['doctor_id'])
            ->whereDate('appointment_date', $date->toDateString())
            ->where('status', '!=', 'cancelled')
            ->get(['appointment_time'])
            ->map(function($a){
                $raw = $a->getRawOriginal('appointment_time');
                if (is_string($raw) && strlen($raw) >= 5) {
                    return substr($raw,0,5);
                }
                $val = $a->appointment_time;
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

            $cursor = $start->copy();
            while ($cursor < $end) {
                $slotEnd = $cursor->copy()->addMinutes($slotMinutes);
                if ($slotEnd > $end) {
                    break;
                }
                $timeLabel = $cursor->format('H:i');
                $slots[] = [
                    'time'       => $timeLabel,
                    'display'    => $cursor->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                    'available'  => !$booked->contains($timeLabel),
                    'end_time'   => $slotEnd->format('H:i'),
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
        $appointmentDate = \Carbon\Carbon::parse($data['appointment_date'])->toDateString();
        $time = \Carbon\Carbon::parse($data['appointment_time'])->format('H:i:s');

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


        $sameClinicConflict = Appointment::where('user_id', Auth::id())
            ->where('clinic_id', $data['clinic_id'])
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('status', '!=', 'cancelled')
            ->exists();
        if ($sameClinicConflict) {
            return back()->withInput()->withErrors([
                'appointment_date' => 'You already have an appointment in this clinic for that date.'
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
            $isDoctorSlotConflict = $sqlState === '23000' && str_contains($errorMessage, 'appointments_doctor_date_time_unique');

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

        $queueService = app(\App\Services\QueueService::class);
        $queueNumber = $queueService->getNextNumber($data['clinic_id']);

        \App\Models\QueueEntry::create([
            'clinic_id'      => $data['clinic_id'],
            'user_id'        => Auth::id(),
            'appointment_id' => $appointment->id,
            'queue_number'   => $queueNumber,
            'status'         => 'waiting',
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

    public function edit(Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id()) {
            abort(403,'Forbidden');
        }

        $appointment->load(['clinic.services','doctor','service']);

        $clinic = $appointment->clinic;
        $clinic->load(['services','doctors']);
        return view('appointments.edit', [
            'appointment' => $appointment,
            'clinic' => $clinic,
        ]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id()) {
            abort(403,'Forbidden');
        }
        if (in_array($appointment->status, ['completed','cancelled'])) {
            return back()->with('warning','This appointment can no longer be modified.');
        }

        $data = $request->validate([
            'service_id'       => 'required|exists:services,id',
            'doctor_id'        => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
        ]);

        $day = \Carbon\Carbon::parse($data['appointment_date'])->dayOfWeek;
        $appointmentDate = \Carbon\Carbon::parse($data['appointment_date'])->toDateString();
        $time = $data['appointment_time'];
        $clinicId = $appointment->clinic_id;

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
            ->where('start_time','<=',$time)
            ->where('end_time','>',$time)
            ->exists();
        if(!$hasSchedule) {
            return back()->withInput()->withErrors(['appointment_time'=>'Doctor not available at that time.']);
        }
        $doctorBusy = Appointment::where('doctor_id', $data['doctor_id'])
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $time)
            ->where('status','!=','cancelled')
            ->where('id','!=',$appointment->id)
            ->exists();
        if($doctorBusy) {
            return back()->withInput()->withErrors(['appointment_time'=>'Doctor already booked for that slot.']);
        }
        $patientConflict = Appointment::where('user_id', Auth::id())
            ->whereDate('appointment_date',$data['appointment_date'])
            ->where('appointment_time',$time)
            ->where('status','!=','cancelled')
            ->where('id','!=',$appointment->id)
            ->exists();
        if($patientConflict) {
            return back()->withInput()->withErrors(['appointment_time'=>'You have another appointment at that time.']);
        }

        $appointment->update($data);

        return redirect()->route('appointments.index')->with('status','Appointment updated.');
    }

    public function destroy(Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id()) {
            abort(403, 'Forbidden');
        }

        if ($appointment->status === 'completed') {
            return back()->with('error', 'Completed appointments cannot be cancelled.');
        }

        \App\Models\QueueEntry::where('appointment_id', $appointment->id)
            ->where('status', 'waiting')
            ->update(['status' => 'cancelled']);

        $appointment->delete();

        return back()->with('status', 'Appointment cancelled and slot freed.');
    }
}
