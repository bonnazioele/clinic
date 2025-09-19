<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\QueueEntry;
use App\Notifications\AppointmentStatusChanged;
use Illuminate\Support\Facades\DB;
use App\Events\QueueUpdated;

class QueueController extends Controller
{

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

    public function queue(Clinic $clinic)
    {
        if (! auth()->user()->secretaryClinics()->where('clinics.id',$clinic->id)->exists()) {
            abort(403,'Not assigned to this clinic');
        }
        $waitingQuery = QueueEntry::with(['user','appointment.service'])
            ->where('clinic_id', $clinic->id)
            ->where('status', 'waiting');

        if ($clinic->queueModeIs('priority')) {
            // Order by appointment date/time if appointment exists, else fall back to queue_number
            $waitingQuery->leftJoin('appointments','queue_entries.appointment_id','=','appointments.id')
                ->select('queue_entries.*')
                ->orderBy('appointments.appointment_date')
                ->orderBy('appointments.appointment_time')
                ->orderBy('queue_number');
        } else {
            $waitingQuery->orderBy('queue_number');
        }
        $waiting = $waitingQuery->get();

        return view('secretary.queue.index', compact('clinic','waiting'));
    }

    public function serve(Clinic $clinic, QueueEntry $entry)
    {
        if (! auth()->user()->secretaryClinics()->where('clinics.id',$clinic->id)->exists()) {
            abort(403,'Not assigned to this clinic');
        }
        if ($entry->clinic_id !== $clinic->id) {
            abort(403, 'Queue entry does not belong to this clinic.');
        }

        if ($entry->status !== 'waiting') {
            return back()->with('error','Only waiting entries can be served.');
        }

    DB::transaction(function() use ($entry) {
            $entry->update([
                'status' => 'served',
                'served_at' => now(),
            ]);

            if ($entry->appointment) {
                $appointment = $entry->appointment;
                if ($appointment->status !== 'completed') {
                    $appointment->update(['status' => 'completed']);
                    if ($appointment->user) {
                        $appointment->user->notify(new AppointmentStatusChanged($appointment));
                    }
                }
            }
        });
    // Determine next based on clinic mode
    $clinic = $entry->clinic;
    $nextQuery = \App\Models\QueueEntry::where('clinic_id',$entry->clinic_id)
        ->where('status','waiting');
    if ($clinic && $clinic->queueModeIs('priority')) {
        $nextQuery->leftJoin('appointments','queue_entries.appointment_id','=','appointments.id')
            ->select('queue_entries.*')
            ->orderBy('appointments.appointment_date')
            ->orderBy('appointments.appointment_time')
            ->orderBy('queue_number');
    } else {
        $nextQuery->orderBy('queue_number');
    }
    $next = $nextQuery->first();
    if ($next && $next->user) {
        $next->user->notify(new \App\Notifications\QueueNextUp($next));
    }

    event(new QueueUpdated($entry->fresh(),'served'));

    return back()->with('status', "Served queue #{$entry->queue_number} and marked appointment as completed.");
    }

    public function cancel(Clinic $clinic, QueueEntry $entry)
    {
        if ($entry->clinic_id !== $clinic->id) {
            abort(403, 'Queue entry does not belong to this clinic.');
        }

        if ($entry->status !== 'waiting') {
            return back()->with('error','Only waiting entries can be cancelled.');
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
