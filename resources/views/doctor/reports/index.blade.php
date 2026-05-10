@extends('doctor.layouts.app')

@section('title', 'Reports & Analytics')

@section('doctor-content')
@php
    $doctorName = auth()->user()->name ?? 'Doctor';
    $clinicName = $activeClinic->name ?? 'Selected Clinic';
    $formatDuration = function ($minutes) {
        $totalSeconds = max(0, (int) round(((float) $minutes) * 60));
        $wholeMinutes = intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;

        if ($totalSeconds < 60) {
            return $totalSeconds . 's';
        }

        if ($wholeMinutes < 60) {
            return $wholeMinutes . 'm ' . $seconds . 's';
        }

        $hours = intdiv($wholeMinutes, 60);
        $remainingMinutes = $wholeMinutes % 60;

        if ($remainingMinutes <= 0) {
            return $hours . 'h';
        }

        return $hours . 'h ' . $remainingMinutes . 'm';
    };
@endphp

<div class="analytics-page doctor-reports-page">
    <div class="analytics-hero mb-4">
        <div class="position-relative" style="z-index: 2;">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <span class="status-pill pill-blue mb-3">
                        <i class="bi bi-graph-up-arrow"></i>
                        Doctor Analytics
                    </span>

                    <h1 class="analytics-title">Analytics Report</h1>

                    <p class="analytics-subtitle mb-0">
                        Clinic performance report for <strong>{{ $clinicName }}</strong>.
                    </p>
                </div>

                <div class="text-lg-end">
                    <div class="text-muted small">Current Period</div>
                    <div class="fw-bold text-primary">
                        {{ $periodLabel ?? ($isTodayRange ? 'Today' : 'Selected Range') }}
                    </div>
                    <div class="text-muted small">
                        {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('doctor.reports.index') }}" class="filter-card mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Report Period</label>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('doctor.reports.index', array_merge(request()->except(['start_date', 'end_date', 'period']), ['period' => 'today', 'start_date' => now()->toDateString(), 'end_date' => now()->toDateString()])) }}" class="btn btn-sm {{ ($periodKey ?? '') === 'today' ? 'btn-primary' : 'btn-outline-primary' }}">Today</a>
                    <a href="{{ route('doctor.reports.index', array_merge(request()->except(['start_date', 'end_date', 'period']), ['period' => 'last_7_days', 'start_date' => now()->subDays(6)->toDateString(), 'end_date' => now()->toDateString()])) }}" class="btn btn-sm {{ ($periodKey ?? '') === 'last_7_days' ? 'btn-primary' : 'btn-outline-primary' }}">Last 7 Days</a>
                    <a href="{{ route('doctor.reports.index', array_merge(request()->except(['start_date', 'end_date', 'period']), ['period' => 'this_month', 'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString()])) }}" class="btn btn-sm {{ ($periodKey ?? '') === 'this_month' ? 'btn-primary' : 'btn-outline-primary' }}">This Month</a>
                    <a href="{{ route('doctor.reports.index', array_merge(request()->except(['start_date', 'end_date', 'period']), ['period' => 'last_month', 'start_date' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(), 'end_date' => now()->subMonthNoOverflow()->endOfMonth()->toDateString()])) }}" class="btn btn-sm {{ ($periodKey ?? '') === 'last_month' ? 'btn-primary' : 'btn-outline-primary' }}">Last Month</a>
                    <a href="{{ route('doctor.reports.index', array_merge(request()->except(['start_date', 'end_date', 'period']), ['period' => 'this_year', 'start_date' => now()->startOfYear()->toDateString(), 'end_date' => now()->endOfYear()->toDateString()])) }}" class="btn btn-sm {{ ($periodKey ?? '') === 'this_year' ? 'btn-primary' : 'btn-outline-primary' }}">This Year</a>
                </div>
            </div>

            <div class="col-md-3">
                <label for="start_date" class="form-label fw-semibold">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
            </div>

            <div class="col-md-3">
                <label for="end_date" class="form-label fw-semibold">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
            </div>

            <div class="col-md-3">
                <label for="service_id" class="form-label fw-semibold">Service (optional)</label>
                <select name="service_id" id="service_id" class="form-select">
                    <option value="">All services</option>
                    @foreach($servicesList ?? [] as $srv)
                        <option value="{{ $srv->id }}" @selected((string)($serviceFilter ?? '') === (string) $srv->id)>{{ $srv->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i>
                    Apply
                </button>

                <a href="{{ route('doctor.reports.index') }}" class="btn btn-light border">
                    Reset
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('doctor.reports.export', request()->query()) }}" class="btn btn-success w-100">
                    <i class="bi bi-download me-1"></i>
                    Download CSV
                </a>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex gap-3">
                    <div class="metric-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>

                    <div>
                        <div class="metric-label">Total Appointments</div>
                        <div class="metric-value">{{ number_format($appointmentStats['total'] ?? 0) }}</div>

                        <div class="metric-note">
                            Appointments during selected range.
                        </div>

                        @if($showComparisons ?? true)
                            @php($appointmentsDelta = $comparisons['appointments_total'] ?? ['difference' => 0, 'percent' => 0, 'direction' => 'flat'])
                            <div class="metric-note {{ $appointmentsDelta['direction'] === 'up' ? 'trend-up' : ($appointmentsDelta['direction'] === 'down' ? 'trend-down' : '') }}">
                                <i class="bi {{ $appointmentsDelta['direction'] === 'up' ? 'bi-arrow-up-right' : ($appointmentsDelta['direction'] === 'down' ? 'bi-arrow-down-right' : 'bi-dash') }}"></i>
                                {{ $appointmentsDelta['direction'] === 'up' ? '+' : '' }}{{ $appointmentsDelta['difference'] }} vs previous period ({{ $appointmentsDelta['percent'] }}%)
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex gap-3">
                    <div class="metric-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>

                    <div>
                        <div class="metric-label">Queue Efficiency</div>
                        <div class="metric-value">{{ number_format($queueStats['served'] ?? 0) }} served</div>

                        <div class="metric-note">
                            Avg wait: {{ $formatDuration($queueStats['average_wait_time'] ?? 0) }}
                        </div>

                        @if($showComparisons ?? true)
                            @php($queueDelta = $comparisons['queue_served'] ?? ['difference' => 0, 'percent' => 0, 'direction' => 'flat'])
                            <div class="metric-note {{ $queueDelta['direction'] === 'up' ? 'trend-up' : ($queueDelta['direction'] === 'down' ? 'trend-down' : '') }}">
                                <i class="bi {{ $queueDelta['direction'] === 'up' ? 'bi-arrow-up-right' : ($queueDelta['direction'] === 'down' ? 'bi-arrow-down-right' : 'bi-dash') }}"></i>
                                {{ $queueDelta['direction'] === 'up' ? '+' : '' }}{{ $queueDelta['difference'] }} served vs previous period
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex gap-3">
                    <div class="metric-icon">
                        <i class="bi bi-people"></i>
                    </div>

                    <div>
                        <div class="metric-label">Unique Patients</div>
                        <div class="metric-value">{{ number_format($patientStats['unique_patients'] ?? 0) }}</div>

                        <div class="metric-note">
                            Repeat: {{ number_format($patientStats['repeat_patients'] ?? 0) }}
                        </div>

                        @if($showComparisons ?? true)
                            @php($patientsDelta = $comparisons['unique_patients'] ?? ['difference' => 0, 'percent' => 0, 'direction' => 'flat'])
                            <div class="metric-note {{ $patientsDelta['direction'] === 'up' ? 'trend-up' : ($patientsDelta['direction'] === 'down' ? 'trend-down' : '') }}">
                                <i class="bi {{ $patientsDelta['direction'] === 'up' ? 'bi-arrow-up-right' : ($patientsDelta['direction'] === 'down' ? 'bi-arrow-down-right' : 'bi-dash') }}"></i>
                                {{ $patientsDelta['direction'] === 'up' ? '+' : '' }}{{ $patientsDelta['difference'] }} unique patients vs previous period
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex gap-3">
                    <div class="metric-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>

                    <div>
                        <div class="metric-label">Schedule Utilization</div>
                        <div class="metric-value">{{ number_format($utilizationRate ?? 0, 1) }}%</div>

                        <div class="metric-note">
                            Filled slots vs available
                        </div>

                        @if($showComparisons ?? true)
                            @php($utilizationDelta = $comparisons['utilization_rate'] ?? ['difference' => 0, 'percent' => 0, 'direction' => 'flat'])
                            <div class="metric-note {{ $utilizationDelta['direction'] === 'up' ? 'trend-up' : ($utilizationDelta['direction'] === 'down' ? 'trend-down' : '') }}">
                                <i class="bi {{ $utilizationDelta['direction'] === 'up' ? 'bi-arrow-up-right' : ($utilizationDelta['direction'] === 'down' ? 'bi-arrow-down-right' : 'bi-dash') }}"></i>
                                {{ $utilizationDelta['direction'] === 'up' ? '+' : '' }}{{ $utilizationDelta['difference'] }} pts vs previous period
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="report-card h-100">
                <h5 class="report-title">Appointment Status</h5>
                <p class="report-description">Distribution of appointment statuses for the selected period.</p>

                <div class="chart-box">
                    <canvas id="appointmentStatusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="report-card h-100">
                <h5 class="report-title">Appointment Summary</h5>
                <p class="report-description">Report-level appointment outcomes only.</p>

                <div class="d-grid gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-blue">
                            <i class="bi bi-calendar-event"></i>
                            Total
                        </span>
                        <strong>{{ number_format($appointmentStats['total'] ?? 0) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-green">
                            <i class="bi bi-check-circle"></i>
                            Completed
                        </span>
                        <strong>{{ number_format($appointmentStats['completed'] ?? 0) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-slate">
                            <i class="bi bi-calendar"></i>
                            Scheduled
                        </span>
                        <strong>{{ number_format($appointmentStats['scheduled'] ?? 0) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-red">
                            <i class="bi bi-x-circle"></i>
                            Cancelled
                        </span>
                        <strong>{{ number_format($appointmentStats['cancelled'] ?? 0) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-orange">
                            <i class="bi bi-person-x"></i>
                            No-show
                        </span>
                        <strong>{{ number_format($appointmentStats['no_show'] ?? 0) }}</strong>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Completion rate</span>
                    <strong>{{ ($appointmentStats['total'] ?? 0) > 0 ? round((($appointmentStats['completed'] ?? 0) / max(1, $appointmentStats['total'])) * 100, 1) : 0 }}%</strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">No-show rate</span>
                    <strong>{{ ($appointmentStats['total'] ?? 0) > 0 ? round((($appointmentStats['no_show'] ?? 0) / max(1, $appointmentStats['total'])) * 100, 1) : 0 }}%</strong>
                </div>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">Cancelled rate</span>
                    <strong>{{ ($appointmentStats['total'] ?? 0) > 0 ? round((($appointmentStats['cancelled'] ?? 0) / max(1, $appointmentStats['total'])) * 100, 1) : 0 }}%</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="report-card h-100">
                <h5 class="report-title">Queue Performance</h5>
                <p class="report-description">Served, waiting, no-show and cancelled queue entries for the selected period.</p>

                <div class="chart-box">
                    <canvas id="queuePerformanceChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="report-card h-100">
                <h5 class="report-title">Queue Summary</h5>
                <p class="report-description">Simplified queue performance report.</p>

                <div class="d-grid gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-blue">
                            <i class="bi bi-list-check"></i>
                            Total Queue
                        </span>
                        <strong>{{ number_format($queueStats['total'] ?? 0) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-green">
                            <i class="bi bi-check-circle"></i>
                            Served
                        </span>
                        <strong>{{ number_format($queueStats['served'] ?? 0) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-slate">
                            <i class="bi bi-hourglass-split"></i>
                            Waiting
                        </span>
                        <strong>{{ number_format($queueStats['waiting'] ?? 0) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-red">
                            <i class="bi bi-x-circle"></i>
                            No-show
                        </span>
                        <strong>{{ number_format($queueStats['no_show'] ?? 0) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-orange">
                            <i class="bi bi-slash-circle"></i>
                            Cancelled
                        </span>
                        <strong>{{ number_format($queueStats['cancelled'] ?? 0) }}</strong>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">Average wait</span>
                    <strong>{{ $formatDuration($queueStats['average_wait_time'] ?? 0) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="report-card h-100">
                <h5 class="report-title">Appointments Over Time</h5>
                <p class="report-description">Daily appointment count for the selected report period.</p>

                <div class="chart-box">
                    <canvas id="appointmentsOverTimeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="report-card h-100">
                <h5 class="report-title">Services Breakdown</h5>
                <p class="report-description">How appointment volume is distributed by service.</p>

                <div class="chart-box smaller-chart">
                    <canvas id="servicesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card glass-panel h-100 p-0 mb-4">
        <div class="section-card-header">
            <div class="section-title-wrap">
                <h2 class="section-title"><i class="bi bi-clock-history"></i> Peak Hours</h2>
                <span class="section-count">Busier times</span>
            </div>
            <p class="section-subtitle mb-0">Appointment volume by hour of day.</p>
        </div>
        <div class="section-card-body p-0">
            <div class="chart-wrapper compact-chart">
                <canvas id="peakHoursChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-4">
        <div class="col-lg-6">
            <div class="report-card h-100">
                <h5 class="report-title">Services Report</h5>
                <p class="report-description">
                    Shows which services receive the most appointments.
                </p>

                @if(empty($serviceReport) || count($serviceReport) === 0)
                    <div class="empty-state">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        No service data found for this period.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Completed</th>
                                    <th class="text-end">Cancelled</th>
                                    <th class="text-end">No-show</th>
                                    <th class="text-end">Rate</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($serviceReport as $service)
                                    <tr>
                                        <td class="fw-semibold">{{ $service['name'] }}</td>
                                        <td class="text-end">{{ number_format($service['total']) }}</td>
                                        <td class="text-end">{{ number_format($service['completed']) }}</td>
                                        <td class="text-end">{{ number_format($service['cancelled']) }}</td>
                                        <td class="text-end">{{ number_format($service['no_show']) }}</td>
                                        <td class="text-end">
                                            <span class="status-pill pill-green">
                                                {{ $service['completion_rate'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="report-card h-100">
                <h5 class="report-title">Patient Appointments</h5>
                <p class="report-description">
                    Appointment distribution by patient.
                </p>

                @if(empty($patientReport) || count($patientReport) === 0)
                    <div class="empty-state">
                        <i class="bi bi-person fs-2 d-block mb-2"></i>
                        No patient appointment data found for this period.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Completed</th>
                                    <th class="text-end">Cancelled</th>
                                    <th class="text-end">No-show</th>
                                    <th class="text-end">Rate</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($patientReport as $patient)
                                    <tr>
                                        <td class="fw-semibold">{{ $patient['name'] }}</td>
                                        <td class="text-end">{{ number_format($patient['total']) }}</td>
                                        <td class="text-end">{{ number_format($patient['completed']) }}</td>
                                        <td class="text-end">{{ number_format($patient['cancelled']) }}</td>
                                        <td class="text-end">{{ number_format($patient['no_show']) }}</td>
                                        <td class="text-end">
                                            <span class="status-pill pill-blue">
                                                {{ $patient['completion_rate'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
  .doctor-reports-page {
    width: 100%;
  }

  .analytics-page {
    width: min(1320px, 94%);
    margin: 0 auto;
    padding: 1.5rem 0 3rem;
  }

  .analytics-hero {
    border: 1px solid rgba(37, 99, 235, .12);
    border-radius: 28px;
    background:
      radial-gradient(circle at top left, rgba(59, 130, 246, .18), transparent 35%),
      linear-gradient(135deg, rgba(255,255,255,.96), rgba(239,246,255,.95));
    box-shadow: 0 18px 50px rgba(15, 23, 42, .08);
    padding: 1.5rem;
    overflow: hidden;
    position: relative;
  }

  .analytics-hero::after {
    content: "";
    position: absolute;
    width: 240px;
    height: 240px;
    border-radius: 999px;
    right: -90px;
    top: -90px;
    background: rgba(37, 99, 235, .12);
  }

  .analytics-title {
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -.03em;
    margin-bottom: .35rem;
  }

  .analytics-subtitle {
    color: #64748b;
    margin-bottom: 0;
  }

  .filter-card,
  .metric-card,
  .report-card {
    border: 1px solid rgba(148, 163, 184, .22);
    border-radius: 24px;
    background: rgba(255,255,255,.94);
    box-shadow: 0 14px 40px rgba(15, 23, 42, .06);
  }

  .filter-card {
    padding: 1rem;
  }

  .metric-card {
    padding: 1.15rem;
    height: 100%;
  }

  .metric-icon {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eff6ff;
    color: #2563eb;
    font-size: 1.25rem;
    flex: 0 0 auto;
  }

  .metric-label {
    color: #64748b;
    font-size: .88rem;
    margin-bottom: .25rem;
  }

  .metric-value {
    color: #0f172a;
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 1;
  }

  .metric-note {
    color: #94a3b8;
    font-size: .8rem;
    margin-top: .35rem;
  }

  .trend-up {
    color: #16a34a;
  }

  .trend-down {
    color: #dc2626;
  }

  .report-card {
    padding: 1.25rem;
  }

  .report-title {
    font-weight: 800;
    color: #0f172a;
    margin-bottom: .25rem;
  }

  .report-description {
    color: #64748b;
    font-size: .9rem;
    margin-bottom: 1rem;
  }

  .status-pill {
    border-radius: 999px;
    padding: .35rem .75rem;
    font-size: .78rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
  }

  .pill-blue {
    background: #eff6ff;
    color: #2563eb;
  }

  .pill-green {
    background: #ecfdf5;
    color: #16a34a;
  }

  .pill-red {
    background: #fef2f2;
    color: #dc2626;
  }

  .pill-orange {
    background: #fff7ed;
    color: #ea580c;
  }

  .pill-slate {
    background: #f1f5f9;
    color: #475569;
  }

  .table thead th {
    color: #64748b;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    border-bottom-color: #e2e8f0;
  }

  .table td {
    vertical-align: middle;
    color: #334155;
  }

  .empty-state {
    border: 1px dashed #cbd5e1;
    border-radius: 18px;
    padding: 2rem;
    text-align: center;
    color: #64748b;
    background: #f8fafc;
  }

  .chart-box {
    min-height: 320px;
    position: relative;
  }

  .section-card {
    border-radius: 24px;
    padding: 1.25rem;
    border: 1px solid rgba(148, 163, 184, .22);
    box-shadow: 0 14px 40px rgba(15, 23, 42, .06);
    overflow: hidden;
    background: rgba(255,255,255,.94);
  }

  .section-card-header {
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(15,23,42,0.02);
    margin-bottom: 10px;
  }

  .section-title {
    font-size: 1.02rem;
    margin: 0;
    display:flex;
    align-items:center;
    gap:.5rem;
  }

  .section-subtitle {
    color:#6b7280;
    font-size:.9rem;
  }

  .chart-wrapper {
    position: relative;
    width: 100%;
    height: 220px;
  }

  .chart-wrapper.smaller-chart {
    height: 180px;
  }

  .chart-wrapper.compact-chart {
    height: 140px;
  }

  .chart-wrapper canvas {
    width:100% !important;
    height:100% !important;
    display:block;
  }

  @media (max-width: 992px) {
    .analytics-page {
      width: 94%;
    }

    .analytics-hero {
      padding: 1.25rem;
    }

    .chart-box,
    .chart-wrapper {
      min-height: 240px;
      height: 180px;
    }
  }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const compactLegend = {
    position: 'bottom',
    labels: {
      boxWidth: 12,
      boxHeight: 12,
      usePointStyle: true,
      pointStyle: 'circle',
      padding: 12,
      font: { size: 11 }
    }
  };

  const selectedPeriodLabel = @json($periodLabel ?? 'selected period');
  const noDataPlugin = {
    id: 'noDataMessage',
    afterDraw(chart) {
      const hasData = chart.data.datasets.some((dataset) => {
        return (dataset.data || []).some((value) => Number(value) > 0);
      });

      if (hasData) {
        return;
      }

      const { ctx, chartArea } = chart;
      ctx.save();
      ctx.fillStyle = '#64748b';
      ctx.font = '600 13px sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText(`No data for ${selectedPeriodLabel}.`, (chartArea.left + chartArea.right) / 2, (chartArea.top + chartArea.bottom) / 2);
      ctx.restore();
    }
  };

  const compactBarOptions = {
    responsive: true,
    maintainAspectRatio: false,
    layout: { padding: { top: 4, right: 8, bottom: 0, left: 8 } },
    scales: {
      y: { beginAtZero: true, ticks: { precision: 0 } },
      x: { ticks: { maxRotation: 0, autoSkip: true } }
    },
    plugins: {
      legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
    }
  };

  const appointmentStatusData = [
    {{ $appointmentStats['completed'] ?? 0 }},
    {{ $appointmentStats['scheduled'] ?? 0 }},
    {{ $appointmentStats['cancelled'] ?? 0 }},
    {{ $appointmentStats['no_show'] ?? 0 }}
  ];

  const queueData = [
    {{ $queueStats['total'] ?? 0 }},
    {{ $queueStats['served'] ?? 0 }},
    {{ $queueStats['no_show'] ?? 0 }},
    {{ $queueStats['waiting'] ?? 0 }},
    {{ $queueStats['cancelled'] ?? 0 }}
  ];

  const dailyLabels = @json(collect($dailyAppointments)->pluck('date')) || [];
  const dailyCounts = @json(collect($dailyAppointments)->pluck('count')) || [];

  const servicesLabels = @json(collect($serviceStats)->pluck('name')) || [];
  const servicesCounts = @json(collect($serviceStats)->pluck('count')) || [];

  const hourlyLabels = @json(collect($hourlyStats)->pluck('hour')) || [];
  const hourlyCounts = @json(collect($hourlyStats)->pluck('count')) || [];

  // Appointment Status
  new Chart(document.getElementById('appointmentStatusChart').getContext('2d'), {
    type: 'doughnut',
    data: { labels: ['Completed','Scheduled','Cancelled','No Show'], datasets: [{ data: appointmentStatusData, backgroundColor: ['#198754','#0d6efd','#dc3545','#ffc107'] }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: compactLegend } },
    plugins: [noDataPlugin]
  });

  // Queue Performance
  new Chart(document.getElementById('queuePerformanceChart').getContext('2d'), {
    type: 'bar',
    data: { labels:['Total','Served','No Show','Waiting','Cancelled'], datasets:[{ label:'Queue Entries', data: queueData, backgroundColor: '#0d6efd' }] },
    options: compactBarOptions,
    plugins: [noDataPlugin]
  });

  // Appointments Over Time
  new Chart(document.getElementById('appointmentsOverTimeChart').getContext('2d'), {
    type: 'line',
    data: { labels: dailyLabels, datasets: [{ label: 'Appointments', data: dailyCounts, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.08)', tension: 0.1 }] },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { top: 4, right: 8, bottom: 0, left: 8 } },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
        x: { ticks: { maxRotation: 0, autoSkip: true } }
      },
      plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } }
    },
    plugins: [noDataPlugin]
  });

  // Services
  new Chart(document.getElementById('servicesChart').getContext('2d'), {
    type: 'pie',
    data: { labels: servicesLabels, datasets:[{ data: servicesCounts, backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545','#6c757d','#0dcaf0'] }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: compactLegend } },
    plugins: [noDataPlugin]
  });

  // Peak hours
  new Chart(document.getElementById('peakHoursChart').getContext('2d'), {
    type: 'bar',
    data: { labels: hourlyLabels, datasets: [{ label: 'Appointments', data: hourlyCounts, backgroundColor: '#0d6efd' }] },
    options: compactBarOptions,
    plugins: [noDataPlugin]
  });


</script>

@endsection
