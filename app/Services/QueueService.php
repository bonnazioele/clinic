<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\QueueEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QueueService
{
    public function getSlotQueueNumber(
        int $clinicId,
        int $doctorId,
        ?int $serviceId,
        Carbon|string $date,
        string $time
    ): int
    {
        $normalizedTime = $this->normalizeTimeLabel($time);

        $slotIndex = $this->buildSlotGrid($clinicId, $doctorId, $serviceId, $date)
            ->search(fn (array $slot) => $slot['time'] === $normalizedTime);

        if ($slotIndex === false) {
            throw new \InvalidArgumentException('Selected time is not part of the doctor schedule slot grid.');
        }

        return $slotIndex + 1;
    }

    public function getSlotMinutes(int $clinicId, ?int $serviceId): int
    {
        if (! $serviceId) {
            return 30;
        }

        $duration = DB::table('clinic_service')
            ->where('clinic_id', $clinicId)
            ->where('service_id', $serviceId)
            ->value('duration_minutes');

        if ($duration && is_numeric($duration) && $duration > 0 && $duration <= 480) {
            return (int) $duration;
        }

        return 30;
    }

    public function schedulesForDate(int $clinicId, int $doctorId, Carbon|string $date): Collection
    {
        $date = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $dateString = $date->toDateString();

        return DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_active', true)
            ->where(function ($q) use ($dateString) {
                $q->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $dateString);
            })
            ->where(function ($q) use ($dateString) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $dateString);
            })
            ->orderBy('start_time')
            ->get(['id', 'start_time', 'end_time']);
    }

    public function buildSlotGrid(int $clinicId, int $doctorId, ?int $serviceId, Carbon|string $date): Collection
    {
        $date = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $slotMinutes = $this->getSlotMinutes($clinicId, $serviceId);
        $slots = collect();

        foreach ($this->schedulesForDate($clinicId, $doctorId, $date) as $schedule) {
            $start = Carbon::createFromFormat(
                strlen((string) $schedule->start_time) === 5 ? 'H:i' : 'H:i:s',
                (string) $schedule->start_time,
                $date->timezone
            )->setDate($date->year, $date->month, $date->day);

            $end = Carbon::createFromFormat(
                strlen((string) $schedule->end_time) === 5 ? 'H:i' : 'H:i:s',
                (string) $schedule->end_time,
                $date->timezone
            )->setDate($date->year, $date->month, $date->day);

            $cursor = $start->copy();

            while ($cursor < $end) {
                $slotEnd = $cursor->copy()->addMinutes($slotMinutes);

                if ($slotEnd > $end) {
                    break;
                }

                $slots->push([
                    'time' => $cursor->format('H:i'),
                    'time_with_seconds' => $cursor->format('H:i:s'),
                    'start_at' => $cursor->copy(),
                    'end_at' => $slotEnd->copy(),
                    'display' => $cursor->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                    'end_time' => $slotEnd->format('H:i'),
                ]);

                $cursor->addMinutes($slotMinutes);
            }
        }

        return $slots
            ->unique('time')
            ->sortBy('time')
            ->values();
    }

    public function occupiedSlotTimes(int $clinicId, int $doctorId, Carbon|string $date): Collection
    {
        $date = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $dateString = $date->toDateString();

        $queueTimes = QueueEntry::query()
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->whereDate('scheduled_slot_date', $dateString)
            ->whereNotNull('scheduled_slot_time')
            ->pluck('scheduled_slot_time')
            ->map(fn ($time) => $this->normalizeTimeLabel($time));

        $appointmentTimes = Appointment::query()
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $dateString)
            ->whereNotIn('status', ['cancelled', 'no_show', 'rescheduled'])
            ->pluck('appointment_time')
            ->map(fn ($time) => $this->normalizeTimeLabel($time));

        return $queueTimes
            ->merge($appointmentTimes)
            ->filter()
            ->unique()
            ->values();
    }

    public function availableSlots(
        int $clinicId,
        int $doctorId,
        ?int $serviceId,
        Carbon|string $date,
        int $bufferMinutes = 0
    ): Collection {
        $date = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $occupied = $this->occupiedSlotTimes($clinicId, $doctorId, $date);
        $cutoff = $date->isToday() ? now()->addMinutes($bufferMinutes) : null;

        return $this->buildSlotGrid($clinicId, $doctorId, $serviceId, $date)
            ->filter(function (array $slot) use ($cutoff) {
                return ! $cutoff || ! $slot['start_at']->lt($cutoff);
            })
            ->map(function (array $slot) use ($occupied, $cutoff) {
                $expired = $cutoff ? $slot['start_at']->lt($cutoff) : false;

                return array_merge($slot, [
                    'occupied' => $occupied->contains($slot['time']),
                    'expired' => $expired,
                    'available' => ! $occupied->contains($slot['time']) && ! $expired,
                ]);
            })
            ->values();
    }

    public function slotIsAvailable(
        int $clinicId,
        int $doctorId,
        ?int $serviceId,
        Carbon|string $date,
        string $time,
        int $bufferMinutes = 0
    ): bool {
        $time = $this->normalizeTimeLabel($time);

        return $this->availableSlots($clinicId, $doctorId, $serviceId, $date, $bufferMinutes)
            ->contains(fn (array $slot) => $slot['time'] === $time && $slot['available']);
    }

    public function findEarliestWalkInSlot(
        int $clinicId,
        int $doctorId,
        int $serviceId,
        Carbon|string|null $date = null,
        int $bufferMinutes = 15
    ): ?array {
        $date = $date ? ($date instanceof Carbon ? $date->copy() : Carbon::parse($date)) : now();

        return $this->availableSlots($clinicId, $doctorId, $serviceId, $date, $bufferMinutes)
            ->firstWhere('available', true);
    }

    public function normalizeTimeLabel($time): ?string
    {
        if ($time instanceof Carbon) {
            return $time->format('H:i');
        }

        if ($time === null || $time === '') {
            return null;
        }

        if (is_string($time) && strlen($time) >= 5) {
            return substr($time, 0, 5);
        }

        return Carbon::parse($time)->format('H:i');
    }
}
