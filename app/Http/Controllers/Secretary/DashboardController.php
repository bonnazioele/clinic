<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Http\Middleware\SecretaryMiddleware;
use App\Models\Clinic;
use App\Models\QueueEntry;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware(['auth', SecretaryMiddleware::class, EnsureSelectedClinic::class]);
    }

    public function index(Request $request)
    {
        $activeClinicId = $this->activeClinicId($request);
        $today = now()->toDateString();
        $requestedServiceTabId = trim((string) $request->query('service_tab', ''));
        $requestedActiveLaneId = trim((string) $request->query('lane', ''));

        $serviceOptions = Service::query()
            ->forClinics([$activeClinicId])
            ->orderBy('name')
            ->distinct()
            ->get(['services.id', 'services.name']);
        $activeClinicServiceIds = $serviceOptions->pluck('id')->map(fn ($id) => (int) $id);

        $queueEntriesForDashboardDay = QueueEntry::query()->forDashboardPanel([$activeClinicId], $today);

        $totalWaiting = (clone $queueEntriesForDashboardDay)
            ->where('status', 'waiting')
            ->count();

        $nowServingCount = (clone $queueEntriesForDashboardDay)
            ->whereIn('status', ['in_progress', 'now_serving'])
            ->count();

        $onDutyDoctorIds = (clone $queueEntriesForDashboardDay)
            ->whereIn('status', ['in_progress', 'now_serving'])
            ->with('appointment:id,doctor_id')
            ->get()
            ->map(fn ($entry) => $entry->doctor_id ?: $entry->appointment?->doctor_id)
            ->filter()
            ->unique();

        $onDutyDoctors = $onDutyDoctorIds->count();

        $totalActiveDoctors = User::query()
            ->where('is_doctor', true)
            ->where('is_active', true)
            ->whereHas('clinicsAsDoctor', function ($clinicQuery) use ($activeClinicId) {
                $clinicQuery->where('clinic_id', $activeClinicId);
            })
            ->count();

        $clinicsWithDoctors = Clinic::query()
            ->forIds([$activeClinicId])
            ->withDashboardDoctorLaneRelations($activeClinicId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $queueEntriesToday = (clone $queueEntriesForDashboardDay)
            ->withDashboardRelations()
            ->get();

        $serviceStatusCards = $serviceOptions
            ->map(function ($service) use ($queueEntriesToday) {
                $serviceId = (int) $service->id;

                $serviceEntries = $queueEntriesToday
                    ->filter(fn ($entry) => (int) ($entry->appointment?->service_id ?? 0) === $serviceId);

                $waitingCount = $serviceEntries
                    ->where('status', 'waiting')
                    ->count();

                $servingDoctors = $serviceEntries
                    ->whereIn('status', ['in_progress', 'now_serving'])
                    ->map(fn ($entry) => $entry->doctor_id ?: $entry->appointment?->doctor_id)
                    ->filter()
                    ->unique()
                    ->count();

                return [
                    'id' => $serviceId,
                    'route_key' => $serviceId,
                    'name' => $service->name,
                    'waiting_count' => $waitingCount,
                    'serving_doctors' => $servingDoctors,
                ];
            })
            ->values();

        $laneEntriesByKey = $queueEntriesToday
            ->filter(fn ($entry) => $entry->laneKey() !== null)
            ->groupBy(fn ($entry) => $entry->laneKey());

        $doctorLanes = collect();
        foreach ($clinicsWithDoctors as $clinic) {
            foreach ($clinic->doctors as $doctor) {
                $doctorServices = $doctor->services
                    ->filter(fn ($service) => $activeClinicServiceIds->contains((int) $service->id))
                    ->values();

                if ($doctorServices->isEmpty()) {
                    continue;
                }

                $laneEntries = $laneEntriesByKey->get($clinic->id . ':' . $doctor->id, collect());

                $nowServing = $laneEntries
                    ->filter(fn ($entry) => in_array($entry->status, ['in_progress', 'now_serving'], true))
                    ->sortByDesc('updated_at')
                    ->first();

                $nextCandidates = $laneEntries
                    ->filter(fn ($entry) => in_array($entry->status, QueueEntry::nextCandidateStatuses(), true))
                    ->sortBy(fn (QueueEntry $entry) => sprintf(
                        '%s %s %010d',
                        $entry->scheduledSlotDateString() ?? '9999-12-31',
                        $entry->scheduledSlotTimeString() ?? '99:99',
                        $entry->queue_number
                    ))
                    ->values();

                $firstNextCandidate = $nextCandidates->first();

                $nextUp = $nextCandidates->take(2);

                $queueDepth = $laneEntries
                    ->filter(fn ($entry) => in_array($entry->status, QueueEntry::activeLaneStatuses(), true))
                    ->count();

                $serviceName = $doctor->dashboardServiceLabel($nowServing, $firstNextCandidate);

                $callNextEntry = $firstNextCandidate;
                $noShowEntry = $nowServing;

                $doctorLanes->push([
                    'id' => 'lane-' . $clinic->id . '-' . $doctor->id,
                    'clinic_id' => $clinic->id,
                    'clinic_name' => $clinic->name,
                    'doctor_id' => (int) $doctor->id,
                    'doctor_name' => $doctor->name,
                    'service_name' => $serviceName,
                    'service_ids' => $doctorServices->pluck('id')->map(fn ($id) => (int) $id)->values(),
                    'now_serving' => $nowServing,
                    'next_up' => $nextUp,
                    'queue_depth' => $queueDepth,
                    'call_next_entry' => $callNextEntry,
                    'no_show_entry' => $noShowEntry,
                ]);
            }
        }

        $doctorLanes = $doctorLanes->sortBy([
            ['doctor_name', 'asc'],
            ['clinic_name', 'asc'],
        ])->values();

        $serviceTabs = $serviceOptions
            ->map(function ($service) use ($doctorLanes, $requestedActiveLaneId) {
                $serviceId = (int) $service->id;

                $lanes = $doctorLanes
                    ->filter(fn ($lane) => $lane['service_ids']->contains($serviceId))
                    ->values();

                $availableLaneIds = $lanes->pluck('id');
                $activeLaneId = $availableLaneIds->contains($requestedActiveLaneId)
                    ? $requestedActiveLaneId
                    : ($availableLaneIds->first() ?? null);

                return [
                    'id' => 'service-tab-' . $serviceId,
                    'service_id' => $serviceId,
                    'service_name' => $service->name,
                    'doctor_lanes' => $lanes,
                    'active_lane_id' => $activeLaneId,
                ];
            })
            ->values();

        $availableServiceTabIds = $serviceTabs->pluck('id');
        $activeServiceTabId = $availableServiceTabIds->contains($requestedServiceTabId)
            ? $requestedServiceTabId
            : ($availableServiceTabIds->first() ?? null);

        return view('secretary.dashboard', [
            'totalWaiting' => $totalWaiting,
            'nowServingCount' => $nowServingCount,
            'onDutyDoctors' => $onDutyDoctors,
            'totalActiveDoctors' => $totalActiveDoctors,
            'serviceStatusCards' => $serviceStatusCards,
            'serviceTabs' => $serviceTabs,
            'activeServiceTabId' => $activeServiceTabId,
        ]);
    }
}
