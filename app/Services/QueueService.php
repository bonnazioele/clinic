<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ClinicOperationalHour;
use App\Models\PatientVisit;
use App\Models\QueueEntry;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

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

    public function getSlotMinutes(int $clinicId, ?int $serviceId, ?int $doctorId = null): int
    {
        if (! $serviceId) {
            return 30;
        }

        // Prefer the per-doctor duration stored in doctor_service pivot
        if ($doctorId) {
            $duration = DB::table('doctor_service')
                ->where('clinic_id', $clinicId)
                ->where('doctor_id', $doctorId)
                ->where('service_id', $serviceId)
                ->value('duration_minutes');

            if ($duration && is_numeric($duration) && $duration > 0 && $duration <= 480) {
                return (int) $duration;
            }
        }

        // Fall back to clinic-level duration if no doctor-specific one exists
        $duration = DB::table('clinic_service')
            ->where('clinic_id', $clinicId)
            ->where('service_id', $serviceId)
            ->value('duration_minutes');

        if ($duration && is_numeric($duration) && $duration > 0 && $duration <= 480) {
            return (int) $duration;
        }

        return 30;
    }

    public function schedulesForDate(int $clinicId, int $doctorId, Carbon|string $date, ?int $serviceId = null): Collection
    {
        $date = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        return app(DoctorScheduleAvailability::class)
            ->schedulesForAppointmentDate($doctorId, $clinicId, $serviceId, $date)
            ->sortBy([
                ['day_of_week', 'asc'],
                ['start_time', 'asc'],
            ])
            ->values();
    }

    public function buildSlotGrid(int $clinicId, int $doctorId, ?int $serviceId, Carbon|string $date): Collection
    {
        $date = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $slotMinutes = $this->getSlotMinutes($clinicId, $serviceId, $doctorId);
        $slots = collect();

        $scheduleAvailability = app(DoctorScheduleAvailability::class);
        $dateString = $date->toDateString();
        $clinicHour = $this->clinicOperationalHourForDate($clinicId, $date);

        if (! $this->clinicIsOpenForDate($clinicHour)) {
            return collect();
        }

        foreach ($this->schedulesForDate($clinicId, $doctorId, $date, $serviceId) as $schedule) {
            [$start, $end] = $scheduleAvailability->scheduleDateTimes($schedule, $date);
            $cursor = $start->copy();

            while ($cursor < $end) {
                $slotEnd = $cursor->copy()->addMinutes($slotMinutes);

                if ($slotEnd > $end) {
                    break;
                }

                if ($cursor->toDateString() === $dateString) {
                    $outsideClinicHours = $this->slotOutsideClinicOperationalHours($clinicHour, $cursor, $slotEnd);
                    $insideClinicBreak = $this->slotOverlapsClinicBreak($clinicHour, $cursor, $slotEnd);

                    $unavailableReason = null;

                    if ($outsideClinicHours) {
                        $unavailableReason = 'Outside clinic hours';
                    } elseif ($insideClinicBreak) {
                        $unavailableReason = 'Clinic break time';
                    }

                    $slots->push([
                        'date' => $cursor->toDateString(),
                        'time' => $cursor->format('H:i'),
                        'time_with_seconds' => $cursor->format('H:i:s'),
                        'start_at' => $cursor->copy(),
                        'end_at' => $slotEnd->copy(),
                        'display' => $cursor->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                        'end_time' => $slotEnd->format('H:i'),
                        'outside_clinic_hours' => $outsideClinicHours,
                        'inside_clinic_break' => $insideClinicBreak,
                        'clinic_unavailable_reason' => $unavailableReason,
                    ]);
                }

                $cursor->addMinutes($slotMinutes);
            }
        }

        return $slots
            ->unique(fn (array $slot) => $slot['date'] . ' ' . $slot['time'])
            ->sortBy(['date', 'time'])
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
            ->whereIn('status', QueueEntry::blockingSlotStatuses())
            ->pluck('scheduled_slot_time')
            ->map(fn ($time) => $this->normalizeTimeLabel($time));

        $appointmentTimes = Appointment::query()
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $dateString)
            ->whereNotIn('status', Appointment::FINAL_STATUSES)
            ->pluck('appointment_time')
            ->map(fn ($time) => $this->normalizeTimeLabel($time));

        return $queueTimes
            ->merge($appointmentTimes)
            ->filter()
            ->unique()
            ->values();
    }

    private function recycleBlockedCompletedSlotTimes(int $clinicId, int $doctorId, Carbon|string $date): Collection
    {
        $date = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $dateString = $date->toDateString();

        $latestBlockingTime = QueueEntry::query()
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->whereDate('scheduled_slot_date', $dateString)
            ->whereNotNull('scheduled_slot_time')
            ->whereIn('status', QueueEntry::blockingSlotStatuses())
            ->max('scheduled_slot_time');

        if (! $latestBlockingTime) {
            return collect();
        }

        $latestBlockingTime = $this->normalizeTimeLabel($latestBlockingTime);

        return QueueEntry::query()
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->whereDate('scheduled_slot_date', $dateString)
            ->whereNotNull('scheduled_slot_time')
            ->whereIn('status', QueueEntry::finalPatientStatuses())
            ->where('scheduled_slot_time', '<=', $latestBlockingTime . ':00')
            ->pluck('scheduled_slot_time')
            ->map(fn ($time) => $this->normalizeTimeLabel($time))
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
        $recycleBlocked = $this->recycleBlockedCompletedSlotTimes($clinicId, $doctorId, $date);
        $cutoff = $date->isToday()
            ? now()->addMinutes($bufferMinutes)
            : ($date->isPast() ? now() : null);

        return $this->buildSlotGrid($clinicId, $doctorId, $serviceId, $date)
            ->filter(function (array $slot) use ($cutoff) {
                /*
                 * Do not show past time slots to patients.
                 * Example: if today is selected and it is already 3:00 PM,
                 * slots before/equal to the current time are removed from the response.
                 */
                return ! $cutoff || $slot['start_at']->gt($cutoff);
            })
            ->map(function (array $slot) use ($occupied, $recycleBlocked) {
                $isOccupied = $occupied->contains($slot['time']);
                $isRecycleBlocked = $recycleBlocked->contains($slot['time']);
                $outsideClinicHours = (bool) ($slot['outside_clinic_hours'] ?? false);
                $insideClinicBreak = (bool) ($slot['inside_clinic_break'] ?? false);

                $reason = null;

                if ($isOccupied) {
                    $reason = 'Booked';
                } elseif ($isRecycleBlocked) {
                    $reason = 'Queue still active';
                } elseif ($outsideClinicHours) {
                    $reason = 'Outside clinic hours';
                } elseif ($insideClinicBreak) {
                    $reason = 'Clinic break time';
                }

                return array_merge($slot, [
                    'occupied' => $isOccupied || $isRecycleBlocked,
                    'expired' => false,
                    'available' => ! $isOccupied && ! $isRecycleBlocked && ! $outsideClinicHours && ! $insideClinicBreak,
                    'reason' => $reason,
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

    public function peopleAheadForEntry(QueueEntry $entry): int
    {
        $entry->loadMissing(['appointment']);

        $doctorId = (int) ($entry->doctor_id ?: $entry->appointment?->doctor_id ?: 0);
        $serviceId = $this->serviceIdForEntry($entry);
        $date = $entry->scheduledSlotDateString();

        if ($doctorId <= 0 || ! $serviceId || ! $date) {
            return 0;
        }

        $entries = QueueEntry::query()
            ->with(['appointment:id,user_id,doctor_id,service_id,appointment_date,appointment_time,status'])
            ->where('clinic_id', $entry->clinic_id)
            ->forDashboardDay($date)
            ->forDoctor($doctorId)
            ->where(function ($statusQuery) use ($entry) {
                $statusQuery->whereIn('status', ['waiting', 'in_progress'])
                    ->orWhere('queue_entries.id', $entry->id);
            })
            ->orderByScheduledSlot()
            ->get()
            ->filter(fn (QueueEntry $candidate) => (int) $this->serviceIdForEntry($candidate) === (int) $serviceId)
            ->values();

        if (! $entries->contains(fn (QueueEntry $candidate) => (int) $candidate->id === (int) $entry->id)) {
            return 0;
        }

        return $entries
            ->takeUntil(fn (QueueEntry $candidate) => (int) $candidate->id === (int) $entry->id)
            ->whereIn('status', ['waiting', 'in_progress'])
            ->count();
    }

    public function createOrReuseSlotEntry(
        int $clinicId,
        int $doctorId,
        Carbon|string $date,
        string $time,
        array $values
    ): QueueEntry {
        $date = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();
        $time = $this->normalizeTimeForStorage($time);

        return DB::transaction(function () use ($clinicId, $doctorId, $date, $time, $values) {
            $slot = QueueEntry::query()
                ->where('clinic_id', $clinicId)
                ->where('doctor_id', $doctorId)
                ->whereDate('scheduled_slot_date', $date)
                ->where('scheduled_slot_time', $time)
                ->lockForUpdate()
                ->first();

            $payload = array_merge($values, [
                'clinic_id' => $clinicId,
                'doctor_id' => $doctorId,
                'scheduled_slot_date' => $date,
                'scheduled_slot_time' => $time,
                'status' => $values['status'] ?? 'waiting',
            ]);

            if (! $slot) {
                return QueueEntry::create($payload);
            }

            if (! in_array($slot->status, QueueEntry::finalPatientStatuses(), true)) {
                throw new RuntimeException('This queue slot is already in use.');
            }

            if ($slot->status === 'completed' && $this->slotRecycleBlockedByDownstreamQueue($clinicId, $doctorId, $date, $time)) {
                throw new RuntimeException('This completed queue slot cannot be reused while later patients are still waiting or being served.');
            }

            $slot->forceFill(array_merge([
                'user_id' => null,
                'patient_id' => null,
                'appointment_id' => null,
                'served_at' => null,
                'called_at' => null,
                'service_started_at' => null,
                'doctor_completed_at' => null,
                'service_ended_at' => null,
                'delay_notice_at' => null,
                'delay_notice_reason' => null,
                'priority_level' => 'regular',
                'priority_rank' => 5,
                'priority_marked_at' => null,
                'priority_marked_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], $payload))->save();

            return $slot->fresh();
        });
    }

    private function slotRecycleBlockedByDownstreamQueue(int $clinicId, int $doctorId, string $date, string $time): bool
    {
        return QueueEntry::query()
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->whereDate('scheduled_slot_date', $date)
            ->whereNotNull('scheduled_slot_time')
            ->where('scheduled_slot_time', '>=', $time)
            ->whereIn('status', QueueEntry::blockingSlotStatuses())
            ->exists();
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

    public function markEntryPriorityAndReflow(QueueEntry $entry, int $secretaryId): array
    {
        return DB::transaction(function () use ($entry, $secretaryId) {
            $fresh = QueueEntry::query()
                ->with(['appointment', 'patient'])
                ->whereKey($entry->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($fresh->status, QueueEntry::nextCandidateStatuses(), true)) {
                throw new RuntimeException('Only waiting or called patients can be marked as priority.');
            }

            $doctorId = (int) ($fresh->doctor_id ?: $fresh->appointment?->doctor_id ?: 0);
            $date = $fresh->scheduledSlotDateString();

            if ($doctorId <= 0 || ! $date) {
                throw new RuntimeException('This queue entry is missing the doctor or scheduled date needed for priority placement.');
            }

            $entries = QueueEntry::query()
                ->with(['appointment', 'patient'])
                ->where('clinic_id', $fresh->clinic_id)
                ->where(function ($doctorQuery) use ($doctorId) {
                    $doctorQuery->where('doctor_id', $doctorId)
                        ->orWhereHas('appointment', function ($appointmentQuery) use ($doctorId) {
                            $appointmentQuery->where('doctor_id', $doctorId);
                        });
                })
                ->whereDate('scheduled_slot_date', $date)
                ->whereIn('status', QueueEntry::nextCandidateStatuses())
                ->orderByScheduledSlot()
                ->lockForUpdate()
                ->get();

            $currentIndex = $entries->search(fn (QueueEntry $candidate) => (int) $candidate->id === (int) $fresh->id);

            if ($currentIndex === false) {
                throw new RuntimeException('This queue entry is no longer available for priority placement.');
            }

            $entriesExceptTarget = $entries
                ->reject(fn (QueueEntry $candidate) => (int) $candidate->id === (int) $fresh->id)
                ->values();

            $lastPriorityIndex = null;

            foreach ($entriesExceptTarget as $index => $candidate) {
                if ($candidate->isPriority()) {
                    $lastPriorityIndex = $index;
                }
            }

            $insertionIndex = $lastPriorityIndex === null ? 0 : $lastPriorityIndex + 1;
            $affected = collect([$fresh]);

            if ($currentIndex > $insertionIndex) {
                $affected = $entries->slice($insertionIndex, $currentIndex - $insertionIndex + 1)->values();
                $this->preserveOriginalSlots($affected);

                $slotAssignments = [];

                foreach ($affected as $index => $candidate) {
                    if ($index === 0) {
                        $slotAssignments[$fresh->id] = $this->slotPayloadFromEntry($candidate);
                        continue;
                    }

                    $previous = $affected[$index - 1];
                    $slotAssignments[$previous->id] = $this->slotPayloadFromEntry($candidate);
                }

                $lastDisplaced = $affected[$affected->count() - 2] ?? null;

                if ($lastDisplaced && empty($slotAssignments[$lastDisplaced->id]['scheduled_slot_time'])) {
                    $slotAssignments[$lastDisplaced->id] = $this->nextGeneratedSlotPayload($fresh, $entries);
                }

                QueueEntry::query()
                    ->whereIn('id', array_keys($slotAssignments))
                    ->update([
                        'scheduled_slot_date' => null,
                        'scheduled_slot_time' => null,
                    ]);

                foreach ($slotAssignments as $entryId => $payload) {
                    QueueEntry::query()
                        ->whereKey($entryId)
                        ->update($payload);
                }
            } else {
                $this->preserveOriginalSlots(collect([$fresh]));
            }

            QueueEntry::query()
                ->whereKey($fresh->id)
                ->update([
                    'priority_level' => 'priority',
                    'priority_rank' => 1,
                    'priority_marked_at' => now(),
                    'priority_marked_by' => $secretaryId,
                ]);

            $affectedIds = $affected
                ->pluck('id')
                ->push($fresh->id)
                ->unique()
                ->values();

            return [
                'entry' => QueueEntry::query()
                    ->with(['appointment.user', 'patient.user', 'clinic', 'user'])
                    ->findOrFail($fresh->id),
                'affected' => QueueEntry::query()
                    ->with(['appointment.user', 'patient.user', 'clinic', 'user'])
                    ->whereIn('id', $affectedIds)
                    ->orderByScheduledSlot()
                    ->get(),
            ];
        });
    }

    private function clinicOperationalHourForDate(int $clinicId, Carbon $date): ?ClinicOperationalHour
    {
        $dayKey = strtolower($date->format('l'));

        return ClinicOperationalHour::query()
            ->where('clinic_id', $clinicId)
            ->where('day_of_week', $dayKey)
            ->first();
    }

    private function clinicIsOpenForDate(?ClinicOperationalHour $clinicHour): bool
    {
        return $clinicHour !== null && (bool) $clinicHour->is_open;
    }

    private function slotOutsideClinicOperationalHours(?ClinicOperationalHour $clinicHour, Carbon $slotStart, Carbon $slotEnd): bool
    {
        if (! $clinicHour || ! $clinicHour->is_open) {
            return true;
        }

        if ($clinicHour->is_24_hours) {
            return false;
        }

        if (! $clinicHour->open_time || ! $clinicHour->close_time) {
            return true;
        }

        $open = $slotStart->copy()->setTimeFromTimeString($this->normalizeTimeLabel($clinicHour->open_time));
        $close = $slotStart->copy()->setTimeFromTimeString($this->normalizeTimeLabel($clinicHour->close_time));

        if ($close->lessThanOrEqualTo($open)) {
            $close->addDay();
        }

        return $slotStart->lt($open) || $slotEnd->gt($close);
    }

    private function slotOverlapsClinicBreak(?ClinicOperationalHour $clinicHour, Carbon $slotStart, Carbon $slotEnd): bool
    {
        if (! $clinicHour || ! $clinicHour->is_open) {
            return false;
        }

        if (! $clinicHour->break_start || ! $clinicHour->break_end) {
            return false;
        }

        $breakStart = $slotStart->copy()->setTimeFromTimeString($this->normalizeTimeLabel($clinicHour->break_start));
        $breakEnd = $slotStart->copy()->setTimeFromTimeString($this->normalizeTimeLabel($clinicHour->break_end));

        if ($breakEnd->lessThanOrEqualTo($breakStart)) {
            $breakEnd->addDay();
        }

        return $slotStart->lt($breakEnd) && $slotEnd->gt($breakStart);
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

    private function normalizeTimeForStorage($time): ?string
    {
        $normalized = $this->normalizeTimeLabel($time);

        return $normalized ? $normalized . ':00' : null;
    }

    private function preserveOriginalSlots(Collection $entries): void
    {
        foreach ($entries as $entry) {
            if ($entry->original_scheduled_slot_date || $entry->original_scheduled_slot_time) {
                continue;
            }

            QueueEntry::query()
                ->whereKey($entry->id)
                ->update([
                    'original_scheduled_slot_date' => $entry->scheduledSlotDateString(),
                    'original_scheduled_slot_time' => $entry->scheduledSlotTimeString()
                        ? $entry->scheduledSlotTimeString() . ':00'
                        : null,
                ]);
        }
    }

    private function slotPayloadFromEntry(QueueEntry $entry): array
    {
        $date = $entry->scheduledSlotDateString();
        $time = $entry->scheduledSlotTimeString();

        return [
            'scheduled_slot_date' => $date,
            'scheduled_slot_time' => $time ? $time . ':00' : null,
        ];
    }

    private function nextGeneratedSlotPayload(QueueEntry $entry, Collection $laneEntries): array
    {
        $serviceId = $this->serviceIdForEntry($entry);

        if (! $serviceId) {
            throw new RuntimeException('Unable to generate a later slot because the queue entry service could not be determined.');
        }

        $date = Carbon::parse($entry->scheduledSlotDateString());
        $doctorId = (int) ($entry->doctor_id ?: $entry->appointment?->doctor_id);
        $used = $this->occupiedSlotTimes((int) $entry->clinic_id, $doctorId, $date)
            ->merge(
                $laneEntries
                    ->map(fn (QueueEntry $candidate) => $candidate->scheduledSlotTimeString())
                    ->filter()
            )
            ->unique()
            ->values();

        $latest = $used->sort()->last();

        $slot = $this->buildSlotGrid((int) $entry->clinic_id, $doctorId, $serviceId, $date)
            ->first(function (array $slot) use ($used, $latest) {
                return ! $used->contains($slot['time'])
                    && (! $latest || $slot['time'] > $latest);
            });

        if (! $slot) {
            throw new RuntimeException('No later doctor slot is available for the displaced patient.');
        }

        return [
            'scheduled_slot_date' => $slot['date'],
            'scheduled_slot_time' => $slot['time_with_seconds'],
        ];
    }

    private function serviceIdForEntry(QueueEntry $entry): ?int
    {
        if ($entry->appointment?->service_id) {
            return (int) $entry->appointment->service_id;
        }

        if (! $entry->patient_id) {
            return null;
        }

        $visit = PatientVisit::query()
            ->where('clinic_id', $entry->clinic_id)
            ->where('patient_id', $entry->patient_id)
            ->whereDate('date_of_visit', $entry->scheduledSlotDateString() ?? now()->toDateString())
            ->latest('time_in')
            ->first();

        if (! $visit?->requested_service) {
            return null;
        }

        return Service::query()
            ->forClinics([(int) $entry->clinic_id])
            ->where('name', $visit->requested_service)
            ->value('services.id');
    }
}
