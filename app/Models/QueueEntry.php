<?php

namespace App\Models;

use App\Events\QueueUpdated;
use App\Notifications\AppointmentStatusChanged;
use App\Notifications\QueueNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QueueEntry extends Model
{
    use HasFactory;

    public const DASHBOARD_STATUS_KEYS = [
        'waiting',
        'called',
        'in_progress',
        'now_serving',
        'served',
        'completed',
        'no_show',
        'rescheduled',
        'cancelled',
    ];

    public static function activePatientStatuses(): array
    {
        return ['waiting', 'called', 'in_progress', 'now_serving'];
    }

    public static function finalPatientStatuses(): array
    {
        return ['served', 'completed', 'rescheduled', 'cancelled', 'no_show'];
    }

    public static function nextCandidateStatuses(): array
    {
        return ['waiting', 'called'];
    }

    public static function activeLaneStatuses(): array
    {
        return ['waiting', 'called', 'in_progress', 'now_serving'];
    }

    public static function blockingSlotStatuses(): array
    {
        return ['waiting', 'called', 'in_progress', 'now_serving', 'served', 'completed'];
    }

    public static function doctorQueueVisibleStatuses(): array
    {
        return ['waiting', 'called', 'in_progress', 'now_serving', 'rescheduled', 'served', 'completed'];
    }

    protected $fillable = [
        'clinic_id',
        'user_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'queue_number',
        'scheduled_slot_date',
        'scheduled_slot_time',
        'status',
        'served_at',
        'service_started_at',
        'service_ended_at',
    ];

    protected $casts = [
        'served_at' => 'datetime',
        'scheduled_slot_date' => 'date',
        'service_started_at' => 'datetime',
        'service_ended_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->appointment?->user?->name
            ?? $this->user?->name
            ?? $this->patient?->full_name
            ?? 'Walk-In Patient';
    }

    public function getDisplayEmailAttribute(): ?string
    {
        return $this->appointment?->user?->email
            ?? $this->user?->email
            ?? $this->patient?->email_address;
    }

    public function getDisplayPhoneAttribute(): ?string
    {
        return $this->appointment?->user?->phone
            ?? $this->user?->phone
            ?? $this->patient?->mobile_number;
    }

    public function getIsWalkInAttribute(): bool
    {
        return $this->patient_id !== null && $this->appointment_id === null;
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeForClinics($query, $clinicIds)
    {
        return $query->whereIn('clinic_id', $clinicIds);
    }

    public function scopeCreatedOn($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    public function scopeForDashboardDay($query, $date)
    {
        return $query->where(function ($dayQuery) use ($date) {
            $dayQuery->whereDate('scheduled_slot_date', $date)
            ->orWhereHas('appointment', function ($appointmentQuery) use ($date) {
                $appointmentQuery->whereDate('appointment_date', $date);
            })->orWhere(function ($walkInQuery) use ($date) {
                $walkInQuery->walkIn()->createdOn($date);
            });
        });
    }

    public function scopeForDashboardPanel($query, $clinicIds, $date)
    {
        return $query->forClinics($clinicIds)
            ->forDashboardDay($date);
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where(function ($doctorQuery) use ($doctorId) {
            $doctorQuery->where('doctor_id', $doctorId)
                ->orWhereHas('appointment', function ($appointmentQuery) use ($doctorId) {
                    $appointmentQuery->where('doctor_id', $doctorId);
                });
        });
    }

    public function scopeForLaneCandidates($query, int $clinicId, int $doctorId, $date)
    {
        return $query->forDashboardPanel([$clinicId], $date)
            ->where('clinic_id', $clinicId)
            ->forDoctor($doctorId);
    }

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeWithStatuses($query, array $statuses)
    {
        return $query->whereIn('status', $statuses);
    }

    public function scopeWalkIn($query)
    {
        return $query->whereNull('appointment_id')
            ->whereNotNull('patient_id');
    }

    public function scopeWithAppointment($query)
    {
        return $query->whereNotNull('appointment_id');
    }

    public function scopeWithDashboardRelations($query)
    {
        return $query->with([
            'user:id,name,email,phone',
            'patient:id,patient_number,status,first_name,last_name,middle_name,email_address,mobile_number',
            'doctor:id,name,first_name,last_name,email',
            'appointment:id,user_id,doctor_id,service_id,appointment_date,appointment_time,medical_document,status',
            'appointment.user:id,name,email,phone',
            'appointment.service:id,name',
            'clinic:id,name',
        ]);
    }

    public function scopeOrderByScheduledSlot($query, string $table = 'queue_entries')
    {
        return $query
            ->orderByRaw("{$table}.scheduled_slot_date IS NULL")
            ->orderBy("{$table}.scheduled_slot_date")
            ->orderByRaw("{$table}.scheduled_slot_time IS NULL")
            ->orderBy("{$table}.scheduled_slot_time")
            ->orderBy("{$table}.priority_rank")
            ->orderBy("{$table}.queue_number")
            ->orderBy("{$table}.id");
    }

    public function scheduledSlotDateString(): ?string
    {
        if ($this->scheduled_slot_date) {
            return $this->scheduled_slot_date->toDateString();
        }

        if ($this->appointment?->appointment_date) {
            return $this->appointment->appointment_date->toDateString();
        }

        return $this->created_at?->toDateString();
    }

    public function scheduledSlotTimeString(): ?string
    {
        $raw = $this->getRawOriginal('scheduled_slot_time');

        if (is_string($raw) && strlen($raw) >= 5) {
            return substr($raw, 0, 5);
        }

        $appointmentTime = $this->appointment?->getRawOriginal('appointment_time');

        if (is_string($appointmentTime) && strlen($appointmentTime) >= 5) {
            return substr($appointmentTime, 0, 5);
        }

        return null;
    }

    public function laneKey(): ?string
    {
        $doctorId = $this->doctor_id ?: $this->appointment?->doctor_id;

        if (! $doctorId) {
            return null;
        }

        return $this->clinic_id . ':' . $doctorId;
    }

    public static function dashboardStatusCounts($query): array
    {
        $counts = [];

        foreach (self::DASHBOARD_STATUS_KEYS as $status) {
            $counts[$status] = (clone $query)->withStatus($status)->count();
        }

        return $counts;
    }

    public static function completeNowServingAndPromoteNext(int $clinicId, int $entryId, $date): array
    {
        return DB::transaction(function () use ($clinicId, $entryId, $date) {
            $currentEntry = self::query()
                ->with(['appointment.user', 'patient', 'clinic'])
                ->lockForUpdate()
                ->find($entryId);

            if (! $currentEntry || (int) $currentEntry->clinic_id !== $clinicId) {
                return ['result' => 'invalid'];
            }

            if (! in_array($currentEntry->status, ['in_progress', 'now_serving'], true)) {
                return [
                    'result' => 'noop',
                    'current' => $currentEntry->fresh(),
                    'next' => null,
                ];
            }

            $doctorId = (int) ($currentEntry->doctor_id ?: $currentEntry->appointment?->doctor_id ?: 0);

            $now = now();

            $currentEntry->update([
                'status' => 'served',
                'served_at' => $now,
                'service_ended_at' => $now,
            ]);

            if (
                $currentEntry->appointment
                && ! in_array($currentEntry->appointment->status, ['completed', 'cancelled', 'no_show', 'rescheduled'], true)
            ) {
                $appointment = $currentEntry->appointment;

                $appointment->update([
                    'status' => 'completed',
                ]);

                if ($appointment->user) {
                    $appointment->user->notify(new AppointmentStatusChanged($appointment));
                }
            }

            $currentEntry = $currentEntry->fresh(['appointment.user', 'patient', 'clinic']);
            event(new QueueUpdated($currentEntry, 'served'));

            if ($doctorId <= 0) {
                return [
                    'result' => 'served_only',
                    'current' => $currentEntry,
                    'next' => null,
                ];
            }

            $nextEntry = self::query()
                ->forLaneCandidates($clinicId, $doctorId, $date)
                ->whereKeyNot($currentEntry->id)
                ->withStatuses(self::nextCandidateStatuses())
                ->orderByScheduledSlot()
                ->lockForUpdate()
                ->first();

            if (! $nextEntry) {
                return [
                    'result' => 'served_only',
                    'current' => $currentEntry,
                    'next' => null,
                ];
            }

            $nextEntry->update([
                'status' => 'in_progress',
                'service_started_at' => now(),
            ]);

            if ($nextEntry->user) {
                $nextEntry->user->notify(new QueueNotification($nextEntry));
            }

            $nextEntry = $nextEntry->fresh(['appointment.user', 'patient', 'clinic']);
            event(new QueueUpdated($nextEntry, 'in_progress'));

            return [
                'result' => 'served_and_promoted',
                'current' => $currentEntry,
                'next' => $nextEntry,
            ];
        });
    }

    public function isNextInLine()
    {
        if ($this->status !== 'waiting') {
            return false;
        }

        $next = $this->clinic->queueEntries()
            ->waiting()
            ->orderByScheduledSlot()
            ->first();

        return $next && (int) $next->id === (int) $this->id;
    }

    public function getEstimatedWaitTime()
    {
        if (! in_array($this->status, ['waiting', 'called'], true)) {
            return 0;
        }

        $entries = $this->clinic->queueEntries()
            ->whereIn('status', self::activePatientStatuses())
            ->orderByScheduledSlot()
            ->get(['id', 'queue_number', 'scheduled_slot_date', 'scheduled_slot_time']);

        $ahead = $entries
            ->takeUntil(fn ($entry) => (int) $entry->id === (int) $this->id)
            ->count();

        return $ahead * 15;
    }

    public function getFormattedScheduledSlotTimeAttribute(): ?string
    {
        $time = $this->scheduledSlotTimeString();

        if (! $time) {
            return null;
        }

        return \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A');
    }

    public function getFormattedCreatedTimeAttribute()
    {
        return \Carbon\Carbon::parse($this->created_at)->format('g:i A');
    }

    public function getFormattedServedTimeAttribute()
    {
        if (! $this->served_at) {
            return null;
        }

        return \Carbon\Carbon::parse($this->served_at)->format('g:i A');
    }

    public function getFormattedServiceStartedTimeAttribute()
    {
        if (! $this->service_started_at) {
            return null;
        }

        return \Carbon\Carbon::parse($this->service_started_at)->format('g:i A');
    }

    public function getFormattedServiceEndedTimeAttribute()
    {
        if (! $this->service_ended_at) {
            return null;
        }

        return \Carbon\Carbon::parse($this->service_ended_at)->format('g:i A');
    }

    public function getServiceDurationMinutesAttribute(): int
    {
        if (! $this->service_started_at || ! $this->service_ended_at) {
            return 0;
        }

        return max(
            0,
            \Carbon\Carbon::parse($this->service_started_at)
                ->diffInMinutes(\Carbon\Carbon::parse($this->service_ended_at))
        );
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'waiting' => 'Waiting',
            'called' => 'Called',
            'in_progress' => 'In Progress',
            'now_serving' => 'Now Serving',
            'served' => 'Completed',
            'completed' => 'Completed',
            'rescheduled' => 'Rescheduled',
            'no_show' => 'No Show',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status ?? 'Unknown')),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'waiting' => 'secondary',
            'called' => 'info',
            'in_progress' => 'primary',
            'now_serving' => 'primary',
            'served' => 'success',
            'completed' => 'success',
            'rescheduled' => 'warning',
            'no_show' => 'dark',
            'cancelled' => 'danger',
            default => 'light',
        };
    }
}
