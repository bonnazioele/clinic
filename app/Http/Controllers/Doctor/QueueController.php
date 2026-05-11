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
use Illuminate\Pagination\LengthAwarePaginator;

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

        $queue = QueueEntry::query()
            ->withDashboardRelations()
            ->where('clinic_id', $activeClinic->id)
            ->forDoctor($doctor->id)
            ->whereIn('status', QueueEntry::doctorQueueVisibleStatuses())
            ->forDashboardDay($today)
            ->orderByScheduledSlot()
            ->get();

        $nowServingEntry = $queue->first(function ($entry) {
            return in_array($entry->status, ['in_progress', 'now_serving'], true);
        });

        $waitingQueue = $queue
            ->filter(fn ($entry) => in_array($entry->status, ['waiting', 'called'], true))
            ->values();

        $completedQueue = $queue
            ->filter(fn ($entry) => in_array($entry->status, ['served', 'completed'], true))
            ->values();

        $perPage = 8;
        $page = LengthAwarePaginator::resolveCurrentPage('page');
        $waitingPage = new LengthAwarePaginator(
            $waitingQueue->forPage($page, $perPage),
            $waitingQueue->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('doctor.queue.index', compact(
            'nowServingEntry',
            'waitingPage',
            'completedQueue'
        ));
    }

    /**
     * Doctor clicks Serve.
     *
     * Correct flow:
     * in_progress / now_serving -> served
     *
     * This should NOT:
     * - set status to completed
     * - set service_ended_at
     * - complete the appointment
     * - promote the next patient
     *
     * Secretary Done & Next handles:
     * served -> completed
     */
    public function serve(Request $request, QueueEntry $entry)
    {
        $doctor = Auth::user();
        $activeClinic = $this->activeClinic($request);

        $updatedEntry = null;
        $message = 'Patient marked as served. Secretary can now click Done & Next.';

        DB::transaction(function () use ($entry, $doctor, $activeClinic, &$updatedEntry, &$message) {
            $fresh = QueueEntry::query()
                ->with(['appointment.user', 'appointment.service', 'patient', 'clinic'])
                ->whereKey($entry->id)
                ->where('clinic_id', $activeClinic->id)
                ->lockForUpdate()
                ->first();

            if (! $fresh) {
                abort(404);
            }

            $assignedDoctorId = (int) ($fresh->doctor_id ?: $fresh->appointment?->doctor_id ?: 0);

            if ($assignedDoctorId !== (int) $doctor->id) {
                abort(403, 'You can only process queue entries assigned to you.');
            }

            if ($fresh->status === 'served') {
                $updatedEntry = $fresh->fresh(['appointment.user', 'appointment.service', 'patient', 'clinic']);
                $message = 'This patient is already marked as served. Secretary can now click Done & Next.';
                return;
            }

            if (! in_array($fresh->status, ['in_progress', 'now_serving'], true)) {
                $message = "This patient cannot be marked as served because the current status is {$fresh->status}.";
                return;
            }

            $fresh->forceFill([
                'status' => 'served',
                'called_at' => $fresh->called_at ?? now(),
                'served_at' => now(),
                'doctor_completed_at' => now(),
            ])->save();

            $updatedEntry = $fresh->fresh(['appointment.user', 'appointment.service', 'patient', 'clinic']);
        });

        if ($updatedEntry) {
            event(new QueueUpdated($updatedEntry, 'served'));
        }

        return back()->with('status', $message);
    }
}
