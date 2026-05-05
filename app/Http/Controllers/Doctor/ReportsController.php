<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\QueueEntry;
use App\Models\PatientVisit;
use App\Models\DoctorSchedule;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\Service as ServiceModel;
use Illuminate\Support\Facades\Response;

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
        $clinicId = $this->activeClinicId($request);

        // Date range filter
        $startDate = $request->get('start_date', Carbon::now()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());

        if (is_string($startDate)) $startDate = Carbon::parse($startDate);
        if (is_string($endDate)) $endDate = Carbon::parse($endDate);

        if ($endDate->lt($startDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $isTodayRange = $startDate->isSameDay(now()) && $endDate->isSameDay(now());
        $showComparisons = ! ($startDate->isSameDay(now()) && $endDate->isSameDay(now()));

        $rangeDays = max(1, $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1);
        $previousEndDate = $startDate->copy()->subDay();
        $previousStartDate = $previousEndDate->copy()->subDays($rangeDays - 1);

        $serviceFilter = $request->get('service_id');

        // services list for filter (clinic scope is stored via pivot)
        $servicesList = ServiceModel::forClinics([$clinicId])->get();

        // KPIs
        $appointmentsQuery = Appointment::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->whereBetween('appointment_date', [$startDate, $endDate]);

        if ($serviceFilter) {
            $appointmentsQuery->where('service_id', $serviceFilter);
        }

        $appointments = $appointmentsQuery->get();

        $queueEntries = QueueEntry::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $patientVisits = PatientVisit::where('clinic_id', $clinicId)
            ->whereBetween('date_of_visit', [$startDate, $endDate])
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
            'average_wait_time' => $this->calculateAverageWaitTime($queueEntries),
        ];

        $previousQueueEntries = QueueEntry::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->get();

        $previousQueueStats = [
            'total' => $previousQueueEntries->count(),
            'served' => $previousQueueEntries->where('status', 'served')->count(),
            'no_show' => $previousQueueEntries->where('status', 'no_show')->count(),
            'waiting' => $previousQueueEntries->where('status', 'waiting')->count(),
            'average_wait_time' => $this->calculateAverageWaitTime($previousQueueEntries),
        ];

        // Patient Insights
        $uniquePatients = $patientVisits->pluck('patient_id')->unique()->count();
        $repeatPatients = $patientVisits->groupBy('patient_id')->filter(function ($visits) {
            return $visits->count() > 1;
        })->count();

        $patientStats = [
            'unique_patients' => $uniquePatients,
            'repeat_patients' => $repeatPatients,
            'total_visits' => $patientVisits->count(),
        ];

        $previousPatientVisits = PatientVisit::where('clinic_id', $clinicId)
            ->whereBetween('date_of_visit', [$previousStartDate, $previousEndDate])
            ->get();

        $previousPatientStats = [
            'unique_patients' => $previousPatientVisits->pluck('patient_id')->unique()->count(),
            'repeat_patients' => $previousPatientVisits->groupBy('patient_id')->filter(function ($visits) {
                return $visits->count() > 1;
            })->count(),
            'total_visits' => $previousPatientVisits->count(),
        ];

        // Schedule Utilization: count available slots from schedules (30-min slots)
        $schedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate);
                  });
            })->get();

        $slotLengthMinutes = 30;
        $totalSlots = 0;

        foreach ($schedules as $schedule) {
            $effectiveStart = $startDate->copy();
            if (! empty($schedule->start_date)) {
                $scheduleStart = Carbon::parse($schedule->start_date)->startOfDay();
                if ($scheduleStart->greaterThan($effectiveStart)) $effectiveStart = $scheduleStart;
            }
            $effectiveEnd = $endDate->copy();
            if (! empty($schedule->end_date)) {
                $scheduleEnd = Carbon::parse($schedule->end_date)->endOfDay();
                if ($scheduleEnd->lessThan($effectiveEnd)) $effectiveEnd = $scheduleEnd;
            }
            if ($effectiveEnd->lessThan($effectiveStart)) continue;

            if ($schedule->schedule_type === 'one_time') {
                $dates = [ Carbon::parse($schedule->start_date) ];
            } else {
                $dates = [];
                $cursor = $effectiveStart->copy()->nextOrSame($schedule->day_of_week);
                while ($cursor->lte($effectiveEnd)) {
                    $dates[] = $cursor->copy();
                    $cursor->addWeek();
                }
            }

            foreach ($dates as $d) {
                if (empty($schedule->start_time) || empty($schedule->end_time)) continue;
                $start = Carbon::parse($schedule->start_time);
                $end = Carbon::parse($schedule->end_time);
                $minutes = $end->diffInMinutes($start);
                if ($minutes <= 0) continue;
                $totalSlots += max(0, floor($minutes / $slotLengthMinutes));
            }
        }

        $bookedSlots = $appointments->count();
        $utilizationRate = $totalSlots > 0 ? round(($bookedSlots / $totalSlots) * 100, 2) : 0;

        $previousSchedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where(function ($q) use ($previousStartDate, $previousEndDate) {
                $q->whereBetween('start_date', [$previousStartDate, $previousEndDate])
                  ->orWhereBetween('end_date', [$previousStartDate, $previousEndDate])
                  ->orWhere(function ($q2) use ($previousStartDate, $previousEndDate) {
                      $q2->where('start_date', '<=', $previousStartDate)->where('end_date', '>=', $previousEndDate);
                  });
            })->get();

        $previousTotalSlots = 0;
        foreach ($previousSchedules as $schedule) {
            $effectiveStart = $previousStartDate->copy();
            if (! empty($schedule->start_date)) {
                $scheduleStart = Carbon::parse($schedule->start_date)->startOfDay();
                if ($scheduleStart->greaterThan($effectiveStart)) $effectiveStart = $scheduleStart;
            }
            $effectiveEnd = $previousEndDate->copy();
            if (! empty($schedule->end_date)) {
                $scheduleEnd = Carbon::parse($schedule->end_date)->endOfDay();
                if ($scheduleEnd->lessThan($effectiveEnd)) $effectiveEnd = $scheduleEnd;
            }
            if ($effectiveEnd->lessThan($effectiveStart)) continue;

            if ($schedule->schedule_type === 'one_time') {
                $dates = [ Carbon::parse($schedule->start_date) ];
            } else {
                $dates = [];
                $cursor = $effectiveStart->copy()->nextOrSame($schedule->day_of_week);
                while ($cursor->lte($effectiveEnd)) {
                    $dates[] = $cursor->copy();
                    $cursor->addWeek();
                }
            }

            foreach ($dates as $d) {
                if (empty($schedule->start_time) || empty($schedule->end_time)) continue;
                $start = Carbon::parse($schedule->start_time);
                $end = Carbon::parse($schedule->end_time);
                $minutes = $end->diffInMinutes($start);
                if ($minutes <= 0) continue;
                $previousTotalSlots += max(0, floor($minutes / $slotLengthMinutes));
            }
        }
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
        $serviceStats = $appointments->groupBy('service_id')->map(function ($apps, $serviceId) {
            $service = Service::find($serviceId);
            return [
                'name' => $service ? $service->name : 'Unknown',
                'count' => $apps->count(),
            ];
        })->values();

        // Appointments Over Time (daily for the period)
        $dailyAppointments = $appointments->groupBy(function ($appointment) {
            return $appointment->appointment_date->format('Y-m-d');
        })->map(function ($apps, $date) {
            return [
                'date' => $date,
                'count' => $apps->count(),
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
        $hourlyStats = $appointments->groupBy(function ($appointment) {
            return $appointment->appointment_time ? Carbon::parse($appointment->appointment_time)->format('H') : '00';
        })->map(function ($apps, $hour) {
            return [
                'hour' => $hour . ':00',
                'count' => $apps->count(),
            ];
        })->sortBy('hour')->values();

        // No-show analysis (trend by day)
        $noShowTrend = $appointments->where('status', 'no_show')->groupBy(function ($a) {
            return $a->appointment_date->format('Y-m-d');
        })->map(fn($apps, $d) => ['date' => $d, 'no_shows' => $apps->count()])->values();

        // Patient demographics for those visits
        $patientIds = $patientVisits->pluck('patient_id')->unique()->filter()->values();
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

        return view('doctor.reports.index', compact(
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
            'endDate'
        ));
    }

    public function export(Request $request)
    {
        $doctorId = auth()->id();
        $clinicId = $this->activeClinicId($request);
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());
        if (is_string($startDate)) $startDate = Carbon::parse($startDate);
        if (is_string($endDate)) $endDate = Carbon::parse($endDate);

        $serviceFilter = $request->get('service_id');

        $q = Appointment::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->whereBetween('appointment_date', [$startDate, $endDate]);
        if ($serviceFilter) $q->where('service_id', $serviceFilter);

        $appointments = $q->with(['patient', 'service'])->orderBy('appointment_date')->get();

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
                    $a->patient?->full_name ?? '—',
                    $a->service?->name ?? '—',
                    $a->status,
                ]);
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function calculateAverageWaitTime($queueEntries)
    {
        $servedEntries = $queueEntries->where('status', 'served')->whereNotNull('served_at');

        if ($servedEntries->isEmpty()) return 0;

        $totalWaitTime = $servedEntries->sum(function ($entry) {
            $created = Carbon::parse($entry->created_at);
            $served = Carbon::parse($entry->served_at);
            return $served->diffInMinutes($created);
        });

        return round($totalWaitTime / $servedEntries->count(), 2);
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
}