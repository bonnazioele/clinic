<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Service;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function __construct()
    {
        // require login…
        $this->middleware('auth');
        // …and require admin flag
        $this->middleware(function($request, $next) {
            if (! $request->user()?->is_admin) {
                abort(403, 'Forbidden');
            }
            return $next($request);
        });
    }

    /**
     * Show paginated clinics (with services loaded).
     */
    public function index()
    {
        $clinics = Clinic::with('services')
                         ->latest()
                         ->paginate(10);

        return view('admin.clinics.index', compact('clinics'));
    }

    /**
     * Form to create a new clinic.
     */
    public function create()
    {
        $services = Service::all();
        return view('admin.clinics.create', compact('services'));
    }

    /**
     * Store a new clinic, and attach selected services.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'address'       => 'required|string',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'service_ids'   => 'array',
            'service_ids.*' => 'exists:services,id',
        ]);

        $clinic = Clinic::create([
            'name'      => $data['name'],
            'address'   => $data['address'],
            'latitude'  => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        // sync pivot table
        $clinic->services()->sync($data['service_ids'] ?? []);

        return redirect()
            ->route('admin.clinics.index')
            ->with('status','Clinic added successfully.');
    }

    /**
     * Form to edit an existing clinic.
     */
    public function edit(Clinic $clinic)
    {
        $services = Service::all();
        return view('admin.clinics.edit', compact('clinic','services'));
    }

    /**
     * Update clinic attributes and its services.
     */
    public function update(Request $request, Clinic $clinic)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'address'       => 'required|string',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'service_ids'   => 'array',
            'service_ids.*' => 'exists:services,id',
        ]);

        $clinic->update([
            'name'      => $data['name'],
            'address'   => $data['address'],
            'latitude'  => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        // sync pivot
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
