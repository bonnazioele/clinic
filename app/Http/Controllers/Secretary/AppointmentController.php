<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Gate;
use App\Notifications\AppointmentStatusChanged;

class AppointmentController extends Controller
{
    // Note: Authorization is handled by RouteServiceProvider using 'can:access-secretary-panel' gate

    public function create()
    {
        // Get current clinic context from session
        $clinicId = Session::get('current_clinic_id');
        
        // Load only the current clinic's services and doctors
        $clinic = Clinic::with(['services'])->findOrFail($clinicId);
        
        // Get doctors assigned to this clinic only
        $doctors = User::whereHas('clinicUserRoles', function($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId)
              ->where('is_active', true)
              ->whereHas('role', function($roleQ) {
                  $roleQ->where('role_name', 'doctor');
              });
        })->get();

        return view('appointments.create', compact('clinic', 'doctors'));
    }

    /** List & manage all appointments */
    public function index()
    {
        // Get current clinic context from session
        $clinicId = Session::get('current_clinic_id');
        
        // Get appointments only for the current clinic
        $appointments = Appointment::with('user','clinic','service','doctor')
            ->where('clinic_id', $clinicId)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(15);

        // Get doctors assigned to this clinic only
        $doctors = User::whereHas('clinicUserRoles', function($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId)
              ->where('is_active', true)
              ->whereHas('role', function($roleQ) {
                  $roleQ->where('role_name', 'doctor');
              });
        })->get();

        return view('secretary.appointments.index', compact('appointments','doctors'));
    }

    /** Show edit form */
    public function edit(Appointment $appointment)
    {
        // Get current clinic context from session
        $clinicId = Session::get('current_clinic_id');
        
        // Ensure appointment belongs to current clinic
        if ($appointment->clinic_id !== $clinicId) {
            abort(403, 'You can only edit appointments for your assigned clinic.');
        }

        $clinic = Clinic::findOrFail($clinicId);
        
        // Get doctors assigned to this clinic only
        $doctors = User::whereHas('clinicUserRoles', function($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId)
              ->where('is_active', true)
              ->whereHas('role', function($roleQ) {
                  $roleQ->where('role_name', 'doctor');
              });
        })->get();

        return view('secretary.appointments.edit', compact('appointment','clinic','doctors'));
    }

    /** Persist changes */
    public function update(Request $req, Appointment $appointment)
    {
        // Get current clinic context from session
        $clinicId = Session::get('current_clinic_id');
        
        // Ensure appointment belongs to current clinic
        if ($appointment->clinic_id !== $clinicId) {
            abort(403, 'You can only edit appointments for your assigned clinic.');
        }

        $data = $req->validate([
            'service_id'       => 'required|exists:services,id',
            'doctor_id'        => 'nullable|exists:users,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'status'           => 'required|in:scheduled,completed,cancelled',
        ]);

        // Force clinic_id to current clinic (security measure)
        $data['clinic_id'] = $clinicId;

        // Verify doctor belongs to current clinic if specified
        if (!empty($data['doctor_id'])) {
            $doctorBelongsToClinic = User::where('id', $data['doctor_id'])
                ->whereHas('clinicUserRoles', function($q) use ($clinicId) {
                    $q->where('clinic_id', $clinicId)
                      ->where('is_active', true)
                      ->whereHas('role', function($roleQ) {
                          $roleQ->where('role_name', 'doctor');
                      });
                })->exists();

            if (!$doctorBelongsToClinic) {
                return back()->withErrors(['doctor_id' => 'Selected doctor is not available at your clinic.']);
            }
        }

        $appointment->update($data);

        $appointment->user->notify(new AppointmentStatusChanged($appointment));

        return redirect()
            ->route('secretary.appointments.index')
            ->with('status','Appointment updated.');
    }

    /** Delete if needed */
    public function destroy(Appointment $appointment)
    {
        // Get current clinic context from session
        $clinicId = Session::get('current_clinic_id');
        
        // Ensure appointment belongs to current clinic
        if ($appointment->clinic_id !== $clinicId) {
            abort(403, 'You can only delete appointments for your assigned clinic.');
        }

        $appointment->delete();
        return back()->with('status','Appointment deleted.');
    }

    // Disable unused actions
    public function store(Request $r) { abort(404); }
    public function show(Appointment $a) { return redirect()->route('secretary.appointments.index'); }
}
