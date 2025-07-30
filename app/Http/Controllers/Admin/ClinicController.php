<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\ClinicType;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function __construct()
    {
        // Authorization handled by route middleware ('auth', 'can:access-admin-panel')
    }

    /**
     * Display clinics.
     */
    public function index()
    {
        $clinics = Clinic::with('services')->latest()->paginate(10);
        return view('admin.clinics.index', compact('clinics'));
    }

    /**
     * Show the form to create a new clinic.
     */
    public function create()
    {
        // Pass all services and clinic types into the view
        $services = Service::all();
        $clinicTypes = ClinicType::all();
        return view('admin.clinics.create', compact('services', 'clinicTypes'));
    }

    /**
     * Store a new clinic.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'address'       => 'required|string',
            'type_id'       => 'required|exists:clinic_types,id',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'service_ids'   => 'array',
            'service_ids.*' => 'exists:services,id',
        ]);

        $clinic = Clinic::create([
            'name'           => $data['name'],
            'address'        => $data['address'],
            'type_id'        => $data['type_id'],
            'gps_latitude'   => $data['latitude'],
            'gps_longitude'  => $data['longitude'],
        ]);

        // Attach selected services
        $clinic->services()->sync($data['service_ids'] ?? []);

        return redirect()
            ->route('admin.clinics.index')
            ->with('status','Clinic added successfully.');
    }

    /**
     * Show the form to edit an existing clinic.
     */
    public function edit(Clinic $clinic)
    {
        $services = Service::all();
        $clinicTypes = ClinicType::all();
        return view('admin.clinics.edit', compact('clinic','services','clinicTypes'));
    }

    /**
     * Update a clinic.
     */
    public function update(Request $request, Clinic $clinic)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'address'       => 'required|string',
            'type_id'       => 'required|exists:clinic_types,id',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'service_ids'   => 'array',
            'service_ids.*' => 'exists:services,id',
        ]);

        $clinic->update([
            'name'           => $data['name'],
            'address'        => $data['address'],
            'type_id'        => $data['type_id'],
            'gps_latitude'   => $data['latitude'],
            'gps_longitude'  => $data['longitude'],
        ]);

        // Sync services pivot
        $clinic->services()->sync($data['service_ids'] ?? []);

        return back()->with('status','Clinic updated successfully.');
    }

    /**
     * Delete a clinic.
     */
    public function destroy(Clinic $clinic)
    {
        $clinic->delete();
        return back()->with('status','Clinic removed.');
    }

    
}
