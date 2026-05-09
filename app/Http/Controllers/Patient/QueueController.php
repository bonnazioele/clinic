<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\QueueEntry;
use App\Services\QueueService;
use App\Events\QueueUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    protected QueueService $queue;

    public function __construct(QueueService $queue)
    {
        $this->middleware('auth')->only(['join', 'status', 'leave']);
        $this->queue = $queue;
    }

    public function join(Request $request, Clinic $clinic)
    {
        /*
        |--------------------------------------------------------------------------
        | Active queue statuses
        |--------------------------------------------------------------------------
        | A queue should still be visible if it is waiting, called, or now serving.
        | It should only disappear from active queue after final status.
        */
        $activeStatuses = QueueEntry::activePatientStatuses();

        $existingEntry = QueueEntry::where('clinic_id', $clinic->id)
            ->where('user_id', Auth::id())
            ->whereIn('status', $activeStatuses)
            ->first();

        if ($existingEntry) {
            return redirect()
                ->route('queue.status.entry', $existingEntry)
                ->with('status', 'You already have an active queue for this clinic.');
        }

        $appointment = null;

        if ($request->input('appointment_id')) {
            $appointment = Auth::user()
                ->appointments()
                ->where('id', $request->input('appointment_id'))
                ->where('clinic_id', $clinic->id)
                ->where('status', 'scheduled')
                ->first();
        }

        if (! $appointment) {
            return redirect()
                ->route('appointments.index')
                ->with('error', 'Book an appointment first so your queue number can match your time slot.');
        }

        $number = $this->queue->getSlotQueueNumber(
            (int) $clinic->id,
            (int) $appointment->doctor_id,
            (int) $appointment->service_id,
            $appointment->appointment_date,
            (string) $appointment->getRawOriginal('appointment_time')
        );

        $entry = QueueEntry::create([
            'clinic_id'           => $clinic->id,
            'user_id'             => Auth::id(),
            'appointment_id'      => $appointment ? $appointment->id : null,
            'doctor_id'           => $appointment?->doctor_id,
            'queue_number'        => $number,
            'scheduled_slot_date' => $appointment?->appointment_date?->toDateString(),
            'scheduled_slot_time' => $appointment?->getRawOriginal('appointment_time'),
            'status'              => 'waiting',
        ]);

        event(new QueueUpdated($entry, 'created'));

        return redirect()
            ->route('queue.status.entry', $entry)
            ->with('status', "You're number {$number} in the queue for {$clinic->name}.");
    }

    public function status(Request $request, $entry = null)
    {
        $activeStatuses = QueueEntry::activePatientStatuses();

        if (! is_null($entry)) {
            if (! $entry instanceof QueueEntry) {
                $entry = QueueEntry::with(['clinic', 'appointment.service', 'appointment.doctor'])
                    ->findOrFail($entry);
            }

            abort_unless((int) $entry->user_id === (int) Auth::id(), 403);

            $ahead = 0;

            if (in_array($entry->status, QueueEntry::activePatientStatuses(), true)) {
                $ahead = QueueEntry::where('clinic_id', $entry->clinic_id)
                    ->whereIn('status', QueueEntry::activePatientStatuses())
                    ->orderByScheduledSlot()
                    ->get(['id', 'queue_number', 'scheduled_slot_date', 'scheduled_slot_time'])
                    ->takeUntil(fn ($candidate) => (int) $candidate->id === (int) $entry->id)
                    ->count();
            }

            return view('queue.status', compact('entry', 'ahead'));
        }

        $userQueues = QueueEntry::with(['clinic', 'appointment.service', 'appointment.doctor'])
            ->where('user_id', Auth::id())
            ->whereIn('status', $activeStatuses)
            ->orderByScheduledSlot()
            ->get();

        return view('queue.status', compact('userQueues'));
    }

    public function leave(QueueEntry $entry)
    {
        if ((int) $entry->user_id !== (int) Auth::id()) {
            abort(403, 'You can only leave your own queue entry.');
        }

        if (! in_array($entry->status, ['waiting', 'called'], true)) {
            return back()->with('error', 'Cannot leave this queue anymore.');
        }

        $clinicName = $entry->clinic?->name ?? 'this clinic';

        $entry->update([
            'status' => 'cancelled',
        ]);

        if ($entry->appointment && $entry->appointment->status === 'scheduled') {
            $entry->appointment->update([
                'status' => 'cancelled',
            ]);
        }

        event(new QueueUpdated($entry->fresh(), 'cancelled'));

        return redirect()
            ->route('queue.status')
            ->with('status', "You have left the queue for {$clinicName}.");
    }
}
