<?php

namespace App\Http\Controllers;

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
        $this->middleware('auth')->only(['join','status']);
        $this->queue = $queue;
    }

    public function join(Request $req, Clinic $clinic)
    {
        $existingEntry = QueueEntry::where('clinic_id', $clinic->id)
            ->where('user_id', Auth::id())
            ->where('status', 'waiting')
            ->first();

        if ($existingEntry) {
            return redirect()
                ->route('queue.status.entry', $existingEntry)
                ->with('status', 'You are already in the queue for this clinic.');
        }

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

    event(new QueueUpdated($entry,'created'));

        return redirect()
            ->route('queue.status.entry', $entry)
            ->with('status', "You're number {$number} in the queue for {$clinic->name}.");
    }

    public function status(Request $request, $entry = null)
    {
        if (!is_null($entry)) {
            if (!$entry instanceof QueueEntry) {
                $entry = QueueEntry::with(['clinic','appointment.service'])->findOrFail($entry);
            }

            abort_unless($entry->user_id === Auth::id(), 403);

            $ahead = QueueEntry::where('clinic_id', $entry->clinic_id)
                ->where('status','waiting')
                ->where('queue_number','<', $entry->queue_number)
                ->count();

            return view('queue.status', compact('entry','ahead'));
        } else {
            $userQueues = QueueEntry::with(['clinic', 'appointment'])
                ->where('user_id', Auth::id())
                ->where('status', 'waiting')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('queue.status', compact('userQueues'));
        }
    }

    public function leave(QueueEntry $entry)
    {
        if ($entry->user_id !== Auth::id()) {
            abort(403, 'You can only leave your own queue entry.');
        }

        if ($entry->status !== 'waiting') {
            return back()->with('error', 'Cannot leave queue entry that is not waiting.');
        }

        $clinicName = $entry->clinic->name;
    $entry->update(['status' => 'cancelled']);
    event(new QueueUpdated($entry->fresh(),'cancelled'));

        return redirect()
            ->route('queue.status')
            ->with('status', "You have left the queue for {$clinicName}.");
    }
}
