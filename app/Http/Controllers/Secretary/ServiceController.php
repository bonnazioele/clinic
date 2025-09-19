<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Models\Clinic;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\SecretaryMiddleware::class]);
    }

    public function index()
    {
        $clinicIds = Auth::user()->secretaryClinics()->pluck('clinics.id');
        $services = Service::whereHas('clinics', function($q) use ($clinicIds){
            $q->whereIn('clinics.id', $clinicIds);
        })
        ->with(['clinics' => function($q) use ($clinicIds){ $q->whereIn('clinics.id',$clinicIds); }])
        ->orderBy('name')
        ->paginate(20);

        return view('secretary.services.index', compact('services'));
    }

    public function create()
    {
        $clinics = Auth::user()->secretaryClinics()->get();
        return view('secretary.services.create', compact('clinics'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'clinic_ids' => 'required|array|min:1',
            'clinic_ids.*' => 'exists:clinics,id'
        ]);
        $allowedClinicIds = Auth::user()->secretaryClinics()->pluck('clinics.id');
        foreach ($data['clinic_ids'] as $cid) {
            if (! $allowedClinicIds->contains($cid)) {
                return back()->withInput()->withErrors(['clinic_ids' => 'One of the selected clinics is not assigned to you.']);
            }
        }
        $service = Service::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        $service->clinics()->sync($data['clinic_ids']);
        return redirect()->route('secretary.services.index')->with('status','Service created.');
    }

    public function edit(Service $service)
    {
        $clinicIds = Auth::user()->secretaryClinics()->pluck('clinics.id');
        if (! $service->clinics()->whereIn('clinics.id',$clinicIds)->exists()) {
            abort(403,'Service not in your clinics');
        }
        $clinics = Auth::user()->secretaryClinics()->get();
        $service->load('clinics:id,name');
        return view('secretary.services.edit', compact('service','clinics'));
    }

    public function update(Request $request, Service $service)
    {
        $clinicIds = Auth::user()->secretaryClinics()->pluck('clinics.id');
        if (! $service->clinics()->whereIn('clinics.id',$clinicIds)->exists()) {
            abort(403,'Service not in your clinics');
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'clinic_ids' => 'required|array|min:1',
            'clinic_ids.*' => 'exists:clinics,id'
        ]);
        foreach ($data['clinic_ids'] as $cid) {
            if (! $clinicIds->contains($cid)) {
                return back()->withInput()->withErrors(['clinic_ids' => 'One of the selected clinics is not assigned to you.']);
            }
        }
        $service->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        $service->clinics()->sync($data['clinic_ids']);
        return redirect()->route('secretary.services.index')->with('status','Service updated.');
    }
}
