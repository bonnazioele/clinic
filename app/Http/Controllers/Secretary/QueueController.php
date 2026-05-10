<?php

namespace App\Http\Controllers\Secretary;

use App\Events\QueueUpdated;
use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\PatientVisit;
use App\Models\QueueEntry;
use App\Notifications\AppointmentStatusChanged;
use App\Notifications\QueueActionNotification;
use App\Services\MoceanSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueController extends Controller
{
    use InteractsWithClinic;

    private array $activeQueueStatuses = [
        'waiting',
        'in_progress',
        'served',
    ];

    private array $modifiableStatuses = [
        'waiting',
        'in_progress',
        'served',
    ];

    public function overview(Request $request)
    {
        $activeClinicId = $this->activeClinicId($request);

        $clinics = Clinic::whereKey($activeClinicId)
            ->with([
                'queueEntries' => function ($q) {
                    $q->whereIn('status', $this->activeQueueStatuses)
                        ->orderByScheduledSlot()
                        ->with(['user', 'patient']);
                },
            ])
            ->withCount([
                'queueEntries as waiting_count' => function ($q) {
                    $q->whereIn('status', $this->activeQueueStatuses);
                },
            ])
            ->get();

        $totalWaiting = QueueEntry::where('clinic_id', $activeClinicId)
            ->where('status', 'waiting')
            ->count();

        $totalServedToday = QueueEntry::where('clinic_id', $activeClinicId)
            ->where('status', 'completed')
            ->whereDate('service_ended_at', today())
            ->count();

        return view('secretary.queue.overview', compact(
            'clinics',
            'totalWaiting',
            'totalServedToday'
        ));
    }

    public function queue(Request $request, Clinic $clinic)
    {
        $activeClinicId = $this->assertRouteClinicMatchesActive($request, $clinic);

        $waiting = QueueEntry::with(['user', 'patient', 'appointment.service'])
            ->where('clinic_id', $activeClinicId)
            ->whereIn('status', $this->activeQueueStatuses)
            ->orderByScheduledSlot()
            ->get();

        return view('secretary.queue.index', compact('clinic', 'waiting'));
    }

    public function call(Request $request, Clinic $clinic, QueueEntry $entry, MoceanSmsService $sms)
{
    $activeClinicId = $this->assertRouteClinicMatchesActive($request, $clinic);
    $this->assertEntryBelongsToActiveClinic($entry, $activeClinicId);

    $message = null;
    $messageType = 'status';
    $updatedEntry = null;
    $smsEntry = null;

    DB::transaction(function () use ($entry, $activeClinicId, &$message, &$messageType, &$updatedEntry, &$smsEntry) {
        $fresh = QueueEntry::query()
            ->where('clinic_id', $activeClinicId)
            ->whereKey($entry->id)
            ->lockForUpdate()
            ->first();

        if (! $fresh) {
            $messageType = 'error';
            $message = 'Queue entry is invalid for this clinic.';
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | If already in_progress
        |--------------------------------------------------------------------------
        | This means the patient was already called before.
        | We still set smsEntry so the SMS can be tested/sent again.
        */
        if ($fresh->status === 'in_progress') {
            $updatedEntry = $fresh->fresh(['appointment.user', 'patient.user', 'clinic', 'user']);
            $smsEntry = $updatedEntry;

            $message = "Queue #{$fresh->queue_number} is already in progress. SMS reminder sent.";
            return;
        }

        if ($fresh->status !== 'waiting') {
            $messageType = 'error';
            $message = $this->invalidActionMessage($fresh->status, 'started');
            return;
        }

        $fresh->update([
            'status' => 'in_progress',
            'service_started_at' => $fresh->service_started_at ?? now(),
        ]);

        $this->markRelatedRecordInProgress($fresh);

        $updatedEntry = $fresh->fresh(['appointment.user', 'patient.user', 'clinic', 'user']);
        $smsEntry = $updatedEntry;

        $this->notifyQueueAction($updatedEntry, 'start');

        $message = "Queue #{$fresh->queue_number} is now in progress.";
    });

    if ($updatedEntry) {
        event(new QueueUpdated($updatedEntry, 'in_progress'));
    }

    Log::info('Mocean call SMS checkpoint', [
        'has_sms_entry' => (bool) $smsEntry,
        'message_type' => $messageType,
        'entry_id' => $smsEntry?->id,
        'queue_number' => $smsEntry?->queue_number,
        'status' => $smsEntry?->status,
    ]);

    if ($smsEntry && $messageType !== 'error') {
        $this->sendQueueSms(
            $sms,
            $smsEntry,
            "CliniQ: Your queue number {$smsEntry->queue_number} is now being called. Please proceed to the secretary desk."
        );
    }

    return back()->with($messageType, $message ?? 'Queue entry updated.');
}

    public function doneNext(Request $request, Clinic $clinic, QueueEntry $entry, MoceanSmsService $sms)
    {
        $activeClinicId = $this->assertRouteClinicMatchesActive($request, $clinic);
        $this->assertEntryBelongsToActiveClinic($entry, $activeClinicId);

        $laneId = trim((string) $request->input('lane', ''));
        $serviceTabId = trim((string) $request->input('service_tab', ''));
        $redirectToDashboard = $laneId !== '';
        $dashboardQuery = ['lane' => $laneId];

        if ($serviceTabId !== '') {
            $dashboardQuery['service_tab'] = $serviceTabId;
        }

        $message = null;
        $messageType = 'status';
        $completedEntry = null;
        $promotedEntry = null;

        DB::transaction(function () use (
            $entry,
            $activeClinicId,
            &$message,
            &$messageType,
            &$completedEntry,
            &$promotedEntry
        ) {
            $fresh = QueueEntry::query()
                ->where('clinic_id', $activeClinicId)
                ->whereKey($entry->id)
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                $messageType = 'error';
                $message = 'Queue entry is invalid for this clinic.';
                return;
            }

            if ($fresh->status === 'completed') {
                $messageType = 'error';
                $message = "Queue #{$fresh->queue_number} is already completed.";
                return;
            }

            if ($fresh->status !== 'served') {
                $messageType = 'error';
                $message = $fresh->status === 'in_progress'
                    ? "Queue #{$fresh->queue_number}: waiting for the doctor to complete the consultation first."
                    : $this->invalidActionMessage($fresh->status, 'completed');
                return;
            }

            $fresh->update([
                'status' => 'completed',
                'service_ended_at' => now(),
            ]);

            if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
                $fresh->appointment->update(['status' => 'completed']);

                if ($fresh->appointment->user) {
                    $fresh->appointment->user->notify(
                        new AppointmentStatusChanged($fresh->appointment)
                    );
                }
            }

            $completedEntry = $fresh->fresh(['appointment.user', 'patient.user', 'clinic', 'user']);
            $this->notifyQueueAction($completedEntry, 'done');

            $doctorId = (int) ($fresh->doctor_id ?: $fresh->appointment?->doctor_id ?: 0);

            $next = $doctorId > 0
                ? QueueEntry::query()
                    ->forLaneCandidates($activeClinicId, $doctorId, now()->toDateString())
                    ->whereKeyNot($fresh->id)
                    ->withStatuses(QueueEntry::nextCandidateStatuses())
                    ->orderByScheduledSlot()
                    ->lockForUpdate()
                    ->first()
                : null;

            if ($next) {
                $next->update([
                    'status' => 'in_progress',
                    'service_started_at' => $next->service_started_at ?? now(),
                ]);

                $this->markRelatedRecordInProgress($next);

                $promotedEntry = $next->fresh(['appointment.user', 'patient.user', 'clinic', 'user']);
                $this->notifyQueueAction($promotedEntry, 'start');

                $message = "Completed #{$fresh->queue_number} and moved #{$next->queue_number} to in progress.";
                return;
            }

            $message = "Completed queue #{$fresh->queue_number}. No more patients in queue.";
        });

        if ($completedEntry) {
            event(new QueueUpdated($completedEntry, 'completed'));
        }

        if ($promotedEntry) {
            event(new QueueUpdated($promotedEntry, 'in_progress'));

            $this->sendQueueSms(
                $sms,
                $promotedEntry,
                "CliniQ: Your queue number {$promotedEntry->queue_number} is now in progress. Please proceed to the secretary desk."
            );
        }

        if ($redirectToDashboard) {
            return redirect()
                ->route('secretary.dashboard', $dashboardQuery)
                ->with($messageType, $message ?? 'Queue entry updated.');
        }

        return back()->with($messageType, $message ?? 'Queue entry updated.');
    }

    public function reschedule(Request $request, Clinic $clinic, QueueEntry $entry, MoceanSmsService $sms)
    {
        $activeClinicId = $this->assertRouteClinicMatchesActive($request, $clinic);
        $this->assertEntryBelongsToActiveClinic($entry, $activeClinicId);

        $data = $request->validate([
            'new_date' => 'required|date',
            'new_time' => 'required',
        ]);

        $message = null;
        $messageType = 'status';
        $updatedEntry = null;

        DB::transaction(function () use ($entry, $data, $activeClinicId, &$message, &$messageType, &$updatedEntry) {
            $fresh = QueueEntry::query()
                ->where('clinic_id', $activeClinicId)
                ->whereKey($entry->id)
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                $messageType = 'error';
                $message = 'Queue entry is invalid for this clinic.';
                return;
            }

            if (! in_array($fresh->status, $this->modifiableStatuses, true)) {
                $messageType = 'error';
                $message = $this->invalidActionMessage($fresh->status, 'rescheduled');
                return;
            }

            if ($fresh->appointment) {
                $fresh->appointment->update([
                    'appointment_date' => $data['new_date'],
                    'appointment_time' => $data['new_time'],
                    'status' => 'rescheduled',
                ]);

                if ($fresh->appointment->user) {
                    $fresh->appointment->user->notify(
                        new AppointmentStatusChanged($fresh->appointment)
                    );
                }
            }

            $fresh->update(['status' => 'rescheduled']);

            $updatedEntry = $fresh->fresh(['appointment.user', 'patient.user', 'clinic', 'user']);
            $message = "Queue #{$fresh->queue_number} rescheduled.";
        });

        if ($updatedEntry) {
            event(new QueueUpdated($updatedEntry, 'rescheduled'));

            $this->sendQueueSms(
                $sms,
                $updatedEntry,
                "CliniQ: Your appointment has been rescheduled to {$data['new_date']} {$data['new_time']}. Please check your dashboard for details."
            );
        }

        return back()->with($messageType, $message ?? 'Queue entry updated.');
    }

    public function cancel(Request $request, Clinic $clinic, QueueEntry $entry, MoceanSmsService $sms)
    {
        $activeClinicId = $this->assertRouteClinicMatchesActive($request, $clinic);
        $this->assertEntryBelongsToActiveClinic($entry, $activeClinicId);

        $message = null;
        $messageType = 'status';
        $updatedEntry = null;

        DB::transaction(function () use ($entry, $activeClinicId, &$message, &$messageType, &$updatedEntry) {
            $fresh = QueueEntry::query()
                ->where('clinic_id', $activeClinicId)
                ->whereKey($entry->id)
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                $messageType = 'error';
                $message = 'Queue entry is invalid for this clinic.';
                return;
            }

            if (! in_array($fresh->status, $this->modifiableStatuses, true)) {
                $messageType = 'error';
                $message = $this->invalidActionMessage($fresh->status, 'cancelled');
                return;
            }

            $fresh->update(['status' => 'cancelled']);

            if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
                $fresh->appointment->update(['status' => 'cancelled']);

                if ($fresh->appointment->user) {
                    $fresh->appointment->user->notify(
                        new AppointmentStatusChanged($fresh->appointment)
                    );
                }
            }

            $updatedEntry = $fresh->fresh(['appointment.user', 'patient.user', 'clinic', 'user']);
            $message = "Cancelled queue #{$fresh->queue_number}.";
        });

        if ($updatedEntry) {
            event(new QueueUpdated($updatedEntry, 'cancelled'));

            $this->sendQueueSms(
                $sms,
                $updatedEntry,
                "CliniQ: Your queue or appointment has been cancelled. Please check your dashboard for details."
            );
        }

        return back()->with($messageType, $message ?? 'Queue entry updated.');
    }

    public function noShow(Request $request, Clinic $clinic, QueueEntry $entry, MoceanSmsService $sms)
    {
        $activeClinicId = $this->assertRouteClinicMatchesActive($request, $clinic);
        $this->assertEntryBelongsToActiveClinic($entry, $activeClinicId);

        $message = null;
        $messageType = 'status';
        $updatedEntry = null;
        $promotedEntry = null;

        DB::transaction(function () use ($entry, $activeClinicId, &$message, &$messageType, &$updatedEntry, &$promotedEntry) {
            $fresh = QueueEntry::query()
                ->where('clinic_id', $activeClinicId)
                ->whereKey($entry->id)
                ->with(['appointment'])
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                $messageType = 'error';
                $message = 'Queue entry is invalid for this clinic.';
                return;
            }

            if (! in_array($fresh->status, $this->modifiableStatuses, true)) {
                $messageType = 'error';
                $message = $this->invalidActionMessage($fresh->status, 'marked as no-show');
                return;
            }

            $shouldPromoteNext = $fresh->status === 'in_progress';
            $doctorId = (int) ($fresh->doctor_id ?: $fresh->appointment?->doctor_id ?: 0);

            $fresh->update(['status' => 'no_show']);

            if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
                if ($fresh->appointment->status !== 'no_show') {
                    $fresh->appointment->update(['status' => 'no_show']);

                    if ($fresh->appointment->user) {
                        $fresh->appointment->user->notify(
                            new AppointmentStatusChanged($fresh->appointment)
                        );
                    }
                }
            }

            $updatedEntry = $fresh->fresh(['appointment.user', 'patient.user', 'clinic', 'user']);

            $next = $shouldPromoteNext && $doctorId > 0
                ? QueueEntry::query()
                    ->forLaneCandidates($activeClinicId, $doctorId, now()->toDateString())
                    ->whereKeyNot($fresh->id)
                    ->withStatuses(QueueEntry::nextCandidateStatuses())
                    ->orderByScheduledSlot()
                    ->lockForUpdate()
                    ->first()
                : null;

            if ($next) {
                $next->update([
                    'status' => 'in_progress',
                    'service_started_at' => $next->service_started_at ?? now(),
                ]);

                $this->markRelatedRecordInProgress($next);

                $promotedEntry = $next->fresh(['appointment.user', 'patient.user', 'clinic', 'user']);
                $this->notifyQueueAction($promotedEntry, 'start');

                $message = "Marked #{$fresh->queue_number} as no-show and moved #{$next->queue_number} to in progress.";
                return;
            }

            $message = "Marked queue #{$fresh->queue_number} as no-show.";
        });

        if ($updatedEntry) {
            event(new QueueUpdated($updatedEntry, 'no_show'));

            $this->sendQueueSms(
                $sms,
                $updatedEntry,
                "CliniQ: You have been marked as no-show for your queue or appointment. Please contact the clinic if this is a mistake."
            );
        }

        if ($promotedEntry) {
            event(new QueueUpdated($promotedEntry, 'in_progress'));

            $this->sendQueueSms(
                $sms,
                $promotedEntry,
                "CliniQ: Your queue number {$promotedEntry->queue_number} is now in progress. Please proceed to the secretary desk."
            );
        }

        return back()->with($messageType, $message ?? 'Queue entry updated.');
    }

    private function assertRouteClinicMatchesActive(Request $request, Clinic $clinic): int
    {
        $activeClinicId = $this->activeClinicId($request);

        if ((int) $clinic->id !== $activeClinicId) {
            abort(403, 'Route clinic does not match your active clinic.');
        }

        return $activeClinicId;
    }

    private function assertEntryBelongsToActiveClinic(QueueEntry $entry, int $activeClinicId): void
    {
        if ((int) $entry->clinic_id !== $activeClinicId) {
            abort(403, 'Queue entry does not belong to your active clinic.');
        }
    }

    private function markRelatedRecordInProgress(QueueEntry $entry): void
    {
        $entry->loadMissing(['appointment', 'patient']);

        if (
            $entry->appointment
            && ! in_array($entry->appointment->status, ['in_progress', 'completed', 'cancelled', 'no_show', 'rescheduled'], true)
        ) {
            $entry->appointment->update(['status' => 'in_progress']);
        }

        if ($entry->patient_id) {
            $visit = PatientVisit::query()
                ->where('clinic_id', $entry->clinic_id)
                ->where('patient_id', $entry->patient_id)
                ->whereDate('date_of_visit', $entry->scheduledSlotDateString() ?? now()->toDateString())
                ->where('status', 'Registered')
                ->latest('time_in')
                ->first();

            $visit?->update(['status' => 'In Progress']);
        }
    }

    private function notifyQueueAction(QueueEntry $entry, string $action): void
    {
        $entry->loadMissing(['appointment.user', 'patient.user', 'clinic', 'user']);

        $recipient = $entry->user
            ?: $entry->appointment?->user
            ?: $entry->patient?->user;

        if ($recipient) {
            $recipient->notify(new QueueActionNotification($entry, $action));
        }
    }

    private function sendQueueSms(MoceanSmsService $sms, QueueEntry $entry, string $message): void
    {
        Log::info('Mocean sendQueueSms reached', [
            'entry_id' => $entry->id,
            'queue_number' => $entry->queue_number,
            'status' => $entry->status,
        ]);

        $entry->loadMissing(['appointment.user', 'patient.user', 'user']);

        $recipient = $entry->user
            ?: $entry->appointment?->user
            ?: $entry->patient?->user;

        $phone = $this->extractPhoneNumber($recipient)
            ?: $this->extractPhoneNumber($entry->patient);

        Log::info('Mocean sendQueueSms phone lookup', [
            'entry_id' => $entry->id,
            'has_recipient' => (bool) $recipient,
            'recipient_id' => $recipient?->id,
            'phone_found' => (bool) $phone,
            'phone_preview' => $phone ? substr($phone, 0, 4) . '****' . substr($phone, -2) : null,
        ]);

        $sms->send($phone, $message);
    }

    private function extractPhoneNumber($model): ?string
    {
        if (! $model) {
            return null;
        }

        foreach ([
            'phone',
            'phone_number',
            'mobile',
            'mobile_number',
            'contact_number',
            'contact',
            'cellphone',
            'cellphone_number',
        ] as $field) {
            if (isset($model->{$field}) && filled($model->{$field})) {
                return (string) $model->{$field};
            }
        }

        return null;
    }

    private function invalidActionMessage(?string $status, string $action): string
    {
        $status = $status ?: 'unknown';

        return match ($status) {
            'waiting' => "Queue is still waiting. Call the patient first before it can be {$action}.",
            'in_progress' => "Queue is in progress. Wait for the doctor to complete before it can be {$action}.",
            'served' => "Queue is served. Use Done & Next to complete it.",
            'completed' => "Queue is already completed and cannot be {$action} again.",
            'cancelled' => "Queue is cancelled and cannot be {$action}.",
            'rescheduled' => "Queue is rescheduled and cannot be {$action}.",
            'no_show' => "Queue is marked as no-show and cannot be {$action}.",
            default => "Queue with status '{$status}' cannot be {$action}.",
        };
    }
}