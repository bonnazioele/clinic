<?php

namespace App\Services;

use App\Models\DoctorSchedule;
use Carbon\Carbon;

class DoctorScheduleAvailability
{
    public function doctorHasScheduleAt(int $doctorId, int $clinicId, int $serviceId, string $appointmentDate, string $time): bool
    {
        $date = Carbon::parse($appointmentDate)->startOfDay();
        $appointmentAt = $date->copy()->setTimeFromTimeString($this->normalizeTime($time));

        return $this->schedulesForAppointmentDate($doctorId, $clinicId, $serviceId, $date)
            ->contains(function ($schedule) use ($date, $appointmentAt) {
                [$start, $end] = $this->scheduleDateTimes($schedule, $date);

                return $appointmentAt->gte($start) && $appointmentAt->lt($end);
            });
    }

    public function schedulesForAppointmentDate(int $doctorId, int $clinicId, ?int $serviceId, Carbon $date)
    {
        $day = (int) $date->dayOfWeek;
        $previousDay = ($day + 6) % 7;

        return DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->whereIn('day_of_week', [$day, $previousDay])
            ->where('is_active', true)
            ->when($serviceId, fn ($query) => $query->where('service_id', $serviceId))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get(['id', 'service_id', 'day_of_week', 'start_date', 'end_date', 'start_time', 'end_time'])
            ->filter(function ($schedule) use ($date, $day, $previousDay) {
                if ((int) $schedule->day_of_week === $previousDay && ! $this->scheduleIsOvernight($schedule)) {
                    return false;
                }

                $occurrenceDate = (int) $schedule->day_of_week === $day
                    ? $date->copy()
                    : $date->copy()->subDay();

                return $this->scheduleAppliesOn($schedule, $occurrenceDate);
            })
            ->values();
    }

    public function scheduleDateTimes($schedule, Carbon $selectedDate): array
    {
        $occurrenceDate = (int) $schedule->day_of_week === (int) $selectedDate->dayOfWeek
            ? $selectedDate->copy()
            : $selectedDate->copy()->subDay();

        $start = $occurrenceDate->copy()->setTimeFromTimeString($this->normalizeTime($schedule->start_time));
        $end = $occurrenceDate->copy()->setTimeFromTimeString($this->normalizeTime($schedule->end_time));

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return [$start, $end];
    }

    private function scheduleAppliesOn($schedule, Carbon $occurrenceDate): bool
    {
        if ($schedule->start_date && $occurrenceDate->lt(Carbon::parse($schedule->start_date)->startOfDay())) {
            return false;
        }

        if ($schedule->end_date && $occurrenceDate->gt(Carbon::parse($schedule->end_date)->startOfDay())) {
            return false;
        }

        return true;
    }

    private function scheduleIsOvernight($schedule): bool
    {
        return $this->normalizeTime($schedule->end_time) <= $this->normalizeTime($schedule->start_time);
    }

    public function normalizeTime($time): string
    {
        if ($time instanceof Carbon) {
            return $time->format('H:i');
        }

        return substr((string) $time, 0, 5);
    }
}
