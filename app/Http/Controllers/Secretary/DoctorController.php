<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Http\Middleware\SecretaryMiddleware;
use App\Models\Service;
use App\Models\User;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware(['auth', SecretaryMiddleware::class, EnsureSelectedClinic::class]);
    }

    public function index(Request $request)
    {
        $activeClinicId = $this->activeClinicId($request);

        $doctors = User::query()
            ->where('is_doctor', true)
            ->whereHas('clinics', function ($q) use ($activeClinicId) {
                $q->where('clinics.id', $activeClinicId);
            })
            ->with([
                'clinics' => function ($q) use ($activeClinicId) {
                    $q->where('clinics.id', $activeClinicId)->select('clinics.id', 'clinics.name');
                },
                'services' => function ($q) use ($activeClinicId) {
                    $q->wherePivot('clinic_id', $activeClinicId)->select('services.id', 'services.name');
                },
            ])
            ->orderBy('name')
            ->paginate(15);

        return view('secretary.doctors.index', compact('doctors'));
    }

    public function create(Request $request)
    {
        $activeClinicId = $this->activeClinicId($request);
        $services = $this->servicesForActiveClinic($activeClinicId);

        return view('secretary.doctors.create', compact('services', 'activeClinicId'));
    }

    public function store(Request $request)
    {
        $activeClinicId = $this->activeClinicId($request);

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'service_ids' => 'array',
            'service_ids.*' => 'exists:services,id',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        $doctor = User::create([
            'name' => trim($data['first_name'] . ' ' . $data['last_name']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'is_doctor' => true,
        ]);

        $allowedServiceIds = $this->serviceIdsForActiveClinic($activeClinicId);
        $chosen = array_values(array_intersect($data['service_ids'] ?? [], $allowedServiceIds));

        DB::transaction(function () use ($doctor, $activeClinicId, $chosen) {
            $doctor->clinics()->sync([$activeClinicId]);
            $doctor->syncServicesForClinic($activeClinicId, $chosen);
        });

        return redirect()->route('secretary.doctors.index')
            ->with('status', 'Doctor added.');
    }

    public function edit(Request $request, User $doctor)
    {
        $activeClinicId = $this->activeClinicId($request);
        $doctor = $this->doctorInActiveClinicOrAbort($doctor, $activeClinicId);
        $doctor->load(['services' => function ($q) use ($activeClinicId) {
            $q->wherePivot('clinic_id', $activeClinicId)->select('services.id', 'services.name');
        }]);
        $services = $this->servicesForActiveClinic($activeClinicId);

        return view('secretary.doctors.edit', compact('doctor', 'services', 'activeClinicId'));
    }

    public function update(Request $request, User $doctor)
    {
        $activeClinicId = $this->activeClinicId($request);
        $doctor = $this->doctorInActiveClinicOrAbort($doctor, $activeClinicId);

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $doctor->id,
            'password' => 'nullable|string|min:6|confirmed',
            'service_ids' => 'array',
            'service_ids.*' => 'exists:services,id',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        $payload = [
            'name' => trim($data['first_name'] . ' ' . $data['last_name']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $allowedServiceIds = $this->serviceIdsForActiveClinic($activeClinicId);
        $chosen = array_values(array_intersect($data['service_ids'] ?? [], $allowedServiceIds));

        DB::transaction(function () use ($doctor, $payload, $activeClinicId, $chosen) {
            $doctor->update($payload);
            $doctor->clinics()->syncWithoutDetaching([$activeClinicId]);
            $doctor->syncServicesForClinic($activeClinicId, $chosen);
        });

        return redirect()->route('secretary.doctors.index')
            ->with('status', 'Doctor updated.');
    }

    public function destroy(Request $request, User $doctor)
    {
        $activeClinicId = $this->activeClinicId($request);
        $doctor = $this->doctorInActiveClinicOrAbort($doctor, $activeClinicId);

        if ($doctor->clinics()->count() > 1) {
            DB::transaction(function () use ($doctor, $activeClinicId) {
                DoctorSchedule::where('doctor_id', $doctor->id)
                    ->where('clinic_id', $activeClinicId)
                    ->delete();

                DB::table('doctor_service')
                    ->where('doctor_id', $doctor->id)
                    ->where('clinic_id', $activeClinicId)
                    ->delete();

                $doctor->clinics()->detach($activeClinicId);
            });

            return back()->with('status', 'Doctor unassigned from active clinic.');
        }

        $doctor->delete();

        return back()->with('status', 'Doctor removed.');
    }

    public function show(Request $request, User $doctor)
    {
        $activeClinicId = $this->activeClinicId($request);
        $doctor = $this->doctorInActiveClinicOrAbort($doctor, $activeClinicId);
        $doctor->load([
            'clinics' => function ($q) use ($activeClinicId) {
                $q->where('clinics.id', $activeClinicId)->select('clinics.id', 'clinics.name');
            },
            'services' => function ($q) use ($activeClinicId) {
                $q->wherePivot('clinic_id', $activeClinicId)->select('services.id', 'services.name');
            },
            'doctorSchedules' => function ($q) use ($activeClinicId) {
                $q->where('clinic_id', $activeClinicId)->with('clinic:id,name');
            },
        ]);

        $scheduleByDay = $doctor->doctorSchedules
            ->sortBy(fn ($s) => [$s->day_of_week, $s->start_time])
            ->groupBy('day_of_week');

        return view('secretary.doctors.show', compact('doctor', 'scheduleByDay'));
    }

    private function doctorInActiveClinicOrAbort(User $doctor, int $activeClinicId): User
    {
        abort_unless($doctor->is_doctor, 404);

        $belongsToClinic = $doctor->clinics()
            ->where('clinics.id', $activeClinicId)
            ->exists();

        abort_unless($belongsToClinic, 403, 'Doctor not in your active clinic.');

        return $doctor;
    }

    private function servicesForActiveClinic(int $activeClinicId)
    {
        return Service::query()
            ->whereHas('clinics', function ($q) use ($activeClinicId) {
                $q->where('clinics.id', $activeClinicId);
            })
            ->orderBy('name')
            ->get();
    }

    private function serviceIdsForActiveClinic(int $activeClinicId): array
    {
        return Service::query()
            ->whereHas('clinics', function ($q) use ($activeClinicId) {
                $q->where('clinics.id', $activeClinicId);
            })
            ->pluck('id')
            ->all();
    }
}
