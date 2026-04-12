<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Service;

class DoctorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($req, $next) {
            if (! $req->user()?->is_secretary) {
                abort(403, 'Forbidden');
            }
            return $next($req);
        });
    }

    public function index()
    {
        $clinicIds = auth()->user()->secretaryClinics()->pluck('clinics.id');

        $doctors = User::where('is_doctor', true)
            ->whereHas('clinics', function ($q) use ($clinicIds) {
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

        $serviceQuery = Service::query();

        if ($activeClinicId && in_array($activeClinicId, $clinicIds)) {
            $serviceQuery->whereHas('clinics', function ($q) use ($activeClinicId) {
                $q->where('clinics.id', $activeClinicId);
            });
        } else {
            $serviceQuery->whereHas('clinics', function ($q) use ($clinicIds) {
                $q->whereIn('clinics.id', $clinicIds);
            });
        }

        $services = $serviceQuery->orderBy('name')->get();

        return view('secretary.doctors.create', compact('services', 'activeClinicId'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'password'      => 'required|string|min:6|confirmed',
            'service_ids'   => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
            'phone'         => 'nullable|string|max:50',
            'address'       => 'nullable|string|max:500',
        ]);

        $secretaryClinicIds = auth()->user()->secretaryClinics()->pluck('clinics.id')->all();
        $activeClinicId = session('active_clinic_id');

        $doctor = User::where('email', $data['email'])->first();

        if ($doctor) {
            if (! $doctor->is_doctor) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'email' => 'This email already exists and belongs to a non-doctor account.',
                    ]);
            }

            $doctor->update([
                'name'       => trim($data['first_name'] . ' ' . $data['last_name']),
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'phone'      => $data['phone'] ?? $doctor->phone,
                'address'    => $data['address'] ?? $doctor->address,
            ]);
        } else {
            $doctor = User::create([
                'name'       => trim($data['first_name'] . ' ' . $data['last_name']),
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'phone'      => $data['phone'] ?? null,
                'address'    => $data['address'] ?? null,
                'is_doctor'  => true,
            ]);
        }

        if ($activeClinicId && in_array($activeClinicId, $secretaryClinicIds)) {
            $doctor->clinics()->syncWithoutDetaching([$activeClinicId]);
        } else {
            $doctor->clinics()->syncWithoutDetaching($secretaryClinicIds);
        }

        $allowedServiceIds = Service::whereHas('clinics', function ($q) use ($secretaryClinicIds) {
            $q->whereIn('clinics.id', $secretaryClinicIds);
        })->pluck('id')->all();

        $chosen = array_intersect($data['service_ids'] ?? [], $allowedServiceIds);
        if (!empty($chosen)) {
            $doctor->services()->syncWithoutDetaching($chosen);
        }

        return redirect()
            ->route('secretary.doctors.index')
            ->with('status', $doctor->wasRecentlyCreated
                ? 'Doctor added.'
                : 'Existing doctor assigned to clinic successfully.');
    }

    public function edit(User $doctor)
    {
        abort_unless($doctor->is_doctor, 404);

        $secretaryClinicIds = auth()->user()->secretaryClinics()->pluck('clinics.id');
        if (! $doctor->clinics()->whereIn('clinics.id', $secretaryClinicIds)->exists()) {
            abort(403, 'Doctor not in your assigned clinics.');
        }

        $user = auth()->user();
        $activeClinicId = session('active_clinic_id');
        $clinicIds = $user->secretaryClinics()->pluck('clinics.id')->all();

        $serviceQuery = Service::query();

        if ($activeClinicId && in_array($activeClinicId, $clinicIds)) {
            $serviceQuery->whereHas('clinics', function ($q) use ($activeClinicId) {
                $q->where('clinics.id', $activeClinicId);
            });
        } else {
            $serviceQuery->whereHas('clinics', function ($q) use ($clinicIds) {
                $q->whereIn('clinics.id', $clinicIds);
            });
        }

        $services = $serviceQuery->orderBy('name')->get();

        return view('secretary.doctors.edit', compact('doctor', 'services', 'activeClinicId'));
    }

    public function update(Request $req, User $doctor)
    {
        abort_unless($doctor->is_doctor, 404);

        $secretaryClinicIds = auth()->user()->secretaryClinics()->pluck('clinics.id')->all();
        if (! $doctor->clinics()->whereIn('clinics.id', $secretaryClinicIds)->exists()) {
            abort(403, 'Doctor not in your assigned clinics.');
        }

        $data = $req->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($doctor->id),
            ],
            'password'      => 'nullable|string|min:6|confirmed',
            'service_ids'   => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
            'phone'         => 'nullable|string|max:50',
            'address'       => 'nullable|string|max:500',
        ]);

        $doctor->update([
            'name'       => trim($data['first_name'] . ' ' . $data['last_name']),
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'address'    => $data['address'] ?? null,
            'password'   => !empty($data['password'])
                ? Hash::make($data['password'])
                : $doctor->password,
        ]);

        $activeClinicId = session('active_clinic_id');

        if ($activeClinicId && in_array($activeClinicId, $secretaryClinicIds)) {
            $doctor->clinics()->syncWithoutDetaching([$activeClinicId]);
        } else {
            $doctor->clinics()->syncWithoutDetaching($secretaryClinicIds);
        }

        $allowedServiceIds = Service::whereHas('clinics', function ($q) use ($secretaryClinicIds) {
            $q->whereIn('clinics.id', $secretaryClinicIds);
        })->pluck('id')->all();

        $chosen = array_intersect($data['service_ids'] ?? [], $allowedServiceIds);
        $doctor->services()->sync($chosen);

        return redirect()
            ->route('secretary.doctors.index')
            ->with('status', 'Doctor updated.');
    }

    public function destroy(User $doctor)
    {
        abort_unless($doctor->is_doctor, 404);

        $secretaryClinicIds = auth()->user()->secretaryClinics()->pluck('clinics.id')->all();
        if (! $doctor->clinics()->whereIn('clinics.id', $secretaryClinicIds)->exists()) {
            abort(403, 'Doctor not in your assigned clinics.');
        }

        $activeClinicId = session('active_clinic_id');

        if ($activeClinicId && $doctor->clinics()->where('clinics.id', $activeClinicId)->exists()) {
            $doctor->clinics()->detach($activeClinicId);

            return back()->with('status', 'Doctor removed from this clinic.');
        }

        $doctor->delete();

        return back()->with('status', 'Doctor removed.');
    }

    public function show(User $doctor)
    {
        abort_unless($doctor->is_doctor, 404);

        $doctor->load(['clinics:id,name', 'services:id,name', 'doctorSchedules.clinic:id,name']);

        $scheduleByDay = $doctor->doctorSchedules
            ->sortBy(fn ($s) => [$s->day_of_week, $s->start_time])
            ->groupBy('day_of_week');

        return view('secretary.doctors.show', compact('doctor', 'scheduleByDay'));
    }
}