<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicType;
use Illuminate\Http\Request;

class ClinicTypeController extends Controller
{
    public function index()
    {
        $clinicTypes = ClinicType::latest()->paginate(10);
        return view('admin.clinic-types.index', compact('clinicTypes'));
    }

    public function create()
    {
        return view('admin.clinic-types.create');
    }

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
        return redirect()->route('admin.clinic-types.index')->with('status', 'Clinic type added successfully.');
    }

    public function show(ClinicType $clinicType)
    {
        return view('admin.clinic-types.show', compact('clinicType'));
    }

    public function edit(ClinicType $clinicType)
    {
        return view('admin.clinic-types.edit', compact('clinicType'));
    }

    public function update(Request $request, ClinicType $clinicType)
    {
        $validated = $request->validate([
            'type_name' => 'required|string|max:100|unique:clinic_types,type_name,' . $clinicType->id,
            'description' => 'nullable|string|max:500',
        ]);

        if ($clinicType->type_name === $validated['type_name'] && $clinicType->description === ($validated['description'] ?? null)) {
            return redirect()->route('admin.clinic-types.index')->with('status', 'No changes made.');
        }

        $clinicType->update($validated);
        return redirect()->route('admin.clinic-types.index')->with('status','Clinic type updated successfully.');
    }

    public function destroy(ClinicType $clinicType)
    {
        if ($clinicType->clinics()->count() > 0) {
            return back()->withErrors([
                'error' => 'Cannot delete clinic type that is being used by clinics.'
            ]);
        }

        $clinicType->delete();
        return back()->with('status', 'Clinic type removed successfully.');
    }

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
            'type_name.required' => 'Please enter clinic type name.',
            'type_name.unique' => 'This clinic type already exists. Please choose a different name.',
            'type_name.max' => 'Clinic type name must not exceed 100 characters.',
            'description.max' => 'Description must not exceed 500 characters.',
        ]);
    }
}
