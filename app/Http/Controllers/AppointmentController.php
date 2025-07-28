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
        $clinics = Clinic::with('services')->get();
        return view('appointments.create', compact('clinics'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'clinic_id'=>'required|exists:clinics,id',
            'service_id'=>'required|exists:services,id',
            'appointment_date'=>'required|date',
            'appointment_time'=>'required|date_format:H:i',
        ]);

        Appointment::create([
            'user_id'=>Auth::id(),
            ...$data
        ]);

        return redirect()->route('appointments.index')
                         ->with('status','Appointment scheduled');
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

