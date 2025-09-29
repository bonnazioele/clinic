<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\QueueEntry;
use App\Models\Appointment;
use App\Events\QueueUpdated;

class QueueController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\DoctorMiddleware::class]);
    }

    public function index(Request $request)
    {
        $doctor = Auth::user();
        $clinics = $doctor->clinics()->pluck('clinics.id');

        $waitingQuery = QueueEntry::with('appointment.user','clinic')
            ->whereIn('clinic_id', $clinics)
            ->where('status','waiting');

        $clinicModes = \App\Models\Clinic::whereIn('id', $clinics)->pluck('queue_mode')->unique();
        if ($clinicModes->count() === 1 && $clinicModes->first() === 'priority') {
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

        return view('doctor.queue.index', compact('waiting'));
    }

   public function serve(QueueEntry $entry)
{
    $doctor = Auth::user();

    // Ensure doctor belongs to this clinic
    if (! $doctor->clinics()->where('clinics.id', $entry->clinic_id)->exists()) {
        abort(403);
    }

    \DB::transaction(function() use ($entry) {
        $fresh = QueueEntry::lockForUpdate()->find($entry->id);

        if ($fresh->status !== 'waiting') {
            return;
        }

        // Mark as served (or completed if ENUM doesn't include 'served')
        $fresh->update([
            'status' => 'completed', // safer if 'served' is not in ENUM
            'served_at' => now()
        ]);

        // Complete appointment if exists
        if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
            $fresh->appointment->update(['status' => 'completed']);
        }

        // Notify secretaries only
        $clinic = $fresh->clinic;
        if ($clinic && $fresh->appointment) { // ensure appointment exists
            $secretaries = $clinic->secretaries()->get();
            foreach ($secretaries as $sec) {
                $sec->notify(new \App\Notifications\DoctorServedQueue($fresh->appointment));
            }
        }

        // Fire event for front-end updates
        event(new QueueUpdated($fresh->fresh(), 'served'));
    });

    return back()->with('status', 'Processed queue entry #'.$entry->queue_number.'.');
}

}
