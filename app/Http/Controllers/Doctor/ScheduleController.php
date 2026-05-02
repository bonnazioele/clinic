<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    use InteractsWithClinic;

    private const DAY_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\DoctorMiddleware::class, EnsureSelectedClinic::class]);
    }

    public function index(Request $request)
    {
        $doctor = Auth::user();
        $activeClinic = $request->attributes->get('active_clinic');
        $clinics = $doctor->clinics()->orderBy('name')->get(['clinics.id', 'clinics.name']);
        $services = $doctor->services()->distinct()->orderBy('services.name')->get(['services.id', 'services.name']);

        $clinicIds = $clinics->pluck('id')->all();

        $schedules = DoctorSchedule::with('clinic', 'service')
            ->where('doctor_id', $doctor->id)
            ->whereIn('clinic_id', $clinicIds)
            ->orderBy('day_of_week')
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        return view('doctor.schedules.index', compact('schedules','clinics','services','activeClinic'));
    }

    public function store(Request $request)
    {
        $doctor = Auth::user();
<<<<<<< Updated upstream
        $assignedClinicIds = $doctor->clinics()->pluck('clinics.id')->map(fn ($id) => (int) $id)->all();
        $clinicNames = $doctor->clinics()->pluck('clinics.name', 'clinics.id');
        $doctorServiceIds = $doctor->services()->pluck('services.id')->map(fn ($id) => (int) $id)->all();

        $data = $request->validate([
            'service_id'  => 'required|integer|exists:services,id',
            'days'        => 'nullable|array|min:1',
            'days.*'      => 'integer|min:0|max:6',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i',
            'time_blocks' => 'nullable|array',
            'time_blocks.*.start_time' => 'nullable|date_format:H:i',
            'time_blocks.*.end_time' => 'nullable|date_format:H:i',
            'is_active'   => 'nullable|boolean'
        ]);

        $scheduleType = 'recurring';
        $startDate = null;
        $endDate = null;
        $isActive = $request->boolean('is_active', true);
        $selectedServiceId = (int) $data['service_id'];

        if (! in_array($selectedServiceId, $doctorServiceIds, true)) {
            return back()->withErrors(['service_id' => 'Please select a service you offer.'])->withInput();
        }

        $selectedClinicIds = $doctor->services()
            ->where('services.id', $selectedServiceId)
            ->pluck('clinic_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedClinicIds->isEmpty()) {
            return back()->withErrors(['service_id' => 'The selected service is not assigned to any clinic.'])->withInput();
        }

        $invalidClinicIds = $selectedClinicIds->diff($assignedClinicIds);
        if ($invalidClinicIds->isNotEmpty()) {
            return back()->withErrors(['service_id' => 'The selected service belongs to a clinic not assigned to your account.'])->withInput();
        }

        $selectedDays = collect($data['days'] ?? [])
            ->map(static fn ($value) => (int) $value)
            ->unique()
            ->sort()
            ->values();

        if ($selectedDays->isEmpty()) {
            return back()->withErrors(['days' => 'Please select at least one day.'])->withInput();
        }

        $timeBlocks = $this->validatedTimeBlocks($data, true);
        if (empty($timeBlocks)) {
            return back()->withErrors(['time_blocks' => 'Please add at least one time block.'])->withInput();
        }

        $overlapDays = [];
        if ($isActive) {
            foreach ($selectedClinicIds as $clinicId) {
                foreach ($selectedDays as $day) {
                    foreach ($timeBlocks as $block) {
                        $overlap = $this->overlappingScheduleQuery(
                            doctorId: (int) $doctor->id,
                            day: (int) $day,
                            startTime: $block['start_time'],
                            endTime: $block['end_time'],
                            startDate: $startDate,
                            endDate: $endDate
                        )->exists();

                        if ($overlap) {
                            $overlapDays[] = (string) ($clinicId ? ($clinicNames[$clinicId] ?? 'Clinic') : 'Clinic').': '.(self::DAY_LABELS[$day] ?? ('Day '.$day));
                        }
                    }
                }
            }
        }

        $overlapDays = array_values(array_unique($overlapDays));
        if (! empty($overlapDays)) {
            return back()
                ->withErrors(['start_time' => 'Overlapping schedule entry across your clinics on: '.implode(', ', $overlapDays).'.'])
                ->withInput();
        }

        foreach ($selectedClinicIds as $clinicId) {
            foreach ($selectedDays as $day) {
                foreach ($timeBlocks as $block) {
                    DoctorSchedule::create([
                        'doctor_id'  => $doctor->id,
                        'service_id' => $selectedServiceId,
                        'clinic_id'  => $clinicId,
                        'schedule_type' => $scheduleType,
                        'day_of_week'=> $day,
                        'start_date' => $startDate,
                        'end_date'   => $endDate,
                        'start_time' => $block['start_time'],
                        'end_time'   => $block['end_time'],
                        'is_active'  => $isActive,
                    ]);
                }
            }
        }

        $suffix = $selectedDays->count() > 1 ? 's' : '';
        return back()->with('status', 'Schedule added for '.$selectedDays->count().' day'.$suffix.' and '.count($timeBlocks).' time block(s).');
=======

        $validator = Validator::make($request->all(), [
            'clinic_id'   => 'required|exists:clinics,id',
            'day_of_weeks' => 'nullable|array|min:1',
            'day_of_weeks.*' => 'integer|min:0|max:6|distinct',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'is_active'   => 'nullable|boolean'
        ]);

        $validator->after(function ($validator) use ($request) {
            if (! $request->filled('day_of_week') && empty($request->input('day_of_weeks', []))) {
                $validator->errors()->add('day_of_weeks', 'Please select at least one day.');
            }
        });

        $data = $validator->validate();

        $days = collect($data['day_of_weeks'] ?? [])
            ->map(fn ($day) => (int) $day);

        if ($days->isEmpty() && isset($data['day_of_week'])) {
            $days = collect([(int) $data['day_of_week']]);
        }

        $days = $days->unique()->values();

        if (! $doctor->clinics()->where('clinics.id', $data['clinic_id'])->exists()) {
            return back()->withErrors(['clinic_id' => 'You are not associated with this clinic.'])->withInput();
        }

        $overlapDays = [];
        foreach ($days as $day) {
            $overlap = DoctorSchedule::where('doctor_id', $doctor->id)
                ->where('clinic_id', $data['clinic_id'])
                ->where('day_of_week', $day)
                ->where(function($q) use ($data) {
                    $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                      ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                      ->orWhere(function($q2) use ($data){
                          $q2->where('start_time', '<=', $data['start_time'])
                             ->where('end_time', '>=', $data['end_time']);
                      });
                })
                ->exists();

            if ($overlap) {
                $overlapDays[] = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][$day] ?? (string) $day;
            }
        }

        if (! empty($overlapDays)) {
            return back()->withErrors([
                'start_time' => 'Overlapping schedule entry for: '.implode(', ', $overlapDays).'.',
            ])->withInput();
        }

        foreach ($days as $day) {
            DoctorSchedule::create([
                'doctor_id'  => $doctor->id,
                'clinic_id'  => $data['clinic_id'],
                'day_of_week'=> $day,
                'start_time' => $data['start_time'],
                'end_time'   => $data['end_time'],
                'is_active'  => $request->boolean('is_active', true),
            ]);
        }

        $count = $days->count();
        return back()->with('status', $count > 1 ? "{$count} schedules added." : 'Schedule added.');
