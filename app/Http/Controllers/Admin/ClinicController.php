<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\ClinicType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $clinics = Clinic::with(['services', 'type'])->latest()->paginate(10);
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
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'type_id'        => 'required|exists:clinic_types,id',
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
            'branch_code'    => 'required|string|max:50|unique:clinics,branch_code',
            'contact_number' => 'required|string|max:20',
            'email'          => 'required|email|max:255|unique:clinics,email',
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'service_ids'    => 'required|array',
            'service_ids.*'  => 'exists:services,id',
        ]);

        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('clinic-logos', 'public');
        }

        $clinic = Clinic::create([
            'name'           => $data['name'],
            'address'        => $data['address'],
            'type_id'        => $data['type_id'],
            'gps_latitude'   => $data['latitude'],
            'gps_longitude'  => $data['longitude'],
            'branch_code'    => $data['branch_code'],
            'contact_number' => $data['contact_number'],
            'email'          => $data['email'],
            'logo'           => $logoPath,
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
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'type_id'        => 'required|exists:clinic_types,id',
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
            'branch_code'    => 'required|string|max:50|unique:clinics,branch_code,' . $clinic->id,
            'contact_number' => 'required|string|max:20',
            'email'          => 'required|email|max:255|unique:clinics,email,' . $clinic->id,
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'service_ids'    => 'array',
            'service_ids.*'  => 'exists:services,id',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($clinic->logo && \Storage::disk('public')->exists($clinic->logo)) {
                \Storage::disk('public')->delete($clinic->logo);
            }
            $data['logo'] = $request->file('logo')->store('clinic-logos', 'public');
        }

        $clinic->update([
            'name'           => $data['name'],
            'address'        => $data['address'],
            'type_id'        => $data['type_id'],
            'gps_latitude'   => $data['latitude'],
            'gps_longitude'  => $data['longitude'],
            'branch_code'    => $data['branch_code'],
            'contact_number' => $data['contact_number'],
            'email'          => $data['email'],
            'logo'           => $data['logo'] ?? $clinic->logo,
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
