<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithActiveClinic;
use App\Models\QueueEntry;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;

class ServiceQueueController extends Controller
{
    use InteractsWithActiveClinic;

    public function index(Request $request, int $service_id)
    {
        $activeClinicId = $this->activeClinicId($request);
        $today = now()->toDateString();

        $serviceQuery = Service::query()
            ->forClinics([$activeClinicId])
            ->where('id', $service_id);

        $service = $serviceQuery->firstOrFail();

        // TODO: Include walk-in counts when walk-in queue integrates with services.
        $queueEntriesForDay = QueueEntry::query()
            ->where('clinic_id', $activeClinicId)
            ->whereIn('status', ['waiting', 'now_serving', 'served'])
            ->whereHas('appointment', function ($appointmentQuery) use ($service, $today) {
                $appointmentQuery
                    ->where('service_id', $service->id)
                    ->whereDate('appointment_date', $today);
            })
            ->with('appointment:id,doctor_id')
            ->orderByDesc('updated_at')
            ->get();

        $waitingCount = $queueEntriesForDay->where('status', 'waiting')->count();
        $nowServingCount = $queueEntriesForDay->where('status', 'now_serving')->count();
        $completedToday = $queueEntriesForDay->where('status', 'served')->count();

        $queueEntries = $queueEntriesForDay
            ->whereIn('status', ['waiting', 'now_serving'])
            ->groupBy(function ($entry) {
                return $entry->appointment?->doctor_id;
            });

        $doctorSelect = ['users.id', 'users.name'];
        if (Schema::hasColumn('users', 'avatar_url')) {
            $doctorSelect[] = 'users.avatar_url';
        }

        $doctors = $service->doctors()
            ->wherePivot('clinic_id', $activeClinicId)
            ->where('users.is_active', true)
            ->get($doctorSelect);

        $doctorCards = $doctors->map(function ($doctor) use ($queueEntries) {
            $entries = $queueEntries->get($doctor->id, collect());
            $nowServingEntry = $entries->firstWhere('status', 'now_serving');
            $waiting = $entries->where('status', 'waiting')->count();

            return [
                'doctor_id' => $doctor->id,
                'name' => $doctor->name,
                'status' => 'active',
                'now_serving' => $nowServingEntry ? '#' . $nowServingEntry->queue_number : '---',
                'waiting' => $waiting,
                'avatar_url' => $doctor->avatar_url ?? null,
                'specialty' => $doctor->specialty ?? null,
            ];
        })->values();

        return view('secretary.queue.service-overview', [
            'service' => $service,
            'serviceName' => $service->name,
            'serviceDescription' => $service->description,
            'waitingCount' => $waitingCount,
            'nowServing' => $nowServingCount,
            'activeDoctors' => $doctorCards->count(),
            'completedToday' => $completedToday,
            'dailyProgress' => 0,
            'doctors' => $doctorCards,
            'serviceId' => $service->id,
            'clinicId' => $activeClinicId,
        ]);
    }
}
