<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    use InteractsWithClinic;

    private const DAY_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    private function dateMatchesAllowedDays(?string $date, array $allowedDays): bool
    {
        if ($date === null || $date === '' || empty($allowedDays)) {
            return true;
        }

        try {
            $day = Carbon::parse($date)->dayOfWeek;
        } catch (\Throwable $e) {
            return false;
        }

        return in_array((int) $day, $allowedDays, true);
    }

    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\DoctorMiddleware::class, EnsureSelectedClinic::class]);
    }

    public function index(Request $request)
    {
        $doctor = Auth::user();
        $activeClinicId = $this->activeClinicId($request);
        $activeClinic = $request->attributes->get('active_clinic');

        $schedules = DoctorSchedule::with('clinic')
            ->where('doctor_id', $doctor->id)
            ->where('clinic_id', $activeClinicId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        $clinics = collect($activeClinic ? [$activeClinic] : []);
        return view('doctor.schedules.index', compact('schedules','clinics'));
    }

    public function store(Request $request)
    {
        $doctor = Auth::user();
        $activeClinicId = $this->activeClinicId($request);

        $data = $request->validate([
            'clinic_id'   => 'required|exists:clinics,id',
            'days'        => 'nullable|array|min:1',
            'days.*'      => 'integer|min:0|max:6',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'is_active'   => 'nullable|boolean'
        ]);

        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;

        $selectedDays = collect($data['days'] ?? [])
            ->push($data['day_of_week'] ?? null)
            ->filter(static fn ($value) => $value !== null)
            ->map(static fn ($value) => (int) $value)
            ->unique()
            ->sort()
            ->values();

        if ($selectedDays->isEmpty()) {
            return back()->withErrors(['days' => 'Please select at least one day.'])->withInput();
        }

        $allowedDays = $selectedDays->all();
        if (! $this->dateMatchesAllowedDays($startDate, $allowedDays)) {
            return back()->withErrors(['start_date' => 'Start date must fall on one of the selected day(s).'])->withInput();
        }
        if (! $this->dateMatchesAllowedDays($endDate, $allowedDays)) {
            return back()->withErrors(['end_date' => 'End date must fall on one of the selected day(s).'])->withInput();
        }

        if ((int) $data['clinic_id'] !== $activeClinicId) {
            return back()->withErrors(['clinic_id' => 'Please use your active clinic.'])->withInput();
        }

        $overlapDays = [];
        foreach ($selectedDays as $day) {
            $overlap = DoctorSchedule::where('doctor_id', $doctor->id)
                ->where('clinic_id', $data['clinic_id'])
                ->where('day_of_week', $day)
                ->where(function ($q) use ($data) {
                    $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                      ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                      ->orWhere(function ($q2) use ($data) {
                          $q2->where('start_time', '<=', $data['start_time'])
                             ->where('end_time', '>=', $data['end_time']);
                      });
                })
                ->where(function ($q) use ($startDate, $endDate) {
                    if ($endDate !== null) {
                        $q->where(function ($q2) use ($endDate) {
                            $q2->whereNull('start_date')
                                ->orWhereDate('start_date', '<=', $endDate);
                        });
                    }

                    if ($startDate !== null) {
                        $q->where(function ($q2) use ($startDate) {
                            $q2->whereNull('end_date')
                                ->orWhereDate('end_date', '>=', $startDate);
                        });
                    }
                })
                ->exists();

            if ($overlap) {
                $overlapDays[] = self::DAY_LABELS[$day] ?? ('Day '.$day);
            }
        }

        if (! empty($overlapDays)) {
            return back()
                ->withErrors(['start_time' => 'Overlapping schedule entry on: '.implode(', ', $overlapDays).'.'])
                ->withInput();
        }

        foreach ($selectedDays as $day) {
            DoctorSchedule::create([
                'doctor_id'  => $doctor->id,
                'clinic_id'  => $data['clinic_id'],
                'day_of_week'=> $day,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'start_time' => $data['start_time'],
                'end_time'   => $data['end_time'],
                'is_active'  => $request->boolean('is_active', true),
            ]);
        }

        $suffix = $selectedDays->count() > 1 ? 's' : '';
        return back()->with('status', 'Schedule added for '.$selectedDays->count().' day'.$suffix.'.');
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
        $activeClinicId = $this->activeClinicId($request);

        if ($schedule->doctor_id !== $doctor->id) {
            abort(403);
        }

        if ((int) $schedule->clinic_id !== $activeClinicId) {
            abort(403, 'Schedule does not belong to the active clinic.');
        }

        $data = $request->validate([
            'clinic_id'   => 'required|exists:clinics,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'is_active'   => 'nullable|boolean'
        ]);

        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;

        $allowedDays = [(int) $data['day_of_week']];
        if (! $this->dateMatchesAllowedDays($startDate, $allowedDays)) {
            return back()->withErrors(['start_date' => 'Start date must match the selected day of week.'])->withInput();
        }
        if (! $this->dateMatchesAllowedDays($endDate, $allowedDays)) {
            return back()->withErrors(['end_date' => 'End date must match the selected day of week.'])->withInput();
        }

        if ((int) $data['clinic_id'] !== $activeClinicId) {
            return back()->withErrors(['clinic_id' => 'Please use your active clinic.'])->withInput();
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
            ->where(function ($q) use ($startDate, $endDate) {
                if ($endDate !== null) {
                    $q->where(function ($q2) use ($endDate) {
                        $q2->whereNull('start_date')
                            ->orWhereDate('start_date', '<=', $endDate);
                    });
                }

                if ($startDate !== null) {
                    $q->where(function ($q2) use ($startDate) {
                        $q2->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $startDate);
                    });
                }
            })
            ->exists();
        if ($overlap) {
            return back()->withErrors(['start_time' => 'Overlapping schedule entry.'])->withInput();
        }

        $schedule->update([
            'clinic_id'  => $data['clinic_id'],
            'day_of_week'=> $data['day_of_week'],
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'start_time' => $data['start_time'],
            'end_time'   => $data['end_time'],
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return back()->with('status','Schedule updated.');
    }

    public function feed(Request $request)
    {
        $doctor = Auth::user();
        $activeClinicId = $this->activeClinicId($request);

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
            ->where('clinic_id', $activeClinicId)
            ->where('is_active', true)
            ->get();

        $events = [];
        foreach ($schedules as $schedule) {
            $effectiveStart = $start->copy();
            if (! empty($schedule->start_date)) {
                $scheduleStart = Carbon::parse($schedule->start_date)->startOfDay();
                if ($scheduleStart->greaterThan($effectiveStart)) {
                    $effectiveStart = $scheduleStart;
                }
            }

            $effectiveEnd = $end->copy();
            if (! empty($schedule->end_date)) {
                $scheduleEnd = Carbon::parse($schedule->end_date)->endOfDay();
                if ($scheduleEnd->lessThan($effectiveEnd)) {
                    $effectiveEnd = $scheduleEnd;
                }
            }

            if ($effectiveEnd->lessThan($effectiveStart)) {
                continue;
            }

            $cursor = $effectiveStart->copy()->nextOrSame($schedule->day_of_week);
            while ($cursor->lte($effectiveEnd)) {
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
                        'start_date' => $schedule->start_date,
                        'end_date' => $schedule->end_date,
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
