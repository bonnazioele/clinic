<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Http\Traits\ClinicContext;
use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    use ClinicContext;

    public function __construct()
    {
        $this->middleware(['auth', 'can:access-secretary-panel', 'clinic.context']);
    }

    /**
     * Display a listing of clinic services.
     */
    public function index()
    {
        $clinicId = $this->getCurrentClinicId();
        
        if (!$clinicId) {
            return redirect()->route('secretary.dashboard')->with('error', 'No clinic context available.');
        }

        // Get services for the current clinic with proper pivot data, ordered A-Z
        $services = Service::whereHas('clinics', function($query) use ($clinicId) {
            $query->where('clinic_id', $clinicId);
        })->with(['clinics' => function($query) use ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }])->orderBy('service_name', 'asc')->paginate(15);

        return view('secretary.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create()
    {
        $clinicId = $this->getCurrentClinicId();
        
        if (!$clinicId) {
            return redirect()->route('secretary.dashboard')->with('error', 'No clinic context available.');
        }

        return view('secretary.services.create');
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request)
    {
        $clinicId = $this->getCurrentClinicId();
        
        if (!$clinicId) {
            return redirect()->route('secretary.dashboard')->with('error', 'No clinic context available.');
        }

        $validated = $request->validate([
            'service_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'is_active' => 'boolean',
        ]);

        // Create the service
        $service = Service::create([
            'service_name' => $validated['service_name'],
            'description' => $validated['description'],
            'is_active' => true,
        ]);

        // Attach to current clinic with duration and active status
        $service->clinics()->attach($clinicId, [
            'duration_minutes' => $validated['duration_minutes'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('secretary.services.index')
            ->with('success', 'Service created successfully.');
    }

    /**
     * Remove the specified service from the clinic.
     */
    public function destroy(Service $service)
    {
        $clinicId = $this->getCurrentClinicId();
        
        if (!$clinicId) {
            return redirect()->route('secretary.dashboard')->with('error', 'No clinic context available.');
        }

        // Check if service belongs to current clinic
        $clinicService = $service->clinics()->where('clinic_id', $clinicId)->first();
        
        if (!$clinicService) {
            return redirect()->route('secretary.services.index')
                ->with('error', 'Service not found for this clinic.');
        }

        // Check if service has any appointments
        $hasAppointments = $service->appointments()
            ->where('clinic_id', $clinicId)
            ->whereIn('status', ['scheduled'])
            ->exists();

        if ($hasAppointments) {
            return redirect()->route('secretary.services.index')
                ->with('error', 'Cannot remove service with scheduled appointments. Please reschedule appointments first.');
        }

        // Deactivate the service for this clinic instead of deleting
        $service->clinics()->updateExistingPivot($clinicId, [
            'is_active' => false,
        ]);

        return redirect()->route('secretary.services.index')
            ->with('success', 'Service removed from clinic successfully.');
    }
}
