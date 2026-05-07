<?php

namespace App\Http\Controllers\Secretary;

use App\Events\QueueUpdated;
use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\QueueEntry;
use App\Notifications\AppointmentStatusChanged;
use App\Notifications\QueueNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    use InteractsWithClinic;

    private array $activeQueueStatuses = [
        'waiting',
        'called',
        'now_serving',
    ];

    private array $callableStatuses = [
        'waiting',
        'called',
    ];

    private array $completableStatuses = [
        'now_serving',
    ];

    private array $modifiableStatuses = [
        'waiting',
        'called',
        'now_serving',
    ];

    private array $terminalStatuses = [
        'served',
        'cancelled',
        'rescheduled',
        'no_show',
    ];

    public function overview(Request $request)
    {
        $activeClinicId = $this->activeClinicId($request);

        $clinics = Clinic::whereKey($activeClinicId)
            ->with([
                'queueEntries' => function ($q) {
                    $q->whereIn('status', $this->activeQueueStatuses)
                        ->orderBy('queue_number')
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
            ->where('status', 'served')
            ->whereDate('served_at', today())
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

        $waitingQuery = QueueEntry::with(['user', 'patient', 'appointment.service'])
            ->where('clinic_id', $activeClinicId)
            ->whereIn('status', $this->activeQueueStatuses);

        if ($clinic->queueModeIs('priority')) {
            $waitingQuery->leftJoin('appointments', 'queue_entries.appointment_id', '=', 'appointments.id')
                ->select('queue_entries.*')
                ->orderByRaw("
                    CASE
                        WHEN queue_entries.status = 'now_serving' THEN 0
                        WHEN queue_entries.status = 'called' THEN 1
                        WHEN queue_entries.status = 'waiting' THEN 2
                        ELSE 3
                    END
                ")
                ->orderByRaw('appointments.appointment_date IS NULL')
                ->orderBy('appointments.appointment_date')
                ->orderBy('appointments.appointment_time')
                ->orderBy('queue_number');
        } else {
            $waitingQuery->orderByRaw("
                CASE
                    WHEN status = 'now_serving' THEN 0
                    WHEN status = 'called' THEN 1
                    WHEN status = 'waiting' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('queue_number');
        }

        $waiting = $waitingQuery->get();

        return view('secretary.queue.index', compact('clinic', 'waiting'));
    }

    public function call(Request $request, Clinic $clinic, QueueEntry $entry)
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

            if ($fresh->status === 'now_serving') {
                $message = "Queue #{$fresh->queue_number} is already now serving.";
                $updatedEntry = $fresh;
                return;
            }

            if (! in_array($fresh->status, $this->callableStatuses, true)) {
                $messageType = 'error';
                $message = $this->invalidActionMessage($fresh->status, 'called');
                return;
            }

            $now = now();

            $fresh->update([
                'status' => 'now_serving',
                'service_started_at' => $fresh->service_started_at ?? $now,
            ]);

            if ($fresh->user) {
                $fresh->user->notify(new QueueNotification($fresh));
            }

            $updatedEntry = $fresh->fresh();
            $message = "Now serving queue #{$fresh->queue_number}.";
        });

        if ($updatedEntry) {
            event(new QueueUpdated($updatedEntry, 'now_serving'));
        }

        return back()->with($messageType, $message ?? 'Queue entry updated.');
    }

    public function doneNext(Request $request, Clinic $clinic, QueueEntry $entry)
    {
        $activeClinicId = $this->assertRouteClinicMatchesActive($request, $clinic);
        $this->assertEntryBelongsToActiveClinic($entry, $activeClinicId);

        $laneId = trim((string) $request->input('lane', ''));
        $serviceTabId = trim((string) $request->input('service_tab', ''));
        $redirectToDashboard = $laneId !== '';

        $dashboardQuery = [
            'lane' => $laneId,
        ];

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

            if ($fresh->status === 'served') {
                $messageType = 'error';
                $message = "Queue #{$fresh->queue_number} is already completed.";
                return;
            }

            if (! in_array($fresh->status, $this->completableStatuses, true)) {
                $messageType = 'error';
                $message = $this->invalidActionMessage($fresh->status, 'completed');
                return;
            }

            $now = now();

            $fresh->update([
                'status' => 'served',
                'served_at' => $now,
                'service_ended_at' => $now,
            ]);

            if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
                $fresh->appointment->update([
                    'status' => 'completed',
                ]);

                if ($fresh->appointment->user) {
                    $fresh->appointment->user->notify(new AppointmentStatusChanged($fresh->appointment));
                }
            }

            $completedEntry = $fresh->fresh();

            $next = QueueEntry::query()
                ->where('clinic_id', $activeClinicId)
                ->where('status', 'waiting')
                ->whereDate('created_at', now()->toDateString())
                ->whereKeyNot($fresh->id)
                ->orderBy('queue_number')
                ->lockForUpdate()
                ->first();

            if ($next) {
                $nextStartedAt = now();

                $next->update([
                    'status' => 'now_serving',
                    'service_started_at' => $next->service_started_at ?? $nextStartedAt,
                ]);

                if ($next->user) {
                    $next->user->notify(new QueueNotification($next));
                }

                $promotedEntry = $next->fresh();

                $message = "Completed #{$fresh->queue_number} and moved #{$next->queue_number} to now serving.";
                return;
            }

            $message = "Completed queue #{$fresh->queue_number}. No next patient to promote.";
        });

        if ($completedEntry) {
            event(new QueueUpdated($completedEntry, 'served'));
        }

        if ($promotedEntry) {
            event(new QueueUpdated($promotedEntry, 'now_serving'));
        }

        if ($redirectToDashboard) {
            return redirect()
                ->route('secretary.dashboard', $dashboardQuery)
                ->with($messageType, $message ?? 'Queue entry updated.');
        }

        return back()->with($messageType, $message ?? 'Queue entry updated.');
    }

    public function reschedule(Request $request, Clinic $clinic, QueueEntry $entry)
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
                $appointment = $fresh->appointment;

                $appointment->update([
                    'appointment_date' => $data['new_date'],
                    'appointment_time' => $data['new_time'],
                    'status' => 'rescheduled',
                ]);

                if ($appointment->user) {
                    $appointment->user->notify(new AppointmentStatusChanged($appointment));
                }
            }

            $fresh->update([
                'status' => 'rescheduled',
            ]);

            if (! $fresh->appointment && $fresh->user) {
                $fresh->user->notify(new QueueNotification($fresh));
            }

            $updatedEntry = $fresh->fresh();
            $message = "Queue #{$fresh->queue_number} rescheduled.";
        });

        if ($updatedEntry) {
            event(new QueueUpdated($updatedEntry, 'rescheduled'));
        }

        return back()->with($messageType, $message ?? 'Queue entry updated.');
    }

    public function cancel(Request $request, Clinic $clinic, QueueEntry $entry)
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

            $fresh->update([
                'status' => 'cancelled',
            ]);

            if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
                $appointment = $fresh->appointment;

                $appointment->update([
                    'status' => 'cancelled',
                ]);

                if ($appointment->user) {
                    $appointment->user->notify(new AppointmentStatusChanged($appointment));
                }
            }

            $updatedEntry = $fresh->fresh();
            $message = "Cancelled queue #{$fresh->queue_number}.";
        });

        if ($updatedEntry) {
            event(new QueueUpdated($updatedEntry, 'cancelled'));
        }

        return back()->with($messageType, $message ?? 'Queue entry updated.');
    }

    public function noShow(Request $request, Clinic $clinic, QueueEntry $entry)
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
                $message = $this->invalidActionMessage($fresh->status, 'marked as no-show');
                return;
            }

            $fresh->update([
                'status' => 'no_show',
            ]);

            if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
                $appointment = $fresh->appointment;

                if ($appointment->status !== 'no_show') {
                    $appointment->update([
                        'status' => 'no_show',
                    ]);

                    if ($appointment->user) {
                        $appointment->user->notify(new AppointmentStatusChanged($appointment));
                    }
                }
            }

            $updatedEntry = $fresh->fresh();
            $message = "Marked queue #{$fresh->queue_number} as no-show.";
        });

        if ($updatedEntry) {
            event(new QueueUpdated($updatedEntry, 'no_show'));
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

    private function invalidActionMessage(?string $status, string $action): string
    {
        $status = $status ?: 'unknown';

        return match ($status) {
            'waiting' => "Queue is still waiting. Call the patient first before it can be {$action}.",
            'called' => "Queue is only called. Move it to now serving first before it can be {$action}.",
            'served' => "Queue is already completed and cannot be {$action} again.",
            'cancelled' => "Queue is cancelled and cannot be {$action}.",
            'rescheduled' => "Queue is rescheduled and cannot be {$action}.",
            'no_show' => "Queue is marked as no-show and cannot be {$action}.",
            default => "Queue with status '{$status}' cannot be {$action}.",
        };
    }
}