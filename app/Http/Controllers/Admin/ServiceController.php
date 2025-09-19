<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function($request, $next) {
            if (! $request->user()?->is_admin) {
                abort(403, 'Forbidden');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $services = Service::latest()->paginate(10);
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|unique:services,name',
            'description' => 'nullable|string',
            'clinic_ids'  => 'required|array|min:1',
            'clinic_ids.*' => 'exists:clinics,id',
        ]);

        $service = Service::create([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        $service->clinics()->attach($data['clinic_ids']);

        return redirect()
            ->route('admin.services.index')
            ->with('status','Service added successfully and linked to selected clinics.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name'        => 'required|string|unique:services,name,'.$service->id,
            'description' => 'nullable|string',
            'clinic_ids'  => 'required|array|min:1',
            'clinic_ids.*' => 'exists:clinics,id',
        ]);

        $service->update([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        $service->clinics()->sync($data['clinic_ids']);

        return back()->with('status','Service updated successfully and clinic associations updated.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('status','Service removed.');
    }
}
