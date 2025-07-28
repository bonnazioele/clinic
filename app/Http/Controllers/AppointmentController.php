<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        $appts = Auth::user()->appointments()->with('clinic','service')->get();
        return view('appointments.index', compact('appts'));
    }

    public function create()
    {
        $clinics = Clinic::with([
            'services',
            // load each doctor’s services so we can filter in JS
            'doctors.services'
        ])->get();
        $doctors = \App\Models\User::where('is_doctor', true)->get();

        return view('appointments.create', compact('clinics', 'doctors'));
    }

   public function store(Request $req)
{
    $data = $req->validate([
        'clinic_id'        => 'required|exists:clinics,id',
        'service_id'       => 'required|exists:services,id',
        'doctor_id'        => 'required|exists:users,id',
        'appointment_date' => 'required|date|after_or_equal:today',
        'appointment_time' => 'required',
    ]);

    // This will now save doctor_id on the appointment
    Auth::user()->appointments()->create([
        'clinic_id'        => $data['clinic_id'],
        'service_id'       => $data['service_id'],
        'doctor_id'        => $data['doctor_id'],
        'appointment_date' => $data['appointment_date'],
        'appointment_time' => $data['appointment_time'],
        'status'           => 'scheduled',
    ]);

    return redirect()
         ->route('appointments.index')
         ->with('status','Appointment booked.');
}

      public function destroy(Appointment $appointment)
    {
        // Ensure the user owns it
        if ($appointment->user_id !== Auth::id()) {
            abort(403);
        }

        $appointment->delete();

        return back()->with('status','Appointment canceled.');
    }
}

