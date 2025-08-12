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
        $this->middleware('auth','admin')->only(['index','serve']);
        $this->queue = $queue;
    }

    /** Patient: join queue for a clinic */
    public function join(Request $req, Clinic $clinic)
    {
        $number = $this->queue->getNextNumber($clinic->id);

        $entry = QueueEntry::create([
            'clinic_id'     => $clinic->id,
            'user_id'       => Auth::id(),
            'appointment_id'=> $req->input('appointment_id'), // optional
            'queue_number'  => $number,
        ]);

        return redirect()
            ->route('queue.status', $entry)
            ->with('status',"You’re number {$number} in the queue.");
    }

    /** Patient: view your queue status */
    public function status(QueueEntry $entry)
    {
        abort_unless($entry->user_id === Auth::id(), 403);

        // How many are still waiting ahead?
        $ahead = QueueEntry::where('clinic_id', $entry->clinic_id)
            ->where('status','waiting')
            ->where('queue_number','<', $entry->queue_number)
            ->count();

        return view('queue.status', compact('entry','ahead'));
    }

    /** Admin: list all waiting entries */
    public function index(Clinic $clinic)
    {
        $waiting = QueueEntry::with('user')
            ->where('clinic_id',$clinic->id)
            ->where('status','waiting')
            ->orderBy('queue_number')
            ->get();

        return view('admin.clinics.queue', compact('clinic','waiting'));
    }

    /** Admin: mark next waiting as served */
    public function serve(QueueEntry $entry)
    {
        $entry->update([
            'status'    => 'served',
            'served_at' => now(),
        ]);

        return back()->with('status',"Served #{$entry->queue_number}.");
    }
}
