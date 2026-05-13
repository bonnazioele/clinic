<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Http\Middleware\SecretaryMiddleware;
use App\Models\Service;
use App\Models\User;
use App\Models\DoctorSchedule;
use App\Models\QueueEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        $today = Carbon::today();
        $todayDate = $today->toDateString();
        $todayDayOfWeek = (int) $today->dayOfWeek;
        $activeQueueStatuses = ['waiting', 'called', 'in_progress', 'now_serving'];

        $onQueueDoctorIds = QueueEntry::query()
            ->forDashboardPanel([$activeClinicId], $todayDate)
            ->whereIn('status', $activeQueueStatuses)
            ->with('appointment:id,doctor_id')
            ->get()
            ->map(fn ($entry) => $entry->doctor_id ?: $entry->appointment?->doctor_id)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $baseDoctorQuery = User::query()
            ->where('is_doctor', true)
            ->whereHas('clinics', function ($q) use ($activeClinicId) {
                $q->where('clinics.id', $activeClinicId);
            });

        $doctorStats = [
            'total' => (clone $baseDoctorQuery)->count(),
            'on_queue_today' => (clone $baseDoctorQuery)
                ->whereIn('users.id', $onQueueDoctorIds)
                ->count(),
            'available_today' => (clone $baseDoctorQuery)
                ->whereHas('doctorSchedules', function ($q) use ($activeClinicId, $todayDate, $todayDayOfWeek) {
                    $q->where('clinic_id', $activeClinicId)
                        ->where('is_active', true)
                        ->where('day_of_week', $todayDayOfWeek)
                        ->where(function ($dateQuery) use ($todayDate) {
                            $dateQuery->whereNull('start_date')
                                ->orWhereDate('start_date', '<=', $todayDate);
                        })
                        ->where(function ($dateQuery) use ($todayDate) {
                            $dateQuery->whereNull('end_date')
                                ->orWhereDate('end_date', '>=', $todayDate);
                        });
                })
                ->count(),
        ];

        $serviceScope = (string) $request->input('service_scope', '');

        if ($serviceScope === 'on_queue_today') {
            $baseDoctorQuery->whereIn('users.id', $onQueueDoctorIds);
        }

        if ($serviceScope === 'available_today') {
            $baseDoctorQuery->whereHas('doctorSchedules', function ($q) use ($activeClinicId, $todayDate, $todayDayOfWeek) {
                $q->where('clinic_id', $activeClinicId)
                    ->where('is_active', true)
                    ->where('day_of_week', $todayDayOfWeek)
                    ->where(function ($dateQuery) use ($todayDate) {
                        $dateQuery->whereNull('start_date')
                            ->orWhereDate('start_date', '<=', $todayDate);
                    })
                    ->where(function ($dateQuery) use ($todayDate) {
                        $dateQuery->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $todayDate);
                    });
            });
        }

        $doctors = $baseDoctorQuery
            ->with([
                'clinics' => function ($q) use ($activeClinicId) {
                    $q->where('clinics.id', $activeClinicId)->select('clinics.id', 'clinics.name');
                },
                'services' => function ($q) use ($activeClinicId) {
                    $q->where('doctor_service.clinic_id', $activeClinicId)
                      ->select('services.id', 'services.name')
                      ->withPivot('duration_minutes');
                },
            ])
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->except('page'));

        return view('secretary.doctors.index', compact('doctors', 'doctorStats'));
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
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'email'              => 'required|email',
            'password'           => 'nullable|string|min:6|confirmed',
            'service_ids'        => 'array',
            'service_ids.*'      => 'exists:services,id',
            'duration_minutes'   => 'array',
            'duration_minutes.*' => 'integer|min:5|max:480',
            'phone'              => ['nullable', 'string', 'max:50', 'regex:/^\+?[0-9\s\-()]+$/'],
            'address'            => 'nullable|string|max:500',
        ], [
            'phone.regex' => 'The phone number may only contain numbers, spaces, dashes, parentheses, and an optional plus sign.',
        ]);

        $existingUser = User::where('email', $data['email'])->first();

        if ($existingUser && ! $existingUser->is_doctor) {
            Validator::make([], [])->after(function ($validator) {
                $validator->errors()->add('email', 'This email belongs to an existing non-doctor account.');
            })->validate();
        }

        if ($existingUser && $existingUser->clinics()->where('clinics.id', $activeClinicId)->exists()) {
            Validator::make([], [])->after(function ($validator) {
                $validator->errors()->add('email', 'This doctor is already assigned to the active clinic.');
            })->validate();
        }

        if (! $existingUser && empty($data['password'])) {
            Validator::make([], [])->after(function ($validator) {
                $validator->errors()->add('password', 'The password field is required for new doctor accounts.');
            })->validate();
        }

        $allowedServiceIds = $this->serviceIdsForActiveClinic($activeClinicId);
        $chosen    = array_values(array_intersect($data['service_ids'] ?? [], $allowedServiceIds));
        $durations = $request->input('duration_minutes', []); // ← read submitted durations

        $doctor = DB::transaction(function () use ($existingUser, $data, $activeClinicId, $chosen, $durations) {
            $doctor = $existingUser ?: User::create([
                'name'       => trim($data['first_name'] . ' ' . $data['last_name']),
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'phone'      => $data['phone'] ?? null,
                'address'    => $data['address'] ?? null,
                'is_doctor'  => true,
                'is_active'  => true,
            ]);

            if ($existingUser) {
                $doctor->update([
                    'first_name' => $data['first_name'],
                    'last_name'  => $data['last_name'],
                    'name'       => trim($data['first_name'] . ' ' . $data['last_name']),
                    'phone'      => $data['phone'] ?? null,
                    'address'    => $data['address'] ?? null,
                    'is_doctor'  => true,
                    'is_active'  => true,
                ]);
            }

            $doctor->clinics()->syncWithoutDetaching([$activeClinicId]);
            $doctor->syncServicesForClinic($activeClinicId, $chosen, $durations); // ← pass durations

            return $doctor;
        });

        return redirect()->route('secretary.doctors.index')
            ->with('status', $existingUser ? 'Existing doctor assigned to this clinic.' : 'Doctor added.');
    }

    public function edit(Request $request, User $doctor)
    {
        $activeClinicId = $this->activeClinicId($request);
        $doctor = $this->doctorInActiveClinicOrAbort($doctor, $activeClinicId);
        $doctor->load(['services' => function ($q) use ($activeClinicId) {
            $q->where('doctor_service.clinic_id', $activeClinicId)
              ->select('services.id', 'services.name')
              ->withPivot('duration_minutes');
        }]);
        $services = $this->servicesForActiveClinic($activeClinicId);

        return view('secretary.doctors.edit', compact('doctor', 'services', 'activeClinicId'));
    }

    public function update(Request $request, User $doctor)
    {
        $activeClinicId = $this->activeClinicId($request);
        $doctor = $this->doctorInActiveClinicOrAbort($doctor, $activeClinicId);

        $data = $request->validate([
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email,' . $doctor->id,
            'password'           => 'nullable|string|min:6|confirmed',
            'service_ids'        => 'array',
            'service_ids.*'      => 'exists:services,id',
            'duration_minutes'   => 'array',
            'duration_minutes.*' => 'integer|min:5|max:480',
            'phone'              => ['nullable', 'string', 'max:50', 'regex:/^\+?[0-9\s\-()]+$/'],
            'address'            => 'nullable|string|max:500',
        ], [
            'phone.regex' => 'The phone number may only contain numbers, spaces, dashes, parentheses, and an optional plus sign.',
        ]);

        $payload = [
            'name'       => trim($data['first_name'] . ' ' . $data['last_name']),
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'address'    => $data['address'] ?? null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $allowedServiceIds = $this->serviceIdsForActiveClinic($activeClinicId);
        $chosen    = array_values(array_intersect($data['service_ids'] ?? [], $allowedServiceIds));
        $durations = $request->input('duration_minutes', []); // ← read submitted durations

        DB::transaction(function () use ($doctor, $payload, $activeClinicId, $chosen, $durations) {
            $doctor->update($payload);
            $doctor->clinics()->syncWithoutDetaching([$activeClinicId]);
            $doctor->syncServicesForClinic($activeClinicId, $chosen, $durations); // ← pass durations
        });

        return redirect()->route('secretary.doctors.index')
            ->with('status', 'Doctor updated.');
    }

    public function destroy(Request $request, User $doctor)
    {
        $activeClinicId = $this->activeClinicId($request);
        $doctor = $this->doctorInActiveClinicOrAbort($doctor, $activeClinicId);

        DB::transaction(function () use ($doctor, $activeClinicId) {
            DoctorSchedule::where('doctor_id', $doctor->id)
                ->where('clinic_id', $activeClinicId)
                ->delete();

            DB::table('doctor_service')
                ->where('doctor_id', $doctor->id)
                ->where('clinic_id', $activeClinicId)
                ->delete();

            $doctor->clinics()->detach($activeClinicId);

            if (! $doctor->clinics()->exists()) {
                $doctor->update(['is_active' => false]);
            }
        });

        return back()->with('status', 'Doctor unassigned from active clinic.');
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
                $q->where('doctor_service.clinic_id', $activeClinicId)
                  ->select('services.id', 'services.name')
                  ->withPivot('duration_minutes');
            },
            'doctorSchedules' => function ($q) use ($activeClinicId) {
                $q->where('clinic_id', $activeClinicId)->with('clinic:id,name');
            },
        ]);

        $scheduleByDay = $doctor->doctorSchedules
            ->sortBy(fn ($s) => [$s->day_of_week, $s->start_time])
            ->groupBy('day_of_week');

        $recent = \App\Models\Appointment::with('user', 'clinic', 'service')
            ->where('doctor_id', $doctor->id)
            ->where('clinic_id', $activeClinicId)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->take(10)
            ->get();

        return view('secretary.doctors.show', compact('doctor', 'scheduleByDay', 'recent'));
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
