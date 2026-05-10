@extends('layouts.app')

@section('content')
<style>
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

    @media (max-width: 768px) {
        .analytics-page {
            width: 94%;
        }

        .analytics-hero {
            padding: 1.25rem;
        }

        .metric-value {
            font-size: 1.45rem;
        }
    }
</style>

<div class="analytics-page">
    @include('partials.alerts')

    <div class="analytics-hero mb-4">
        <div class="position-relative" style="z-index: 2;">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <span class="status-pill pill-blue mb-3">
                        <i class="bi bi-graph-up-arrow"></i>
                        Secretary Analytics
                    </span>

                    <h1 class="analytics-title">
                        Analytics Report
                    </h1>

                    <p class="analytics-subtitle">
                        Clinic performance report for <strong>{{ $activeClinic->name }}</strong>.
                    </p>
                </div>

                <div class="text-lg-end">
                    <div class="text-muted small">Current Period</div>
                    <div class="fw-bold text-primary">
                        {{ $periodLabel }}
                    </div>
                    <div class="text-muted small">
                        {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('secretary.analytics.index') }}" class="filter-card mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Report Period</label>
                <select name="period" class="form-select" onchange="toggleCustomDates(this.value)">
                    <option value="today" @selected(request('period') === 'today')>Today</option>
                    <option value="7days" @selected(request('period') === '7days')>Last 7 Days</option>
                    <option value="30days" @selected(request('period') === '30days')>Last 30 Days</option>
                    <option value="month" @selected(request('period', 'month') === 'month')>This Month</option>
                    <option value="custom" @selected(request('period') === 'custom')>Custom Range</option>
                </select>
            </div>

            <div class="col-md-3 custom-date-field">
                <label class="form-label fw-semibold">Start Date</label>
                <input type="date"
                       name="start_date"
                       value="{{ request('start_date', $startDate->toDateString()) }}"
                       class="form-control">
            </div>

            <div class="col-md-3 custom-date-field">
                <label class="form-label fw-semibold">End Date</label>
                <input type="date"
                       name="end_date"
                       value="{{ request('end_date', $endDate->toDateString()) }}"
                       class="form-control">
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i>
                    Apply
                </button>

                <a href="{{ route('secretary.analytics.index') }}" class="btn btn-light border">
                    Reset
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('secretary.analytics.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
                   class="btn btn-success w-100">
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
                        <div class="metric-value">{{ number_format($averageServiceMinutes, 1) }}m</div>

                        <div class="metric-note">
                            @if(! is_null($trends['appointments']))
                                <span class="{{ $trends['appointments'] >= 0 ? 'trend-up' : 'trend-down' }}">
                                    <i class="bi {{ $trends['appointments'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                    {{ abs($trends['appointments']) }}%
                                </span>
                                vs previous period
                            @else
                                No previous data
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex gap-3">
                    <div class="metric-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>

                    <div>
                        <div class="metric-label">Completed Appointments</div>
                        <div class="metric-value">{{ number_format($appointmentSummary['completed']) }}</div>

                        <div class="metric-note">
                            Completion rate: {{ $completionRate }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex gap-3">
                    <div class="metric-icon">
                        <i class="bi bi-person-walking"></i>
                    </div>

                    <div>
                        <div class="metric-label">Total Walk-ins</div>
                        <div class="metric-value">{{ number_format($walkInSummary['total']) }}</div>

                        <div class="metric-note">
                            @if(! is_null($trends['walk_ins']))
                                <span class="{{ $trends['walk_ins'] >= 0 ? 'trend-up' : 'trend-down' }}">
                                    <i class="bi {{ $trends['walk_ins'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                    {{ abs($trends['walk_ins']) }}%
                                </span>
                                vs previous period
                            @else
                                No previous data
                            @endif
                        </div>
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
                        <div class="metric-label">Avg. Service Time</div>
                        <div class="metric-value">{{ $averageServiceMinutes }}m</div>

                        <div class="metric-note">
                            Now Serving to Done and Next
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="report-card h-100">
                <h5 class="report-title">Appointment Activity</h5>
                <p class="report-description">
                    Daily appointment trend for the selected report period.
                </p>

                <div class="chart-box">
                    <canvas id="appointmentChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="report-card h-100">
                <h5 class="report-title">Appointment Summary</h5>
                <p class="report-description">
                    Report-level appointment outcomes only.
                </p>

                <div class="d-grid gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-blue">
                            <i class="bi bi-calendar-event"></i>
                            Total
                        </span>
                        <strong>{{ number_format($appointmentSummary['total']) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-green">
                            <i class="bi bi-check-circle"></i>
                            Completed
                        </span>
                        <strong>{{ number_format($appointmentSummary['completed']) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-red">
                            <i class="bi bi-x-circle"></i>
                            Cancelled
                        </span>
                        <strong>{{ number_format($appointmentSummary['cancelled']) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-orange">
                            <i class="bi bi-person-x"></i>
                            No-show
                        </span>
                        <strong>{{ number_format($appointmentSummary['no_show']) }}</strong>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Completion rate</span>
                    <strong>{{ $completionRate }}%</strong>
                </div>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">No-show rate</span>
                    <strong>{{ $appointmentNoShowRate }}%</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="report-card h-100">
                <h5 class="report-title">Walk-in Activity</h5>
                <p class="report-description">
                    Daily walk-in volume, served walk-ins, and not served walk-ins.
                </p>

                <div class="chart-box">
                    <canvas id="walkInChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="report-card h-100">
                <h5 class="report-title">Walk-in Summary</h5>
                <p class="report-description">
                    Simplified walk-in performance report.
                </p>

                <div class="d-grid gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-blue">
                            <i class="bi bi-person-walking"></i>
                            Total Walk-ins
                        </span>
                        <strong>{{ number_format($walkInSummary['total']) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-green">
                            <i class="bi bi-check-circle"></i>
                            Served Walk-ins
                        </span>
                        <strong>{{ number_format($walkInSummary['served']) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-slate">
                            <i class="bi bi-dash-circle"></i>
                            Not Served
                        </span>
                        <strong>{{ number_format($walkInSummary['not_served']) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-red">
                            <i class="bi bi-x-circle"></i>
                            Cancelled
                        </span>
                        <strong>{{ number_format($walkInSummary['cancelled']) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-pill pill-orange">
                            <i class="bi bi-person-x"></i>
                            No-show
                        </span>
                        <strong>{{ number_format($walkInSummary['no_show']) }}</strong>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">Walk-in served rate</span>
                    <strong>{{ $walkInServedRate }}%</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="report-card h-100">
                <h5 class="report-title">Services Report</h5>
                <p class="report-description">
                    Shows which clinic services receive the most appointments.
                </p>

                @if($serviceReport->isEmpty())
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
                <h5 class="report-title">Doctor Workload</h5>
                <p class="report-description">
                    Appointment distribution per doctor in this clinic.
                </p>

                @if($doctorReport->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-person-badge fs-2 d-block mb-2"></i>
                        No doctor appointment data found for this period.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Completed</th>
                                    <th class="text-end">No-show</th>
                                    <th class="text-end">Rate</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($doctorReport as $doctor)
                                    <tr>
                                        <td class="fw-semibold">Dr. {{ $doctor['name'] }}</td>
                                        <td class="text-end">{{ number_format($doctor['total']) }}</td>
                                        <td class="text-end">{{ number_format($doctor['completed']) }}</td>
                                        <td class="text-end">{{ number_format($doctor['no_show']) }}</td>
                                        <td class="text-end">
                                            <span class="status-pill pill-blue">
                                                {{ $doctor['completion_rate'] }}%
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    function toggleCustomDates(value) {
        const fields = document.querySelectorAll('.custom-date-field');

        fields.forEach(function(field) {
            field.style.display = value === 'custom' ? 'block' : 'none';
        });
    }

    toggleCustomDates(@json(request('period', 'month')));

    const appointmentData = @json($dailyAppointmentData);
    const walkInData = @json($dailyWalkInData);

    const appointmentCanvas = document.getElementById('appointmentChart');

    if (appointmentCanvas) {
        new Chart(appointmentCanvas, {
            type: 'line',
            data: {
                labels: appointmentData.labels,
                datasets: [
                    {
                        label: 'Total Appointments',
                        data: appointmentData.total,
                        tension: 0.35,
                        borderWidth: 3,
                        fill: false
                    },
                    {
                        label: 'Completed',
                        data: appointmentData.completed,
                        tension: 0.35,
                        borderWidth: 3,
                        fill: false
                    },
                    {
                        label: 'Cancelled',
                        data: appointmentData.cancelled,
                        tension: 0.35,
                        borderWidth: 3,
                        fill: false
                    },
                    {
                        label: 'No-show',
                        data: appointmentData.no_show,
                        tension: 0.35,
                        borderWidth: 3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    const walkInCanvas = document.getElementById('walkInChart');

    if (walkInCanvas) {
        new Chart(walkInCanvas, {
            type: 'bar',
            data: {
                labels: walkInData.labels,
                datasets: [
                    {
                        label: 'Total Walk-ins',
                        data: walkInData.total,
                        borderWidth: 1
                    },
                    {
                        label: 'Served Walk-ins',
                        data: walkInData.served,
                        borderWidth: 1
                    },
                    {
                        label: 'Not Served Walk-ins',
                        data: walkInData.not_served,
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
</script>
@endsection