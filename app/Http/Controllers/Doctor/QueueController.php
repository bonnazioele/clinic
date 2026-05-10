<?php

namespace App\Http\Controllers\Doctor;

use App\Events\QueueUpdated;
use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\QueueEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware([
            'auth',
            \App\Http\Middleware\DoctorMiddleware::class,
            EnsureSelectedClinic::class,
        ]);
    }

    public function index(Request $request)
    {
        $doctor = Auth::user();
        $activeClinic = $this->activeClinic($request);
        $today = now()->toDateString();

        $waitingQuery = QueueEntry::query()
            ->withDashboardRelations()
            ->where('clinic_id', $activeClinic->id)
            ->forDoctor($doctor->id)
            ->whereIn('status', QueueEntry::doctorQueueVisibleStatuses())
            ->where(function ($query) use ($today) {
                $query->where(function ($appointmentQueue) use ($today) {
                    $appointmentQueue->whereNotNull('appointment_id')
                        ->whereHas('appointment', function ($appointmentQuery) use ($today) {
                            $appointmentQuery->whereDate('appointment_date', $today);
                        });
                })->orWhere(function ($walkInQueue) use ($today) {
                    $walkInQueue->whereNull('appointment_id')
                        ->whereNotNull('patient_id')
                        ->whereDate('created_at', $today);
                });
            });

        if ($activeClinic->queue_mode === 'priority') {
            $waitingQuery
                ->leftJoin('appointments', 'queue_entries.appointment_id', '=', 'appointments.id')
                ->select('queue_entries.*')
                ->orderByRaw("
                    CASE
                        WHEN queue_entries.status = 'now_serving' THEN 0
                        WHEN queue_entries.status = 'in_progress' THEN 0
                        WHEN queue_entries.status = 'called' THEN 1
                        WHEN queue_entries.status = 'waiting' THEN 2
                        WHEN queue_entries.status = 'rescheduled' THEN 3
                        WHEN queue_entries.status IN ('served', 'completed') THEN 4
                        ELSE 5
                    END
                ")
                ->orderByRaw('appointments.appointment_date IS NULL')
                ->orderBy('appointments.appointment_date')
                ->orderBy('appointments.appointment_time')
                ->orderBy('queue_entries.queue_number');
        } else {
            $waitingQuery
                ->orderByRaw("
                    CASE
                        WHEN status = 'now_serving' THEN 0
                        WHEN status = 'in_progress' THEN 0
                        WHEN status = 'called' THEN 1
                        WHEN status = 'waiting' THEN 2
                        WHEN status = 'rescheduled' THEN 3
                        WHEN status IN ('served', 'completed') THEN 4
                        ELSE 5
                    END
                ")
                ->orderBy('queue_number');
        }

        $waiting = $waitingQuery->get();

        return view('doctor.queue.index', compact('waiting'));
    }

    public function serve(Request $request, QueueEntry $entry)
    {
        $doctor = Auth::user();
        $activeClinic = $this->activeClinic($request);

        if ((int) $entry->clinic_id !== (int) $activeClinic->id) {
            abort(403);
        }

        $assignedDoctorId = (int) ($entry->doctor_id ?: $entry->appointment?->doctor_id ?: 0);

        if ($assignedDoctorId !== (int) $doctor->id) {
            abort(403, 'You can only process queue entries assigned to you.');
        }

        $data = $request->validate([
            'doctor_notes' => ['nullable', 'string', 'max:2000'],
            'prescription' => ['nullable', 'string', 'max:2000'],
            'follow_up_at' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($entry, $doctor, $activeClinic, $data) {
            $fresh = QueueEntry::with(['appointment.user', 'patient', 'clinic'])
                ->lockForUpdate()
                ->find($entry->id);

            if (! $fresh || (int) $fresh->clinic_id !== (int) $activeClinic->id) {
                abort(403);
            }

            $freshAssignedDoctorId = (int) ($fresh->doctor_id ?: $fresh->appointment?->doctor_id ?: 0);

            if ($freshAssignedDoctorId !== (int) $doctor->id) {
                abort(403, 'You can only process queue entries assigned to you.');
            }

            if (! in_array($fresh->status, ['waiting', 'called', 'in_progress', 'now_serving'], true)) {
                return;
            }

            $fresh->update([
                'status' => 'served',
                'called_at' => $fresh->called_at ?? now(),
                'served_at' => now(),
                'patient_disposition' => 'completed',
                'doctor_notes' => $data['doctor_notes'] ?? $fresh->doctor_notes,
                'prescription' => $data['prescription'] ?? $fresh->prescription,
                'follow_up_at' => $data['follow_up_at'] ?? $fresh->follow_up_at,
            ]);

            if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
                $fresh->appointment->update(['status' => 'completed']);
            }

            $clinic = $fresh->clinic;

            if ($clinic && $fresh->appointment) {
                $secretaries = $clinic->secretaries()->get();

                foreach ($secretaries as $secretary) {
                    $secretary->notify(new \App\Notifications\DoctorServedQueue($fresh->appointment));
                }
            }

            event(new QueueUpdated($fresh->fresh(['appointment.user', 'patient', 'clinic']), 'served'));
        });

        return back()->with('status', 'Processed queue entry #' . $entry->queue_number . '.');
    }
}
