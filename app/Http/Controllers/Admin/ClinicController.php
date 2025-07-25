<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function __construct()
    {
        // require authentication...
        $this->middleware('auth');

        // ...and require is_admin flag
        $this->middleware(function($request, $next) {
            if (! $request->user()?->is_admin) {
                abort(403, 'Forbidden');
            }
            return $next($request);
        });
    }

    /**
     * Display a paginated list of clinics.
     */
    public function index()
    {
        $clinics = Clinic::latest()->paginate(10);
        return view('admin.clinics.index', compact('clinics'));
    }

    /**
     * Show form to create a new clinic.
     */
    public function create()
    {
        return view('admin.clinics.create');
    }

    /**
     * Persist a new clinic.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'address'   => 'required|string',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        Clinic::create($data);

        return redirect()
            ->route('admin.clinics.index')
            ->with('status', 'Clinic added successfully.');
    }

    /**
     * Show form to edit an existing clinic.
     */
    public function edit(Clinic $clinic)
    {
        return view('admin.clinics.edit', compact('clinic'));
    }

    /**
     * Persist updates to a clinic.
     */
    public function update(Request $request, Clinic $clinic)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'address'   => 'required|string',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $clinic->update($data);

        return back()
            ->with('status', 'Clinic updated successfully.');
    }

    /**
     * Delete a clinic.
     */
    public function destroy(Clinic $clinic)
    {
        $clinic->delete();

        return back()
            ->with('status', 'Clinic removed.');
    }
}
