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

    public function overview(Request $request)
    {
        $activeClinicId = $this->activeClinicId($request);

        $clinics = Clinic::whereKey($activeClinicId)
            ->with([
                'queueEntries' => function ($q) {
                    $q->whereIn('status', ['waiting', 'now_serving', 'called'])
                        ->orderBy('queue_number')
                        ->with(['user', 'patient']);
                },
            ])
            ->withCount([
                'queueEntries as waiting_count' => function ($q) {
        $q->whereIn('status', ['waiting', 'now_serving', 'called']);
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

        return view('secretary.queue.overview', compact('clinics', 'totalWaiting', 'totalServedToday'));
    }

    public function queue(Request $request, Clinic $clinic)
    {
        $activeClinicId = $this->assertRouteClinicMatchesActive($request, $clinic);

        $waitingQuery = QueueEntry::with(['user', 'patient', 'appointment.service'])
            ->where('clinic_id', $activeClinicId)
            ->where('status', 'waiting');

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
    ")->orderBy('queue_number');
}
        $waiting = $waitingQuery->get();

        return view('secretary.queue.index', compact('clinic', 'waiting'));
    }

    public function call(Request $request, Clinic $clinic, QueueEntry $entry)
    {
        $activeClinicId = $this->assertRouteClinicMatchesActive($request, $clinic);
        $this->assertEntryBelongsToActiveClinic($entry, $activeClinicId);

        DB::transaction(function () use ($entry, $activeClinicId) {
            $fresh = QueueEntry::query()
                ->where('clinic_id', $activeClinicId)
                ->whereKey($entry->id)
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                return;
            }

            if ($fresh->status === 'served') {
                return;
            }

            if (!in_array($fresh->status, ['waiting', 'called', 'rescheduled', 'now_serving'])) {
                return;
            }

            if ($fresh->status !== 'now_serving') {
                $fresh->update(['status' => 'now_serving']);

                if ($fresh->user) {
                    $fresh->user->notify(new QueueNotification($fresh));
                }
            }

            event(new QueueUpdated($fresh->fresh(), 'now_serving'));
        });

        return back()->with('status', 'Now serving queue entry #' . $entry->queue_number . '.');
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

        $result = QueueEntry::completeNowServingAndPromoteNext(
            $activeClinicId,
            (int) $entry->id,
            now()->toDateString()
        );

        if (($result['result'] ?? '') === 'invalid') {
            if ($redirectToDashboard) {
                return redirect()->route('secretary.dashboard', $dashboardQuery)->with('error', 'Queue entry is invalid for this clinic.');
            }

            return back()->with('error', 'Queue entry is invalid for this clinic.');
        }

        if (($result['result'] ?? '') === 'noop') {
            if ($redirectToDashboard) {
                return redirect()->route('secretary.dashboard', $dashboardQuery)->with('status', 'Queue entry is no longer in now serving state.');
            }

            return back()->with('status', 'Queue entry is no longer in now serving state.');
        }

        if (($result['result'] ?? '') === 'served_and_promoted' && isset($result['next'])) {
            if ($redirectToDashboard) {
                return redirect()->route('secretary.dashboard', $dashboardQuery)->with('status', 'Completed #' . $entry->queue_number . ' and moved #' . $result['next']->queue_number . ' to now serving.');
            }

            return back()->with('status', 'Completed #' . $entry->queue_number . ' and moved #' . $result['next']->queue_number . ' to now serving.');
        }

        if ($redirectToDashboard) {
            return redirect()->route('secretary.dashboard', $dashboardQuery)->with('status', 'Completed queue entry #' . $entry->queue_number . '. No next patient to promote.');
        }

        return back()->with('status', 'Completed queue entry #' . $entry->queue_number . '. No next patient to promote.');
    }

    public function reschedule(Request $request, Clinic $clinic, QueueEntry $entry)
    {
        $activeClinicId = $this->assertRouteClinicMatchesActive($request, $clinic);
        $this->assertEntryBelongsToActiveClinic($entry, $activeClinicId);

        $data = $request->validate([
            'new_date' => 'required|date',
            'new_time' => 'required',
        ]);

        DB::transaction(function () use ($entry, $data, $activeClinicId) {
            $fresh = QueueEntry::query()
                ->where('clinic_id', $activeClinicId)
                ->whereKey($entry->id)
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
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

                $fresh->update(['status' => 'rescheduled']);
            } else {
                $fresh->update(['status' => 'rescheduled']);
                if ($fresh->user) {
                    $fresh->user->notify(new QueueNotification($fresh));
                }
            }
        });

        event(new QueueUpdated($entry->fresh(), 'rescheduled'));

        return back()->with('status', "Queue #{$entry->queue_number} rescheduled.");
    }

    public function cancel(Request $request, Clinic $clinic, QueueEntry $entry)
    {
        $activeClinicId = $this->assertRouteClinicMatchesActive($request, $clinic);
        $this->assertEntryBelongsToActiveClinic($entry, $activeClinicId);

        if (!in_array($entry->status, ['waiting', 'called', 'rescheduled', 'now_serving'])) {
            return back()->with('error', 'Only waiting/called/now serving/rescheduled entries can be cancelled.');
        }

        DB::transaction(function () use ($entry, $activeClinicId) {
            $fresh = QueueEntry::query()
                ->where('clinic_id', $activeClinicId)
                ->whereKey($entry->id)
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                return;
            }

            $fresh->update(['status' => 'cancelled']);

            if ($fresh->appointment) {
                $appointment = $fresh->appointment;

                if ($appointment->status !== 'completed') {
                    $appointment->update(['status' => 'cancelled']);
                    if ($appointment->user) {
                        $appointment->user->notify(new AppointmentStatusChanged($appointment));
                    }
                }
            }
        });

        event(new QueueUpdated($entry->fresh(), 'cancelled'));

        return back()->with('status', "Cancelled queue #{$entry->queue_number}.");
    }

    public function noShow(Request $request, Clinic $clinic, QueueEntry $entry)
    {
        $activeClinicId = $this->assertRouteClinicMatchesActive($request, $clinic);
        $this->assertEntryBelongsToActiveClinic($entry, $activeClinicId);

        if (!in_array($entry->status, ['waiting', 'called', 'rescheduled', 'now_serving'])) {
            return back()->with('error', 'Only waiting/called/now serving/rescheduled entries can be marked no-show.');
        }

        DB::transaction(function () use ($entry, $activeClinicId) {
            $fresh = QueueEntry::query()
                ->where('clinic_id', $activeClinicId)
                ->whereKey($entry->id)
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                return;
            }

            $fresh->update(['status' => 'no_show']);
            if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
                if ($fresh->appointment->status !== 'no_show') {
                    $fresh->appointment->update(['status' => 'no_show']);
                    if ($fresh->appointment->user) {
                        $fresh->appointment->user->notify(new AppointmentStatusChanged($fresh->appointment));
                    }
                }
            }
        });

        event(new QueueUpdated($entry->fresh(), 'no_show'));

        return back()->with('status', "Marked queue #{$entry->queue_number} as no-show.");
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
}

