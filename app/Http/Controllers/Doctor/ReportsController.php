<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\QueueEntry;
use App\Models\PatientVisit;
use App\Models\DoctorSchedule;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\Service as ServiceModel;
use Illuminate\Support\Facades\Response;
use App\Services\QueueService;

class ReportsController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware([
            'auth',
            \App\Http\Middleware\DoctorMiddleware::class,
            \App\Http\Middleware\EnsureSelectedClinic::class,
        ]);
    }

    public function index(Request $request)
    {
        $doctorId = auth()->id();
        $activeClinic = $this->activeClinic($request);
        $clinicId = $this->activeClinicId($request);

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'period' => ['nullable', 'string', 'in:today,last_7_days,this_month,last_month,this_year'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
        ]);

        // Date range filter
        $startDate = Carbon::parse($validated['start_date'] ?? now()->toDateString())->startOfDay();
        $endDate = Carbon::parse($validated['end_date'] ?? now()->toDateString())->endOfDay();

        if ($endDate->lt($startDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $isTodayRange = $startDate->isSameDay(now()) && $endDate->isSameDay(now());
        $showComparisons = ! ($startDate->isSameDay(now()) && $endDate->isSameDay(now()));

        $rangeDays = max(1, $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1);
        $previousEndDate = $startDate->copy()->subDay()->endOfDay();
        $previousStartDate = $previousEndDate->copy()->subDays($rangeDays - 1)->startOfDay();

        $serviceFilter = $validated['service_id'] ?? null;

        $servicesList = ServiceModel::forClinics([$clinicId])->get();

        // KPIs
        $appointmentsQuery = Appointment::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->whereBetween('appointment_date', [$startDate, $endDate]);

        if ($serviceFilter) {
            $appointmentsQuery->where('service_id', $serviceFilter);
        }

        $appointments = $appointmentsQuery->get();

        $queueEntries = $this->doctorQueueEntriesForPeriod($doctorId, $clinicId, $startDate, $endDate, $serviceFilter)
            ->with(['appointment:id,appointment_date,appointment_time'])
            ->get();

        $doctorPatientIds = $this->doctorQueueEntriesForPeriod($doctorId, $clinicId, $startDate, $endDate, $serviceFilter)
            ->whereNotNull('patient_id')
            ->pluck('patient_id')
            ->unique()
            ->values();

        $patientVisits = PatientVisit::where('clinic_id', $clinicId)
            ->whereBetween('date_of_visit', [$startDate, $endDate])
            ->whereIn('patient_id', $doctorPatientIds)
            ->get();

        // Appointment Statistics
        $appointmentStats = [
            'total' => $appointments->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'scheduled' => $appointments->where('status', 'scheduled')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'no_show' => $appointments->where('status', 'no_show')->count(),
        ];

        $previousAppointmentsQuery = Appointment::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->whereBetween('appointment_date', [$previousStartDate, $previousEndDate]);

        if ($serviceFilter) {
            $previousAppointmentsQuery->where('service_id', $serviceFilter);
        }

        $previousAppointments = $previousAppointmentsQuery->get();

        $previousAppointmentStats = [
            'total' => $previousAppointments->count(),
            'completed' => $previousAppointments->where('status', 'completed')->count(),
            'scheduled' => $previousAppointments->where('status', 'scheduled')->count(),
            'cancelled' => $previousAppointments->where('status', 'cancelled')->count(),
            'no_show' => $previousAppointments->where('status', 'no_show')->count(),
        ];

        // Queue Performance
        $queueStats = [
            'total' => $queueEntries->count(),
            'served' => $queueEntries->where('status', 'served')->count(),
            'no_show' => $queueEntries->where('status', 'no_show')->count(),
            'waiting' => $queueEntries->where('status', 'waiting')->count(),
            'cancelled' => $queueEntries->where('status', 'cancelled')->count(),
            'average_wait_time' => $this->calculateAverageWaitTime($queueEntries, $startDate, $endDate),
        ];

        $previousQueueEntries = $this->doctorQueueEntriesForPeriod($doctorId, $clinicId, $previousStartDate, $previousEndDate, $serviceFilter)
            ->with(['appointment:id,appointment_date,appointment_time'])
            ->get();

        $previousQueueStats = [
            'total' => $previousQueueEntries->count(),
            'served' => $previousQueueEntries->where('status', 'served')->count(),
            'no_show' => $previousQueueEntries->where('status', 'no_show')->count(),
            'waiting' => $previousQueueEntries->where('status', 'waiting')->count(),
            'cancelled' => $previousQueueEntries->where('status', 'cancelled')->count(),
            'average_wait_time' => $this->calculateAverageWaitTime($previousQueueEntries, $previousStartDate, $previousEndDate),
        ];

        // Patient Insights
        $patientStats = $this->patientStats($appointments, $patientVisits);

        $previousDoctorPatientIds = $this->doctorQueueEntriesForPeriod($doctorId, $clinicId, $previousStartDate, $previousEndDate, $serviceFilter)
            ->whereNotNull('patient_id')
            ->pluck('patient_id')
            ->unique()
            ->values();

        $previousPatientVisits = PatientVisit::where('clinic_id', $clinicId)
            ->whereBetween('date_of_visit', [$previousStartDate, $previousEndDate])
            ->whereIn('patient_id', $previousDoctorPatientIds)
            ->get();

        $previousPatientStats = $this->patientStats($previousAppointments, $previousPatientVisits);

        // Schedule <Utilization></Utilization>
        $schedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->when($serviceFilter, function ($query) use ($serviceFilter) {
                $query->where('service_id', $serviceFilter);
            })
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($dateQuery) use ($endDate) {
                    $dateQuery->whereNull('start_date')
                        ->orWhereDate('start_date', '<=', $endDate);
                })->where(function ($dateQuery) use ($startDate) {
                    $dateQuery->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $startDate);
                });
            })
            ->get();

        $totalSlots = $this->calculateScheduleSlotsForPeriod(
            $schedules,
            $startDate,
            $endDate,
            $clinicId,
            $doctorId
        );

        $bookedSlots = $appointments->count();
        $utilizationRate = $totalSlots > 0 ? round(($bookedSlots / $totalSlots) * 100, 2) : 0;

        $previousSchedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->when($serviceFilter, function ($query) use ($serviceFilter) {
                $query->where('service_id', $serviceFilter);
            })
            ->where(function ($q) use ($previousStartDate, $previousEndDate) {
                $q->where(function ($dateQuery) use ($previousEndDate) {
                    $dateQuery->whereNull('start_date')
                        ->orWhereDate('start_date', '<=', $previousEndDate);
                })->where(function ($dateQuery) use ($previousStartDate) {
                    $dateQuery->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $previousStartDate);
                });
            })
            ->get();

        $previousTotalSlots = $this->calculateScheduleSlotsForPeriod(
            $previousSchedules,
            $previousStartDate,
            $previousEndDate,
            $clinicId,
            $doctorId
        );
        $previousUtilizationRate = $previousTotalSlots > 0 ? round(($previousAppointments->count() / $previousTotalSlots) * 100, 2) : 0;

        $comparisons = [
            'appointments_total' => $this->calculateDelta($appointmentStats['total'], $previousAppointmentStats['total']),
            'queue_served' => $this->calculateDelta($queueStats['served'], $previousQueueStats['served']),
            'avg_wait_time' => $this->calculateDelta($queueStats['average_wait_time'], $previousQueueStats['average_wait_time']),
            'unique_patients' => $this->calculateDelta($patientStats['unique_patients'], $previousPatientStats['unique_patients']),
            'utilization_rate' => $this->calculateDelta($utilizationRate, $previousUtilizationRate),
            'no_show_rate' => $this->calculateDelta(
                $appointmentStats['total'] > 0 ? round(($appointmentStats['no_show'] / max(1, $appointmentStats['total'])) * 100, 1) : 0,
                $previousAppointmentStats['total'] > 0 ? round(($previousAppointmentStats['no_show'] / max(1, $previousAppointmentStats['total'])) * 100, 1) : 0
            ),
        ];

        // Appointments by Service
        $serviceCounts = $appointments->groupBy('service_id')->map->count();
        $serviceChartSource = $serviceFilter
            ? $servicesList->where('id', (int) $serviceFilter)->values()
            : $servicesList;

        $serviceStats = $serviceChartSource->map(function ($service) use ($serviceCounts) {
            return [
                'name' => $service->name,
                'count' => (int) ($serviceCounts[$service->id] ?? 0),
            ];
        })->values();

        if ($serviceStats->isEmpty() && $appointments->isNotEmpty()) {
            $serviceNames = Service::query()
                ->whereIn('id', $appointments->pluck('service_id')->filter()->unique()->values())
                ->pluck('name', 'id');

            $serviceStats = $serviceCounts->map(function ($count, $serviceId) use ($serviceNames) {
                return [
                    'name' => $serviceNames[$serviceId] ?? 'Unassigned Service',
                    'count' => (int) $count,
                ];
            })->values();
        }

        // Appointments Over Time (daily for the period)
        $appointmentCountsByDate = $appointments->groupBy(function ($appointment) {
            return $appointment->appointment_date->format('Y-m-d');
        })->map->count();

        $dailyAppointments = $this->dateSeries($startDate, $endDate)->map(function ($date) use ($appointmentCountsByDate) {
            return [
                'date' => $date,
                'count' => (int) ($appointmentCountsByDate[$date] ?? 0),
            ];
        })->values();

        // Weekly and monthly trends
        $weeklyAppointments = $appointments->groupBy(function ($a) {
            return Carbon::parse($a->appointment_date)->startOfWeek()->format('Y-m-d');
        })->map(fn($apps, $week) => ['week_start' => $week, 'count' => $apps->count()])->values();

        $monthlyAppointments = $appointments->groupBy(function ($a) {
            return Carbon::parse($a->appointment_date)->startOfMonth()->format('Y-m');
        })->map(fn($apps, $month) => ['month' => $month, 'count' => $apps->count()])->values();

        // Peak Hours
        $appointmentCountsByHour = $appointments->groupBy(function ($appointment) {
            return $appointment->appointment_time ? Carbon::parse($appointment->appointment_time)->format('H') : '00';
        })->map->count();

        $hourlyStats = collect(range(0, 23))->map(function ($hour) use ($appointmentCountsByHour) {
            $hourKey = str_pad((string) $hour, 2, '0', STR_PAD_LEFT);

            return [
                'hour' => $hourKey . ':00',
                'count' => (int) ($appointmentCountsByHour[$hourKey] ?? 0),
            ];
        })->values();

        // No-show analysis (trend by day)
        $noShowTrend = $appointments->where('status', 'no_show')->groupBy(function ($a) {
            return $a->appointment_date->format('Y-m-d');
        })->map(fn($apps, $d) => ['date' => $d, 'no_shows' => $apps->count()])->values();

        // Patient demographics for those visits
        $appointmentPatientIds = Patient::query()
            ->whereIn('user_id', $appointments->pluck('user_id')->filter()->unique()->values())
            ->pluck('id');

        $patientIds = $patientVisits->pluck('patient_id')
            ->merge($appointmentPatientIds)
            ->unique()
            ->filter()
            ->values();
        $patientAges = [];
        if ($patientIds->isNotEmpty()) {
            $patients = Patient::whereIn('id', $patientIds)->get();
            $ageGroups = [
                '0-17' => 0,
                '18-35' => 0,
                '36-60' => 0,
                '61+' => 0,
            ];
            foreach ($patients as $p) {
                $age = $p->age;
                if ($age === null) continue;
                if ($age <= 17) $ageGroups['0-17']++;
                elseif ($age <= 35) $ageGroups['18-35']++;
                elseif ($age <= 60) $ageGroups['36-60']++;
                else $ageGroups['61+']++;
            }
            $patientAges = $ageGroups;
        }

        // Services Report
        $serviceReport = $this->serviceReport($appointments);

        // Patient Appointments Report
        $patientReport = $this->patientReport($appointments);

        $reportPeriod = $this->resolveReportPeriod($startDate, $endDate, $validated['period'] ?? null);
        $periodLabel = $reportPeriod['label'];
        $periodKey = $reportPeriod['key'];

        return view('doctor.reports.index', compact(
            'activeClinic',
            'appointmentStats',
            'queueStats',
            'patientStats',
            'utilizationRate',
            'serviceStats',
            'dailyAppointments',
            'weeklyAppointments',
            'monthlyAppointments',
            'hourlyStats',
            'noShowTrend',
            'patientAges',
            'servicesList',
            'serviceFilter',
            'comparisons',
            'showComparisons',
            'isTodayRange',
            'startDate',
            'endDate',
            'periodLabel',
            'periodKey',
            'serviceReport',
            'patientReport'
        ));
    }

    public function export(Request $request)
    {
        $doctorId = auth()->id();
        $clinicId = $this->activeClinicId($request);
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
        ]);

        $startDate = Carbon::parse($validated['start_date'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($validated['end_date'] ?? now()->endOfMonth()->toDateString())->endOfDay();

        if ($endDate->lt($startDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $serviceFilter = $validated['service_id'] ?? null;

        $q = Appointment::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->whereBetween('appointment_date', [$startDate, $endDate]);
        if ($serviceFilter) $q->where('service_id', $serviceFilter);

        $appointments = $q->with(['user', 'service'])->orderBy('appointment_date')->get();

        $filename = 'appointments-'.$doctorId.'-'.$startDate->format('Ymd').'-'.$endDate->format('Ymd').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($appointments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID','Date','Time','Patient','Service','Status']);
            foreach ($appointments as $a) {
                fputcsv($handle, [
                    $a->id,
                    $a->appointment_date->format('Y-m-d'),
                    $a->appointment_time,
                    $a->user?->name ?? 'N/A',
                    $a->service?->name ?? 'N/A',
                    $a->status,
                ]);
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function serviceReport($appointments)
    {
        $serviceIds = $appointments
            ->pluck('service_id')
            ->filter()
            ->unique()
            ->values();

        $serviceNames = Service::query()
            ->whereIn('id', $serviceIds)
            ->pluck('name', 'id');

        return $appointments
            ->groupBy('service_id')
            ->map(function ($items, $serviceId) use ($serviceNames) {
                $total = $items->count();
                $completed = $items->where('status', 'completed')->count();
                $cancelled = $items->where('status', 'cancelled')->count();
                $noShow = $items->where('status', 'no_show')->count();

                return [
                    'service_id' => $serviceId,
                    'name' => $serviceNames[$serviceId] ?? 'Unassigned Service',
                    'total' => $total,
                    'completed' => $completed,
                    'cancelled' => $cancelled,
                    'no_show' => $noShow,
                    'completion_rate' => $this->percent($completed, $total),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    private function patientReport($appointments)
    {
        $userIds = $appointments
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        $patients = User::query()
            ->whereIn('id', $userIds)
            ->get();

        $patientNames = $patients->pluck('name', 'id');

        return $appointments
            ->filter(fn ($appointment) => ! empty($appointment->user_id))
            ->groupBy('user_id')
            ->map(function ($items, $userId) use ($patientNames) {
                $total = $items->count();
                $completed = $items->where('status', 'completed')->count();
                $cancelled = $items->where('status', 'cancelled')->count();
                $noShow = $items->where('status', 'no_show')->count();

                return [
                    'user_id' => $userId,
                    'name' => $patientNames[$userId] ?? 'Unknown Patient',
                    'total' => $total,
                    'completed' => $completed,
                    'cancelled' => $cancelled,
                    'no_show' => $noShow,
                    'completion_rate' => $this->percent($completed, $total),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    private function patientStats($appointments, $patientVisits): array
    {
        $appointmentPatientKeys = $appointments
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => 'user:' . (int) $id);

        $walkInPatientKeys = $patientVisits
            ->pluck('patient_id')
            ->filter()
            ->map(fn ($id) => 'patient:' . (int) $id);

        $patientEncounters = $appointments
            ->map(fn ($appointment) => $appointment->user_id ? 'user:' . (int) $appointment->user_id : null)
            ->filter()
            ->merge(
                $patientVisits
                    ->map(fn ($visit) => $visit->patient_id ? 'patient:' . (int) $visit->patient_id : null)
                    ->filter()
            );

        return [
            'unique_patients' => $appointmentPatientKeys
                ->merge($walkInPatientKeys)
                ->unique()
                ->count(),
            'repeat_patients' => $patientEncounters
                ->countBy()
                ->filter(fn ($count) => $count > 1)
                ->count(),
            'total_visits' => $appointments->count() + $patientVisits->count(),
        ];
    }

    private function percent($value, $total): int
    {
        if ($total <= 0) return 0;
        return (int) round(($value / $total) * 100);
    }

    private function dateSeries(Carbon $startDate, Carbon $endDate)
    {
        $dates = collect();
        $cursor = $startDate->copy()->startOfDay();
        $lastDate = $endDate->copy()->startOfDay();

        while ($cursor->lte($lastDate)) {
            $dates->push($cursor->toDateString());
            $cursor->addDay();
        }

        return $dates;
    }

    private function scheduleDurationMinutes($startTime, $endTime): int
    {
        $start = $this->timeToMinutes((string) $startTime);
        $end = $this->timeToMinutes((string) $endTime);

        if ($end <= $start) {
            $end += 24 * 60;
        }

        return $end - $start;
    }

    private function timeToMinutes(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', substr($time, 0, 5)));

        return ($hour * 60) + $minute;
    }

    private function calculateAverageWaitTime($queueEntries, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $servedEntries = $queueEntries
            ->where('status', 'served')
            ->whereNotNull('served_at')
            ->whereNotNull('called_at')
            ->filter(function ($entry) use ($startDate, $endDate) {
                $called = $this->queueCalledAt($entry);
                $served = Carbon::parse($entry->served_at);

                if ($startDate && $served->lt($startDate)) {
                    return false;
                }

                if ($endDate && $served->gt($endDate)) {
                    return false;
                }

                return $called && $served->greaterThanOrEqualTo($called);
            });

        if ($servedEntries->isEmpty()) return 0;

        $totalWaitTime = $servedEntries->sum(function ($entry) {
            $called = $this->queueCalledAt($entry);
            $served = Carbon::parse($entry->served_at);

            return $called->diffInMinutes($served);
        });

        return round($totalWaitTime / $servedEntries->count(), 2);
    }

    private function doctorQueueEntriesForPeriod(int $doctorId, int $clinicId, Carbon $startDate, Carbon $endDate, ?int $serviceId = null)
    {
        return QueueEntry::query()
            ->forDoctor($doctorId)
            ->where('clinic_id', $clinicId)
            ->when($serviceId, function ($query) use ($serviceId) {
                $query->whereHas('appointment', function ($appointmentQuery) use ($serviceId) {
                    $appointmentQuery->where('service_id', $serviceId);
                });
            })
            ->where(function ($periodQuery) use ($startDate, $endDate) {
                $periodQuery->where(function ($appointmentQueue) use ($startDate, $endDate) {
                    $appointmentQueue->whereNotNull('appointment_id')
                        ->whereHas('appointment', function ($appointmentQuery) use ($startDate, $endDate) {
                            $appointmentQuery->whereBetween('appointment_date', [$startDate, $endDate]);
                        });
                })->orWhere(function ($walkInQueue) use ($startDate, $endDate) {
                    $walkInQueue->whereNull('appointment_id')
                        ->whereNotNull('patient_id')
                        ->whereBetween('created_at', [$startDate, $endDate]);
                });
            });
    }

    private function queueCalledAt($entry): ?Carbon
    {
        if ($entry->called_at) {
            return Carbon::parse($entry->called_at);
        }

        return null;
    }

    private function calculateDelta($current, $previous): array
    {
        $currentValue = (float) $current;
        $previousValue = (float) $previous;
        $difference = round($currentValue - $previousValue, 2);
        $percent = $previousValue == 0.0
            ? ($currentValue > 0 ? 100.0 : 0.0)
            : round(($difference / $previousValue) * 100, 1);

        return [
            'difference' => $difference,
            'percent' => $percent,
            'direction' => $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'flat'),
        ];
    }

    private function resolveReportPeriod(Carbon $startDate, Carbon $endDate, ?string $requestedPeriod = null): array
    {
        $today = now();
        $periods = [
            'today' => [
                'label' => 'Today',
                'start' => $today->copy()->startOfDay(),
                'end' => $today->copy()->endOfDay(),
            ],
            'last_7_days' => [
                'label' => 'Last 7 Days',
                'start' => $today->copy()->subDays(6)->startOfDay(),
                'end' => $today->copy()->endOfDay(),
            ],
            'this_month' => [
                'label' => 'This Month',
                'start' => $today->copy()->startOfMonth()->startOfDay(),
                'end' => $today->copy()->endOfMonth()->endOfDay(),
            ],
            'last_month' => [
                'label' => 'Last Month',
                'start' => $today->copy()->subMonthNoOverflow()->startOfMonth()->startOfDay(),
                'end' => $today->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay(),
            ],
            'this_year' => [
                'label' => 'This Year',
                'start' => $today->copy()->startOfYear()->startOfDay(),
                'end' => $today->copy()->endOfYear()->endOfDay(),
            ],
        ];

        if ($requestedPeriod && isset($periods[$requestedPeriod])) {
            $period = $periods[$requestedPeriod];

            if ($startDate->isSameDay($period['start']) && $endDate->isSameDay($period['end'])) {
                return [
                    'key' => $requestedPeriod,
                    'label' => $period['label'],
                ];
            }
        }

        foreach ($periods as $key => $period) {
            if ($startDate->isSameDay($period['start']) && $endDate->isSameDay($period['end'])) {
                return [
                    'key' => $key,
                    'label' => $period['label'],
                ];
            }
        }

        return [
            'key' => 'custom',
            'label' => 'Custom Range',
        ];
    }
}
