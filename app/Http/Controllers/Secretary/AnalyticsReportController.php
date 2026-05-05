<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\QueueEntry;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsReportController extends Controller
{
    use InteractsWithClinic;

    public function index(Request $request)
    {
        $activeClinic = $this->activeClinic($request);
        $activeClinicId = (int) $activeClinic->id;

        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($request);

        $appointmentsBase = Appointment::query()
            ->where('clinic_id', $activeClinicId)
            ->whereBetween('appointment_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ]);

        $walkInsBase = QueueEntry::query()
            ->where('clinic_id', $activeClinicId)
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ]);

        $appointmentSummary = [
            'total' => (clone $appointmentsBase)->count(),
            'completed' => (clone $appointmentsBase)->where('status', 'completed')->count(),
            'cancelled' => (clone $appointmentsBase)->where('status', 'cancelled')->count(),
            'no_show' => (clone $appointmentsBase)->where('status', 'no_show')->count(),
        ];

        $walkInSummary = [
            'total' => (clone $walkInsBase)->count(),
            'served' => (clone $walkInsBase)->where('status', 'served')->count(),
            'cancelled' => (clone $walkInsBase)->where('status', 'cancelled')->count(),
            'no_show' => (clone $walkInsBase)->where('status', 'no_show')->count(),
        ];

        $walkInSummary['not_served'] = max(
            0,
            $walkInSummary['total'] - $walkInSummary['served']
        );

        $completionRate = $this->percent(
            $appointmentSummary['completed'],
            $appointmentSummary['total']
        );

        $appointmentNoShowRate = $this->percent(
            $appointmentSummary['no_show'],
            $appointmentSummary['total']
        );

        $walkInServedRate = $this->percent(
            $walkInSummary['served'],
            $walkInSummary['total']
        );

        $averageServiceMinutes = $this->averageServiceMinutes(
            (clone $walkInsBase)
                ->where('status', 'served')
                ->whereNotNull('served_at')
                ->get(['created_at', 'served_at'])
        );

        $dailyAppointmentData = $this->dailyAppointmentData(
            (clone $appointmentsBase)->get(['appointment_date', 'status']),
            $startDate,
            $endDate
        );

        $dailyWalkInData = $this->dailyWalkInData(
            (clone $walkInsBase)->get(['created_at', 'status']),
            $startDate,
            $endDate
        );

        $serviceReport = $this->serviceReport(
            (clone $appointmentsBase)->get(['service_id', 'status'])
        );

        $doctorReport = $this->doctorReport(
            (clone $appointmentsBase)->get(['doctor_id', 'status'])
        );

        $previousRange = $this->previousRange($startDate, $endDate);

        $previousAppointmentsTotal = Appointment::query()
            ->where('clinic_id', $activeClinicId)
            ->whereBetween('appointment_date', [
                $previousRange['start']->toDateString(),
                $previousRange['end']->toDateString(),
            ])
            ->count();

        $previousCompletedAppointments = Appointment::query()
            ->where('clinic_id', $activeClinicId)
            ->where('status', 'completed')
            ->whereBetween('appointment_date', [
                $previousRange['start']->toDateString(),
                $previousRange['end']->toDateString(),
            ])
            ->count();

        $previousWalkInsTotal = QueueEntry::query()
            ->where('clinic_id', $activeClinicId)
            ->whereBetween('created_at', [
                $previousRange['start']->copy()->startOfDay(),
                $previousRange['end']->copy()->endOfDay(),
            ])
            ->count();

        $trends = [
            'appointments' => $this->trendPercent(
                $appointmentSummary['total'],
                $previousAppointmentsTotal
            ),
            'completed_appointments' => $this->trendPercent(
                $appointmentSummary['completed'],
                $previousCompletedAppointments
            ),
            'walk_ins' => $this->trendPercent(
                $walkInSummary['total'],
                $previousWalkInsTotal
            ),
        ];

        $reportData = [
            'activeClinic' => $activeClinic,
            'periodLabel' => $periodLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'appointmentSummary' => $appointmentSummary,
            'walkInSummary' => $walkInSummary,
            'completionRate' => $completionRate,
            'appointmentNoShowRate' => $appointmentNoShowRate,
            'walkInServedRate' => $walkInServedRate,
            'averageServiceMinutes' => $averageServiceMinutes,
            'dailyAppointmentData' => $dailyAppointmentData,
            'dailyWalkInData' => $dailyWalkInData,
            'serviceReport' => $serviceReport,
            'doctorReport' => $doctorReport,
            'trends' => $trends,
        ];

        if ($request->query('export') === 'csv') {
            return $this->downloadCsv($reportData);
        }

        return view('secretary.analytics.index', $reportData);
    }

    private function downloadCsv(array $reportData): StreamedResponse
{
    $clinicName = $reportData['activeClinic']->name ?? 'Clinic';

    $safeClinicName = str($clinicName)
        ->replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '')
        ->slug('-');

    $fileName = 'cliniq-secretary-analytics-' . $safeClinicName . '-' . now()->format('Y-m-d-His') . '.csv';

    return response()->streamDownload(function () use ($reportData) {
        $handle = fopen('php://output', 'w');

        /*
        |--------------------------------------------------------------------------
        | UTF-8 BOM
        |--------------------------------------------------------------------------
        | Helps Microsoft Excel read special characters correctly.
        |--------------------------------------------------------------------------
        */
        fwrite($handle, "\xEF\xBB\xBF");

        /*
        |--------------------------------------------------------------------------
        | Report Information
        |--------------------------------------------------------------------------
        */

        fputcsv($handle, [
            'Section',
            'Field',
            'Value',
            'Notes',
        ]);

        fputcsv($handle, [
            'Report Information',
            'Report Title',
            'CliniQ Secretary Analytics Report',
            '',
        ]);

        fputcsv($handle, [
            'Report Information',
            'Clinic',
            $reportData['activeClinic']->name ?? 'N/A',
            '',
        ]);

        fputcsv($handle, [
            'Report Information',
            'Period',
            $reportData['periodLabel'],
            '',
        ]);

        fputcsv($handle, [
            'Report Information',
            'Start Date',
            $reportData['startDate']->format('Y-m-d'),
            '',
        ]);

        fputcsv($handle, [
            'Report Information',
            'End Date',
            $reportData['endDate']->format('Y-m-d'),
            '',
        ]);

        fputcsv($handle, [
            'Report Information',
            'Generated At',
            now()->format('Y-m-d H:i:s'),
            '',
        ]);

        fputcsv($handle, []);

        /*
        |--------------------------------------------------------------------------
        | Key Metrics
        |--------------------------------------------------------------------------
        */

        fputcsv($handle, [
            'Section',
            'Metric',
            'Count',
            'Rate / Value',
            'Trend vs Previous Period',
        ]);

        $appointmentTrend = is_null($reportData['trends']['appointments'])
            ? 'No previous data'
            : $reportData['trends']['appointments'] . '%';

        $completedAppointmentTrend = is_null($reportData['trends']['completed_appointments'])
            ? 'No previous data'
            : $reportData['trends']['completed_appointments'] . '%';

        $walkInTrend = is_null($reportData['trends']['walk_ins'])
            ? 'No previous data'
            : $reportData['trends']['walk_ins'] . '%';

        fputcsv($handle, [
            'Key Metrics',
            'Total Appointments',
            $reportData['appointmentSummary']['total'],
            '',
            $appointmentTrend,
        ]);

        fputcsv($handle, [
            'Key Metrics',
            'Completed Appointments',
            $reportData['appointmentSummary']['completed'],
            $reportData['completionRate'] . '% completion rate',
            $completedAppointmentTrend,
        ]);

        fputcsv($handle, [
            'Key Metrics',
            'Cancelled Appointments',
            $reportData['appointmentSummary']['cancelled'],
            '',
            '',
        ]);

        fputcsv($handle, [
            'Key Metrics',
            'No-show Appointments',
            $reportData['appointmentSummary']['no_show'],
            $reportData['appointmentNoShowRate'] . '% no-show rate',
            '',
        ]);

        fputcsv($handle, [
            'Key Metrics',
            'Total Walk-ins',
            $reportData['walkInSummary']['total'],
            '',
            $walkInTrend,
        ]);

        fputcsv($handle, [
            'Key Metrics',
            'Served Walk-ins',
            $reportData['walkInSummary']['served'],
            $reportData['walkInServedRate'] . '% served rate',
            '',
        ]);

        fputcsv($handle, [
            'Key Metrics',
            'Not Served Walk-ins',
            $reportData['walkInSummary']['not_served'],
            '',
            '',
        ]);

        fputcsv($handle, [
            'Key Metrics',
            'Cancelled Walk-ins',
            $reportData['walkInSummary']['cancelled'],
            '',
            '',
        ]);

        fputcsv($handle, [
            'Key Metrics',
            'No-show Walk-ins',
            $reportData['walkInSummary']['no_show'],
            '',
            '',
        ]);

        fputcsv($handle, [
            'Key Metrics',
            'Average Service Time',
            '',
            $reportData['averageServiceMinutes'] . ' minutes',
            '',
        ]);

        fputcsv($handle, []);

        /*
        |--------------------------------------------------------------------------
        | Appointment Summary
        |--------------------------------------------------------------------------
        */

        fputcsv($handle, [
            'Section',
            'Total Appointments',
            'Completed',
            'Cancelled',
            'No-show',
            'Completion Rate',
            'No-show Rate',
        ]);

        fputcsv($handle, [
            'Appointment Summary',
            $reportData['appointmentSummary']['total'],
            $reportData['appointmentSummary']['completed'],
            $reportData['appointmentSummary']['cancelled'],
            $reportData['appointmentSummary']['no_show'],
            $reportData['completionRate'] . '%',
            $reportData['appointmentNoShowRate'] . '%',
        ]);

        fputcsv($handle, []);

        /*
        |--------------------------------------------------------------------------
        | Walk-in Summary
        |--------------------------------------------------------------------------
        */

        fputcsv($handle, [
            'Section',
            'Total Walk-ins',
            'Served',
            'Not Served',
            'Cancelled',
            'No-show',
            'Served Rate',
            'Average Service Time',
        ]);

        fputcsv($handle, [
            'Walk-in Summary',
            $reportData['walkInSummary']['total'],
            $reportData['walkInSummary']['served'],
            $reportData['walkInSummary']['not_served'],
            $reportData['walkInSummary']['cancelled'],
            $reportData['walkInSummary']['no_show'],
            $reportData['walkInServedRate'] . '%',
            $reportData['averageServiceMinutes'] . ' minutes',
        ]);

        fputcsv($handle, []);

        /*
        |--------------------------------------------------------------------------
        | Daily Appointment Trend
        |--------------------------------------------------------------------------
        */

        fputcsv($handle, [
            'Section',
            'Date',
            'Total Appointments',
            'Completed',
            'Cancelled',
            'No-show',
            'Completion Rate',
        ]);

        foreach ($reportData['dailyAppointmentData']['dates'] ?? [] as $index => $date) {
            $total = (int) ($reportData['dailyAppointmentData']['total'][$index] ?? 0);
            $completed = (int) ($reportData['dailyAppointmentData']['completed'][$index] ?? 0);
            $cancelled = (int) ($reportData['dailyAppointmentData']['cancelled'][$index] ?? 0);
            $noShow = (int) ($reportData['dailyAppointmentData']['no_show'][$index] ?? 0);
            $dailyCompletionRate = $this->percent($completed, $total);

            fputcsv($handle, [
                'Daily Appointment Trend',
                $date,
                $total,
                $completed,
                $cancelled,
                $noShow,
                $dailyCompletionRate . '%',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback for older dailyAppointmentData without dates key.
        |--------------------------------------------------------------------------
        */

        if (! isset($reportData['dailyAppointmentData']['dates'])) {
            foreach ($reportData['dailyAppointmentData']['labels'] as $index => $label) {
                $total = (int) ($reportData['dailyAppointmentData']['total'][$index] ?? 0);
                $completed = (int) ($reportData['dailyAppointmentData']['completed'][$index] ?? 0);
                $cancelled = (int) ($reportData['dailyAppointmentData']['cancelled'][$index] ?? 0);
                $noShow = (int) ($reportData['dailyAppointmentData']['no_show'][$index] ?? 0);
                $dailyCompletionRate = $this->percent($completed, $total);

                fputcsv($handle, [
                    'Daily Appointment Trend',
                    $label,
                    $total,
                    $completed,
                    $cancelled,
                    $noShow,
                    $dailyCompletionRate . '%',
                ]);
            }
        }

        fputcsv($handle, []);

        /*
        |--------------------------------------------------------------------------
        | Daily Walk-in Trend
        |--------------------------------------------------------------------------
        */

        fputcsv($handle, [
            'Section',
            'Date',
            'Total Walk-ins',
            'Served',
            'Not Served',
            'Served Rate',
        ]);

        foreach ($reportData['dailyWalkInData']['dates'] ?? [] as $index => $date) {
            $total = (int) ($reportData['dailyWalkInData']['total'][$index] ?? 0);
            $served = (int) ($reportData['dailyWalkInData']['served'][$index] ?? 0);
            $notServed = (int) ($reportData['dailyWalkInData']['not_served'][$index] ?? 0);
            $dailyServedRate = $this->percent($served, $total);

            fputcsv($handle, [
                'Daily Walk-in Trend',
                $date,
                $total,
                $served,
                $notServed,
                $dailyServedRate . '%',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback for older dailyWalkInData without dates key.
        |--------------------------------------------------------------------------
        */

        if (! isset($reportData['dailyWalkInData']['dates'])) {
            foreach ($reportData['dailyWalkInData']['labels'] as $index => $label) {
                $total = (int) ($reportData['dailyWalkInData']['total'][$index] ?? 0);
                $served = (int) ($reportData['dailyWalkInData']['served'][$index] ?? 0);
                $notServed = (int) ($reportData['dailyWalkInData']['not_served'][$index] ?? 0);
                $dailyServedRate = $this->percent($served, $total);

                fputcsv($handle, [
                    'Daily Walk-in Trend',
                    $label,
                    $total,
                    $served,
                    $notServed,
                    $dailyServedRate . '%',
                ]);
            }
        }

        fputcsv($handle, []);

        /*
        |--------------------------------------------------------------------------
        | Services Report
        |--------------------------------------------------------------------------
        */

        fputcsv($handle, [
            'Section',
            'Service',
            'Total Appointments',
            'Completed',
            'Cancelled',
            'No-show',
            'Completion Rate',
        ]);

        foreach ($reportData['serviceReport'] as $service) {
            fputcsv($handle, [
                'Services Report',
                $service['name'],
                $service['total'],
                $service['completed'],
                $service['cancelled'],
                $service['no_show'],
                $service['completion_rate'] . '%',
            ]);
        }

        if ($reportData['serviceReport']->isEmpty()) {
            fputcsv($handle, [
                'Services Report',
                'No service data found',
                0,
                0,
                0,
                0,
                '0%',
            ]);
        }

        fputcsv($handle, []);

        /*
        |--------------------------------------------------------------------------
        | Doctor Workload
        |--------------------------------------------------------------------------
        */

        fputcsv($handle, [
            'Section',
            'Doctor',
            'Total Appointments',
            'Completed',
            'Cancelled',
            'No-show',
            'Completion Rate',
        ]);

        foreach ($reportData['doctorReport'] as $doctor) {
            fputcsv($handle, [
                'Doctor Workload',
                'Dr. ' . $doctor['name'],
                $doctor['total'],
                $doctor['completed'],
                $doctor['cancelled'],
                $doctor['no_show'],
                $doctor['completion_rate'] . '%',
            ]);
        }

        if ($reportData['doctorReport']->isEmpty()) {
            fputcsv($handle, [
                'Doctor Workload',
                'No doctor data found',
                0,
                0,
                0,
                0,
                '0%',
            ]);
        }

        fclose($handle);
    }, $fileName, [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
}

    private function resolveDateRange(Request $request): array
    {
        $period = (string) $request->query('period', 'month');

        if ($period === 'today') {
            return [
                now()->startOfDay(),
                now()->endOfDay(),
                'Today',
            ];
        }

        if ($period === '7days') {
            return [
                now()->subDays(6)->startOfDay(),
                now()->endOfDay(),
                'Last 7 Days',
            ];
        }

        if ($period === '30days') {
            return [
                now()->subDays(29)->startOfDay(),
                now()->endOfDay(),
                'Last 30 Days',
            ];
        }

        if ($period === 'custom') {
            $start = $request->query('start_date')
                ? Carbon::parse($request->query('start_date'))->startOfDay()
                : now()->startOfMonth();

            $end = $request->query('end_date')
                ? Carbon::parse($request->query('end_date'))->endOfDay()
                : now()->endOfDay();

            if ($start->greaterThan($end)) {
                [$start, $end] = [
                    $end->copy()->startOfDay(),
                    $start->copy()->endOfDay(),
                ];
            }

            return [
                $start,
                $end,
                'Custom Range',
            ];
        }

        return [
            now()->startOfMonth(),
            now()->endOfDay(),
            'This Month',
        ];
    }

    private function percent(int|float $value, int|float $total): float
    {
        if ($total <= 0) {
            return 0;
        }

        return round(($value / $total) * 100, 1);
    }

    private function trendPercent(int $current, int $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function previousRange(Carbon $startDate, Carbon $endDate): array
    {
        $days = $startDate->diffInDays($endDate) + 1;

        return [
            'start' => $startDate->copy()->subDays($days)->startOfDay(),
            'end' => $startDate->copy()->subDay()->endOfDay(),
        ];
    }

    private function averageServiceMinutes($servedEntries): int
    {
        if ($servedEntries->isEmpty()) {
            return 0;
        }

        $minutes = $servedEntries
            ->map(function ($entry) {
                if (! $entry->created_at || ! $entry->served_at) {
                    return null;
                }

                $createdAt = Carbon::parse($entry->created_at);
                $servedAt = Carbon::parse($entry->served_at);

                return max(0, $createdAt->diffInMinutes($servedAt));
            })
            ->filter();

        if ($minutes->isEmpty()) {
            return 0;
        }

        return (int) round($minutes->avg());
    }

    private function dailyAppointmentData($appointments, Carbon $startDate, Carbon $endDate): array
    {
        $grouped = $appointments->groupBy(function ($appointment) {
            return Carbon::parse($appointment->appointment_date)->toDateString();
        });

        $labels = [];
        $total = [];
        $completed = [];
        $cancelled = [];
        $noShow = [];

        foreach (CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay()) as $date) {
            $key = $date->toDateString();
            $items = $grouped->get($key, collect());

            $labels[] = $date->format('M d');
            $total[] = $items->count();
            $completed[] = $items->where('status', 'completed')->count();
            $cancelled[] = $items->where('status', 'cancelled')->count();
            $noShow[] = $items->where('status', 'no_show')->count();
        }

        return [
            'labels' => $labels,
            'total' => $total,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'no_show' => $noShow,
        ];
    }

    private function dailyWalkInData($entries, Carbon $startDate, Carbon $endDate): array
    {
        $grouped = $entries->groupBy(function ($entry) {
            return Carbon::parse($entry->created_at)->toDateString();
        });

        $labels = [];
        $total = [];
        $served = [];
        $notServed = [];

        foreach (CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay()) as $date) {
            $key = $date->toDateString();
            $items = $grouped->get($key, collect());

            $totalCount = $items->count();
            $servedCount = $items->where('status', 'served')->count();

            $labels[] = $date->format('M d');
            $total[] = $totalCount;
            $served[] = $servedCount;
            $notServed[] = max(0, $totalCount - $servedCount);
        }

        return [
            'labels' => $labels,
            'total' => $total,
            'served' => $served,
            'not_served' => $notServed,
        ];
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

    private function doctorReport($appointments)
    {
        $doctorIds = $appointments
            ->pluck('doctor_id')
            ->filter()
            ->unique()
            ->values();

        $doctorNames = User::query()
            ->whereIn('id', $doctorIds)
            ->pluck('name', 'id');

        return $appointments
            ->filter(fn ($appointment) => ! empty($appointment->doctor_id))
            ->groupBy('doctor_id')
            ->map(function ($items, $doctorId) use ($doctorNames) {
                $total = $items->count();
                $completed = $items->where('status', 'completed')->count();
                $cancelled = $items->where('status', 'cancelled')->count();
                $noShow = $items->where('status', 'no_show')->count();

                return [
                    'doctor_id' => $doctorId,
                    'name' => $doctorNames[$doctorId] ?? 'Unknown Doctor',
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
}