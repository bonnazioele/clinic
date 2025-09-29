<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\QueueEntry;
use App\Notifications\QueueNotification;
use App\Notifications\AppointmentStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\QueueUpdated;

class QueueController extends Controller
{
    /**
     * Overview for secretary (unchanged)
     */
    public function overview()
    {
        $user = auth()->user();
        $clinicIds = $user->secretaryClinics()->pluck('clinics.id');

        $clinics = \App\Models\Clinic::whereIn('id', $clinicIds)
            ->with([
                'queueEntries' => function($q){
                    $q->where('status','waiting')->orderBy('queue_number');
                }
            ])
            ->withCount([
                'queueEntries as waiting_count' => function($q){
                    $q->where('status','waiting');
                }
            ])
            ->get();

        $totalWaiting = \App\Models\QueueEntry::whereIn('clinic_id', $clinicIds)->where('status','waiting')->count();
        $totalServedToday = \App\Models\QueueEntry::whereIn('clinic_id', $clinicIds)
            ->where('status','served')
            ->whereDate('served_at', today())
            ->count();

        return view('secretary.queue.overview', compact('clinics','totalWaiting','totalServedToday'));
    }

    /**
     * Queue view for a single clinic (unchanged)
     */
    public function queue(Clinic $clinic)
    {
        if (! auth()->user()->secretaryClinics()->where('clinics.id',$clinic->id)->exists()) {
            abort(403,'Not assigned to this clinic');
        }
        $waitingQuery = QueueEntry::with(['user','appointment.service'])
            ->where('clinic_id', $clinic->id)
            ->where('status', 'waiting');

        if ($clinic->queueModeIs('priority')) {
            $waitingQuery->leftJoin('appointments','queue_entries.appointment_id','=','appointments.id')
                ->select('queue_entries.*')
                ->orderByRaw('appointments.appointment_date IS NULL')
                ->orderBy('appointments.appointment_date')
                ->orderBy('appointments.appointment_time')
                ->orderBy('queue_number');
        } else {
            $waitingQuery->orderBy('queue_number');
        }
        $waiting = $waitingQuery->get();

        return view('secretary.queue.index', compact('clinic','waiting'));
    }

    /**
     * CALL action — secretary notifies the patient to come in.
     * Replaces the old 'serve' secretary action.
     */
    public function call(Clinic $clinic, QueueEntry $entry)
{
    if (! auth()->user()->secretaryClinics()->where('clinics.id', $clinic->id)->exists()) {
        abort(403,'Not assigned to this clinic');
    }
    if ($entry->clinic_id !== $clinic->id) {
        abort(403, 'Queue entry does not belong to this clinic.');
    }

    if ($entry->status !== 'waiting') {
        return back()->with('error','Only waiting entries can be notified.');
    }

    // ✅ Don't update status here
    if ($entry->user) {
        $entry->user->notify(new QueueNotification($entry));
    }

    // Still broadcast for real-time updates
    event(new QueueUpdated($entry->fresh(), 'called'));

    return back()->with('status', "Patient #{$entry->queue_number} has been called (not served yet).");
}

    /**
     * RESCHEDULE action — secretary reschedules appointment (from modal).
     * Expects POST with new_date and new_time.
     */
    public function reschedule(Request $request, Clinic $clinic, QueueEntry $entry)
    {
        if (! auth()->user()->secretaryClinics()->where('clinics.id',$clinic->id)->exists()) {
            abort(403,'Not assigned to this clinic');
        }
        if ($entry->clinic_id !== $clinic->id) {
            abort(403, 'Queue entry does not belong to this clinic.');
        }

        $data = $request->validate([
            'new_date' => 'required|date',
            'new_time' => 'required'
        ]);

        DB::transaction(function() use ($entry, $data) {
            // If this entry is linked to an appointment, update it
            if ($entry->appointment) {
                $appointment = $entry->appointment;
                $appointment->update([
                    'appointment_date' => $data['new_date'],
                    'appointment_time' => $data['new_time'],
                    'status' => 'rescheduled'
                ]);

                // Notify patient about appointment change
                if ($appointment->user) {
                    $appointment->user->notify(new AppointmentStatusChanged($appointment));
                }

                // Keep queue entry marked as rescheduled (so secretary knows)
                $entry->update(['status' => 'rescheduled']);
            } else {
                // For walk-ins or entries without appointment, simply mark rescheduled
                $entry->update(['status' => 'rescheduled']);
                if ($entry->user) {
                    $entry->user->notify(new QueueNotification($entry)); // can be used to inform
                }
            }
        });

        event(new QueueUpdated($entry->fresh(),'rescheduled'));

        return back()->with('status', "Queue #{$entry->queue_number} rescheduled.");
    }

    /**
     * CANCEL action — secretary cancels a waiting/called/rescheduled entry
     */
    public function cancel(Clinic $clinic, QueueEntry $entry)
    {
        if ($entry->clinic_id !== $clinic->id) {
            abort(403, 'Queue entry does not belong to this clinic.');
        }

        if (! in_array($entry->status, ['waiting','called','rescheduled'])) {
            return back()->with('error','Only waiting/called/rescheduled entries can be cancelled.');
        }

        DB::transaction(function() use ($entry) {
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

        event(new QueueUpdated($entry->fresh(),'cancelled'));

        return back()->with('status', "Cancelled queue #{$entry->queue_number}.");
    }
}
