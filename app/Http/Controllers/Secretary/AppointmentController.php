<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AppointmentStatusChanged;
use App\Notifications\AppointmentBooked;

class AppointmentController extends Controller
{
    public function __construct()
    {
        // 1) Must be logged in
        $this->middleware('auth');

        // 2) Must be a secretary
        $this->middleware(function($req, $next) {
            if (! Auth::user()?->is_secretary) {
                abort(403, 'Forbidden');
            }
            return $next($req);
        });
    }

    public function create()
    {
        // load each clinic's services AND its assigned doctors
        $clinics = \App\Models\Clinic::with(['services','doctors'])->get();

        return view('secretary.appointments.create', compact('clinics'));
    }

    /** List & manage all appointments */
    public function index()
    {
        $appointments = Appointment::with('user','clinic','service','doctor')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(15);

        $doctors = User::where('is_doctor', true)->get();

        return view('secretary.appointments.index', compact('appointments','doctors'));
    }

    /** Show edit form */
    public function edit(Appointment $appointment)
    {
        $clinics = Clinic::all();
        $doctors = User::where('is_doctor', true)->get();

        return view('secretary.appointments.edit', compact('appointment','clinics','doctors'));
    }

    /** Persist changes */
    public function update(Request $req, Appointment $appointment)
    {
        $data = $req->validate([
            'clinic_id'        => 'required|exists:clinics,id',
            'service_id'       => 'required|exists:services,id',
            'doctor_id'        => 'nullable|exists:users,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'status'           => 'required|in:scheduled,completed,cancelled',
        ]);

        $appointment->update($data);

        $appointment->user->notify(new AppointmentStatusChanged($appointment));

        return redirect()
            ->route('secretary.appointments.index')
            ->with('status','Appointment updated.');
    }

    /** Delete if needed */
    public function destroy(Appointment $appointment)
    {
        // Remove from queue if exists
        \App\Models\QueueEntry::where('appointment_id', $appointment->id)
            ->where('status', 'waiting')
            ->update(['status' => 'cancelled']);

        $appointment->delete();
        return back()->with('status','Appointment deleted.');
    }

    /** Create appointment for a patient */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'          => 'required|exists:users,id',
            'clinic_id'        => 'required|exists:clinics,id',
            'service_id'       => 'required|exists:services,id',
            'doctor_id'        => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'notes'            => 'nullable|string|max:500',
        ]);

        // Create the appointment
        $appointment = Appointment::create([
            'user_id'          => $data['user_id'],
            'clinic_id'        => $data['clinic_id'],
            'service_id'       => $data['service_id'],
            'doctor_id'        => $data['doctor_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'status'           => 'scheduled',
            'notes'            => $data['notes'] ?? null,
        ]);

        // Automatically add patient to the clinic's queue
        $queueService = app(\App\Services\QueueService::class);
        $queueNumber = $queueService->getNextNumber($data['clinic_id']);

        \App\Models\QueueEntry::create([
            'clinic_id'     => $data['clinic_id'],
            'user_id'       => $data['user_id'],
            'appointment_id'=> $appointment->id,
            'queue_number'  => $queueNumber,
            'status'        => 'waiting',
        ]);

        // Send notification to the patient
        $appointment->user->notify(new AppointmentBooked($appointment));

        return redirect()
            ->route('secretary.appointments.index')
            ->with('status', 'Appointment created successfully for patient.');
    }

    // Disable unused actions
    public function show(Appointment $a) { return redirect()->route('secretary.appointments.index'); }
}
