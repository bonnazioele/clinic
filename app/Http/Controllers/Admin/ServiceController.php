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

    public function index(Request $request)
    {
        $query = Service::query()->withCount('clinics');

        // Search by name or description
        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where('name','like',"%{$term}%");
        }

        // Sorting: default newest, az, za
        $sort = $request->input('sort');
        if ($sort === 'az') {
            $query->orderBy('name','asc');
        } elseif ($sort === 'za') {
            $query->orderBy('name','desc');
        } else {
            $query->latest();
        }

        $services = $query->paginate(10)->withQueryString();
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
        ]);

        $service = Service::create([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        return redirect()
            ->route('admin.services.index')
            ->with('status','Service added successfully.');
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
        ]);

        $service->update([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        return redirect()->route('admin.services.index')
            ->with('status','Service updated');
    }

        public function destroy(Service $service)
        {
            if ($service->clinics()->exists()) {
                return redirect()->route('admin.services.index')
                    ->with('error', 'Service cannot be deleted because one or more clinics are using it');
            }

            $service->delete();

            return redirect()->route('admin.services.index')
                ->with('status','Service removed successfully.');
        }
}
