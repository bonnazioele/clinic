<?php

namespace App\Http\Controllers\Secretary;

use App\Events\QueueUpdated;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\QueueEntry;
use App\Notifications\AppointmentStatusChanged;
use App\Notifications\QueueNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function overview()
    {
        $user = auth()->user();
        $clinicIds = $user->secretaryClinics()->pluck('clinics.id');

        $clinics = Clinic::whereIn('id', $clinicIds)
            ->with([
                'queueEntries' => function ($q) {
                    $q->where('status', 'waiting')->orderBy('queue_number');
                },
            ])
            ->withCount([
                'queueEntries as waiting_count' => function ($q) {
                    $q->where('status', 'waiting');
                },
            ])
            ->get();

        $totalWaiting = QueueEntry::whereIn('clinic_id', $clinicIds)
            ->where('status', 'waiting')
            ->count();
        $totalServedToday = QueueEntry::whereIn('clinic_id', $clinicIds)
            ->where('status', 'served')
            ->whereDate('served_at', today())
            ->count();

        return view('secretary.queue.overview', compact('clinics', 'totalWaiting', 'totalServedToday'));
    }

    public function queue(Clinic $clinic)
    {
        if (!auth()->user()->secretaryClinics()->where('clinics.id', $clinic->id)->exists()) {
            abort(403, 'Not assigned to this clinic');
        }

        $waitingQuery = QueueEntry::with(['user', 'appointment.service'])
            ->where('clinic_id', $clinic->id)
            ->where('status', 'waiting');

        if ($clinic->queueModeIs('priority')) {
            $waitingQuery->leftJoin('appointments', 'queue_entries.appointment_id', '=', 'appointments.id')
                ->select('queue_entries.*')
                ->orderByRaw('appointments.appointment_date IS NULL')
                ->orderBy('appointments.appointment_date')
                ->orderBy('appointments.appointment_time')
                ->orderBy('queue_number');
        } else {
            $waitingQuery->orderBy('queue_number');
        }
        $waiting = $waitingQuery->get();

        return view('secretary.queue.index', compact('clinic', 'waiting'));
    }

    public function call(Clinic $clinic, QueueEntry $entry)
    {
        if (!auth()->user()->secretaryClinics()->where('clinics.id', $clinic->id)->exists()) {
            abort(403, 'Not assigned to this clinic');
        }
        if ($entry->clinic_id !== $clinic->id) {
            abort(403, 'Queue entry does not belong to this clinic.');
        }

        DB::transaction(function () use ($entry) {
            $fresh = QueueEntry::lockForUpdate()->find($entry->id);

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

    public function reschedule(Request $request, Clinic $clinic, QueueEntry $entry)
    {
        if (!auth()->user()->secretaryClinics()->where('clinics.id', $clinic->id)->exists()) {
            abort(403, 'Not assigned to this clinic');
        }
        if ($entry->clinic_id !== $clinic->id) {
            abort(403, 'Queue entry does not belong to this clinic.');
        }

        $data = $request->validate([
            'new_date' => 'required|date',
            'new_time' => 'required',
        ]);

        DB::transaction(function () use ($entry, $data) {
            if ($entry->appointment) {
                $appointment = $entry->appointment;
                $appointment->update([
                    'appointment_date' => $data['new_date'],
                    'appointment_time' => $data['new_time'],
                    'status' => 'rescheduled',
                ]);

                if ($appointment->user) {
                    $appointment->user->notify(new AppointmentStatusChanged($appointment));
                }

                $entry->update(['status' => 'rescheduled']);
            } else {
                $entry->update(['status' => 'rescheduled']);
                if ($entry->user) {
                    $entry->user->notify(new QueueNotification($entry));
                }
            }
        });

        event(new QueueUpdated($entry->fresh(), 'rescheduled'));

        return back()->with('status', "Queue #{$entry->queue_number} rescheduled.");
    }

    public function cancel(Clinic $clinic, QueueEntry $entry)
    {
        if ($entry->clinic_id !== $clinic->id) {
            abort(403, 'Queue entry does not belong to this clinic.');
        }

        if (!in_array($entry->status, ['waiting', 'called', 'rescheduled', 'now_serving'])) {
            return back()->with('error', 'Only waiting/called/now serving/rescheduled entries can be cancelled.');
        }

        DB::transaction(function () use ($entry) {
            $entry->update(['status' => 'cancelled']);

            if ($entry->appointment) {
                $appointment = $entry->appointment;

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

    public function noShow(Clinic $clinic, QueueEntry $entry)
    {
        if ($entry->clinic_id !== $clinic->id) {
            abort(403, 'Queue entry does not belong to this clinic.');
        }
        if (!in_array($entry->status, ['waiting', 'called', 'rescheduled', 'now_serving'])) {
            return back()->with('error', 'Only waiting/called/now serving/rescheduled entries can be marked no-show.');
        }

        DB::transaction(function () use ($entry) {
            $entry->update(['status' => 'no_show']);
            if ($entry->appointment && $entry->appointment->status !== 'completed') {
                if ($entry->appointment->status !== 'cancelled') {
                    $entry->appointment->update(['status' => 'cancelled']);
                    if ($entry->appointment->user) {
                        $entry->appointment->user->notify(new AppointmentStatusChanged($entry->appointment));
                    }
                }
            }
        });

        event(new QueueUpdated($entry->fresh(), 'no_show'));

        return back()->with('status', "Marked queue #{$entry->queue_number} as no-show.");
    }
}

