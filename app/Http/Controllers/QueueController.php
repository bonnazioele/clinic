<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\QueueEntry;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    protected QueueService $queue;

    public function __construct(QueueService $queue)
    {
        $this->middleware('auth')->only(['join','status']);
        $this->queue = $queue;
    }

    /** Patient: join queue for a clinic */
    public function join(Request $req, Clinic $clinic)
    {
        // Check if user is already in queue for this clinic
        $existingEntry = QueueEntry::where('clinic_id', $clinic->id)
            ->where('user_id', Auth::id())
            ->where('status', 'waiting')
            ->first();

        if ($existingEntry) {
            return redirect()
                ->route('queue.status.entry', $existingEntry)
                ->with('status', 'You are already in the queue for this clinic.');
        }

        // Check if user has an appointment for today
        $appointment = null;
        if ($req->input('appointment_id')) {
            $appointment = Auth::user()->appointments()
                ->where('id', $req->input('appointment_id'))
                ->where('clinic_id', $clinic->id)
                ->where('status', 'scheduled')
                ->first();
        }

        $number = $this->queue->getNextNumber($clinic->id);

        $entry = QueueEntry::create([
            'clinic_id'     => $clinic->id,
            'user_id'       => Auth::id(),
            'appointment_id'=> $appointment ? $appointment->id : null,
            'queue_number'  => $number,
            'status'        => 'waiting',
        ]);

        return redirect()
            ->route('queue.status.entry', $entry)
            ->with('status', "You're number {$number} in the queue for {$clinic->name}.");
    }

    /** Patient: view your queue status */
    public function status(Request $request, $entry = null)
    {
        if (!is_null($entry)) {
            // Accept either a bound model or an ID
            if (!$entry instanceof QueueEntry) {
                $entry = QueueEntry::with(['clinic','appointment.service'])->findOrFail($entry);
            }

            // Specific queue entry status
            abort_unless($entry->user_id === Auth::id(), 403);

            // How many are still waiting ahead?
            $ahead = QueueEntry::where('clinic_id', $entry->clinic_id)
                ->where('status','waiting')
                ->where('queue_number','<', $entry->queue_number)
                ->count();

            return view('queue.status', compact('entry','ahead'));
        } else {
            // General queue status - show user's active queue entries
            $userQueues = QueueEntry::with(['clinic', 'appointment'])
                ->where('user_id', Auth::id())
                ->where('status', 'waiting')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('queue.status', compact('userQueues'));
        }
    }

    // Note: Queue monitoring/actions are handled on the secretary side.

    /** Patient: leave queue */
    public function leave(QueueEntry $entry)
    {
        // Verify the entry belongs to the authenticated user
        if ($entry->user_id !== Auth::id()) {
            abort(403, 'You can only leave your own queue entry.');
        }

        // Only allow leaving if still waiting
        if ($entry->status !== 'waiting') {
            return back()->with('error', 'Cannot leave queue entry that is not waiting.');
        }

        $clinicName = $entry->clinic->name;
        $entry->update(['status' => 'cancelled']);

        return redirect()
            ->route('queue.status')
            ->with('status', "You have left the queue for {$clinicName}.");
    }
}
