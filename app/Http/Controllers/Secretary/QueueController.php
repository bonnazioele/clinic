<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\QueueEntry;
use App\Notifications\AppointmentStatusChanged;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    /**
     * Overview of queues across clinics for secretaries
     */
    public function overview()
    {
        $clinics = \App\Models\Clinic::with([
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

        $totalWaiting = \App\Models\QueueEntry::where('status','waiting')->count();
        $totalServedToday = \App\Models\QueueEntry::where('status','served')
            ->whereDate('served_at', today())
            ->count();

        return view('secretary.queue.overview', compact('clinics','totalWaiting','totalServedToday'));
    }
    /**
     * View queue for a clinic
     */
    public function queue(Clinic $clinic)
    {
        $waiting = QueueEntry::with(['user','appointment.service'])
            ->where('clinic_id', $clinic->id)
            ->where('status', 'waiting')
            ->orderBy('queue_number')
            ->get();

        return view('secretary.queue.index', compact('clinic','waiting'));
    }

    /**
     * Mark a queue entry as served
     */
    public function serve(Clinic $clinic, QueueEntry $entry)
    {
        if ($entry->clinic_id !== $clinic->id) {
            abort(403, 'Queue entry does not belong to this clinic.');
        }

        if ($entry->status !== 'waiting') {
            return back()->with('error','Only waiting entries can be served.');
        }

        DB::transaction(function() use ($entry) {
            // Mark queue entry as served
            $entry->update([
                'status' => 'served',
                'served_at' => now(),
            ]);

            // If tied to an appointment, mark it completed and notify patient
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

        return back()->with('status', "Served queue #{$entry->queue_number} and marked appointment as completed.");
    }

    /**
     * Cancel a waiting entry (patient left)
     */
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
                // Don't override completed appointments
                if ($appointment->status !== 'completed') {
                    $appointment->update(['status' => 'cancelled']);
                    if ($appointment->user) {
                        $appointment->user->notify(new AppointmentStatusChanged($appointment));
                    }
                }
            }
        });

        return back()->with('status', "Cancelled queue #{$entry->queue_number}.");
    }
}
