<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\Appointment;
use App\Notifications\DoctorMarkedAppointmentDone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AppointmentActionController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(EnsureSelectedClinic::class);
        $this->middleware(function($req,$next){
            if(!Auth::user()?->is_doctor){
                abort(403,'Forbidden');
            }
            return $next($req);
        });
    }

    public function markDone(Request $request, Appointment $appointment)
    {
        $activeClinicId = $this->activeClinicId($request);

        if ((int) $appointment->clinic_id !== $activeClinicId) {
            abort(403, 'Appointment does not belong to the active clinic.');
        }

        if($appointment->doctor_id !== Auth::id()){
            abort(403,'Forbidden');
        }

        if($appointment->status !== 'scheduled'){
            return back()->with('status','Appointment not in scheduled state.');
        }
        if($appointment->doctor_completed){
            return back()->with('status','Already marked done.');
        }

        $appointment->doctor_completed = true;
        $appointment->save();

        $clinicSecretaries = $appointment->clinic->secretaries;
        foreach($clinicSecretaries as $sec){
            $sec->notify(new DoctorMarkedAppointmentDone($appointment));
        }

        return back()->with('status','Marked as done and secretaries notified.');
    }
}
