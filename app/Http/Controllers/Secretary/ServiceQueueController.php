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

        $service = Service::query()
            ->forClinics([$activeClinicId])
            ->where('id', $service_id)
            ->firstOrFail();

        $doctorSelect = ['users.id', 'users.name', 'users.is_active'];

        if (Schema::hasColumn('users', 'avatar_url')) {
            $doctorSelect[] = 'users.avatar_url';
        }

        if (Schema::hasColumn('users', 'specialty')) {
            $doctorSelect[] = 'users.specialty';
        }

        $doctors = $service->doctors()
            ->wherePivot('clinic_id', $activeClinicId)
            ->where('users.is_active', true)
            ->orderBy('users.name')
            ->get($doctorSelect);

        /*
        |--------------------------------------------------------------------------
        | New flow
        |--------------------------------------------------------------------------
        | Dashboard -> click service -> open queue management immediately.
        | The doctor queue page already has browser-like doctor tabs on top, so we
        | redirect to the first active doctor for this service.
        */
        if ($doctors->isNotEmpty()) {
            return redirect()->route('secretary.services.doctors.queue', [
                'service_id' => $service->id,
                'doctor_id' => $doctors->first()->id,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback only when the service has no active doctors
        |--------------------------------------------------------------------------
        | This prevents an error if a secretary clicks a service with no assigned
        | doctor yet.
        */
        $queueEntriesForDay = QueueEntry::query()
            ->where('clinic_id', $activeClinicId)
            ->whereIn('status', ['waiting', 'in_progress', 'now_serving', 'served'])
            ->where(function ($queueQuery) use ($service, $activeClinicId, $today) {
                $queueQuery->whereHas('appointment', function ($appointmentQuery) use ($service, $today) {
                    $appointmentQuery
                        ->where('service_id', $service->id)
                        ->whereDate('appointment_date', $today);
                })->orWhere(function ($walkInQuery) use ($service, $activeClinicId, $today) {
                    $walkInQuery
                        ->walkIn()
                        ->whereExists(function ($visitQuery) use ($service, $activeClinicId, $today) {
                            $visitQuery->selectRaw('1')
                                ->from('patient_visits')
                                ->whereColumn('patient_visits.patient_id', 'queue_entries.patient_id')
                                ->where('patient_visits.clinic_id', $activeClinicId)
                                ->whereDate('patient_visits.date_of_visit', $today)
                                ->where('patient_visits.requested_service', $service->name);
                        });
                });
            })
            ->with('appointment:id,doctor_id')
            ->orderByDesc('updated_at')
            ->get();

        $waitingCount = $queueEntriesForDay->where('status', 'waiting')->count();
        $nowServingCount = $queueEntriesForDay
            ->filter(fn ($entry) => in_array($entry->status, ['in_progress', 'now_serving'], true))
            ->count();
        $completedToday = $queueEntriesForDay->where('status', 'served')->count();

        return view('secretary.queue.service-overview', [
            'service' => $service,
            'serviceName' => $service->name,
            'serviceDescription' => $service->description,
            'waitingCount' => $waitingCount,
            'nowServing' => $nowServingCount,
            'activeDoctors' => 0,
            'completedToday' => $completedToday,
            'dailyProgress' => 0,
            'doctors' => collect(),
            'serviceId' => $service->id,
            'clinicId' => $activeClinicId,
        ]);
    }
}