>>>>>>> Stashed changes
    }

    public function destroy(DoctorSchedule $schedule)
    {
        $doctor = Auth::user();
        $assignedClinicIds = $doctor->clinics()->pluck('clinics.id')->map(fn ($id) => (int) $id)->all();

        if ($schedule->doctor_id !== $doctor->id) {
            abort(403);
        }

        if (! in_array((int) $schedule->clinic_id, $assignedClinicIds, true)) {
            abort(403, 'Schedule does not belong to one of your assigned clinics.');
        }

        $schedule->delete();
        return back()->with('status','Schedule removed.');
    }

    public function update(Request $request, DoctorSchedule $schedule)
    {
        $doctor = Auth::user();
        $assignedClinicIds = $doctor->clinics()->pluck('clinics.id')->map(fn ($id) => (int) $id)->all();

        if ($schedule->doctor_id !== $doctor->id) {
            abort(403);
        }

        if (! in_array((int) $schedule->clinic_id, $assignedClinicIds, true)) {
            abort(403, 'Schedule does not belong to one of your assigned clinics.');
        }

        $data = $request->validate([
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i',
            'time_blocks' => 'nullable|array',
            'time_blocks.*.start_time' => 'nullable|date_format:H:i',
            'time_blocks.*.end_time' => 'nullable|date_format:H:i',
            'is_active'   => 'nullable|boolean'
        ]);

        $scheduleType = 'recurring';
        $startDate = null;
        $endDate = null;
        $isActive = $request->boolean('is_active', true);

        $timeBlocks = $this->validatedTimeBlocks($data, false);
        $block = $timeBlocks[0];

        $overlap = $isActive && $this->overlappingScheduleQuery(
            doctorId: (int) $doctor->id,
            day: (int) $data['day_of_week'],
            startTime: $block['start_time'],
            endTime: $block['end_time'],
            startDate: $startDate,
            endDate: $endDate,
            exceptId: (int) $schedule->id
        )->exists();

        if ($overlap) {
            return back()->withErrors(['start_time' => 'Overlapping schedule entry across your clinics.'])->withInput();
        }

        $schedule->update([
            'schedule_type' => $scheduleType,
            'day_of_week'=> $data['day_of_week'],
            'start_date' => null,
            'end_date'   => null,
            'start_time' => $block['start_time'],
            'end_time'   => $block['end_time'],
            'is_active'  => $isActive,
        ]);

        return back()->with('status','Schedule updated.');
    }



    public function feed(Request $request)
    {
        $doctor = Auth::user();
        $clinicIds = $doctor->clinics()->pluck('clinics.id')->map(fn ($id) => (int) $id)->all();

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

        $schedules = DoctorSchedule::with('clinic', 'service')
            ->where('doctor_id', $doctor->id)
            ->whereIn('clinic_id', $clinicIds)
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
                    'title' => $schedule->service?->name ?? '—',
                    'start' => $cursor->copy()->setTimeFromTimeString($schedule->start_time)->toIso8601String(),
                    'end' => $cursor->copy()->setTimeFromTimeString($schedule->end_time)->toIso8601String(),
                    'display' => 'block',
                    'extendedProps' => [
                        'clinic' => $schedule->clinic?->name ?? 'Clinic',
                        'service' => $schedule->service?->name ?? null,
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

    private function validatedTimeBlocks(array $data, bool $allowMultiple): array
    {
        $rawBlocks = [];

        if (($allowMultiple || empty($data['start_time']) || empty($data['end_time'])) && ! empty($data['time_blocks']) && is_array($data['time_blocks'])) {
            $rawBlocks = $data['time_blocks'];
        } else {
            $rawBlocks[] = [
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
            ];
        }

        $blocks = [];
        foreach ($rawBlocks as $index => $block) {
            $start = $block['start_time'] ?? null;
            $end = $block['end_time'] ?? null;

            if ($start === null && $end === null) {
                continue;
            }

            if (! $start || ! $end) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'time_blocks' => 'Each time block needs both a start and end time.',
                ]);
            }

            if (Carbon::createFromFormat('H:i', $end)->lessThanOrEqualTo(Carbon::createFromFormat('H:i', $start))) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'time_blocks' => 'Each time block end must be after its start time.',
                ]);
            }

            foreach ($blocks as $existing) {
                if ($start < $existing['end_time'] && $end > $existing['start_time']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'time_blocks' => 'Time blocks cannot overlap each other.',
                    ]);
                }
            }

            $blocks[] = [
                'start_time' => $start,
                'end_time' => $end,
            ];
        }

        usort($blocks, fn ($a, $b) => strcmp($a['start_time'], $b['start_time']));

        if (empty($blocks)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'time_blocks' => 'Please add at least one time block.',
            ]);
        }

        return $blocks;
    }



    private function overlappingScheduleQuery(
        int $doctorId,
        int $day,
        string $startTime,
        string $endTime,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $exceptId = null
    ) {
        return DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('day_of_week', $day)
            ->where('is_active', true)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->where(function ($query) use ($startDate, $endDate) {
                if ($endDate !== null) {
                    $query->where(function ($dateQuery) use ($endDate) {
                        $dateQuery->whereNull('start_date')
                            ->orWhereDate('start_date', '<=', $endDate);
                    });
                }

                if ($startDate !== null) {
                    $query->where(function ($dateQuery) use ($startDate) {
                        $dateQuery->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $startDate);
                    });
                }
            });
    }
}
