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

        // Mixed clinics: order per clinic mode would require union; simplify: if all selected clinics share mode priority, use priority ordering
        $clinicModes = \App\Models\Clinic::whereIn('id', $clinics)->pluck('queue_mode')->unique();
        if ($clinicModes->count() === 1 && $clinicModes->first() === 'priority') {
            $waitingQuery->leftJoin('appointments','queue_entries.appointment_id','=','appointments.id')
                ->select('queue_entries.*')
                // Push walk-ins (NULL appointment_date) after scheduled appointment holders
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
        if (! $doctor->clinics()->where('clinics.id', $entry->clinic_id)->exists()) {
            abort(403);
        }

    \DB::transaction(function() use ($entry) {
            $fresh = QueueEntry::lockForUpdate()->find($entry->id);
            if ($fresh->status !== 'waiting') {
                return;
            }
            $fresh->update(['status' => 'served', 'served_at' => now()]);
            if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
                $fresh->appointment->update(['status' => 'completed']);
                if ($fresh->appointment->user) {
                    $fresh->appointment->user->notify(new \App\Notifications\AppointmentStatusChanged($fresh->appointment));
                }
            }

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
            if ($next && $next->user) {
                $next->user->notify(new \App\Notifications\QueueNextUp($next));
            }
            event(new QueueUpdated($fresh->fresh(),'served'));
        });

        return back()->with('status', 'Processed queue entry #'.$entry->queue_number.'.');
    }
}
