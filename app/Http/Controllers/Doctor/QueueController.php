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

        $waiting = QueueEntry::query()
            ->withDashboardRelations()
            ->where('clinic_id', $activeClinic->id)
            ->forDoctor($doctor->id)
            ->whereIn('status', QueueEntry::doctorQueueVisibleStatuses())
            ->forDashboardDay($today)
            ->orderByScheduledSlot()
            ->get();

        return view('doctor.queue.index', compact('waiting'));
    }

    /**
     * Doctor clicks "Complete".
     *
     * Moves: in_progress → served
     *
     * This makes the patient visible as "served" on both the doctor
     * and secretary sides. The secretary then clicks "Done & Next"
     * to mark the patient as fully completed and promote the next
     * waiting patient to in_progress.
     */
    public function serve(Request $request, QueueEntry $entry)
    {
        $doctor       = Auth::user();
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
            'prescription'  => ['nullable', 'string', 'max:2000'],
            'follow_up_at'  => ['nullable', 'date'],
        ]);

        $updatedEntry = null;

        DB::transaction(function () use ($entry, $data, &$updatedEntry) {
            $fresh = QueueEntry::with(['appointment.user', 'patient', 'clinic'])
                ->lockForUpdate()
                ->find($entry->id);

            if (! $fresh || $fresh->status !== 'in_progress') {
                return;
            }

            // Save clinical notes and move to served.
            // Secretary's Done & Next will promote this to completed
            // and advance the queue.
            $fresh->update([
                'status'               => 'served',
                'served_at'            => now(),
                'doctor_notes'         => $data['doctor_notes'] ?? $fresh->doctor_notes,
                'prescription'         => $data['prescription']  ?? $fresh->prescription,
                'follow_up_at'         => $data['follow_up_at']  ?? $fresh->follow_up_at,
                'doctor_completed_at'  => now(),
            ]);

            // Notify secretaries so they know to click Done & Next.
            $secretaries = $fresh->clinic->secretaries()->get();

            foreach ($secretaries as $secretary) {
                $secretary->notify(
                    new \App\Notifications\DoctorServedQueue($fresh->appointment ?? $fresh)
                );
            }

            $updatedEntry = $fresh->fresh(['appointment.user', 'patient', 'clinic']);
        });

        if ($updatedEntry) {
            event(new QueueUpdated($updatedEntry, 'served'));
        }

        return back()->with('status', 'Patient marked as served. Secretary will advance the queue.');
    }
}