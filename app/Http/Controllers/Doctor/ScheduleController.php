<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\DoctorMiddleware::class]);
    }

    public function index()
    {
        $doctor = Auth::user();
        $schedules = DoctorSchedule::with('clinic')
            ->where('doctor_id', $doctor->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        $clinics = $doctor->clinics()->get();
        return view('doctor.schedules.index', compact('schedules','clinics'));
    }

    public function store(Request $request)
    {
        $doctor = Auth::user();
        $data = $request->validate([
            'clinic_id'   => 'required|exists:clinics,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'is_active'   => 'nullable|boolean'
        ]);

        if (! $doctor->clinics()->where('clinics.id', $data['clinic_id'])->exists()) {
            return back()->withErrors(['clinic_id' => 'You are not associated with this clinic.']);
        }

        $overlap = DoctorSchedule::where('doctor_id',$doctor->id)
            ->where('clinic_id',$data['clinic_id'])
            ->where('day_of_week',$data['day_of_week'])
            ->where(function($q) use ($data) {
                $q->whereBetween('start_time', [$data['start_time'],$data['end_time']])
                  ->orWhereBetween('end_time', [$data['start_time'],$data['end_time']])
                  ->orWhere(function($q2) use ($data){
                      $q2->where('start_time','<=',$data['start_time'])
                         ->where('end_time','>=',$data['end_time']);
                  });
            })
            ->exists();
        if ($overlap) {
            return back()->withErrors(['start_time' => 'Overlapping schedule entry.']);
        }

        DoctorSchedule::create([
            'doctor_id'  => $doctor->id,
            'clinic_id'  => $data['clinic_id'],
            'day_of_week'=> $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time'   => $data['end_time'],
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return back()->with('status','Schedule added.');
    }

    public function destroy(DoctorSchedule $schedule)
    {
        $doctor = Auth::user();
        if ($schedule->doctor_id !== $doctor->id) {
            abort(403);
        }
        $schedule->delete();
        return back()->with('status','Schedule removed.');
    }

    public function update(Request $request, DoctorSchedule $schedule)
    {
        $doctor = Auth::user();
        if ($schedule->doctor_id !== $doctor->id) {
            abort(403);
        }

        $data = $request->validate([
            'clinic_id'   => 'required|exists:clinics,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'is_active'   => 'nullable|boolean'
        ]);

        if (! $doctor->clinics()->where('clinics.id', $data['clinic_id'])->exists()) {
            return back()->withErrors(['clinic_id' => 'You are not associated with this clinic.']);
        }

        $overlap = DoctorSchedule::where('doctor_id',$doctor->id)
            ->where('clinic_id',$data['clinic_id'])
            ->where('day_of_week',$data['day_of_week'])
            ->where('id','!=',$schedule->id)
            ->where(function($q) use ($data) {
                $q->whereBetween('start_time', [$data['start_time'],$data['end_time']])
                  ->orWhereBetween('end_time', [$data['start_time'],$data['end_time']])
                  ->orWhere(function($q2) use ($data){
                      $q2->where('start_time','<=',$data['start_time'])
                         ->where('end_time','>=',$data['end_time']);
                  });
            })
            ->exists();
        if ($overlap) {
            return back()->withErrors(['start_time' => 'Overlapping schedule entry.'])->withInput();
        }

        $schedule->update([
            'clinic_id'  => $data['clinic_id'],
            'day_of_week'=> $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time'   => $data['end_time'],
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return back()->with('status','Schedule updated.');
    }

    public function feed(Request $request)
    {
        $doctor = Auth::user();

        try {
            $start = Carbon::parse($request->input('start', now()->startOfMonth()))->startOfDay();
        } catch (\Throwable $e) {
            $start = now()->startOfMonth();
        }

        try {
            $end = Carbon::parse($request->input('end', $start->copy()->endOfMonth()))->endOfDay();
        } catch (\Throwable $e) {
            $end = $start->copy()->addMonth();
        }

        if ($end->lessThan($start)) {
            $end = $start->copy()->addMonth();
        }

        $schedules = DoctorSchedule::with('clinic')
            ->where('doctor_id', $doctor->id)
            ->where('is_active', true)
            ->get();

        $events = [];
        foreach ($schedules as $schedule) {
            $cursor = $start->copy()->nextOrSame($schedule->day_of_week);
            while ($cursor->lte($end)) {
                $events[] = [
                    'id' => 'schedule-'.$schedule->id.'-'.$cursor->format('Ymd'),
                    'title' => $schedule->clinic?->name ?? 'Clinic',
                    'start' => $cursor->copy()->setTimeFromTimeString($schedule->start_time)->toIso8601String(),
                    'end' => $cursor->copy()->setTimeFromTimeString($schedule->end_time)->toIso8601String(),
                    'display' => 'block',
                    'extendedProps' => [
                        'clinic' => $schedule->clinic?->name ?? 'Clinic',
                        'scheduleId' => $schedule->id,
                        'day_of_week' => $schedule->day_of_week,
                        'start_time' => substr($schedule->start_time, 0, 5),
                        'end_time' => substr($schedule->end_time, 0, 5),
                    ],
                ];
                $cursor->addWeek();
            }
        }

        return response()->json($events);
    }
}
