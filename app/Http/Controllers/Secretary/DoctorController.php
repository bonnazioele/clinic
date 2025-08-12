<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\Role;
use App\Models\ClinicUserRole;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    // Note: Authorization is handled by RouteServiceProvider using 'can:access-secretary-panel' gate

    /** GET /secretary/doctors */
    public function index()
    {
        // Get current clinic context from session
        $clinicId = Session::get('current_clinic_id');
        
        // Get users who have doctor profiles and are associated with current clinic
        $doctors = User::whereHas('doctor', function($query) use ($clinicId) {
            $query->whereHas('clinics', function($clinicQuery) use ($clinicId) {
                $clinicQuery->where('clinic_doctor.clinic_id', $clinicId)
                           ->where('clinic_doctor.is_active', true);
            });
        })
        ->with(['doctor.clinics', 'doctor.schedules', 'doctor.services'])
        ->where('is_active', true)
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->paginate(15);

        // Get current clinic for context
        $clinic = Clinic::findOrFail($clinicId);

        // Get available services for the clinic (for filter dropdown)
        $availableServices = Service::whereHas('clinics', function($query) use ($clinicId) {
            $query->where('clinic_id', $clinicId);
        })->orderBy('service_name')->get();

        return view('secretary.doctors.index', compact('doctors', 'clinic', 'availableServices'));
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

        // Comprehensive validation
        $validated = $request->validate([
            // Personal Information
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'phone' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:18|max:100',
            'birthdate' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            
            // Account Credentials
            'password' => 'required|string|min:8|confirmed',
            
            // Professional Information
            'years_of_experience' => 'nullable|integer|min:0|max:50',
            'biography' => 'nullable|string|max:1000',
            
            // Services Assignment
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
            
            // Schedule Information
            'schedules' => 'nullable|array',
            'schedules.*.day_of_week' => ['required_with:schedules', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'schedules.*.start_time' => 'required_with:schedules|date_format:H:i',
            'schedules.*.end_time' => 'required_with:schedules|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.max_patients' => 'nullable|integer|min:1|max:50',
        ]);

        // Additional custom validation for schedules
        if (!empty($validated['schedules'])) {
            $daySchedules = [];
            foreach ($validated['schedules'] as $index => $schedule) {
                $day = $schedule['day_of_week'];
                
                // Check for duplicate days
                if (in_array($day, $daySchedules)) {
                    return back()->withErrors(['schedules' => "Duplicate schedule for {$day}. Each day can only have one schedule."])
                               ->withInput();
                }
                $daySchedules[] = $day;
                
                // Validate time logic
                if ($schedule['start_time'] >= $schedule['end_time']) {
                    return back()->withErrors(['schedules' => "End time must be after start time for {$day}."])
                               ->withInput();
                }
            }
        }

        DB::beginTransaction();

        try {
            // 1. Create the user
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'age' => $validated['age'] ?? null,
                'birthdate' => $validated['birthdate'] ?? null,
                'address' => $validated['address'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'is_active' => true,
                'is_system_admin' => false,
            ]);

            // 2. Create doctor profile
            $doctorProfile = Doctor::create([
                'user_id' => $user->id,
                'years_of_experience' => $validated['years_of_experience'] ?? 0,
                'biography' => $validated['biography'] ?? null,
            ]);

            //3. Assign doctor to current clinic through clinic_doctor pivot table
            $doctorProfile->clinics()->attach($clinicId, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Assign services to doctor for current clinic
            if (!empty($validated['services'])) {
                $clinic = Clinic::findOrFail($clinicId);
                $validServices = $clinic->services()->whereIn('services.id', $validated['services'])->pluck('services.id');
                
                foreach ($validServices as $serviceId) {
                    DB::table('clinic_doctor_services')->insert([
                        'doctor_id' => $doctorProfile->id,
                        'clinic_id' => $clinicId,
                        'service_id' => $serviceId,
                        'duration' => 30, // Default 30 minutes
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 5. Create doctor schedules
            if (!empty($validated['schedules'])) {
                foreach ($validated['schedules'] as $scheduleData) {
                    DoctorSchedule::create([
                        'doctor_id' => $doctorProfile->id,
                        'clinic_id' => $clinicId,
                        'day_of_week' => $scheduleData['day_of_week'],
                        'start_time' => $scheduleData['start_time'],
                        'end_time' => $scheduleData['end_time'],
                        'max_patients' => $scheduleData['max_patients'] ?? 10,
                        'is_active' => true,
                    ]);
                }
            }

            DB::commit();

            $successMessage = 'Doctor created successfully and assigned to your clinic.';
            if (!empty($validated['services'])) {
                $successMessage .= ' Services have been assigned.';
            }
            if (!empty($validated['schedules'])) {
                $successMessage .= ' Schedule has been configured.';
            }

            return redirect()->route('secretary.doctors.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors(['error' => 'Failed to create doctor: ' . $e->getMessage()])
                         ->withInput();
        }
    }

    /** GET /secretary/doctors/{doctor} */
    public function show(User $doctor)
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

        // Load doctor with all necessary relationships in one query to avoid N+1
        $doctor->load([
            'doctor.schedules' => function($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId)
                      ->where('is_active', true)
                      ->orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
            },
            'doctor.appointments' => function($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId)
                      ->whereDate('appointment_date', today())
                      ->whereIn('status', ['scheduled', 'confirmed', 'in_progress']);
            }
        ]);

        // Ensure doctor belongs to current clinic using doctor profile relationship
        $doctorBelongsToClinic = $doctor->doctor && 
            $doctor->doctor->isAssignedToClinic($clinicId);

        if (!$doctorBelongsToClinic) {
            abort(403, 'You can only view doctors assigned to your clinic.');
        }

        // Get data using loaded relationships and existing methods
        $doctorProfile = $doctor->doctor;
        $clinic = Clinic::findOrFail($clinicId);
        $doctorServices = $doctor->servicesForClinic($clinicId)->get();
        $doctorSchedules = $doctorProfile ? $doctorProfile->schedules : collect();
        $todayAppointments = $doctorProfile ? $doctorProfile->appointments->count() : 0;

        return view('secretary.doctors.show', compact(
            'doctor', 
            'doctorProfile', 
            'clinic', 
            'doctorServices', 
            'doctorSchedules', 
            'todayAppointments'
        ));
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

        // Ensure doctor belongs to current clinic using doctor profile relationship
        $doctorBelongsToClinic = $doctor->doctor && 
            $doctor->doctor->isAssignedToClinic($clinicId);

        if (!$doctorBelongsToClinic) {
            abort(403, 'You can only edit doctors assigned to your clinic.');
        }

        // Load doctor with schedule relationships to avoid additional queries
        $doctor->load([
            'doctor.schedules' => function($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId)
                      ->where('is_active', true)
                      ->orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
            }
        ]);

        // Get services available at current clinic
        $clinic = Clinic::with('services')->findOrFail($clinicId);
        $services = $clinic->services;

        // Get doctor's current services for this clinic using existing method
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
        $doctorBelongsToClinic = $doctor->doctor && 
            $doctor->doctor->isAssignedToClinic($clinicId);

        if (!$doctorBelongsToClinic) {
            abort(403, 'You can only edit doctors assigned to your clinic.');
        }

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $doctor->id,
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'phone' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:18|max:100',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'years_of_experience' => 'nullable|integer|min:0|max:50',
            'biography' => 'nullable|string|max:1000',
            'services' => 'array',
            'services.*' => 'exists:services,id',
            
            // Schedule validation rules
            'schedules' => 'nullable|array',
            'schedules.*.day_of_week' => ['required_with:schedules', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'schedules.*.start_time' => 'required_with:schedules|date_format:H:i',
            'schedules.*.end_time' => 'required_with:schedules|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.max_patients' => 'nullable|integer|min:1|max:50',
            'schedules.*.id' => 'nullable|exists:doctors_schedules,id',
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
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'age' => $validated['age'] ?? null,
            'birthdate' => $validated['birthdate'] ?? null,
            'address' => $validated['address'] ?? null,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $doctor->update($updateData);

        // Update doctor professional information
        if ($doctor->doctor) {
            $doctor->doctor->update([
                'years_of_experience' => $validated['years_of_experience'] ?? null,
                'biography' => $validated['biography'] ?? null,
            ]);
        }

        // Update services for current clinic only
        if (isset($validated['services'])) {
            // Ensure doctor has a profile before updating services
            if (!$doctor->doctor) {
                return redirect()->back()->withErrors(['error' => 'Doctor profile not found. Cannot update services.']);
            }

            // Remove existing services for this clinic
            DB::table('clinic_doctor_services')
                ->where('doctor_id', $doctor->doctor->id)  // Use doctor profile ID, not user ID
                ->where('clinic_id', $clinicId)
                ->delete();

            // Get clinic with services in one query to verify all services belong to current clinic
            $clinic = Clinic::with('services')->findOrFail($clinicId);
            $validServices = $clinic->services()->whereIn('services.id', $validated['services'])->pluck('services.id');

            // Add new services for current clinic
            foreach ($validServices as $serviceId) {
                DB::table('clinic_doctor_services')->insert([
                    'doctor_id' => $doctor->doctor->id,  // Use doctor profile ID, not user ID
                    'clinic_id' => $clinicId, // Enforce current clinic
                    'service_id' => $serviceId,
                    'duration' => 30, // Default duration
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Update schedules for current clinic only
        if (isset($validated['schedules']) && $doctor->doctor) {
            // Remove existing schedules for this clinic
            $doctor->doctor->schedules()
                ->where('clinic_id', $clinicId)
                ->delete();

            // Add/update schedules
            foreach ($validated['schedules'] as $scheduleData) {
                if (!empty($scheduleData['day_of_week']) && !empty($scheduleData['start_time']) && !empty($scheduleData['end_time'])) {
                    $doctor->doctor->schedules()->create([
                        'clinic_id' => $clinicId,
                        'day_of_week' => $scheduleData['day_of_week'],
                        'start_time' => $scheduleData['start_time'],
                        'end_time' => $scheduleData['end_time'],
                        'max_patients' => $scheduleData['max_patients'] ?? 10,
                        'is_active' => true,
                    ]);
                }
            }
        }

        return redirect()->route('secretary.doctors.show', $doctor)
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
        $doctorBelongsToClinic = $doctor->doctor && 
            $doctor->doctor->isAssignedToClinic($clinicId);

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

        // Deactivate doctor-clinic relationship (soft delete)
        if ($doctor->doctor) {
            $doctor->doctor->clinics()
                ->where('clinic_id', $clinicId)
                ->updateExistingPivot($clinicId, ['is_active' => false]);
        }

        // Deactivate doctor services for current clinic
        if ($doctor->doctor) {
            DB::table('clinic_doctor_services')
                ->where('doctor_id', $doctor->doctor->id)  // Use doctor profile ID, not user ID
                ->where('clinic_id', $clinicId)
                ->update(['is_active' => false]);
        }

        return redirect()->route('secretary.doctors.index')
            ->with('success', 'Doctor removed from your clinic successfully.');
    }
}
