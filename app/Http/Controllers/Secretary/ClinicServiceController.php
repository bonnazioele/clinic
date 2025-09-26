<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\Appointment;

class ClinicServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\SecretaryMiddleware::class]);
    }

    public function create(Request $request)
    {
        $assignedClinics = Auth::user()->secretaryClinics()->orderBy('name')->get();
        if ($assignedClinics->isEmpty()) {
            abort(403,'No assigned clinics');
        }
        $defaultClinicId = $request->input('clinic_id') ?: $assignedClinics->first()->id;
        return view('secretary.services.create_master', [
            'assignedClinics' => $assignedClinics,
            'defaultClinicId' => (int)$defaultClinicId,
        ]);
    }

    public function store(Request $request)
    {
        $assignedClinicIds = Auth::user()->secretaryClinics()->pluck('clinics.id');
        if ($assignedClinicIds->isEmpty()) { abort(403); }
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:1000',
            'clinic_ids' => 'required|array|min:1',
            'clinic_ids.*' => 'integer|exists:clinics,id',
            'duration_minutes' => 'nullable|integer|min:5|max:480'
        ]);
        foreach ($data['clinic_ids'] as $cid) {
            if (! $assignedClinicIds->contains($cid)) {
                return back()->withInput()->withErrors(['clinic_ids' => 'Clinic not assigned to you.']);
            }
        }
        $service = Service::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        $duration = $data['duration_minutes'] ?? 30;
        $attach = [];
        foreach ($data['clinic_ids'] as $cid) {
            $attach[$cid] = ['duration_minutes' => $duration];
        }
        $service->clinics()->attach($attach);
        return redirect()->route('secretary.services.index', ['clinic_id' => head($data['clinic_ids'])])
            ->with('status','Service created and attached.');
    }

    public function index(Request $request)
    {
        $assignedClinics = Auth::user()->secretaryClinics()->orderBy('name')->get();
        if ($assignedClinics->isEmpty()) {
            return view('secretary.services.index', [
                'clinic' => null,
                'assignedClinics' => $assignedClinics,
                'services' => collect(),
                'availableServices' => collect(),
            ]);
        }

    $clinicId = $request->input('clinic_id') ?: session('active_clinic_id') ?: $assignedClinics->first()->id;
        $clinic = $assignedClinics->firstWhere('id', (int)$clinicId);
        if (! $clinic) {
            abort(403,'Clinic not assigned to you.');
        }

        $clinic->load('services');
        $attachedIds = $clinic->services->pluck('id');
        $availableServices = Service::whereNotIn('id', $attachedIds)->orderBy('name')->get();

        return view('secretary.services.index', [
            'clinic' => $clinic,
            'assignedClinics' => $assignedClinics,
            'services' => $clinic->services->sortBy('name'),
            'availableServices' => $availableServices,
        ]);
    }

    public function attach(Request $request, Clinic $clinic)
    {
        if (! Auth::user()->secretaryClinics()->where('clinics.id',$clinic->id)->exists()) {
            abort(403,'Clinic not assigned to you.');
        }
        $data = $request->validate([
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'duration_minutes' => 'nullable|integer|min:5|max:480'
        ]);
        $duration = $data['duration_minutes'] ?? 30;
        foreach ($data['service_ids'] as $sid) {
            if (! $clinic->services()->where('services.id',$sid)->exists()) {
                $clinic->services()->attach($sid, ['duration_minutes' => $duration]);
            }
        }
        return redirect()->route('secretary.services.index', ['clinic_id' => $clinic->id])
            ->with('status','Service(s) attached to clinic.');
    }

    public function detach(Clinic $clinic, Service $service)
    {
        if (! Auth::user()->secretaryClinics()->where('clinics.id',$clinic->id)->exists()) {
            abort(403,'Clinic not assigned to you.');
        }

        if (! $clinic->services()->where('services.id',$service->id)->exists()) {
            return back()->with('error','Service not attached to clinic.');
        }

        $inUse = Appointment::where('clinic_id',$clinic->id)
            ->where('service_id',$service->id)
            ->where('status','!=','cancelled')
            ->exists();
        if ($inUse) {
            return back()->with('error','Cannot remove: service already used in appointments.');
        }
        $clinic->services()->detach($service->id);
        return back()->with('status','Service detached from clinic.');
    }

    public function destroy(Service $service)
    {
        $inUse = Appointment::where('service_id',$service->id)
            ->where('status','!=','cancelled')
            ->exists();
        if ($inUse) {
            return back()->with('error','Cannot delete: service used in appointments.');
        }

        $assignedClinicIds = Auth::user()->secretaryClinics()->pluck('clinics.id');
        $attachedInAssigned = $service->clinics()->whereIn('clinics.id',$assignedClinicIds)->exists();
        if (! $attachedInAssigned) {
            return back()->with('error','You can only delete services attached to your clinics (and unused).');
        }
        $service->clinics()->detach();
        $service->delete();
        return redirect()->route('secretary.services.index')->with('status','Service deleted.');
    }
}
