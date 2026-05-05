@extends('doctor.layouts.app')

@section('title', 'Reports & Analytics')

@section('doctor-content')
@php
    $doctorName = auth()->user()->name ?? 'Doctor';
@endphp

<div class="doctor-reports-page">
  <div class="doctor-dashboard-shell">
    <!-- Header -->
    <div class="doctor-hero glass-panel mb-4">
      <div class="d-flex flex-column flex-lg-row align-items-start justify-content-between gap-4">
        <div class="d-flex gap-3 align-items-center">
          <div class="doctor-hero-icon">
            <i class="bi bi-bar-chart-line"></i>
          </div>
          <div>
            <h1 class="doctor-hero-title">Reports & Analytics</h1>
            <p class="doctor-hero-text mb-0">Track appointments, queue performance, patient volume and utilization from one polished dashboard.</p>
          </div>
        </div>
        <div class="text-lg-end">
          <div class="doctor-hero-badge">
            <i class="bi bi-person-badge"></i>
            {{ $doctorName }}
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="glass-panel mb-4 p-4">
      <div class="d-flex flex-wrap gap-2 mb-3">
        <a class="btn btn-sm {{ ($isTodayRange ?? false) ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('doctor.reports.index', array_merge(request()->except(['start_date', 'end_date']), ['start_date' => now()->toDateString(), 'end_date' => now()->toDateString()])) }}">Today</a>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('doctor.reports.index', array_merge(request()->except(['start_date', 'end_date']), ['start_date' => now()->subDays(6)->toDateString(), 'end_date' => now()->toDateString()])) }}">Last 7 Days</a>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('doctor.reports.index', array_merge(request()->except(['start_date', 'end_date']), ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString()])) }}">This Month</a>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('doctor.reports.index', array_merge(request()->except(['start_date', 'end_date']), ['start_date' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(), 'end_date' => now()->subMonthNoOverflow()->endOfMonth()->toDateString()])) }}">Last Month</a>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('doctor.reports.index', array_merge(request()->except(['start_date', 'end_date']), ['start_date' => now()->startOfYear()->toDateString(), 'end_date' => now()->toDateString()])) }}">Year to Date</a>
      </div>
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label for="start_date" class="form-label">Start Date</label>
          <input type="date" class="form-control" id="start_date" name="start_date"
                 value="{{ $startDate->format('Y-m-d') }}">
        </div>
        <div class="col-md-3">
          <label for="end_date" class="form-label">End Date</label>
          <input type="date" class="form-control" id="end_date" name="end_date"
                 value="{{ $endDate->format('Y-m-d') }}">
        </div>
        <div class="col-md-3">
          <label for="service_id" class="form-label">Service (optional)</label>
          <select name="service_id" id="service_id" class="form-select">
            <option value="">All services</option>
            @foreach($servicesList ?? [] as $srv)
              <option value="{{ $srv->id }}" @if((string)($serviceFilter ?? '') === (string)$srv->id) selected @endif>{{ $srv->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary me-2">
            <i class="bi bi-filter me-1"></i>Apply
          </button>
            <a href="{{ route('doctor.reports.export', request()->all()) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i>Export CSV
          </a>
        </div>
      </form>
    </div>

    <!-- KPIs -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4 mb-4">
      <div class="col">
        <div class="doctor-highlight-card p-4 glass-panel">
          <div class="doctor-highlight-label">Total Appointments</div>
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="doctor-highlight-value mb-0">{{ $appointmentStats['total'] ?? 0 }}</h3>
            <div class="doctor-highlight-icon bg-primary text-white">
              <i class="bi bi-calendar-check"></i>
            </div>
          </div>
          <p class="doctor-highlight-sub mb-0">Appointments during selected range.</p>
          @if($showComparisons ?? true)
            @php($appointmentsDelta = $comparisons['appointments_total'] ?? ['difference' => 0, 'percent' => 0, 'direction' => 'flat'])
            <div class="small mt-2 {{ $appointmentsDelta['direction'] === 'up' ? 'text-success' : ($appointmentsDelta['direction'] === 'down' ? 'text-danger' : 'text-muted') }}">
              {{ $appointmentsDelta['direction'] === 'up' ? '+' : '' }}{{ $appointmentsDelta['difference'] }} vs previous period ({{ $appointmentsDelta['percent'] }}%)
            </div>
          @endif
        </div>
      </div>

      <div class="col">
        <div class="doctor-highlight-card p-4 glass-panel">
          <div class="doctor-highlight-label">Queue Efficiency</div>
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="doctor-highlight-value mb-0">{{ $queueStats['served'] ?? 0 }} served</h3>
            <div class="doctor-highlight-icon bg-success text-white">
              <i class="bi bi-speedometer2"></i>
            </div>
          </div>
          <p class="doctor-highlight-sub mb-0">Avg wait: {{ $queueStats['average_wait_time'] ?? 0 }} min</p>
          @if($showComparisons ?? true)
            @php($queueDelta = $comparisons['queue_served'] ?? ['difference' => 0, 'percent' => 0, 'direction' => 'flat'])
            <div class="small mt-2 {{ $queueDelta['direction'] === 'up' ? 'text-success' : ($queueDelta['direction'] === 'down' ? 'text-danger' : 'text-muted') }}">
              {{ $queueDelta['direction'] === 'up' ? '+' : '' }}{{ $queueDelta['difference'] }} served vs previous period
            </div>
          @endif
        </div>
      </div>

      <div class="col">
        <div class="doctor-highlight-card p-4 glass-panel">
          <div class="doctor-highlight-label">Unique Patients</div>
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="doctor-highlight-value mb-0">{{ $patientStats['unique_patients'] ?? 0 }}</h3>
            <div class="doctor-highlight-icon bg-info text-white">
              <i class="bi bi-people"></i>
            </div>
          </div>
          <p class="doctor-highlight-sub mb-0">Repeat: {{ $patientStats['repeat_patients'] ?? 0 }}</p>
          @if($showComparisons ?? true)
            @php($patientsDelta = $comparisons['unique_patients'] ?? ['difference' => 0, 'percent' => 0, 'direction' => 'flat'])
            <div class="small mt-2 {{ $patientsDelta['direction'] === 'up' ? 'text-success' : ($patientsDelta['direction'] === 'down' ? 'text-danger' : 'text-muted') }}">
              {{ $patientsDelta['direction'] === 'up' ? '+' : '' }}{{ $patientsDelta['difference'] }} unique patients vs previous period
            </div>
          @endif
        </div>
      </div>

      <div class="col">
        <div class="doctor-highlight-card p-4 glass-panel">
          <div class="doctor-highlight-label">Schedule Utilization</div>
          <div class="d-flex align-items-center justify-content-between">
            <h3 class="doctor-highlight-value mb-0">{{ $utilizationRate ?? 0 }}%</h3>
            <div class="doctor-highlight-icon bg-warning text-white">
              <i class="bi bi-clock"></i>
            </div>
          </div>
          <p class="doctor-highlight-sub mb-0">Filled slots vs available</p>
          @if($showComparisons ?? true)
            @php($utilizationDelta = $comparisons['utilization_rate'] ?? ['difference' => 0, 'percent' => 0, 'direction' => 'flat'])
            <div class="small mt-2 {{ $utilizationDelta['direction'] === 'up' ? 'text-success' : ($utilizationDelta['direction'] === 'down' ? 'text-danger' : 'text-muted') }}">
              {{ $utilizationDelta['direction'] === 'up' ? '+' : '' }}{{ $utilizationDelta['difference'] }} pts vs previous period
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
      <div class="col-xl-6">
        <div class="section-card glass-panel h-100">
          <div class="section-card-header">
            <div class="section-title-wrap">
              <h2 class="section-title"><i class="bi bi-pie-chart-fill"></i> Appointment Status</h2>
              <span class="section-count">Status mix</span>
            </div>
            <p class="section-subtitle mb-0">Distribution of appointment statuses.</p>
          </div>
          <div class="section-card-body p-0">
            <div class="chart-wrapper">
              <canvas id="appointmentStatusChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-6">
        <div class="section-card glass-panel h-100">
          <div class="section-card-header">
            <div class="section-title-wrap">
              <h2 class="section-title"><i class="bi bi-sliders"></i> Queue Performance</h2>
              <span class="section-count">Current queue</span>
            </div>
            <p class="section-subtitle mb-0">Served, waiting and no-shows.</p>
          </div>
          <div class="section-card-body p-0">
            <div class="chart-wrapper">
              <canvas id="queuePerformanceChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-xl-6">
        <div class="section-card glass-panel h-100">
          <div class="section-card-header">
            <div class="section-title-wrap">
              <h2 class="section-title"><i class="bi bi-graph-up"></i> Appointments Over Time</h2>
              <span class="section-count">Trend</span>
            </div>
            <p class="section-subtitle mb-0">Daily appointment count.</p>
          </div>
          <div class="section-card-body p-0">
            <div class="chart-wrapper">
              <canvas id="appointmentsOverTimeChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-6">
        <div class="section-card glass-panel h-100">
          <div class="section-card-header">
            <div class="section-title-wrap">
              <h2 class="section-title"><i class="bi bi-pie-chart"></i> Services Breakdown</h2>
              <span class="section-count">Service mix</span>
            </div>
            <p class="section-subtitle mb-0">How appointment volume is distributed by service.</p>
          </div>
          <div class="section-card-body p-0">
            <div class="chart-wrapper smaller-chart">
              <canvas id="servicesChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Peak Hours -->
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

    <!-- Patient Demographics -->
    <div class="row g-4">
      <div class="col-md-6">
        <div class="section-card glass-panel p-4">
          <h5 class="mb-3">Patient Age Groups</h5>
          <ul class="list-unstyled mb-0">
            @foreach($patientAges ?? [] as $group => $count)
              <li class="d-flex justify-content-between py-1">
                <span>{{ $group }}</span>
                <strong>{{ $count }}</strong>
              </li>
            @endforeach
          </ul>
        </div>
      </div>

      <div class="col-md-6">
        <div class="section-card glass-panel p-4">
          <h5 class="mb-3">No-show Trend (by day)</h5>
          <div>
            <canvas id="noShowTrendChart" style="height:140px;"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mt-0">
      <div class="col-12">
        <div class="glass-panel p-4">
          <h5 class="mb-3">No-show Rate</h5>
          <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div>
              <h3 class="mb-1 text-warning">{{ ($appointmentStats['total'] ?? 0) > 0 ? round((($appointmentStats['no_show'] ?? 0) / max(1, $appointmentStats['total'])) * 100, 1) : 0 }}%</h3>
              @if($showComparisons ?? true)
                <div class="text-muted small">Compared to previous period</div>
              @endif
            </div>
            @if($showComparisons ?? true)
              <div class="small {{ (($comparisons['no_show_rate']['direction'] ?? 'flat') === 'up' ? 'text-danger' : (($comparisons['no_show_rate']['direction'] ?? 'flat') === 'down' ? 'text-success' : 'text-muted')) }}">
                {{ (($comparisons['no_show_rate']['direction'] ?? 'flat') === 'up' ? '+' : '') }}{{ $comparisons['no_show_rate']['difference'] ?? 0 }} points vs previous period ({{ $comparisons['no_show_rate']['percent'] ?? 0 }}%)
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
  .doctor-reports-page { width: 100%; }
  .doctor-dashboard-shell { max-width: 1280px; margin: 0 auto; padding: 0 18px; }
  .doctor-hero { border-radius: 14px; padding: 18px; display: flex; align-items: center; justify-content: space-between; gap: 1rem; background: linear-gradient(90deg, rgba(13,110,253,0.06), rgba(15,23,42,0.02)); box-shadow: 0 8px 20px rgba(15,23,42,0.06); }
  .doctor-hero-icon { width:56px; height:56px; border-radius:12px; display:flex; align-items:center; justify-content:center; background: rgba(13,110,253,0.12); color: #0d6efd; font-size:1.35rem; }
  .doctor-hero-title { margin:0; font-size:1.2rem; font-weight:700; }
  .doctor-hero-text { margin:0; color:#6b7280; }
  .doctor-hero-badge { display:inline-flex; align-items:center; gap:.5rem; padding:.45rem .75rem; border-radius:999px; background:#fff; border:1px solid rgba(15,23,42,0.04); box-shadow:0 6px 18px rgba(13,110,253,0.03); font-weight:600; }
  .glass-panel { background: #fff; border-radius: 12px; border: 1px solid rgba(15,23,42,0.04); box-shadow: 0 10px 30px rgba(15,23,42,0.06); }
  .doctor-highlight-card { border-radius: 12px; padding: 1rem; background: transparent; }
  .doctor-highlight-label { font-size: .78rem; color: #6b7280; text-transform: uppercase; margin-bottom: .25rem; }
  .doctor-highlight-value { font-size: 1.4rem; font-weight:700; }
  .doctor-highlight-sub { color:#6b7280; font-size:.92rem; }
  .doctor-highlight-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.05rem; }
  .section-card { border-radius: 12px; padding: 16px; border: 1px solid rgba(15,23,42,0.04); box-shadow: 0 10px 24px rgba(15,23,42,0.04); overflow: hidden; }
  .section-card-header { padding-bottom: 10px; border-bottom: 1px solid rgba(15,23,42,0.02); margin-bottom: 10px; }
  .section-title { font-size: 1.02rem; margin: 0; display:flex; align-items:center; gap:.5rem; }
  .section-subtitle { color:#6b7280; font-size:.9rem; }
  .chart-wrapper { position: relative; width: 100%; height: 220px; }
  .chart-wrapper.smaller-chart { height: 180px; }
  .chart-wrapper.compact-chart { height: 140px; }
  .chart-wrapper canvas { width:100% !important; height:100% !important; display:block; }
  .glass-panel.p-4 { padding: 16px; }
  @media (max-width: 992px) { .doctor-hero { flex-direction: column; align-items: flex-start; } .chart-wrapper { height: 180px; } }
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
  const appointmentStatusFallback = [1,2,0,0];
  const appointmentStatusFinal = appointmentStatusData.reduce((s,v)=>s+Number(v),0) > 0 ? appointmentStatusData : appointmentStatusFallback;

  const queueData = [
    {{ $queueStats['total'] ?? 0 }},
    {{ $queueStats['served'] ?? 0 }},
    {{ $queueStats['no_show'] ?? 0 }},
    {{ $queueStats['waiting'] ?? 0 }}
  ];
  const queueFallback = [1,0,0,1];
  const queueFinal = queueData.reduce((s,v)=>s+Number(v),0) > 0 ? queueData : queueFallback;

  const dailyLabels = @json(collect($dailyAppointments)->pluck('date')) || [];
  const dailyCounts = @json(collect($dailyAppointments)->pluck('count')) || [];
  const dailyLabelsFallback = ["2026-05-04","2026-05-05"];
  const dailyCountsFallback = [2,1];
  const dailyLabelsFinal = (dailyLabels && dailyLabels.length) ? dailyLabels : dailyLabelsFallback;
  const dailyCountsFinal = (dailyCounts && dailyCounts.length) ? dailyCounts : dailyCountsFallback;

  const servicesLabels = @json(collect($serviceStats)->pluck('name')) || [];
  const servicesCounts = @json(collect($serviceStats)->pluck('count')) || [];
  const servicesLabelsFinal = (servicesLabels && servicesLabels.length) ? servicesLabels : ["General Consultation"];
  const servicesCountsFinal = (servicesCounts && servicesCounts.length) ? servicesCounts : [3];

  const hourlyLabels = @json(collect($hourlyStats)->pluck('hour')) || [];
  const hourlyCounts = @json(collect($hourlyStats)->pluck('count')) || [];
  const hourlyLabelsFinal = (hourlyLabels && hourlyLabels.length) ? hourlyLabels : ["08:00","09:00","10:00"];
  const hourlyCountsFinal = (hourlyCounts && hourlyCounts.length) ? hourlyCounts : [1,1,1];

  // Appointment Status
  new Chart(document.getElementById('appointmentStatusChart').getContext('2d'), {
    type: 'doughnut',
    data: { labels: ['Completed','Scheduled','Cancelled','No Show'], datasets: [{ data: appointmentStatusFinal, backgroundColor: ['#198754','#0d6efd','#dc3545','#ffc107'] }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: compactLegend } }
  });

  // Queue Performance
  new Chart(document.getElementById('queuePerformanceChart').getContext('2d'), {
    type: 'bar',
    data: { labels:['Total','Served','No Show','Waiting'], datasets:[{ label:'Queue Entries', data: queueFinal, backgroundColor: '#0d6efd' }] },
    options: compactBarOptions
  });

  // Appointments Over Time
  new Chart(document.getElementById('appointmentsOverTimeChart').getContext('2d'), {
    type: 'line',
    data: { labels: dailyLabelsFinal, datasets: [{ label: 'Appointments', data: dailyCountsFinal, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.08)', tension: 0.1 }] },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { top: 4, right: 8, bottom: 0, left: 8 } },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
        x: { ticks: { maxRotation: 0, autoSkip: true } }
      },
      plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } }
    }
  });

  // Services
  new Chart(document.getElementById('servicesChart').getContext('2d'), {
    type: 'pie',
    data: { labels: servicesLabelsFinal, datasets:[{ data: servicesCountsFinal, backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545','#6c757d','#0dcaf0'] }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: compactLegend } }
  });

  // Peak hours
  new Chart(document.getElementById('peakHoursChart').getContext('2d'), {
    type: 'bar',
    data: { labels: hourlyLabelsFinal, datasets: [{ label: 'Appointments', data: hourlyCountsFinal, backgroundColor: '#0d6efd' }] },
    options: compactBarOptions
  });

  // No-show trend
  const noShowLabels = @json(collect($noShowTrend ?? [])->pluck('date')) || [];
  const noShowCounts = @json(collect($noShowTrend ?? [])->pluck('no_shows')) || [];
  const noShowLabelsFinal = (noShowLabels && noShowLabels.length) ? noShowLabels : [];
  const noShowCountsFinal = (noShowCounts && noShowCounts.length) ? noShowCounts : [];
  if (noShowLabelsFinal.length) {
    new Chart(document.getElementById('noShowTrendChart').getContext('2d'), {
      type: 'bar',
      data: { labels: noShowLabelsFinal, datasets:[{ label: 'No-shows', data: noShowCountsFinal, backgroundColor: '#dc3545' }] },
      options: compactBarOptions
    });
  }
</script>

@endsection
