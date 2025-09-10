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

        $waiting = QueueEntry::with('appointment.user','clinic')
            ->whereIn('clinic_id', $clinics)
            ->where('status','waiting')
            ->orderBy('queue_number')
            ->get();

        return view('doctor.queue.index', compact('waiting'));
    }

    // Doctor signals completion of current appointment (serve first waiting item in a clinic)
    public function serve(QueueEntry $entry)
    {
        $doctor = Auth::user();
        if (! $doctor->clinics()->where('clinics.id', $entry->clinic_id)->exists()) {
            abort(403);
        }

        // Atomic serve to prevent race conditions
    \DB::transaction(function() use ($entry) {
            $fresh = QueueEntry::lockForUpdate()->find($entry->id);
            if ($fresh->status !== 'waiting') {
                return; // another process handled it
            }
            $fresh->update(['status' => 'served', 'served_at' => now()]);
            if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
                $fresh->appointment->update(['status' => 'completed']);
                // Notify patient that appointment completed
                if ($fresh->appointment->user) {
                    $fresh->appointment->user->notify(new \App\Notifications\AppointmentStatusChanged($fresh->appointment));
                }
            }

            // Notify secretaries + include next
            $next = QueueEntry::where('clinic_id', $fresh->clinic_id)
                ->where('status','waiting')
                ->orderBy('queue_number')
                ->first();
            $clinic = $fresh->clinic;
            if ($clinic) {
                $secretaries = $clinic->secretaries()->get();
                foreach ($secretaries as $sec) {
                    $sec->notify(new \App\Notifications\DoctorServedQueue($fresh, $next));
                }
            }
            event(new QueueUpdated($fresh->fresh(),'served'));
        });

        return back()->with('status', 'Processed queue entry #'.$entry->queue_number.'.');
    }
}
