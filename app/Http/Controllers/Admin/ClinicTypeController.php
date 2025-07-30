<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicType;
use Illuminate\Http\Request;

class ClinicTypeController extends Controller
{
    public function __construct()
    {
        // Authorization handled by route middleware ('auth', 'can:access-admin-panel')
    }

    /**
     * Display a listing of clinic types.
     */
    public function index()
    {
        $clinicTypes = ClinicType::latest()->paginate(10);
        return view('admin.clinic-types.index', compact('clinicTypes'));
    }

    /**
     * Show the form for creating a new clinic type.
     */
    public function create()
    {
        return view('admin.clinic-types.create');
    }

    /**
     * Store a newly created clinic type in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validator($request->all())->validate();

        try {
            $clinicType = ClinicType::create([
                'type_name' => $validated['type_name'],
                'description' => $validated['description'] ?? null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Clinic type creation failed: ' . $e->getMessage());
            return back()->withErrors([
                'type_name' => 'Could not add clinic type. Please try again.'
            ])->withInput();
        }

        return redirect()
            ->route('admin.clinic-types.index')
            ->with('status', 'Clinic type added successfully.');
    }

    /**
     * Display the specified clinic type.
     */
    public function show(ClinicType $clinicType)
    {
        return view('admin.clinic-types.show', compact('clinicType'));
    }

    /**
     * Show the form for editing the specified clinic type.
     */
    public function edit(ClinicType $clinicType)
    {
        return view('admin.clinic-types.edit', compact('clinicType'));
    }

    /**
     * Update the specified clinic type in storage.
     */
    public function update(Request $request, ClinicType $clinicType)
    {
        $validated = $request->validate([
            'type_name' => 'required|string|max:100|unique:clinic_types,type_name,' . $clinicType->id,
            'description' => 'nullable|string|max:500',
        ]);

        $clinicType->update($validated);

        return back()->with('status', 'Clinic type updated successfully.');
    }

    /**
     * Remove the specified clinic type from storage.
     */
    public function destroy(ClinicType $clinicType)
    {
        // Check if any clinics are using this type
        if ($clinicType->clinics()->count() > 0) {
            return back()->withErrors([
                'error' => 'Cannot delete clinic type that is being used by clinics.'
            ]);
        }

        $clinicType->delete();
        return back()->with('status', 'Clinic type removed successfully.');
    }

    /**
     * Get a validator for an incoming clinic type creation request.
     */
    protected function validator(array $data)
    {
        return \Validator::make($data, [
            'type_name' => [
                'required',
                'string',
                'max:100',
                'unique:clinic_types,type_name',
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'type_name.required' => 'Clinic type name is required.',
            'type_name.unique' => 'This clinic type already exists. Please choose a different name.',
            'type_name.max' => 'Clinic type name must not exceed 100 characters.',
            'description.max' => 'Description must not exceed 500 characters.',
        ]);
    }
}
