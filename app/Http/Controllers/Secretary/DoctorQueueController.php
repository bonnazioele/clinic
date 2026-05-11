<?php

namespace App\Http\Controllers\Secretary;

use App\Events\QueueUpdated;
use App\Http\Controllers\Concerns\InteractsWithActiveClinic;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PatientVisit;
use App\Models\QueueEntry;
use App\Models\Service;
use App\Notifications\AppointmentStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DoctorQueueController extends Controller
{
    use InteractsWithActiveClinic;

    public function show(Request $request, int $service_id, int $doctor_id)
    {
        $activeClinicId = $this->activeClinicId($request);
        $today = now()->toDateString();

        $service = Service::query()
            ->forClinics([$activeClinicId])
            ->where('id', $service_id)
            ->firstOrFail();

        $doctorSelect = [
            'users.id',
            'users.name',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.is_active',
        ];

        if (Schema::hasColumn('users', 'avatar_url')) {
            $doctorSelect[] = 'users.avatar_url';
        }

        $doctor = $service->doctors()
            ->wherePivot('clinic_id', $activeClinicId)
            ->where('users.id', $doctor_id)
            ->firstOrFail($doctorSelect);

        $queueEntries = $this->todayDoctorServiceQueueQuery($activeClinicId, $service, $doctor_id, $today)
            ->with([
                'appointment:id,user_id,doctor_id,service_id,appointment_date,appointment_time,status',
                'appointment.user:id,name,email,phone',
                'patient:id,patient_number,first_name,last_name,middle_name,email_address,mobile_number',
                'user:id,name,email,phone',
            ])
            ->whereIn('status', QueueEntry::activeLaneStatuses())
            ->orderByRaw("
                CASE
                    WHEN status = 'served' THEN 1
                    WHEN status IN ('in_progress', 'now_serving') THEN 2
                    WHEN status IN ('waiting', 'called') THEN 3
                    ELSE 4
                END
            ")
            ->orderByScheduledSlot()
            ->get();

        $visitsByPatient = PatientVisit::query()
            ->where('clinic_id', $activeClinicId)
            ->whereDate('date_of_visit', $today)
            ->where('requested_service', $service->name)
            ->whereIn('patient_id', $queueEntries->pluck('patient_id')->filter()->unique())
            ->latest('time_in')
            ->get()
            ->groupBy('patient_id');

        /*
        |--------------------------------------------------------------------------
        | Current patient logic
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | served must be treated as the current patient.
        |
        | Flow:
        | in_progress / now_serving -> doctor clicks Serve -> served
        | served -> secretary clicks Done and Next -> completed
        |
        */

        $nowServing = $queueEntries->first(function (QueueEntry $entry) {
            return in_array($entry->status, ['served', 'in_progress', 'now_serving'], true);
        });

        $nextEntry = $queueEntries
            ->whereIn('status', QueueEntry::nextCandidateStatuses())
            ->sortBy(fn (QueueEntry $entry) => sprintf(
                '%s %s %010d',
                $entry->scheduledSlotDateString() ?? '9999-12-31',
                $entry->scheduledSlotTimeString() ?? '99:99',
                $entry->queue_number
            ))
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
                'time' => $entry->formatted_scheduled_slot_time
                    ?? ($appointmentTime ? $appointmentTime->format('g:i A') : ($visit?->time_in?->format('g:i A') ?? $entry->created_at->format('g:i A'))),
                'status' => $entry->status_label,
                'status_key' => $entry->status,
                'is_priority' => $entry->isPriority(),
                'active' => in_array($entry->status, ['served', 'in_progress', 'now_serving'], true),
                'call_url' => route('secretary.queue.call', [
                    'clinic' => $entry->clinic_id,
                    'entry' => $entry->id,
                ]),
                'priority_url' => route('secretary.queue.priority', [
                    'clinic' => $entry->clinic_id,
                    'entry' => $entry->id,
                ]),
                'done_next_url' => route('secretary.queue.done_next', [
                    'clinic' => $entry->clinic_id,
                    'entry' => $entry->id,
                ]),
                'no_show_url' => route('secretary.queue.no_show', [
                    'clinic' => $entry->clinic_id,
                    'entry' => $entry->id,
                ]),
                'cancel_url' => route('secretary.queue.cancel', [
                    'clinic' => $entry->clinic_id,
                    'entry' => $entry->id,
                ]),
            ];
        })->values();

        $waitingCount = $queueEntries->whereIn('status', QueueEntry::nextCandidateStatuses())->count();
        $estimatedWaitMinutes = $waitingCount * 15;

        $nowServingDoneNextUrl = null;
        $nowServingNoShowUrl = null;

        if ($nowServing) {
            /*
             * Done and Next should ONLY be enabled when doctor already clicked Serve.
             * That means queue_entries.status must be served.
             */
            if ($nowServing->status === 'served') {
                $nowServingDoneNextUrl = route('secretary.queue.done_next', [
                    'clinic' => $activeClinicId,
                    'entry' => $nowServing->id,
                ]);
            }

            /*
             * No-show should only be available while the patient is still being called
             * or still with the doctor. Once served, secretary should use Done and Next.
             */
            if (in_array($nowServing->status, ['in_progress', 'now_serving'], true)) {
                $nowServingNoShowUrl = route('secretary.queue.no_show', [
                    'clinic' => $activeClinicId,
                    'entry' => $nowServing->id,
                ]);
            }
        }

        return view('secretary.queue.doctor', [
            'service' => $service,
            'serviceName' => $service->name,
            'doctor' => $doctor,
            'clinicId' => $activeClinicId,

            'nowServing' => $nowServing,
            'nowServingNumber' => $nowServing ? '#' . $nowServing->queue_number : null,
            'nowServingName' => $nowServing?->display_name,
            'nowServingType' => $nowServing ? ($nowServing->is_walk_in ? 'Walk-in' : 'Appointment') : null,
            'nowServingDoneNextUrl' => $nowServingDoneNextUrl,
            'nowServingNoShowUrl' => $nowServingNoShowUrl,

            'queueStatusCount' => $waitingCount,
            'queueStatusEta' => $estimatedWaitMinutes > 0 ? $estimatedWaitMinutes . ' mins' : 'No wait',

            'queueNextNumber' => $nextEntry ? '#' . $nextEntry->queue_number : null,
            'queueNextName' => $nextEntry?->display_name,
            'queueNextType' => $nextEntry ? ($nextEntry->is_walk_in ? 'Walk-in' : 'Appointment') : null,
            'queueNextCallUrl' => $nextEntry ? route('secretary.queue.call', [
                'clinic' => $activeClinicId,
                'entry' => $nextEntry->id,
            ]) : null,

            'queueRows' => $queueRows,
        ]);
    }

    public function cancelToday(Request $request, int $service_id, int $doctor_id)
    {
        $activeClinicId = $this->activeClinicId($request);
        $today = now()->toDateString();

        $service = Service::query()
            ->forClinics([$activeClinicId])
            ->where('id', $service_id)
            ->firstOrFail();

        $service->doctors()
            ->wherePivot('clinic_id', $activeClinicId)
            ->where('users.id', $doctor_id)
            ->firstOrFail();

        $cancelledEntries = collect();
        $cancelledAppointments = collect();

        DB::transaction(function () use (
            $activeClinicId,
            $service,
            $doctor_id,
            $today,
            &$cancelledEntries,
            &$cancelledAppointments
        ) {
            $entries = $this->todayDoctorServiceQueueQuery($activeClinicId, $service, $doctor_id, $today)
                ->with(['appointment.user', 'patient'])
                ->whereIn('status', QueueEntry::activeLaneStatuses())
                ->lockForUpdate()
                ->get();

            if ($entries->isEmpty()) {
                return;
            }

            $entryIds = $entries->pluck('id');
            $appointmentIds = $entries->pluck('appointment_id')->filter()->unique()->values();

            $walkInPatientIds = $entries
                ->filter(fn (QueueEntry $entry) => $entry->is_walk_in)
                ->pluck('patient_id')
                ->filter()
                ->unique()
                ->values();

            QueueEntry::query()
                ->whereIn('id', $entryIds)
                ->update([
                    'status' => 'cancelled',
                ]);

            if ($appointmentIds->isNotEmpty()) {
                Appointment::query()
                    ->whereIn('id', $appointmentIds)
                    ->whereNotIn('status', Appointment::FINAL_STATUSES)
                    ->update([
                        'status' => 'cancelled',
                    ]);

                $cancelledAppointments = Appointment::query()
                    ->with('user')
                    ->whereIn('id', $appointmentIds)
                    ->where('status', 'cancelled')
                    ->get();
            }

            if ($walkInPatientIds->isNotEmpty()) {
                PatientVisit::query()
                    ->where('clinic_id', $activeClinicId)
                    ->whereDate('date_of_visit', $today)
                    ->where('requested_service', $service->name)
                    ->whereIn('patient_id', $walkInPatientIds)
                    ->whereIn('status', ['Registered', 'In Progress'])
                    ->update([
                        'status' => 'Cancelled',
                        'time_out' => DB::raw('COALESCE(time_out, CURRENT_TIMESTAMP)'),
                    ]);
            }

            $cancelledEntries = QueueEntry::query()
                ->with(['appointment.user', 'patient', 'clinic'])
                ->whereIn('id', $entryIds)
                ->get();
        });

        foreach ($cancelledAppointments as $appointment) {
            if ($appointment->user) {
                $appointment->user->notify(new AppointmentStatusChanged($appointment));
            }
        }

        foreach ($cancelledEntries as $entry) {
            event(new QueueUpdated($entry, 'cancelled'));
        }

        $count = $cancelledEntries->count();

        $message = $count === 1
            ? 'Cancelled 1 queue entry for today.'
            : "Cancelled {$count} queue entries for today.";

        return back()->with(
            $count > 0 ? 'status' : 'warning',
            $count > 0 ? $message : 'No active queue entries were available to cancel.'
        );
    }

    private function todayDoctorServiceQueueQuery(int $activeClinicId, Service $service, int $doctorId, string $today)
    {
        return QueueEntry::query()
            ->where('clinic_id', $activeClinicId)
            ->forDoctor($doctorId)
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
            });
    }
}
