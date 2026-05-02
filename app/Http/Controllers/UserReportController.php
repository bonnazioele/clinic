<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\QueueEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $appointmentStatus = (string) $request->input('appointment_status', 'all');
        $queueStatus = (string) $request->input('queue_status', 'all');
        $from = $request->input('from');
        $to = $request->input('to');

        $appointmentsQuery = Appointment::with(['clinic', 'service', 'doctor'])
            ->where('user_id', $user->id);

        if ($appointmentStatus !== 'all') {
            $appointmentsQuery->where('status', $appointmentStatus);
        }

        if (! empty($from)) {
            $appointmentsQuery->whereDate('appointment_date', '>=', $from);
        }

        if (! empty($to)) {
            $appointmentsQuery->whereDate('appointment_date', '<=', $to);
        }

        $appointments = $appointmentsQuery
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(8)
            ->withQueryString();

        $queuesQuery = QueueEntry::with(['clinic', 'appointment.service'])
            ->where('user_id', $user->id);

        if ($queueStatus !== 'all') {
            $queuesQuery->where('status', $queueStatus);
        }

        if (! empty($from)) {
            $queuesQuery->whereDate('created_at', '>=', $from);
        }

        if (! empty($to)) {
            $queuesQuery->whereDate('created_at', '<=', $to);
        }

        $queueEntries = $queuesQuery
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'queue_page')
            ->withQueryString();

        $summary = [
            'total_appointments' => Appointment::where('user_id', $user->id)->count(),
            'completed_appointments' => Appointment::where('user_id', $user->id)->where('status', 'completed')->count(),
            'active_queues' => QueueEntry::where('user_id', $user->id)->whereIn('status', ['waiting', 'now_serving'])->count(),
            'served_queues' => QueueEntry::where('user_id', $user->id)->where('status', 'served')->count(),
        ];

        return view('reports.index', [
            'appointments' => $appointments,
            'queueEntries' => $queueEntries,
            'summary' => $summary,
            'appointmentStatus' => $appointmentStatus,
            'queueStatus' => $queueStatus,
            'from' => $from,
            'to' => $to,
        ]);
    }
}
