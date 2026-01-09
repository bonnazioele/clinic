<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Events\QueueUpdated;
use App\Models\Appointment;
use App\Models\QueueEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            ->whereIn('status', ['waiting','now_serving']);

        $clinicModes = \App\Models\Clinic::whereIn('id', $clinics)->pluck('queue_mode')->unique();
        if ($clinicModes->count() === 1 && $clinicModes->first() === 'priority') {
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

        return view('doctor.queue.index', compact('waiting'));
    }

    public function serve(Request $request, QueueEntry $entry)
    {
        $doctor = Auth::user();

        if (! $doctor->clinics()->where('clinics.id', $entry->clinic_id)->exists()) {
            abort(403);
        }

        $dispositionOptions = ['completed','follow_up','referred','cancelled'];
        $data = $request->validate([
            'patient_disposition' => ['required', Rule::in($dispositionOptions)],
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
                'patient_disposition' => $data['patient_disposition'],
                'doctor_notes' => $data['doctor_notes'] ?? null,
                'prescription' => $data['prescription'] ?? null,
                'follow_up_at' => $data['follow_up_at'] ?? null,
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
