<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AppointmentBooked;

class AppointmentController extends Controller
{
    public function __construct()
    {
        // Ensure only authenticated & verified patients can book
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the patient dashboard of upcoming vs past appointments.
     */
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

    /**
     * Show the booking form.
     */
    public function create()
    {
        $clinics = Clinic::with(['services','doctors.services'])->get();
        return view('appointments.create', compact('clinics'));
    }

    /**
     * Persist a new appointment.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'clinic_id'        => 'required|exists:clinics,id',
            'service_id'       => 'required|exists:services,id',
            'doctor_id'        => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
        ]);

        // Create the appointment
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

        // Automatically add patient to the clinic's queue
        $queueService = app(\App\Services\QueueService::class);
        $queueNumber = $queueService->getNextNumber($data['clinic_id']);

        \App\Models\QueueEntry::create([
            'clinic_id'     => $data['clinic_id'],
            'user_id'       => Auth::id(),
            'appointment_id'=> $appointment->id,
            'queue_number'  => $queueNumber,
            'status'        => 'waiting',
        ]);

        // Send in-app notification to the patient
        Auth::user()->notify(new AppointmentBooked($appointment));

        return redirect()
            ->route('appointments.index')
            ->with('status', 'Appointment booked successfully.');
    }

    /**
     * Cancel (delete) an appointment.
     */
    public function destroy(Appointment $appointment)
    {
        // Only the owner may cancel
        if ($appointment->user_id !== Auth::id()) {
            abort(403, 'Forbidden');
        }

        // If already completed, do not allow cancel
        if ($appointment->status === 'completed') {
            return back()->with('error', 'Completed appointments cannot be cancelled.');
        }

        // Update queue entry (if any) to cancelled
        \App\Models\QueueEntry::where('appointment_id', $appointment->id)
            ->where('status', 'waiting')
            ->update(['status' => 'cancelled']);

        // Mark appointment as cancelled instead of deleting (so secretaries can see it)
        $appointment->update(['status' => 'cancelled']);

        return back()->with('status', 'Appointment cancelled.');
    }
}
