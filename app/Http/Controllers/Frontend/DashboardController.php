<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only('index');
    }

    public function welcome()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            }
            $clinic = \App\Models\Clinic::where('created_by_user_id', $user->id)
                ->orderByRaw("CASE WHEN status IN ('approved','active') THEN 0 ELSE 1 END")
                ->orderByDesc('id')
                ->first();
            if ($clinic) {
                if ($clinic->isApprovedLike()) {
                    if (! $user->is_secretary) { $user->is_secretary = true; $user->save(); }
                    return redirect()->route('secretary.dashboard');
                }
                if (! $user->is_doctor && ! $user->is_secretary) {
                    session()->flash('warning', 'Your clinic application is pending review.');
                    return view('welcome');
                }
            }
            if ($user->is_doctor) {
                return redirect()->route('doctor.dashboard');
            }
            if ($user->is_secretary) {
                return redirect()->route('secretary.dashboard');
            }
            return redirect()->route('dashboard');
        }
        return view('welcome');
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        $clinic = \App\Models\Clinic::where('created_by_user_id', $user->id)
            ->orderByRaw("CASE WHEN status IN ('approved','active') THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->first();
        if ($clinic) {
            if ($clinic->isApprovedLike()) {
                if (! $user->is_secretary) { $user->is_secretary = true; $user->save(); }
                return redirect()->route('secretary.dashboard');
            }
            if (! $user->is_doctor && ! $user->is_secretary) {
                return redirect()->route('welcome')->with('warning','Your clinic application is pending review.');
            }
        }

        if ($user->is_doctor) {
            return redirect()->route('doctor.dashboard');
        }

        if ($user->is_secretary) {
            return redirect()->route('secretary.dashboard');
        }

        $upcoming = $user->appointments()
                         ->where('appointment_date','>=',now()->toDateString())
                         ->where('status','scheduled')
                         ->orderBy('appointment_date')
                         ->orderBy('appointment_time')
                         ->take(5)
                         ->with('clinic','service')
                         ->get();

        $past = $user->appointments()
                     ->where(function($query) {
                         $query->where('appointment_date','<', now()->toDateString())
                               ->orWhereIn('status', \App\Models\Appointment::HISTORY_STATUSES);
                     })
                     ->orderBy('appointment_date','desc')
                     ->take(10)
                     ->with('clinic','service')
                     ->get();

        $allAppointments = $user->appointments()
                               ->with('clinic','service')
                               ->orderBy('appointment_date','desc')
                               ->get();

        $pastAlternative = $allAppointments->filter(function($appointment) {
            return $appointment->shouldAppearInHistory();
        })->take(10);

        \Log::info('Dashboard data for user ' . $user->id, [
            'upcoming_count' => $upcoming->count(),
            'past_count' => $past->count(),
            'past_alternative_count' => $pastAlternative->count(),
            'total_appointments' => $user->appointments()->count(),
            'today' => now()->toDateString(),
            'appointments_debug' => $allAppointments->map(function($a) {
                return [
                    'id' => $a->id,
                    'date' => $a->appointment_date,
                    'status' => $a->status,
                    'is_past' => $a->isPast(),
                    'is_completed' => $a->isCompleted(),
                    'is_cancelled' => $a->isCancelled()
                ];
            })->toArray()
        ]);

    return view('dashboard.index', compact('upcoming','past'));
    }
}
