<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Service;
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
        $clinics = Clinic::with(['services'])->latest()->paginate(10);
        // Separate collection for map markers (independent of pagination)
        $clinicsWithCoords = Clinic::query()
            ->whereNotNull('gps_latitude')
            ->whereNotNull('gps_longitude')
            ->get(['id','name','address','gps_latitude','gps_longitude']);

        return view('admin.clinics.index', [
            'clinics' => $clinics,
            'clinicsWithCoords' => $clinicsWithCoords,
        ]);
    }

    /**
     * Show the form to create a new clinic.
     */
    public function create()
    {
        // Pass all services into the view
        $services = Service::all();
        return view('admin.clinics.create', compact('services'));
    }

    /**
     * Store a new clinic.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
            'branch_code'    => 'required|string|max:255|unique:clinics,branch_code',
            'contact_number' => 'required|string|max:50',
            'email'          => 'required|email|max:255|unique:clinics,email',
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'service_ids'    => 'nullable|array',
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
            // Map UI lat/lng into model fields
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
     * Display a specific clinic.
     */
    public function show(Clinic $clinic)
    {
        return view('admin.clinics.show', compact('clinic'));
    }

    /**
     * Show the form to edit an existing clinic.
     */
    public function edit(Clinic $clinic)
    {
        $services = Service::all();
        return view('admin.clinics.edit', compact('clinic','services'));
    }

    /**
     * Update a clinic.
     */
    public function update(Request $request, Clinic $clinic)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
            'branch_code'    => 'required|string|max:255|unique:clinics,branch_code,' . $clinic->id,
            'contact_number' => 'required|string|max:50',
            'email'          => 'required|email|max:255|unique:clinics,email,' . $clinic->id,
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'service_ids'    => 'nullable|array',
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
            'gps_latitude'   => $data['latitude'],
            'gps_longitude'  => $data['longitude'],
            'branch_code'    => $data['branch_code'],
            'contact_number' => $data['contact_number'],
            'email'          => $data['email'],
            'logo'           => $data['logo'] ?? $clinic->logo,
        ]);

        // Sync services pivot
        $clinic->services()->sync($data['service_ids'] ?? []);

        return redirect()->route('admin.clinics.index')->with('status', 'Clinic updated successfully.');
    }

    /**
     * Delete a clinic.
     */
    public function destroy(Clinic $clinic)
    {
        $clinic->delete();
        return back()->with('status','Clinic removed.');
    }

    // Admin queue-related features removed per request

}
