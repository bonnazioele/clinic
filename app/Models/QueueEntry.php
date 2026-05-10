<?php

namespace App\Models;

use App\Events\QueueUpdated;
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

    /*
    |--------------------------------------------------------------------------
    | Queue Status Flow
    |--------------------------------------------------------------------------
    |
    | waiting / called
    |      ↓ Secretary clicks Call
    | in_progress / now_serving
    |      ↓ Doctor clicks Serve
    | served
    |      ↓ Secretary clicks Done & Next
    | completed
    |
    | IMPORTANT:
    | "served" is NOT final yet.
    | It must remain visible on secretary side until Done & Next is clicked.
    |
    */

    public static function activePatientStatuses(): array
    {
        return [
            'waiting',
            'called',
            'in_progress',
            'now_serving',
            'served',
        ];
    }

    public static function finalPatientStatuses(): array
    {
        return [
            'completed',
            'rescheduled',
            'cancelled',
            'no_show',
        ];
    }

    public static function nextCandidateStatuses(): array
    {
        return [
            'waiting',
            'called',
        ];
    }

    public static function activeLaneStatuses(): array
    {
        return [
            'waiting',
            'called',
            'in_progress',
            'now_serving',
            'served',
        ];
    }

    public static function blockingSlotStatuses(): array
    {
        return [
            'waiting',
            'called',
            'in_progress',
            'now_serving',
            'served',
            'completed',
        ];
    }

    public static function doctorQueueVisibleStatuses(): array
    {
        return [
            'waiting',
            'called',
            'in_progress',
            'now_serving',
            'served',
            'completed',
            'rescheduled',
        ];
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
        'priority_level',
        'priority_rank',
        'delay_notice_at',
        'delay_notice_reason',
        'served_at',
        'called_at',
        'service_started_at',
        'doctor_completed_at',
        'service_ended_at',
    ];

    protected $casts = [
        'scheduled_slot_date' => 'date',
        'delay_notice_at' => 'datetime',
        'served_at' => 'datetime',
        'called_at' => 'datetime',
        'service_started_at' => 'datetime',
        'doctor_completed_at' => 'datetime',
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
                })
                ->orWhere(function ($walkInQuery) use ($date) {
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

    /*
    |--------------------------------------------------------------------------
    | Doctor Serve Action Helper
    |--------------------------------------------------------------------------
    |
    | This method is used by the doctor side.
    |
    | It must ONLY change:
    | in_progress / now_serving -> served
    |
    | It must NOT:
    | - change served -> completed
    | - set service_ended_at
    | - complete the appointment
    | - promote the next patient
    |
    | Secretary Done & Next should be the only action that changes:
    | served -> completed
    |
    */

    public static function completeNowServingAndPromoteNext(int $clinicId, int $entryId, $date): array
    {
        return DB::transaction(function () use ($clinicId, $entryId) {
            $currentEntry = self::query()
                ->with(['appointment.user', 'patient', 'clinic'])
                ->whereKey($entryId)
                ->where('clinic_id', $clinicId)
                ->lockForUpdate()
                ->first();

            if (! $currentEntry) {
                return [
                    'result' => 'invalid',
                    'current' => null,
                    'next' => null,
                ];
            }

            if ($currentEntry->status === 'served') {
                return [
                    'result' => 'already_served',
                    'current' => $currentEntry->fresh(['appointment.user', 'patient', 'clinic']),
                    'next' => null,
                ];
            }

            if (! in_array($currentEntry->status, ['in_progress', 'now_serving'], true)) {
                return [
                    'result' => 'noop',
                    'current' => $currentEntry->fresh(['appointment.user', 'patient', 'clinic']),
                    'next' => null,
                ];
            }

            $currentEntry->forceFill([
                'status' => 'served',
                'served_at' => now(),
                'doctor_completed_at' => now(),
            ])->save();

            $currentEntry = $currentEntry->fresh(['appointment.user', 'patient', 'clinic']);

            event(new QueueUpdated($currentEntry, 'served'));

            return [
                'result' => 'served_only',
                'current' => $currentEntry,
                'next' => null,
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
            ->get([
                'id',
                'queue_number',
                'scheduled_slot_date',
                'scheduled_slot_time',
            ]);

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

    public function getFormattedCalledTimeAttribute()
    {
        if (! $this->called_at) {
            return null;
        }

        return \Carbon\Carbon::parse($this->called_at)->format('g:i A');
    }

    public function getFormattedServiceStartedTimeAttribute()
    {
        if (! $this->service_started_at) {
            return null;
        }

        return \Carbon\Carbon::parse($this->service_started_at)->format('g:i A');
    }

    public function getFormattedDoctorCompletedTimeAttribute()
    {
        if (! $this->doctor_completed_at) {
            return null;
        }

        return \Carbon\Carbon::parse($this->doctor_completed_at)->format('g:i A');
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
            'served' => 'Served',
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