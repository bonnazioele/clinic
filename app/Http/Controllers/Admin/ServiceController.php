<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct()
    {
        // Authorization handled by route middleware ('auth', 'can:access-admin-panel')
    }

    /**
     * Form to create a new service.
     */
    public function create()
    {
        return view('admin.services.create');
    }

    /**
     * Store a new service.
     */
    public function store(Request $request)
    {
        $validated = $this->validator($request->all())->validate();
        $validated['is_active'] = $request->boolean('is_active', true);

        try {
            $service = Service::create([
                'service_name' => $validated['service_name'],
                'description'  => $validated['description'] ?? null,
                'is_active'    => $validated['is_active'],
            ]);
        } catch (\Exception $e) {
            \Log::error('Service creation failed: ' . $e->getMessage());
            return back()->withErrors([
                'service_name' => 'Could not add service. Please try again.'
            ])->withInput();
        }

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Service added successfully.');
    }

    /**
     * Get a validator for an incoming service creation request.
     */
    protected function validator(array $data)
    {
        return \Validator::make($data, [
            'service_name' => [
                'required',
                'string',
                'max:100',
                'unique:services,service_name',
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'service_name.required' => 'Service name is required.',
            'service_name.unique' => 'This service already exists. Please modify the name.',
            'service_name.max' => 'Service name must not exceed 100 characters.',
        ]);
    }

    /**
     * Form to edit an existing service.
     */
    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    /**
     * Update a service.
     */
    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'service_name' => 'required|string|unique:services,service_name,'.$service->id,
            'description'  => 'nullable|string',
        ]);

        $service->update($data);

        return back()->with('status','Service updated successfully.');
    }

    /**
     * Delete a service.
     */
    public function destroy(Service $service)
    {
        if ($service->is_active) {
            return back()->withErrors(['error' => 'You must deactivate this service before deleting it.']);
        }
        if ($service->clinics()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete a service that is currently in use. Please remove it from all clinics first.']);
        }
        $service->delete();
        return redirect()->route('admin.services.index')->with('status','Service removed.');
    }
}
