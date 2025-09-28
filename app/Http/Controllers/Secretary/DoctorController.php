<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Service;

class DoctorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function($req, $next) {
            if (! $req->user()?->is_secretary) {
                abort(403,'Forbidden');
            }
            return $next($req);
        });
    }

    public function index()
    {
        $clinicIds = auth()->user()->secretaryClinics()->pluck('clinics.id');
        $doctors = User::where('is_doctor', true)
            ->whereHas('clinics', function($q) use ($clinicIds){
                $q->whereIn('clinics.id', $clinicIds);
            })
            ->with(['clinics:id,name', 'services:id,name'])
            ->orderBy('name')
            ->paginate(15);

        return view('secretary.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $user = auth()->user();
        $activeClinicId = session('active_clinic_id');
        $clinicIds = $user->secretaryClinics()->pluck('clinics.id')->all();
        // If active clinic set & belongs to secretary, restrict to that clinic's services; else union of all assigned clinics' services
        $serviceQuery = Service::query();
        if ($activeClinicId && in_array($activeClinicId, $clinicIds)) {
            $serviceQuery->whereHas('clinics', function($q) use ($activeClinicId){
                $q->where('clinics.id',$activeClinicId);
            });
        } else {
            $serviceQuery->whereHas('clinics', function($q) use ($clinicIds){
                $q->whereIn('clinics.id',$clinicIds);
            });
        }
        $services = $serviceQuery->orderBy('name')->get();
        return view('secretary.doctors.create', compact('services','activeClinicId'));
    }

    public function store(Request $req)
{
    $data = $req->validate([
        'name'             => 'required|string|max:255',
        'email'            => 'required|email|unique:users,email',
        'password'         => 'required|string|min:6|confirmed',
        'service_ids'      => 'array',
        'service_ids.*'    => 'exists:services,id',
        'phone'            => 'nullable|string|max:50',
        'address'          => 'nullable|string|max:500',
    ]);

    $doctor = User::create([
        'name'       => $data['name'],
        'email'      => $data['email'],
        'password'   => Hash::make($data['password']),
        'phone'      => $data['phone'] ?? null,
        'address'    => $data['address'] ?? null,
        'is_doctor'  => true,
    ]);

    $activeClinicId = session('active_clinic_id');
    $secretaryClinicIds = auth()->user()->secretaryClinics()->pluck('clinics.id')->all();
    if ($activeClinicId && in_array($activeClinicId, $secretaryClinicIds)) {
        $doctor->clinics()->sync([$activeClinicId]);
    } else {
        $doctor->clinics()->sync($secretaryClinicIds);
    }
    // Filter submitted service IDs to only those allowed (services offered by assigned clinics)
    $allowedServiceIds = Service::whereHas('clinics', function($q) use ($secretaryClinicIds){
        $q->whereIn('clinics.id', $secretaryClinicIds);
    })->pluck('id')->all();
    $chosen = array_intersect($data['service_ids'] ?? [], $allowedServiceIds);
    $doctor->services()->sync($chosen);

    return redirect()->route('secretary.doctors.index')
                     ->with('status','Doctor added.');
}

    public function edit(User $doctor)
    {
        abort_unless($doctor->is_doctor,404);

        $secretaryClinicIds = auth()->user()->secretaryClinics()->pluck('clinics.id');
        if (! $doctor->clinics()->whereIn('clinics.id', $secretaryClinicIds)->exists()) {
            abort(403,'Doctor not in your assigned clinics.');
        }

        $user = auth()->user();
        $activeClinicId = session('active_clinic_id');
        $clinicIds = $user->secretaryClinics()->pluck('clinics.id')->all();
        $serviceQuery = Service::query();
        if ($activeClinicId && in_array($activeClinicId, $clinicIds)) {
            $serviceQuery->whereHas('clinics', function($q) use ($activeClinicId){
                $q->where('clinics.id',$activeClinicId);
            });
        } else {
            $serviceQuery->whereHas('clinics', function($q) use ($clinicIds){
                $q->whereIn('clinics.id',$clinicIds);
            });
        }
        $services = $serviceQuery->orderBy('name')->get();
        return view('secretary.doctors.edit', compact('doctor','services','activeClinicId'));
    }

    public function update(Request $req, User $doctor)
{
    abort_unless($doctor->is_doctor,404);

    $data = $req->validate([
        'name'             => 'required|string|max:255',
        'email'            => 'required|email|unique:users,email,'.$doctor->id,
        'password'         => 'nullable|string|min:6|confirmed',
        'service_ids'      => 'array',
        'service_ids.*'    => 'exists:services,id',
        'phone'            => 'nullable|string|max:50',
        'address'          => 'nullable|string|max:500',
    ]);

    $doctor->update([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'phone'    => $data['phone'] ?? null,
        'address'  => $data['address'] ?? null,
        'password' => $data['password']
                        ? Hash::make($data['password'])
                        : $doctor->password,
    ]);

    $secretaryClinicIds = auth()->user()->secretaryClinics()->pluck('clinics.id')->all();
    $activeClinicId = session('active_clinic_id');
    if ($activeClinicId && in_array($activeClinicId, $secretaryClinicIds)) {
        $doctor->clinics()->sync([$activeClinicId]);
    } else {
        $doctor->clinics()->sync($secretaryClinicIds);
    }
    $allowedServiceIds = Service::whereHas('clinics', function($q) use ($secretaryClinicIds){
        $q->whereIn('clinics.id', $secretaryClinicIds);
    })->pluck('id')->all();
    $chosen = array_intersect($data['service_ids'] ?? [], $allowedServiceIds);
    $doctor->services()->sync($chosen);

    return redirect()->route('secretary.doctors.index')
                     ->with('status','Doctor updated.');
}

    public function destroy(User $doctor)
    {
        abort_unless($doctor->is_doctor, 404);
        $doctor->delete();
        return back()->with('status','Doctor removed.');
    }

    public function show(User $doctor)
    {
        abort_unless($doctor->is_doctor,404);
        $doctor->load(['clinics:id,name','services:id,name','doctorSchedules.clinic:id,name']);

        $scheduleByDay = $doctor->doctorSchedules
            ->sortBy(fn($s)=>[$s->day_of_week,$s->start_time])
            ->groupBy('day_of_week');

        return view('secretary.doctors.show', compact('doctor','scheduleByDay'));
    }
}
