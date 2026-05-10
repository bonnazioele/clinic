<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\Appointment;
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
        $activeClinic = $this->activeClinic($request);
        $activeClinicId = (int) $activeClinic->id;
        $clinics = $doctor->clinics()->orderBy('name')->get(['clinics.id', 'clinics.name']);
        $services = $doctor->servicesForClinic($activeClinicId)
            ->distinct()
            ->orderBy('services.name')
            ->get(['services.id', 'services.name']);

        $schedules = DoctorSchedule::with('clinic', 'service')
            ->where('doctor_id', $doctor->id)
            ->where('clinic_id', $activeClinicId)
            ->orderBy('day_of_week')
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        return view('doctor.schedules.index', compact('schedules','clinics','services','activeClinic'));
    }

    public function store(Request $request)
    {
        $doctor = Auth::user();
        $activeClinicId = $this->activeClinicId($request);
        $assignedClinicIds = $doctor->clinics()->pluck('clinics.id')->map(fn ($id) => (int) $id)->all();
        $clinicNames = $doctor->clinics()->pluck('clinics.name', 'clinics.id');
        $doctorServiceIds = $doctor->servicesForClinic($activeClinicId)
            ->pluck('services.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $data = $request->validate([
            'service_ids'  => 'nullable|array|min:1',
            'service_ids.*' => 'integer|exists:services,id',
            'schedule_type' => 'required|in:recurring,one_time',
            'days'        => 'nullable|array|min:1',
            'days.*'      => 'integer|min:0|max:6',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'one_time_date' => 'nullable|date',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i',
            'time_blocks' => 'nullable|array',
            'time_blocks.*.start_time' => 'nullable|date_format:H:i',
            'time_blocks.*.end_time' => 'nullable|date_format:H:i',
            'is_active'   => 'nullable|boolean'
        ]);

        $scheduleType = $data['schedule_type'];
        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;
        $isActive = $request->boolean('is_active', true);

        $selectedServiceIds = collect($data['service_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedServiceIds->isEmpty()) {
            return back()->withErrors(['service_ids' => 'Please select at least one service.'])->withInput();
        }

        $invalidServiceIds = $selectedServiceIds->diff($doctorServiceIds);
        if ($invalidServiceIds->isNotEmpty()) {
            return back()->withErrors(['service_ids' => 'Please select only services you offer.'])->withInput();
        }

        if (! in_array($activeClinicId, $assignedClinicIds, true)) {
            return back()->withErrors(['service_ids' => 'The active clinic is not assigned to your account.'])->withInput();
        }

        if ($scheduleType === 'one_time') {
            if (empty($data['one_time_date'])) {
                return back()->withErrors(['one_time_date' => 'Please choose a specific date for the one-time schedule.'])->withInput();
            }

            $oneTimeDate = Carbon::parse($data['one_time_date'])->toDateString();
            $startDate = $oneTimeDate;
            $endDate = $oneTimeDate;
            $selectedDays = collect([Carbon::parse($oneTimeDate)->dayOfWeek]);
        } else {
            $selectedDays = collect($data['days'] ?? [])
                ->push($data['day_of_week'] ?? null)
                ->filter(static fn ($value) => $value !== null)
                ->map(static fn ($value) => (int) $value)
                ->unique()
                ->sort()
                ->values();
        }

        if ($selectedDays->isEmpty()) {
            return back()->withErrors(['days' => 'Please select at least one day.'])->withInput();
        }

        if ($scheduleType === 'one_time') {
            $allowedDays = $selectedDays->all();
            if (! $this->dateMatchesAllowedDays($startDate, $allowedDays)) {
                return back()->withErrors(['start_date' => 'Start date must fall on the selected one-time schedule day.'])->withInput();
            }
            if (! $this->dateMatchesAllowedDays($endDate, $allowedDays)) {
                return back()->withErrors(['end_date' => 'End date must fall on the selected one-time schedule day.'])->withInput();
            }
        }

        $timeBlocks = $this->validatedTimeBlocks($data, true);
        if (empty($timeBlocks)) {
            return back()->withErrors(['time_blocks' => 'Please add at least one time block.'])->withInput();
        }

        $overlapDays = [];
        if ($isActive) {
            foreach ($selectedDays as $day) {
                foreach ($timeBlocks as $block) {
                    $overlap = $this->hasOverlappingSchedule(
                        doctorId: (int) $doctor->id,
                        day: (int) $day,
                        startTime: $block['start_time'],
                        endTime: $block['end_time'],
                        startDate: $startDate,
                        endDate: $endDate
                    );

                    if ($overlap) {
                        $overlapDays[] = (string) ($clinicNames[$activeClinicId] ?? 'Clinic').': '.(self::DAY_LABELS[$day] ?? ('Day '.$day));
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

        foreach ($selectedServiceIds as $serviceId) {
            foreach ($selectedDays as $day) {
                foreach ($timeBlocks as $block) {
                    DoctorSchedule::create([
                        'doctor_id'  => $doctor->id,
                        'service_id' => $serviceId,
                        'clinic_id'  => $activeClinicId,
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
    }

    public function destroy(Request $request, DoctorSchedule $schedule)
    {
        $doctor = Auth::user();
        $activeClinicId = $this->activeClinicId($request);

        if ($schedule->doctor_id !== $doctor->id) {
            abort(403);
        }

        if ((int) $schedule->clinic_id !== $activeClinicId) {
            abort(403, 'Schedule does not belong to your active clinic.');
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
            abort(403, 'Schedule does not belong to your active clinic.');
        }

        $data = $request->validate([
            'service_ids'  => 'nullable|array|min:1',
            'service_ids.*' => 'integer|exists:services,id',
            'schedule_type' => 'required|in:recurring,one_time',
            'days'        => 'nullable|array',
            'days.*'      => 'integer|min:0|max:6',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'one_time_date' => 'nullable|date',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i',
            'time_blocks' => 'nullable|array',
            'time_blocks.*.start_time' => 'nullable|date_format:H:i',
            'time_blocks.*.end_time' => 'nullable|date_format:H:i',
            'is_active'   => 'nullable|boolean'
        ]);

        $scheduleType = $data['schedule_type'];
        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;
        $isActive = $request->boolean('is_active', true);

        $selectedServiceIds = collect($data['service_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedServiceIds->isEmpty()) {
            return back()->withErrors(['service_ids' => 'Please select a service.'])->withInput();
        }

        $doctorServiceIds = $doctor->servicesForClinic($activeClinicId)
            ->pluck('services.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $invalidServiceIds = $selectedServiceIds->diff($doctorServiceIds);
        if ($invalidServiceIds->isNotEmpty()) {
            return back()->withErrors(['service_ids' => 'Please select only services you offer.'])->withInput();
        }

        $selectedServiceId = (int) $selectedServiceIds->first();

        if ($scheduleType === 'one_time' && ! empty($data['one_time_date'])) {
            $oneTimeDate = Carbon::parse($data['one_time_date'])->toDateString();
            $startDate = $oneTimeDate;
            $endDate = $oneTimeDate;
            $data['day_of_week'] = Carbon::parse($oneTimeDate)->dayOfWeek;
        } else {
            $selectedDays = collect($data['days'] ?? [])
                ->push($data['day_of_week'] ?? null)
                ->filter(static fn ($value) => $value !== null)
                ->map(static fn ($value) => (int) $value)
                ->unique()
                ->values();

            if ($selectedDays->isEmpty()) {
                return back()->withErrors(['days' => 'Please select a day.'])->withInput();
            }

            if ($selectedDays->count() > 1) {
                return back()->withErrors(['days' => 'Choose one day when updating a single schedule.'])->withInput();
            }

            $data['day_of_week'] = $selectedDays->first();
        }

        if ($scheduleType === 'one_time') {
            $allowedDays = [(int) $data['day_of_week']];
            if (! $this->dateMatchesAllowedDays($startDate, $allowedDays)) {
                return back()->withErrors(['start_date' => 'Start date must match the selected one-time schedule day.'])->withInput();
            }
            if (! $this->dateMatchesAllowedDays($endDate, $allowedDays)) {
                return back()->withErrors(['end_date' => 'End date must match the selected one-time schedule day.'])->withInput();
            }
        }

        $timeBlocks = $this->validatedTimeBlocks($data, false);
        $block = $timeBlocks[0];

        $overlap = $isActive && $this->hasOverlappingSchedule(
            doctorId: (int) $doctor->id,
            day: (int) $data['day_of_week'],
            startTime: $block['start_time'],
            endTime: $block['end_time'],
            startDate: $startDate,
            endDate: $endDate,
            exceptId: (int) $schedule->id
        );

        if ($overlap) {
            return back()->withErrors(['start_time' => 'Overlapping schedule entry across your clinics.'])->withInput();
        }

        $proposedSchedule = [
            'service_id' => $selectedServiceId,
            'schedule_type' => $scheduleType,
            'day_of_week' => (int) $data['day_of_week'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => $block['start_time'],
            'end_time' => $block['end_time'],
            'is_active' => $isActive,
        ];

        $invalidatedAppointments = $this->activeAppointmentsInvalidatedByScheduleChange($schedule, $proposedSchedule);

        if ($invalidatedAppointments->isNotEmpty()) {
            return back()
                ->withErrors([
                    'start_time' => 'This edit would make '.$invalidatedAppointments->count().' active booked appointment(s) fall outside the doctor schedule. Reschedule or cancel those appointments first.',
                ])
                ->withInput();
        }

        $schedule->update([
            'service_id' => $selectedServiceId,
            'schedule_type' => $scheduleType,
            'day_of_week'=> $data['day_of_week'],
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'start_time' => $block['start_time'],
            'end_time'   => $block['end_time'],
            'is_active'  => $isActive,
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

        $schedules = DoctorSchedule::with('clinic', 'service')
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

            if ($schedule->schedule_type === 'one_time') {
                $eventDate = $schedule->start_date ? Carbon::parse($schedule->start_date) : $effectiveStart->copy();
                if ($eventDate->betweenIncluded($effectiveStart, $effectiveEnd)) {
                    $events[] = [
                        'id' => 'schedule-'.$schedule->id.'-'.$eventDate->format('Ymd'),
                        'title' => $schedule->service?->name ?? 'Service',
                        'start' => $this->eventStart($eventDate, $schedule->start_time)->toIso8601String(),
                        'end' => $this->eventEnd($eventDate, $schedule->start_time, $schedule->end_time)->toIso8601String(),
                        'display' => 'block',
                        'extendedProps' => [
                            'clinic' => $schedule->clinic?->name ?? 'Clinic',
                            'service' => $schedule->service?->name ?? null,
                            'scheduleId' => $schedule->id,
                            'schedule_type' => $schedule->schedule_type,
                            'day_of_week' => $schedule->day_of_week,
                            'start_date' => $schedule->start_date,
                            'end_date' => $schedule->end_date,
                            'start_time' => substr($schedule->start_time, 0, 5),
                            'end_time' => substr($schedule->end_time, 0, 5),
                        ],
                    ];
                }
                continue;
            }

            $scheduleDay = (int) $schedule->day_of_week;
            $daysUntilScheduleDay = ($scheduleDay - (int) $effectiveStart->dayOfWeek + 7) % 7;
            $cursor = $effectiveStart->copy()->addDays($daysUntilScheduleDay);
            while ($cursor->lte($effectiveEnd)) {
                $events[] = [
                    'id' => 'schedule-'.$schedule->id.'-'.$cursor->format('Ymd'),
                    'title' => $schedule->service?->name ?? 'Service',
                    'start' => $this->eventStart($cursor, $schedule->start_time)->toIso8601String(),
                    'end' => $this->eventEnd($cursor, $schedule->start_time, $schedule->end_time)->toIso8601String(),
                    'display' => 'block',
                    'extendedProps' => [
                        'clinic' => $schedule->clinic?->name ?? 'Clinic',
                        'service' => $schedule->service?->name ?? null,
                        'scheduleId' => $schedule->id,
                        'schedule_type' => $schedule->schedule_type,
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

            /*
             * Overnight schedules are allowed.
             * Example: 20:00 to 01:00 means the schedule continues into the next day.
             * Only reject exactly equal times because that would create a zero-length block.
             */
            if ($start === $end) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'time_blocks' => 'Start time and end time cannot be exactly the same.',
                ]);
            }

            $newInterval = $this->normalizedInterval(0, $start, $end);

            foreach ($blocks as $existing) {
                $existingInterval = $this->normalizedInterval(0, $existing['start_time'], $existing['end_time']);

                if ($this->intervalsOverlap($newInterval, $existingInterval)) {
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



    private function hasOverlappingSchedule(
        int $doctorId,
        int $day,
        string $startTime,
        string $endTime,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $exceptId = null
    ): bool {
        $day = (int) $day;

        /*
         * For overnight support, a schedule can overlap:
         * - schedules on the same day,
         * - overnight schedules from the previous day,
         * - schedules on the next day when the new schedule crosses midnight.
         */
        $relatedDays = array_values(array_unique([
            $day,
            ($day + 6) % 7,
            ($day + 1) % 7,
        ]));

        $existingSchedules = DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->whereIn('day_of_week', $relatedDays)
            ->where('is_active', true)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
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
            })
            ->get(['id', 'day_of_week', 'start_time', 'end_time']);

        $newIntervals = $this->weeklyIntervals($day, $startTime, $endTime);

        foreach ($existingSchedules as $existing) {
            $existingIntervals = $this->weeklyIntervals(
                (int) $existing->day_of_week,
                substr((string) $existing->start_time, 0, 5),
                substr((string) $existing->end_time, 0, 5)
            );

            foreach ($newIntervals as $newInterval) {
                foreach ($existingIntervals as $existingInterval) {
                    if ($this->intervalsOverlap($newInterval, $existingInterval)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function activeAppointmentsInvalidatedByScheduleChange(DoctorSchedule $schedule, array $proposedSchedule)
    {
        $otherSchedules = DoctorSchedule::query()
            ->where('doctor_id', $schedule->doctor_id)
            ->where('clinic_id', $schedule->clinic_id)
            ->where('service_id', $schedule->service_id)
            ->where('is_active', true)
            ->whereKeyNot($schedule->id)
            ->get();

        return Appointment::query()
            ->where('doctor_id', $schedule->doctor_id)
            ->where('clinic_id', $schedule->clinic_id)
            ->where('service_id', $schedule->service_id)
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get()
            ->filter(function (Appointment $appointment) use ($schedule, $proposedSchedule, $otherSchedules) {
                $date = $appointment->appointment_date?->toDateString();
                $time = $appointment->getRawOriginal('appointment_time');

                if (! $date || ! $time || ! $this->scheduleCoversAppointment($schedule, $date, $time)) {
                    return false;
                }

                if ($this->scheduleCoversAppointment($proposedSchedule, $date, $time, (int) $appointment->service_id)) {
                    return false;
                }

                return ! $otherSchedules->contains(function (DoctorSchedule $otherSchedule) use ($date, $time) {
                    return $this->scheduleCoversAppointment($otherSchedule, $date, $time);
                });
            })
            ->values();
    }

    private function scheduleCoversAppointment($schedule, string $appointmentDate, string $appointmentTime, ?int $requiredServiceId = null): bool
    {
        $serviceId = $this->scheduleValue($schedule, 'service_id');

        if ($requiredServiceId !== null && (int) $serviceId !== $requiredServiceId) {
            return false;
        }

        if (! (bool) $this->scheduleValue($schedule, 'is_active', true)) {
            return false;
        }

        $appointmentDay = Carbon::parse($appointmentDate)->startOfDay();
        $appointmentAt = $appointmentDay->copy()->setTimeFromTimeString(substr($appointmentTime, 0, 5));
        $scheduleDay = (int) $this->scheduleValue($schedule, 'day_of_week');
        $startTime = substr((string) $this->scheduleValue($schedule, 'start_time'), 0, 5);
        $endTime = substr((string) $this->scheduleValue($schedule, 'end_time'), 0, 5);
        $isOvernight = $endTime <= $startTime;

        $occurrenceDates = [];

        if ((int) $appointmentDay->dayOfWeek === $scheduleDay) {
            $occurrenceDates[] = $appointmentDay->copy();
        }

        $previousDay = $appointmentDay->copy()->subDay();
        if ($isOvernight && (int) $previousDay->dayOfWeek === $scheduleDay) {
            $occurrenceDates[] = $previousDay;
        }

        foreach ($occurrenceDates as $occurrenceDate) {
            if (! $this->scheduleAppliesOnDate($schedule, $occurrenceDate)) {
                continue;
            }

            $start = $occurrenceDate->copy()->setTimeFromTimeString($startTime);
            $end = $occurrenceDate->copy()->setTimeFromTimeString($endTime);

            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }

            if ($appointmentAt->gte($start) && $appointmentAt->lt($end)) {
                return true;
            }
        }

        return false;
    }

    private function scheduleAppliesOnDate($schedule, Carbon $occurrenceDate): bool
    {
        $startDate = $this->scheduleValue($schedule, 'start_date');
        $endDate = $this->scheduleValue($schedule, 'end_date');

        if ($startDate && $occurrenceDate->lt(Carbon::parse($startDate)->startOfDay())) {
            return false;
        }

        if ($endDate && $occurrenceDate->gt(Carbon::parse($endDate)->startOfDay())) {
            return false;
        }

        return true;
    }

    private function scheduleValue($schedule, string $key, $default = null)
    {
        if (is_array($schedule)) {
            return $schedule[$key] ?? $default;
        }

        return $schedule->{$key} ?? $default;
    }

    private function weeklyIntervals(int $day, string $startTime, string $endTime): array
    {
        $base = $this->normalizedInterval($day, $startTime, $endTime);
        $weekMinutes = 7 * 24 * 60;

        /*
         * Duplicates shifted by one week let Sunday-to-Monday and Saturday-to-Sunday
         * overnight schedules overlap correctly around the week boundary.
         */
        return [
            $base,
            [$base[0] - $weekMinutes, $base[1] - $weekMinutes],
            [$base[0] + $weekMinutes, $base[1] + $weekMinutes],
        ];
    }

    private function normalizedInterval(int $day, string $startTime, string $endTime): array
    {
        $start = ($day * 24 * 60) + $this->timeToMinutes($startTime);
        $end = ($day * 24 * 60) + $this->timeToMinutes($endTime);

        if ($end <= $start) {
            $end += 24 * 60;
        }

        return [$start, $end];
    }

    private function intervalsOverlap(array $first, array $second): bool
    {
        return $first[0] < $second[1] && $first[1] > $second[0];
    }

    private function timeToMinutes(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', substr($time, 0, 5)));

        return ($hour * 60) + $minute;
    }

    private function eventStart(Carbon $date, string $startTime): Carbon
    {
        return $date->copy()->setTimeFromTimeString($startTime);
    }

    private function eventEnd(Carbon $date, string $startTime, string $endTime): Carbon
    {
        $start = $date->copy()->setTimeFromTimeString($startTime);
        $end = $date->copy()->setTimeFromTimeString($endTime);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return $end;
    }

}
