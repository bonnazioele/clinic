<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithActiveClinic;
use App\Http\Controllers\Controller;
use App\Models\PatientVisit;
use App\Models\QueueEntry;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DoctorQueueController extends Controller
{
    use InteractsWithActiveClinic;

    public function show(Request $request, int $service_id, int $doctor_id)
    {
        $activeClinicId = $this->activeClinicId($request);
        $today = now()->toDateString();

        $serviceQuery = Service::query()
            ->forClinics([$activeClinicId])
            ->where('id', $service_id);

        $service = $serviceQuery->firstOrFail();

        $doctorSelect = ['users.id', 'users.name', 'users.first_name', 'users.last_name', 'users.email', 'users.is_active'];
        if (Schema::hasColumn('users', 'avatar_url')) {
            $doctorSelect[] = 'users.avatar_url';
        }

        $doctor = $service->doctors()
            ->wherePivot('clinic_id', $activeClinicId)
            ->where('users.id', $doctor_id)
            ->firstOrFail($doctorSelect);

        $queueEntries = QueueEntry::query()
            ->where('clinic_id', $activeClinicId)
            ->forDoctor($doctor_id)
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
            ->with([
                'appointment:id,user_id,doctor_id,service_id,appointment_date,appointment_time,status',
                'appointment.user:id,name,email,phone',
                'patient:id,patient_number,first_name,last_name,middle_name,email_address,mobile_number',
                'user:id,name,email,phone',
            ])
            ->whereIn('status', QueueEntry::activeLaneStatuses())
            ->orderByRaw("CASE status WHEN 'now_serving' THEN 0 WHEN 'called' THEN 1 WHEN 'waiting' THEN 2 WHEN 'rescheduled' THEN 3 ELSE 4 END")
            ->orderBy('queue_number')
            ->get();

        $visitsByPatient = PatientVisit::query()
            ->where('clinic_id', $activeClinicId)
            ->whereDate('date_of_visit', $today)
            ->where('requested_service', $service->name)
            ->whereIn('patient_id', $queueEntries->pluck('patient_id')->filter()->unique())
            ->latest('time_in')
            ->get()
            ->groupBy('patient_id');

        $nowServing = $queueEntries->firstWhere('status', 'now_serving');
        $nextEntry = $queueEntries
            ->whereIn('status', QueueEntry::nextCandidateStatuses())
            ->sortBy('queue_number')
            ->first();

        $queueRows = $queueEntries->map(function (QueueEntry $entry) use ($visitsByPatient) {
            $appointmentTime = $entry->appointment?->appointment_time;
            $visit = $entry->patient_id ? $visitsByPatient->get($entry->patient_id)?->first() : null;

            return [
                'entry_id' => $entry->id,
                'number' => '#' . $entry->queue_number,
                'name' => $entry->display_name,
                'id' => $entry->patient?->patient_number ?? ($entry->user_id ? 'USR-' . $entry->user_id : 'QE-' . $entry->id),
                'visit' => $entry->is_walk_in ? 'Walk-in' : 'Appointment',
                'time' => $appointmentTime ? $appointmentTime->format('g:i A') : ($visit?->time_in?->format('g:i A') ?? $entry->created_at->format('g:i A')),
                'status' => $entry->status_label,
                'status_key' => $entry->status,
                'active' => $entry->status === 'now_serving',
                'call_url' => route('secretary.queue.call', ['clinic' => $entry->clinic_id, 'entry' => $entry->id]),
                'done_next_url' => route('secretary.queue.done_next', ['clinic' => $entry->clinic_id, 'entry' => $entry->id]),
                'no_show_url' => route('secretary.queue.no_show', ['clinic' => $entry->clinic_id, 'entry' => $entry->id]),
                'cancel_url' => route('secretary.queue.cancel', ['clinic' => $entry->clinic_id, 'entry' => $entry->id]),
            ];
        })->values();

        $waitingCount = $queueEntries->whereIn('status', QueueEntry::nextCandidateStatuses())->count();
        $estimatedWaitMinutes = $waitingCount * 15;

        return view('secretary.queue.doctor', [
            'service' => $service,
            'serviceName' => $service->name,
            'doctor' => $doctor,
            'clinicId' => $activeClinicId,
            'nowServing' => $nowServing,
            'nowServingNumber' => $nowServing ? '#' . $nowServing->queue_number : null,
            'nowServingName' => $nowServing?->display_name,
            'nowServingType' => $nowServing ? ($nowServing->is_walk_in ? 'Walk-in' : 'Appointment') : null,
            'nowServingDoneNextUrl' => $nowServing ? route('secretary.queue.done_next', ['clinic' => $activeClinicId, 'entry' => $nowServing->id]) : null,
            'nowServingNoShowUrl' => $nowServing ? route('secretary.queue.no_show', ['clinic' => $activeClinicId, 'entry' => $nowServing->id]) : null,
            'queueStatusCount' => $waitingCount,
            'queueStatusEta' => $estimatedWaitMinutes > 0 ? $estimatedWaitMinutes . ' mins' : 'No wait',
            'queueNextNumber' => $nextEntry ? '#' . $nextEntry->queue_number : null,
            'queueNextName' => $nextEntry?->display_name,
            'queueNextType' => $nextEntry ? ($nextEntry->is_walk_in ? 'Walk-in' : 'Appointment') : null,
            'queueNextCallUrl' => $nextEntry ? route('secretary.queue.call', ['clinic' => $activeClinicId, 'entry' => $nextEntry->id]) : null,
            'queueRows' => $queueRows,
        ]);
    }
}
