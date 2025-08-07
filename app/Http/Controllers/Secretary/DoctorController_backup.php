<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $user = auth()->user();
        
        // Get clinics where the current user is a secretary
        $secretaryClinics = $user->clinicUserRoles()
            ->whereHas('role', function($q) {
                $q->where('role_name', 'secretary');
            })
            ->where('is_active', true)
            ->pluck('clinic_id');

        // Get doctors assigned to those clinics
        $doctors = User::whereHas('clinicUserRoles', function($query) use ($secretaryClinics) {
            $query->whereHas('role', function($roleQuery) {
                $roleQuery->where('role_name', 'doctor');
            })
            ->whereIn('clinic_id', $secretaryClinics)
            ->where('is_active', true);
        })
        ->with(['clinicUserRoles.clinic', 'clinicUserRoles.role'])
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->paginate(15);

        // Get the clinics for context (optional - for displaying which clinics this secretary manages)
        $clinics = \App\Models\Clinic::whereIn('id', $secretaryClinics)->get();

        return view('secretary.doctors.index', compact('doctors', 'clinics'));
    }

    /** GET /secretary/doctors/create */
    public function create()
{
    $user = auth()->user();
    
    // Get clinics where the current user is a secretary
    $secretaryClinics = $user->clinicUserRoles()
        ->whereHas('role', function($q) {
            $q->where('role_name', 'secretary');
        })
        ->where('is_active', true)
        ->pluck('clinic_id');

    $clinics = \App\Models\Clinic::whereIn('id', $secretaryClinics)->get();
    $services = Service::all();
    
    return view('secretary.doctors.create', compact('clinics','services'));
}


    /** POST /secretary/doctors */
    public function store(Request $req)
{
    $user = auth()->user();
    
    // Get clinics where the current user is a secretary
    $secretaryClinics = $user->clinicUserRoles()
        ->whereHas('role', function($q) {
            $q->where('role_name', 'secretary');
        })
        ->where('is_active', true)
        ->pluck('clinic_id')
        ->toArray();

    $data = $req->validate([
        'first_name'       => 'required|string|max:255',
        'last_name'        => 'required|string|max:255',
        'email'            => 'required|email|unique:users,email',
        'password'         => 'required|string|min:6|confirmed',
        'clinic_ids'       => 'array',
        'clinic_ids.*'     => 'exists:clinics,id',
        'service_ids'      => 'array',
        'service_ids.*'    => 'exists:services,id',
        'phone'            => 'nullable|string|max:50',
        'address'          => 'nullable|string|max:500',
        'age'              => 'nullable|integer|min:18|max:100',
        'birthdate'        => 'nullable|date',
    ]);

    // Ensure secretary can only assign doctors to their own clinics
    if (isset($data['clinic_ids'])) {
        $invalidClinics = array_diff($data['clinic_ids'], $secretaryClinics);
        if (!empty($invalidClinics)) {
            return back()->withErrors(['clinic_ids' => 'You can only assign doctors to clinics where you are a secretary.']);
        }
    }

    $doctor = User::create([
        'first_name' => $data['first_name'],
        'last_name'  => $data['last_name'],
        'email'      => $data['email'],
        'password'   => Hash::make($data['password']),
        'phone'      => $data['phone'] ?? null,
        'address'    => $data['address'] ?? null,
        'age'        => $data['age'] ?? null,
        'birthdate'  => $data['birthdate'] ?? null,
        'is_active'  => true,
    ]);

    // Get the doctor role
    $doctorRole = Role::where('role_name', 'doctor')->first();
    
    if ($doctorRole && isset($data['clinic_ids'])) {
        // Assign doctor role to selected clinics (only secretary's clinics)
        foreach ($data['clinic_ids'] as $clinicId) {
            ClinicUserRole::create([
                'user_id' => $doctor->id,
                'clinic_id' => $clinicId,
                'role_id' => $doctorRole->id,
                'is_active' => true,
                'assigned_by' => auth()->id(),
            ]);
        }
    }

    // sync services if the relationship exists (may need to update this based on your service-doctor relationship)
    if (method_exists($doctor, 'services')) {
        $doctor->services()->sync($data['service_ids'] ?? []);
    }

    return redirect()->route('secretary.doctors.index')
                     ->with('status','Doctor added successfully.');
}

    /** GET /secretary/doctors/{doctor}/edit */
    public function edit(User $doctor)
{
    $user = auth()->user();
    
    // Get clinics where the current user is a secretary
    $secretaryClinics = $user->clinicUserRoles()
        ->whereHas('role', function($q) {
            $q->where('role_name', 'secretary');
        })
        ->where('is_active', true)
        ->pluck('clinic_id');

    // Check if the doctor is assigned to any of the secretary's clinics
    $doctorInSecretaryClinics = $doctor->clinicUserRoles()
        ->whereHas('role', function($q) {
            $q->where('role_name', 'doctor');
        })
        ->whereIn('clinic_id', $secretaryClinics)
        ->where('is_active', true)
        ->exists();

    abort_unless($doctorInSecretaryClinics, 404, 'You can only edit doctors from your clinics.');

    $clinics = \App\Models\Clinic::whereIn('id', $secretaryClinics)->get();
    $services = Service::all();
    
    return view('secretary.doctors.edit', compact('doctor','clinics','services'));
}

    /** PATCH /secretary/doctors/{doctor} */
    public function update(Request $req, User $doctor)
{
    $user = auth()->user();
    
    // Get clinics where the current user is a secretary
    $secretaryClinics = $user->clinicUserRoles()
        ->whereHas('role', function($q) {
            $q->where('role_name', 'secretary');
        })
        ->where('is_active', true)
        ->pluck('clinic_id')
        ->toArray();

    // Check if the doctor is assigned to any of the secretary's clinics
    $doctorInSecretaryClinics = $doctor->clinicUserRoles()
        ->whereHas('role', function($q) {
            $q->where('role_name', 'doctor');
        })
        ->whereIn('clinic_id', $secretaryClinics)
        ->where('is_active', true)
        ->exists();

    abort_unless($doctorInSecretaryClinics, 404, 'You can only edit doctors from your clinics.');

    $data = $req->validate([
        'first_name'       => 'required|string|max:255',
        'last_name'        => 'required|string|max:255',
        'email'            => 'required|email|unique:users,email,'.$doctor->id,
        'password'         => 'nullable|string|min:6|confirmed',
        'clinic_ids'       => 'array',
        'clinic_ids.*'     => 'exists:clinics,id',
        'service_ids'      => 'array',
        'service_ids.*'    => 'exists:services,id',
        'phone'            => 'nullable|string|max:50',
        'address'          => 'nullable|string|max:500',
        'age'              => 'nullable|integer|min:18|max:100',
        'birthdate'        => 'nullable|date',
    ]);

    // Ensure secretary can only assign doctors to their own clinics
    if (isset($data['clinic_ids'])) {
        $invalidClinics = array_diff($data['clinic_ids'], $secretaryClinics);
        if (!empty($invalidClinics)) {
            return back()->withErrors(['clinic_ids' => 'You can only assign doctors to clinics where you are a secretary.']);
        }
    }

    $doctor->update([
        'first_name' => $data['first_name'],
        'last_name'  => $data['last_name'],
        'email'      => $data['email'],
        'phone'      => $data['phone'] ?? null,
        'address'    => $data['address'] ?? null,
        'age'        => $data['age'] ?? null,
        'birthdate'  => $data['birthdate'] ?? null,
        'password'   => $data['password']
                        ? Hash::make($data['password'])
                        : $doctor->password,
    ]);

    // Update clinic assignments for doctor role (only within secretary's clinics)
    $doctorRole = Role::where('role_name', 'doctor')->first();
    if ($doctorRole) {
        // Remove existing doctor role assignments for the secretary's clinics only
        ClinicUserRole::where('user_id', $doctor->id)
                     ->where('role_id', $doctorRole->id)
                     ->whereIn('clinic_id', $secretaryClinics)
                     ->delete();
        
        // Add new clinic assignments (only secretary's clinics)
        if (isset($data['clinic_ids'])) {
            foreach ($data['clinic_ids'] as $clinicId) {
                ClinicUserRole::create([
                    'user_id' => $doctor->id,
                    'clinic_id' => $clinicId,
                    'role_id' => $doctorRole->id,
                    'is_active' => true,
                    'assigned_by' => auth()->id(),
                ]);
            }
        }
    }

    // Update services if the relationship exists
    if (method_exists($doctor, 'services')) {
        $doctor->services()->sync($data['service_ids'] ?? []);
    }

    return back()->with('status','Doctor updated successfully.');
}

    /** DELETE /secretary/doctors/{doctor} */
    public function destroy(User $doctor)
    {
        $user = auth()->user();
        
        // Get clinics where the current user is a secretary
        $secretaryClinics = $user->clinicUserRoles()
            ->whereHas('role', function($q) {
                $q->where('role_name', 'secretary');
            })
            ->where('is_active', true)
            ->pluck('clinic_id');

        // Check if the doctor is assigned to any of the secretary's clinics
        $doctorInSecretaryClinics = $doctor->clinicUserRoles()
            ->whereHas('role', function($q) {
                $q->where('role_name', 'doctor');
            })
            ->whereIn('clinic_id', $secretaryClinics)
            ->where('is_active', true)
            ->exists();

        abort_unless($doctorInSecretaryClinics, 404, 'You can only remove doctors from your clinics.');
        
        // Remove doctor role assignments only from the secretary's clinics
        $doctorRole = Role::where('role_name', 'doctor')->first();
        if ($doctorRole) {
            ClinicUserRole::where('user_id', $doctor->id)
                         ->where('role_id', $doctorRole->id)
                         ->whereIn('clinic_id', $secretaryClinics)
                         ->delete();
        }
        
        // Check if doctor has any remaining clinic assignments
        $remainingAssignments = $doctor->clinicUserRoles()
            ->whereHas('role', function($q) {
                $q->where('role_name', 'doctor');
            })
            ->where('is_active', true)
            ->exists();
        
        // Only delete the user if they have no other doctor assignments
        if (!$remainingAssignments) {
            $doctor->delete();
            $message = 'Doctor removed completely.';
        } else {
            $message = 'Doctor removed from your clinics.';
        }
        
        return back()->with('status', $message);
    }
}
