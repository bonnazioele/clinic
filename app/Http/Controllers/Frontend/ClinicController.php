<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\QueueEntry;
use App\Models\PatientVisit;
use App\Models\User;
use App\Notifications\ClinicRegistrationSubmitted;
use App\Models\Service;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ClinicController extends Controller
{
    public function index(Request $req, QueueService $queueService)
    {
        $services = Service::orderBy('name')->get();

        $query = Clinic::with([
                'services',
                'doctors' => function ($q) {
                    $q->where('is_doctor', true)
                        ->where('is_active', true)
                        ->with([
                            'services',
                            'doctorSchedules',
                        ])
                        ->orderBy('name');
                },
            ])
            ->whereIn('status', ['approved', 'active']);

        if ($req->filled('search')) {
            $search = trim($req->search);

            $query->where(function ($q) use ($search) {
                $q->where('clinics.name', 'like', '%' . $search . '%')
                    ->orWhere('clinics.address', 'like', '%' . $search . '%')
                    ->orWhere('clinics.contact_number', 'like', '%' . $search . '%')
                    ->orWhere('clinics.email', 'like', '%' . $search . '%')
                    ->orWhereHas('services', function ($serviceQuery) use ($search) {
                        $serviceQuery->where('services.name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('doctors', function ($doctorQuery) use ($search) {
                        $doctorQuery->where('users.name', 'like', '%' . $search . '%')
                            ->orWhere('users.first_name', 'like', '%' . $search . '%')
                            ->orWhere('users.last_name', 'like', '%' . $search . '%')
                            ->orWhere('users.email', 'like', '%' . $search . '%')
                            ->orWhere('users.phone', 'like', '%' . $search . '%')
                            ->orWhereHas('services', function ($doctorServiceQuery) use ($search) {
                                $doctorServiceQuery->where('services.name', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        if ($req->filled('lat') && $req->filled('lng')) {
            $lat = (float) $req->lat;
            $lng = (float) $req->lng;
            $radiusKm = $req->filled('radius') ? (float) $req->radius : 10;

            $distanceSql = "(6371 * acos(
                cos(radians(?)) * cos(radians(gps_latitude))
                * cos(radians(gps_longitude) - radians(?))
                + sin(radians(?)) * sin(radians(gps_latitude))
            ))";

            $query->whereNotNull('gps_latitude')
                ->whereNotNull('gps_longitude')
                ->select('clinics.*')
                ->selectRaw($distanceSql . ' as distance_km', [$lat, $lng, $lat])
                ->having('distance_km', '<=', $radiusKm)
                ->orderBy('distance_km');
        } else {
            $query->latest('id');
        }

        $mapClinics = (clone $query)
            ->whereNotNull('gps_latitude')
            ->whereNotNull('gps_longitude')
            ->limit(200)
            ->get();

        $clinics = $query->paginate(10)->withQueryString();

        $this->attachDoctorServiceQueueSnapshotsToClinics($clinics->getCollection(), $queueService);

        return view('clinics.index', compact('clinics', 'services', 'mapClinics'));
    }

    public function create()
    {
        $services = Service::orderBy('name')->get();

        return view('clinics.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'branch_code'          => 'nullable|string|max:255',
            'address'              => 'required|string|max:500',
            'gps_latitude'         => 'nullable|numeric|between:-90,90',
            'gps_longitude'        => 'nullable|numeric|between:-180,180',
            'contact_number'       => 'nullable|string|max:255',
            'email'                => 'nullable|email|max:255',
            'contact_first_name'   => 'nullable|string|max:255',
            'contact_last_name'    => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'description'          => 'nullable|string',
            'queue_mode'           => 'nullable|string|in:fcfs,appointment',
            'logo'                 => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cover_image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'services'             => 'nullable|array',
            'services.*'           => 'integer|exists:services,id',
        ]);

        if (empty($validated['gps_latitude']) || empty($validated['gps_longitude'])) {
            $coords = $this->geocodeAddress($validated['address']);

            if ($coords) {
                $validated['gps_latitude'] = $coords['lat'];
                $validated['gps_longitude'] = $coords['lng'];
            }
        }

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('clinics/logos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('clinics/covers', 'public');
        }

        $selectedServices = $validated['services'] ?? [];
        unset($validated['services']);

        $validated['created_by_user_id'] = Auth::id();
        $validated['status'] = 'pending';

        $clinic = Clinic::create($validated);

        if (!empty($selectedServices)) {
            $clinic->services()->sync($selectedServices);
        }

        $this->notifyAdminsOfClinicRegistration($clinic);

        return redirect()
            ->route('clinics.index')
            ->with('success', 'Clinic registered successfully. It will be visible once approved.');
    }

    public function show(Request $request, Clinic $clinic)
    {
        if (! $clinic->isApprovedLike()) {
            abort(404);
        }

        $clinic->load([
            'services',
            'doctors' => function ($q) use ($clinic) {
                $q->where('is_doctor', true)
                    ->where('is_active', true)
                    ->with([
                        'services' => function ($serviceQuery) use ($clinic) {
                            $serviceQuery->where('doctor_service.clinic_id', $clinic->id);
                        },
                        'doctorSchedules' => function ($scheduleQuery) use ($clinic) {
                            $scheduleQuery->where('clinic_id', $clinic->id)
                                ->orderBy('day_of_week')
                                ->orderBy('start_time');
                        },
                    ])
                    ->orderBy('name');
            },
        ]);

        $user = Auth::user();
        $canEdit = false;

        if ($user) {
            $canEdit = $user->is_secretary
                && $clinic->secretaries()
                    ->where('clinic_secretary.secretary_id', $user->id)
                    ->exists();
        }

        return response()->json([
            'id'              => $clinic->id,
            'name'            => $clinic->name,
            'address'         => $clinic->address,
            'gps_latitude'    => $clinic->gps_latitude,
            'gps_longitude'   => $clinic->gps_longitude,
            'contact_number'  => $clinic->contact_number,
            'email'           => $clinic->email,
            'description'     => $clinic->description,
            'logo_url'        => $clinic->logo ? asset('storage/' . $clinic->logo) : null,
            'cover_image_url' => $clinic->cover_image ? asset('storage/' . $clinic->cover_image) : null,
            'services'        => $clinic->services->map(fn ($s) => [
                'id'               => $s->id,
                'name'             => $s->name,
                'duration_minutes' => $s->pivot->duration_minutes ?? null,
            ]),
            'doctors'         => $clinic->doctors->map(fn ($d) => [
                'id'       => $d->id,
                'name'     => $d->name ?: trim($d->first_name . ' ' . $d->last_name),
                'email'    => $d->email,
                'phone'    => $d->phone,
                'services' => $d->services->map(fn ($s) => [
                    'id'   => $s->id,
                    'name' => $s->name,
                ])->values(),
                'schedules' => $d->doctorSchedules->map(fn ($schedule) => [
                    'id'          => $schedule->id,
                    'day_of_week' => $schedule->day_of_week,
                    'start_time'  => $schedule->start_time,
                    'end_time'    => $schedule->end_time,
                ])->values(),
            ]),
            'can_edit' => $canEdit,
            'edit_url' => $canEdit ? route('secretary.clinic.edit', $clinic) : null,
        ]);
    }

    public function edit(Clinic $clinic)
    {
        $this->authorizeSecretary($clinic);

        $services = Service::orderBy('name')->get();
        $selectedServices = $clinic->services->pluck('id')->toArray();

        return view('clinics.edit', compact('clinic', 'services', 'selectedServices'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        $this->authorizeSecretary($clinic);

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'branch_code'          => 'nullable|string|max:255',
            'address'              => 'required|string|max:500',
            'gps_latitude'         => 'nullable|numeric|between:-90,90',
            'gps_longitude'        => 'nullable|numeric|between:-180,180',
            'contact_number'       => 'nullable|string|max:255',
            'email'                => 'nullable|email|max:255',
            'contact_first_name'   => 'nullable|string|max:255',
            'contact_last_name'    => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'description'          => 'nullable|string',
            'queue_mode'           => 'nullable|string|in:fcfs,appointment',
            'logo'                 => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cover_image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'services'             => 'nullable|array',
            'services.*'           => 'integer|exists:services,id',
        ]);

        if (empty($validated['gps_latitude']) || empty($validated['gps_longitude'])) {
            $coords = $this->geocodeAddress($validated['address']);

            if ($coords) {
                $validated['gps_latitude'] = $coords['lat'];
                $validated['gps_longitude'] = $coords['lng'];
            }
        }

        if ($request->hasFile('logo')) {
            if ($clinic->logo) {
                Storage::disk('public')->delete($clinic->logo);
            }

            $validated['logo'] = $request->file('logo')->store('clinics/logos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($clinic->cover_image) {
                Storage::disk('public')->delete($clinic->cover_image);
            }

            $validated['cover_image'] = $request->file('cover_image')->store('clinics/covers', 'public');
        }

        $selectedServices = $validated['services'] ?? [];
        unset($validated['services']);

        $clinic->update($validated);
        $clinic->services()->sync($selectedServices);

        return redirect()
            ->route('secretary.clinic.edit', $clinic)
            ->with('success', 'Clinic updated successfully.');
    }


    private function attachDoctorServiceQueueSnapshotsToClinics($clinics, QueueService $queueService): void
    {
        $today = now()->toDateString();
        $userId = Auth::id();

        foreach ($clinics as $clinic) {
            $snapshots = [];

            foreach (collect($clinic->doctors ?? []) as $doctor) {
                $doctorServices = collect($doctor->services ?? [])
                    ->filter(function ($service) use ($clinic) {
                        $pivotClinicId = $service->pivot->clinic_id ?? null;

                        return $pivotClinicId === null || (int) $pivotClinicId === (int) $clinic->id;
                    })
                    ->values();

                foreach ($doctorServices as $service) {
                    $snapshots[(int) $service->id][(int) $doctor->id] = $this->buildDoctorServiceQueueSnapshot(
                        $clinic,
                        (int) $doctor->id,
                        (int) $service->id,
                        $queueService,
                        $today,
                        $userId
                    );
                }
            }

            $clinic->setAttribute('doctor_service_queue_snapshots', $snapshots);
        }
    }

    private function buildDoctorServiceQueueSnapshot(
        Clinic $clinic,
        int $doctorId,
        int $serviceId,
        QueueService $queueService,
        string $date,
        ?int $userId
    ): array {
        $doctorQueueEntries = QueueEntry::query()
            ->with(['appointment:id,user_id,doctor_id,service_id,appointment_date,appointment_time,status'])
            ->where('clinic_id', $clinic->id)
            ->forDashboardDay($date)
            ->forDoctor($doctorId)
            ->whereIn('status', QueueEntry::activePatientStatuses())
            ->orderByScheduledSlot()
            ->get();

        $serviceQueueEntries = $doctorQueueEntries
            ->filter(function (QueueEntry $entry) use ($clinic, $serviceId, $date) {
                return (int) $this->serviceIdForQueueEntry($entry, (int) $clinic->id, $date) === (int) $serviceId;
            })
            ->values();

        $waitingPatients = $serviceQueueEntries
            ->whereIn('status', ['waiting', 'called'])
            ->count();

        $currentlyServing = $serviceQueueEntries
            ->whereIn('status', ['in_progress', 'now_serving'])
            ->count();

        $activePatientQueue = null;
        $peopleAhead = null;

        if ($userId) {
            $activePatientQueue = $serviceQueueEntries
                ->first(function (QueueEntry $entry) use ($userId) {
                    return (int) ($entry->user_id ?? 0) === (int) $userId
                        || (int) ($entry->appointment?->user_id ?? 0) === (int) $userId;
                });

            if ($activePatientQueue) {
                $peopleAhead = $serviceQueueEntries
                    ->takeUntil(fn (QueueEntry $candidate) => (int) $candidate->id === (int) $activePatientQueue->id)
                    ->count();
            }
        }

        $availability = $this->getDoctorServiceAvailabilitySummary(
            $clinic,
            $doctorId,
            $serviceId,
            $queueService,
            $date
        );

        return [
            'has_personal_queue' => (bool) $activePatientQueue,
            'queue_number' => $activePatientQueue?->queue_number,
            'queue_status' => $activePatientQueue?->status,
            'people_ahead' => $peopleAhead,
            'waiting_patients' => $waitingPatients,
            'currently_serving' => $currentlyServing,
            'available_slots_today' => $availability['available_slots_today'],
            'next_available_slot' => $availability['next_available_slot'],
        ];
    }

    private function serviceIdForQueueEntry(QueueEntry $entry, int $clinicId, string $date): ?int
    {
        if ($entry->appointment?->service_id) {
            return (int) $entry->appointment->service_id;
        }

        if (! $entry->patient_id) {
            return null;
        }

        $visit = PatientVisit::query()
            ->where('clinic_id', $clinicId)
            ->where('patient_id', $entry->patient_id)
            ->whereDate('date_of_visit', $entry->scheduledSlotDateString() ?? $date)
            ->latest('time_in')
            ->first();

        if (! $visit?->requested_service) {
            return null;
        }

        return Service::query()
            ->forClinics([$clinicId])
            ->where('name', $visit->requested_service)
            ->value('services.id');
    }

    private function getDoctorServiceAvailabilitySummary(
        Clinic $clinic,
        int $doctorId,
        int $serviceId,
        QueueService $queueService,
        string $date
    ): array {
        try {
            $availableSlots = $queueService->availableSlots(
                (int) $clinic->id,
                $doctorId,
                $serviceId,
                $date
            )
                ->where('available', true)
                ->filter(fn ($slot) => filled($slot['time'] ?? null))
                ->unique(fn ($slot) => ($slot['date'] ?? $date) . '|' . ($slot['time'] ?? ''))
                ->sortBy('start_at')
                ->values();
        } catch (\Throwable $e) {
            $availableSlots = collect();
        }

        $nextSlot = $availableSlots->first();

        return [
            'available_slots_today' => $availableSlots->count(),
            'next_available_slot' => $nextSlot
                ? ($nextSlot['display'] ?? $nextSlot['time'])
                : null,
        ];
    }

    private function geocodeAddress(string $address): ?array
    {
        $address = trim($address);

        if ($address === '') {
            return null;
        }

        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => config('app.name', 'Laravel') . '/1.0 clinic geocoder',
                'Accept-Language' => 'en',
            ])
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'jsonv2',
                'limit' => 1,
                'addressdetails' => 1,
                'countrycodes' => 'ph',
            ]);

        if (! $response->successful()) {
            return null;
        }

        $first = $response->json()[0] ?? null;

        if (! $first || ! isset($first['lat'], $first['lon'])) {
            return null;
        }

        return [
            'lat' => round((float) $first['lat'], 7),
            'lng' => round((float) $first['lon'], 7),
        ];
    }

    private function authorizeSecretary(Clinic $clinic): void
    {
        $user = Auth::user();

        abort_unless(
            $user
            && $user->is_secretary
            && $clinic->secretaries()
                ->where('clinic_secretary.secretary_id', $user->id)
                ->exists(),
            403,
            'You are not authorized to manage this clinic.'
        );
    }

    private function notifyAdminsOfClinicRegistration(Clinic $clinic): void
    {
        $admins = User::query()
            ->where('is_admin', true)
            ->where('is_active', true)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new ClinicRegistrationSubmitted($clinic));
    }
}