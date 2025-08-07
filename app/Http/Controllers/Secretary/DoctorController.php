<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\Role;
use App\Models\ClinicUserRole;

class DoctorController extends Controller
{
    // Note: Authorization is handled by RouteServiceProvider using 'can:access-secretary-panel' gate

    /** GET /secretary/doctors */
    public function index()
    {
        // Get current clinic context from session
        $clinicId = Session::get('current_clinic_id');
        
        // Get doctors assigned to current clinic only
        $doctors = User::whereHas('clinicUserRoles', function($query) use ($clinicId) {
            $query->whereHas('role', function($roleQuery) {
                $roleQuery->where('role_name', 'doctor');
            })
            ->where('clinic_id', $clinicId)
            ->where('is_active', true);
        })
        ->with(['clinicUserRoles.clinic', 'clinicUserRoles.role'])
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->paginate(15);

        // Get current clinic for context
        $clinic = Clinic::findOrFail($clinicId);

        return view('secretary.doctors.index', compact('doctors', 'clinic'));
    }

    /** GET /secretary/doctors/create */
    public function create()
    {
        // Get current clinic context
        $clinicId = Session::get('current_clinic_id');
        
        if (!$clinicId) {
            return redirect()->route('dashboard')->with('error', 'No clinic context found. Please contact administrator.');
        }

        // Ensure user can manage doctors for this clinic
        if (!Gate::allows('manage-clinic-doctors', $clinicId)) {
            abort(403, 'Unauthorized to manage doctors for this clinic.');
        }

        // Get services available at current clinic only
        $clinic = Clinic::with('services')->findOrFail($clinicId);
        $services = $clinic->services;

        return view('secretary.doctors.create', compact('services', 'clinic'));
    }

    /** POST /secretary/doctors */
    public function store(Request $request)
    {
        // Get current clinic context
        $clinicId = Session::get('current_clinic_id');
        
        if (!$clinicId) {
            return redirect()->route('dashboard')->with('error', 'No clinic context found. Please contact administrator.');
        }

        // Ensure user can manage doctors for this clinic
        if (!Gate::allows('manage-clinic-doctors', $clinicId)) {
            abort(403, 'Unauthorized to manage doctors for this clinic.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:18|max:100',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'services' => 'array',
            'services.*' => 'exists:services,id',
        ]);

        // Create the user
        $doctor = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'age' => $validated['age'] ?? null,
            'birthdate' => $validated['birthdate'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => true,
            'is_system_admin' => false,
        ]);

        // Assign doctor role to current clinic only
        $doctorRole = Role::where('role_name', 'doctor')->first();
        
        ClinicUserRole::create([
            'user_id' => $doctor->id,
            'clinic_id' => $clinicId, // Enforce current clinic
            'role_id' => $doctorRole->id,
            'is_active' => true,
            'assigned_by' => auth()->id(),
        ]);

