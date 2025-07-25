<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
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
     * Show paginated services.
     */
    public function index()
    {
        $services = Service::latest()->paginate(10);
        return view('admin.services.index', compact('services'));
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
        $data = $request->validate([
            'name'        => 'required|string|unique:services,name',
            'description' => 'nullable|string',
        ]);

        Service::create($data);

        return redirect()
            ->route('admin.services.index')
            ->with('status','Service added successfully.');
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
            'name'        => 'required|string|unique:services,name,'.$service->id,
            'description' => 'nullable|string',
        ]);

        $service->update($data);

        return back()->with('status','Service updated successfully.');
    }

    /**
     * Delete a service.
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('status','Service removed.');
    }
}
