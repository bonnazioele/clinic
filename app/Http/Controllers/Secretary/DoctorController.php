<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Clinic;
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
    $services = Service::all();
    return view('secretary.doctors.create', compact('services'));
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

    // Auto-assign to all clinics the secretary is assigned to
    $secretaryClinicIds = auth()->user()->secretaryClinics()->pluck('clinics.id')->all();
    $doctor->clinics()->sync($secretaryClinicIds);
    $doctor->services()->sync($data['service_ids'] ?? []);

    return redirect()->route('secretary.doctors.index')
                     ->with('status','Doctor added.');
}

    public function edit(User $doctor)
{
    abort_unless($doctor->is_doctor,404);
    $clinics  = auth()->user()->secretaryClinics()->get();
    $services = Service::all();

    if (! $doctor->clinics()->whereIn('clinics.id', $clinics->pluck('id'))->exists()) {
        abort(403,'Doctor not in your assigned clinics.');
    }
    return view('secretary.doctors.edit', compact('doctor','clinics','services'));
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

    // Keep clinics scoped to secretary's clinics only
    $secretaryClinicIds = auth()->user()->secretaryClinics()->pluck('clinics.id')->all();
    $doctor->clinics()->sync($secretaryClinicIds);
    $doctor->services()->sync($data['service_ids'] ?? []);

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