        // Assign services to doctor for current clinic only
        if (!empty($validated['services'])) {
            // Verify all services belong to current clinic
            $clinic = Clinic::findOrFail($clinicId);
            $validServices = $clinic->services()->whereIn('id', $validated['services'])->pluck('id');
            
            foreach ($validServices as $serviceId) {
                DB::table('clinic_doctor_services')->insert([
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $clinicId, // Enforce current clinic
                    'service_id' => $serviceId,
                    'duration' => 30, // Default duration
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('secretary.doctors.index')
            ->with('success', 'Doctor created successfully and assigned to your clinic.');
    }

    /** GET /secretary/doctors/{doctor}/edit */
    public function edit(User $doctor)
    {
        // Get current clinic context
        $clinicId = Session::get('current_clinic_id');
        
        if (!$clinicId) {
            return redirect()->route('dashboard')->with('error', 'No clinic context found. Please contact administrator.');
        }

        // Ensure user can manage doctors for this clinic
        if (!Gate::allows('manage-clinic-doctors', $clinicId)) {
            abort(403, 'Unauthorized to manage doctors for this clinic.');
        }

        // Ensure doctor belongs to current clinic
        $doctorBelongsToClinic = $doctor->clinicUserRoles()
            ->where('clinic_id', $clinicId)
            ->whereHas('role', function($q) {
                $q->where('role_name', 'doctor');
            })
            ->where('is_active', true)
            ->exists();

        if (!$doctorBelongsToClinic) {
            abort(403, 'You can only edit doctors assigned to your clinic.');
        }

        // Get services available at current clinic
        $clinic = Clinic::with('services')->findOrFail($clinicId);
        $services = $clinic->services;

        // Get doctor's current services for this clinic
        $doctorServices = $doctor->servicesForClinic($clinicId)->pluck('id')->toArray();

        return view('secretary.doctors.edit', compact('doctor', 'services', 'clinic', 'doctorServices'));
    }

    /** PUT /secretary/doctors/{doctor} */
    public function update(Request $request, User $doctor)
    {
        // Get current clinic context
        $clinicId = Session::get('current_clinic_id');
        
        if (!$clinicId) {
            return redirect()->route('dashboard')->with('error', 'No clinic context found. Please contact administrator.');
        }

        // Ensure user can manage doctors for this clinic
        if (!Gate::allows('manage-clinic-doctors', $clinicId)) {
            abort(403, 'Unauthorized to manage doctors for this clinic.');
        }

        // Ensure doctor belongs to current clinic
        $doctorBelongsToClinic = $doctor->clinicUserRoles()
            ->where('clinic_id', $clinicId)
            ->whereHas('role', function($q) {
                $q->where('role_name', 'doctor');
            })
            ->where('is_active', true)
            ->exists();

        if (!$doctorBelongsToClinic) {
            abort(403, 'You can only edit doctors assigned to your clinic.');
        }

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $doctor->id,
            'phone' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:18|max:100',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'services' => 'array',
            'services.*' => 'exists:services,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        // Update doctor details
        $updateData = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'age' => $validated['age'] ?? null,
            'birthdate' => $validated['birthdate'] ?? null,
            'address' => $validated['address'] ?? null,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $doctor->update($updateData);

        // Update services for current clinic only
        if (isset($validated['services'])) {
            // Remove existing services for this clinic
            DB::table('clinic_doctor_services')
                ->where('doctor_id', $doctor->id)
                ->where('clinic_id', $clinicId)
                ->delete();

            // Verify all services belong to current clinic
            $clinic = Clinic::findOrFail($clinicId);
            $validServices = $clinic->services()->whereIn('id', $validated['services'])->pluck('id');

            // Add new services for current clinic
            foreach ($validServices as $serviceId) {
                DB::table('clinic_doctor_services')->insert([
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $clinicId, // Enforce current clinic
                    'service_id' => $serviceId,
                    'duration' => 30, // Default duration
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('secretary.doctors.index')
            ->with('success', 'Doctor updated successfully.');
    }

    /** DELETE /secretary/doctors/{doctor} */
    public function destroy(User $doctor)
    {
        // Get current clinic context
        $clinicId = Session::get('current_clinic_id');
        
        if (!$clinicId) {
            return redirect()->route('dashboard')->with('error', 'No clinic context found. Please contact administrator.');
        }

        // Ensure user can manage doctors for this clinic
        if (!Gate::allows('manage-clinic-doctors', $clinicId)) {
            abort(403, 'Unauthorized to manage doctors for this clinic.');
        }

        // Ensure doctor belongs to current clinic
        $doctorBelongsToClinic = $doctor->clinicUserRoles()
            ->where('clinic_id', $clinicId)
            ->whereHas('role', function($q) {
                $q->where('role_name', 'doctor');
            })
            ->where('is_active', true)
            ->exists();

        if (!$doctorBelongsToClinic) {
            abort(403, 'You can only remove doctors assigned to your clinic.');
        }

        // Check if doctor has appointments in this clinic
        $hasAppointments = $doctor->appointments()
            ->where('clinic_id', $clinicId)
            ->whereIn('status', ['scheduled'])
            ->exists();

        if ($hasAppointments) {
            return redirect()->route('secretary.doctors.index')
                ->with('error', 'Cannot remove doctor with scheduled appointments. Please reschedule or cancel appointments first.');
        }

        // Deactivate doctor role for current clinic only (soft delete)
        $doctor->clinicUserRoles()
            ->where('clinic_id', $clinicId)
            ->whereHas('role', function($q) {
                $q->where('role_name', 'doctor');
            })
            ->update(['is_active' => false]);

        // Deactivate doctor services for current clinic
        DB::table('clinic_doctor_services')
            ->where('doctor_id', $doctor->id)
            ->where('clinic_id', $clinicId)
            ->update(['is_active' => false]);

        return redirect()->route('secretary.doctors.index')
            ->with('success', 'Doctor removed from your clinic successfully.');
    }
}
