<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Events\QueueUpdated;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\QueueEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\DoctorMiddleware::class, EnsureSelectedClinic::class]);
    }

    public function index(Request $request)
    {
        $doctor = Auth::user();
<<<<<<< Updated upstream
        $activeClinic = $this->activeClinic($request);

        $waitingQuery = QueueEntry::with('appointment.user','clinic')
            ->where('clinic_id', $activeClinic->id)
            ->whereHas('appointment', function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id);
            })
            ->whereIn('status', ['waiting','now_serving']);

        if ($activeClinic->queue_mode === 'priority') {
=======
        $assignedClinics = $doctor->clinics()->orderBy('name')->get(['clinics.id', 'clinics.name']);
        $clinicIds = $assignedClinics->pluck('id');

        $selectedClinicId = (int) $request->input('clinic_id', 0);
        if ($selectedClinicId > 0 && ! $clinicIds->contains($selectedClinicId)) {
            $selectedClinicId = 0;
        }

        $waitingQuery = QueueEntry::with('appointment.user','clinic')
            ->whereIn('clinic_id', $clinicIds)
            ->whereIn('status', ['waiting','now_serving']);

        if ($selectedClinicId > 0) {
            $waitingQuery->where('clinic_id', $selectedClinicId);
        }

        $clinicModes = \App\Models\Clinic::whereIn('id', $selectedClinicId > 0 ? [$selectedClinicId] : $clinicIds)
            ->pluck('queue_mode')
            ->unique();
        if ($clinicModes->count() === 1 && $clinicModes->first() === 'priority') {
>>>>>>> Stashed changes
            $waitingQuery->leftJoin('appointments','queue_entries.appointment_id','=','appointments.id')
                ->select('queue_entries.*')
                ->orderByRaw("CASE WHEN queue_entries.status = 'now_serving' THEN 0 ELSE 1 END")
                ->orderByRaw('appointments.appointment_date IS NULL')
                ->orderBy('appointments.appointment_date')
                ->orderBy('appointments.appointment_time')
                ->orderBy('queue_number');
        } else {
            $waitingQuery
                ->orderByRaw("CASE WHEN status = 'now_serving' THEN 0 ELSE 1 END")
                ->orderBy('queue_number');
        }
        $waiting = $waitingQuery->get();

        return view('doctor.queue.index', compact('waiting', 'assignedClinics', 'selectedClinicId'));
    }


public function serve(Request $request, QueueEntry $entry)
{
    $doctor = Auth::user();
    $activeClinic = $this->activeClinic($request);

    if ((int) $entry->clinic_id !== (int) $activeClinic->id) {
        abort(403);
    }

    if (! $entry->appointment || (int) $entry->appointment->doctor_id !== (int) $doctor->id) {
        abort(403, 'You can only process queue entries assigned to you.');
    }

    $data = $request->validate([
        'doctor_notes' => ['nullable','string','max:2000'],
        'prescription' => ['nullable','string','max:2000'],
        'follow_up_at' => ['nullable','date'],
    ]);

    \DB::transaction(function() use ($entry, $data) {
        $fresh = QueueEntry::lockForUpdate()->find($entry->id);

        if (! in_array($fresh->status, ['waiting','now_serving'])) {
            return;
        }

        $fresh->update([
            'status' => 'served',
            'served_at' => now(),
            'patient_disposition' => 'completed',
        ]);

        if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
            $fresh->appointment->update(['status' => 'completed']);
        }

        $clinic = $fresh->clinic;
        if ($clinic && $fresh->appointment) {
            $secretaries = $clinic->secretaries()->get();
            foreach ($secretaries as $sec) {
                $sec->notify(new \App\Notifications\DoctorServedQueue($fresh->appointment));
            }
        }

        event(new QueueUpdated($fresh->fresh(), 'served'));
    });

    return back()->with('status', 'Processed queue entry #'.$entry->queue_number.'.');
}
}
