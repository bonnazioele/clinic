<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithActiveClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Http\Middleware\SecretaryMiddleware;
use App\Models\Clinic;
use App\Models\QueueEntry;
use App\Models\Service;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use InteractsWithActiveClinic;

    public function __construct()
    {
        $this->middleware(['auth', SecretaryMiddleware::class, EnsureSelectedClinic::class]);
    }

    public function index(Request $request)
    {
        $activeClinicId = $this->activeClinicId($request);
        $today = now()->toDateString();
        $laneSort = strtolower((string) $request->query('sort_by', 'doctor'));
        $selectedServiceId = (int) $request->query('service_id', 0);
        $requestedActiveLaneId = trim((string) $request->query('lane', ''));

        if (!in_array($laneSort, ['doctor', 'service'], true)) {
            $laneSort = 'doctor';
        }

        $serviceOptions = Service::query()
            ->forClinics([$activeClinicId])
            ->orderBy('name')
            ->distinct()
            ->get(['services.id', 'services.name']);

        if ($laneSort !== 'service') {
            $selectedServiceId = 0;
        } elseif ($selectedServiceId > 0 && !$serviceOptions->pluck('id')->contains($selectedServiceId)) {
            $selectedServiceId = 0;
        }

        $queueEntriesForDashboardDay = QueueEntry::query()->forDashboardPanel([$activeClinicId], $today);

        $walkInTodayCount = (clone $queueEntriesForDashboardDay)->walkIn()->count();
        $appointmentTodayCount = (clone $queueEntriesForDashboardDay)->withAppointment()->count();
        $statusCounts = QueueEntry::dashboardStatusCounts($queueEntriesForDashboardDay);

        $stats = [
            'totalTodayCount' => $walkInTodayCount + $appointmentTodayCount,
            'waitingCount' => $statusCounts['waiting'],
            'servedCount' => $statusCounts['served'],
            'noShowCount' => $statusCounts['no_show'],
            'rescheduledCount' => $statusCounts['rescheduled'],
            'walkInTodayCount' => $walkInTodayCount,
        ];

        $clinicsWithDoctors = Clinic::query()
            ->forIds([$activeClinicId])
            ->withDashboardDoctorLaneRelations()
            ->orderBy('name')
            ->get(['id', 'name']);

        $queueEntriesToday = (clone $queueEntriesForDashboardDay)
            ->withDashboardRelations()
            ->get();

        $laneEntriesByKey = $queueEntriesToday
            ->filter(fn ($entry) => $entry->laneKey() !== null)
            ->groupBy(fn ($entry) => $entry->laneKey());

        $doctorLanes = collect();
        foreach ($clinicsWithDoctors as $clinic) {
            foreach ($clinic->doctors as $doctor) {
                $laneEntries = $laneEntriesByKey->get($clinic->id . ':' . $doctor->id, collect());

                $nowServing = $laneEntries
                    ->where('status', 'now_serving')
                    ->sortByDesc('updated_at')
                    ->first();

                $nextCandidates = $laneEntries
                    ->filter(fn ($entry) => in_array($entry->status, QueueEntry::nextCandidateStatuses(), true))
                    ->sortBy('queue_number')
                    ->values();

                $firstNextCandidate = $nextCandidates->first();

                if (
                    $laneSort === 'service'
                    && $selectedServiceId > 0
                    && !$doctor->matchesDashboardServiceFilter($selectedServiceId, $nowServing, $firstNextCandidate)
                ) {
                    continue;
                }

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
                    'doctor_name' => $doctor->name,
                    'service_name' => $serviceName,
                    'now_serving' => $nowServing,
                    'next_up' => $nextUp,
                    'queue_depth' => $queueDepth,
                    'call_next_entry' => $callNextEntry,
                    'no_show_entry' => $noShowEntry,
                ]);
            }
        }

        $doctorLanes = $laneSort === 'service'
            ? $doctorLanes->sortBy([
                ['service_name', 'asc'],
                ['doctor_name', 'asc'],
                ['clinic_name', 'asc'],
            ])->values()
            : $doctorLanes->sortBy([
                ['doctor_name', 'asc'],
                ['service_name', 'asc'],
                ['clinic_name', 'asc'],
            ])->values();

        $availableLaneIds = $doctorLanes->pluck('id');
        $activeLaneId = $availableLaneIds->contains($requestedActiveLaneId)
            ? $requestedActiveLaneId
            : ($availableLaneIds->first() ?? null);

        return view('secretary.dashboard', array_merge($stats, compact('doctorLanes', 'laneSort', 'serviceOptions', 'selectedServiceId', 'activeLaneId')));
    }
}
