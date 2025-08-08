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
     * Show available services to add to clinic.
     */
    public function create()
    {
        $clinicId = $this->getCurrentClinicId();
        
        if (!$clinicId) {
            return response()->json(['error' => 'No clinic context available.'], 400);
        }

        // Get all active services not assigned to current clinic
        $assignedServiceIds = Service::whereHas('clinics', function($query) use ($clinicId) {
            $query->where('clinic_id', $clinicId);
        })->pluck('id')->toArray();

        $availableServices = Service::where('is_active', true)
            ->whereNotIn('id', $assignedServiceIds)
            ->orderBy('service_name', 'asc')
            ->get(['id', 'service_name', 'description']);

        return response()->json([
            'services' => $availableServices,
            'assigned_count' => count($assignedServiceIds)
        ]);
    }

    /**
     * Store selected services to clinic.
     */
    public function store(Request $request)
    {
        $clinicId = $this->getCurrentClinicId();
        
        if (!$clinicId) {
            return response()->json(['error' => 'No clinic context available.'], 400);
        }

        $validated = $request->validate([
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'required|integer|exists:services,id',
            'duration_minutes' => 'required|integer|min:5|max:480',
        ]);

        $addedServices = [];
        $errors = [];

        foreach ($validated['service_ids'] as $serviceId) {
            $service = Service::find($serviceId);
            
            if (!$service || !$service->is_active) {
                $errors[] = "Service ID {$serviceId} is not available.";
                continue;
            }

            // Check if already assigned
            if ($service->clinics()->where('clinic_id', $clinicId)->exists()) {
                $errors[] = "Service '{$service->service_name}' is already assigned to this clinic.";
                continue;
            }

            // Attach to current clinic
            $service->clinics()->attach($clinicId, [
                'duration_minutes' => $validated['duration_minutes'],
                'is_active' => true,
            ]);

            $addedServices[] = $service->service_name;
        }

        $response = [];
        
        if (count($addedServices) > 0) {
            $response['success'] = count($addedServices) === 1 
                ? "Service '{$addedServices[0]}' added successfully."
                : count($addedServices) . " services added successfully: " . implode(', ', $addedServices);
        }

        if (count($errors) > 0) {
            $response['errors'] = $errors;
        }

        if (empty($addedServices)) {
            return response()->json(['error' => 'No services were added.'], 400);
        }

        return response()->json($response);
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
