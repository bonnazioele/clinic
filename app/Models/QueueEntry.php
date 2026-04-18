<?php

namespace App\Models;

use App\Events\QueueUpdated;
use App\Notifications\QueueNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QueueEntry extends Model
{
    use HasFactory;

    public const DASHBOARD_STATUS_KEYS = [
        'waiting',
        'served',
        'no_show',
        'rescheduled',
    ];

    public static function nextCandidateStatuses(): array
    {
        return ['waiting', 'called', 'rescheduled'];
    }

    public static function activeLaneStatuses(): array
    {
        return ['waiting', 'called', 'now_serving', 'rescheduled'];
    }

    protected $fillable = [
        'clinic_id',
        'user_id',
        'patient_id',
        'appointment_id',
        'queue_number',
        'status',
        'served_at',
        'patient_disposition',
        'doctor_notes',
        'prescription',
        'follow_up_at',
    ];

    protected $casts = [
        'served_at' => 'datetime',
        'follow_up_at' => 'datetime',
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

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->user?->name
            ?? $this->patient?->full_name
            ?? 'Walk-In Patient';
    }

    public function getDisplayEmailAttribute(): ?string
    {
        return $this->user?->email
            ?? $this->patient?->email_address;
    }

    public function getDisplayPhoneAttribute(): ?string
    {
        return $this->user?->phone
            ?? $this->patient?->mobile_number;
    }

    public function getIsWalkInAttribute(): bool
    {
        return $this->patient_id !== null && $this->user_id === null;
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
            $dayQuery->whereHas('appointment', function ($appointmentQuery) use ($date) {
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

    public function scopeForLaneCandidates($query, int $clinicId, int $doctorId, $date)
    {
        return $query->forDashboardPanel([$clinicId], $date)
            ->where('clinic_id', $clinicId)
            ->whereHas('appointment', function ($appointmentQuery) use ($doctorId) {
                $appointmentQuery->where('doctor_id', $doctorId);
            });
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
        return $query->whereNull('user_id')
            ->whereNotNull('patient_id')
            ->whereNull('appointment_id');
    }

    public function scopeWithAppointment($query)
    {
        return $query->whereNotNull('appointment_id');
    }

    public function scopeWithDashboardRelations($query)
    {
        return $query->with([
            'user:id,name',
            'patient:id,full_name,first_name,last_name',
            'appointment:id,doctor_id,service_id',
            'appointment.service:id,name',
        ]);
    }

    public function laneKey(): ?string
    {
        $doctorId = $this->appointment?->doctor_id;

        if (!$doctorId) {
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
            $currentEntry = self::query()->lockForUpdate()->find($entryId);

            if (!$currentEntry || (int) $currentEntry->clinic_id !== $clinicId) {
                return ['result' => 'invalid'];
            }

            if ($currentEntry->status !== 'now_serving') {
                return [
                    'result' => 'noop',
                    'current' => $currentEntry->fresh(),
                    'next' => null,
                ];
            }

            $doctorId = (int) ($currentEntry->appointment?->doctor_id ?? 0);

            $currentEntry->update([
                'status' => 'served',
                'served_at' => now(),
            ]);

            if ($currentEntry->appointment && !in_array($currentEntry->appointment->status, ['completed', 'cancelled'], true)) {
                $currentEntry->appointment->update(['status' => 'completed']);
            }

            $currentEntry = $currentEntry->fresh();
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
                ->orderBy('queue_number')
                ->lockForUpdate()
                ->first();

            if (!$nextEntry) {
                return [
                    'result' => 'served_only',
                    'current' => $currentEntry,
                    'next' => null,
                ];
            }

            $nextEntry->update(['status' => 'now_serving']);

            if ($nextEntry->user) {
                $nextEntry->user->notify(new QueueNotification($nextEntry));
            }

            $nextEntry = $nextEntry->fresh();
            event(new QueueUpdated($nextEntry, 'now_serving'));

            return [
                'result' => 'served_and_promoted',
                'current' => $currentEntry,
                'next' => $nextEntry,
            ];
        });
    }

    public function isNextInLine()
    {
        return $this->status === 'waiting' &&
               $this->queue_number === $this->clinic->queueEntries()
                   ->waiting()
                   ->min('queue_number');
    }

    public function getEstimatedWaitTime()
    {
        if ($this->status !== 'waiting') {
            return 0;
        }

        $ahead = $this->clinic->queueEntries()
            ->waiting()
            ->where('queue_number', '<', $this->queue_number)
            ->count();

        return $ahead * 15;
    }

    public function getFormattedCreatedTimeAttribute()
    {
        return \Carbon\Carbon::parse($this->created_at)->format('g:i A');
    }

    public function getFormattedServedTimeAttribute()
    {
        if (!$this->served_at) {
            return null;
        }
        return \Carbon\Carbon::parse($this->served_at)->format('g:i A');
    }


    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'waiting' => 'Waiting',
            'now_serving' => 'Now Serving',
            'called' => 'Called',
            'rescheduled' => 'Rescheduled',
            'no_show' => 'No Show',
            'cancelled' => 'Cancelled',
            'served' => 'Served',
            default => ucfirst(str_replace('_',' ', $this->status ?? 'Unknown')),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'waiting' => 'secondary',
            'now_serving' => 'primary',
            'called' => 'info',
            'rescheduled' => 'warning',
            'no_show' => 'dark',
            'cancelled' => 'danger',
            'served' => 'success',
            default => 'light',
        };
    }
}
