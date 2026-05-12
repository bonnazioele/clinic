{{-- resources/views/admin/dashboard.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Administrator Dashboard')

@push('styles')
<style>
  /* ── Base ── */
  .admin-dashboard-page { width: 100%; }

  /* ── Hero ── */
  .admin-dashboard-hero {
    position: relative;
    overflow: hidden;
    border-radius: 34px;
    padding: 32px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    background:
      radial-gradient(circle at top left, rgba(22, 119, 255, 0.16), transparent 30%),
      radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.12), transparent 28%),
      rgba(255, 255, 255, 0.86);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
  }

  .admin-dashboard-hero::after {
    content: "";
    position: absolute;
    right: -80px;
    bottom: -90px;
    width: 260px;
    height: 260px;
    border-radius: 999px;
    background: rgba(22, 119, 255, 0.10);
    pointer-events: none;
  }

  .admin-hero-icon {
    width: 76px;
    height: 76px;
    border-radius: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    background: linear-gradient(135deg, #1677ff, #06b6d4);
    font-size: 2.1rem;
    box-shadow: 0 18px 34px rgba(22, 119, 255, 0.28);
    flex-shrink: 0;
  }

  .admin-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    padding: 7px 14px;
    background: rgba(22, 119, 255, 0.10);
    color: #1677ff;
    font-size: 0.80rem;
    font-weight: 700;
  }

  .admin-hero-title {
    margin: 0;
    color: #0f172a;
    font-size: clamp(1.75rem, 3vw, 2.6rem);
    font-weight: 900;
    letter-spacing: -0.05em;
    line-height: 1.05;
  }

  .admin-hero-text {
    color: #64748b;
    margin: 8px 0 0;
    font-size: 0.97rem;
  }

  /* Pending alert pill in hero */
  .admin-hero-alert {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    padding: 10px 18px;
    background: rgba(245, 158, 11, 0.14);
    border: 1px solid rgba(245, 158, 11, 0.30);
    color: #92400e;
    font-size: 0.84rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .admin-hero-alert .pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #f59e0b;
    animation: pulse-dot 1.6s ease-in-out infinite;
    flex-shrink: 0;
  }

  @keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.7); }
  }

  /* ── Stat Cards ── */
  .admin-stat-card {
    height: 100%;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.90);
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.07);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    padding: 22px;
    transition: 0.22s ease;
    cursor: default;
  }

  .admin-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 22px 50px rgba(15, 23, 42, 0.11);
  }

  .admin-stat-label {
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 700;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .admin-stat-value {
    color: #0f172a;
    font-size: 2.5rem;
    font-weight: 900;
    line-height: 1;
    margin: 0 0 6px;
  }

  .admin-stat-desc {
    color: #64748b;
    font-size: 0.86rem;
    margin: 0;
  }

  .admin-stat-trend {
    font-size: 0.78rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border-radius: 999px;
    padding: 3px 10px;
  }

  .trend-up { color: #047857; }
  .trend-down { color: #b91c1c; }
  .trend-neutral { color: #475569; }

  .admin-stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
  }

  .icon-blue  { background: rgba(22, 119, 255, 0.12); color: #1677ff; }
  .icon-green { background: rgba(16, 185, 129, 0.14); color: #047857; }
  .icon-amber { background: rgba(245, 158, 11, 0.16); color: #92400e; }
  .icon-slate { background: rgba(100, 116, 139, 0.14); color: #475569; }
  .icon-rose  { background: rgba(244, 63, 94, 0.12);  color: #be123c; }

  /* Live dot for real-time stats */
  .live-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.70rem;
    font-weight: 700;
    color: #047857;
    background: rgba(16, 185, 129, 0.12);
    border-radius: 999px;
    padding: 2px 8px;
  }

  .live-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    animation: pulse-dot 1.4s ease-in-out infinite;
  }

  /* ── Panels ── */
  .admin-panel {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.90);
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.07);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    overflow: hidden;
    height: 100%;
  }

  .admin-panel-header {
    padding: 20px 24px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.07);
    background: linear-gradient(180deg, #ffffff, #f8fafc);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }

  .admin-panel-title {
    color: #0f172a;
    font-size: 1.1rem;
    font-weight: 900;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .admin-panel-title i { color: #1677ff; }

  .admin-panel-subtitle {
    color: #64748b;
    font-size: 0.86rem;
    margin: 5px 0 0;
  }

  .admin-panel-body { padding: 20px 24px; }

  /* ── Bar Chart (clinic activity) ── */
  .bar-chart-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
  }

  .bar-chart-row:last-child { margin-bottom: 0; }

  .bar-chart-label {
    font-size: 0.82rem;
    color: #475569;
    width: 120px;
    flex-shrink: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .bar-chart-track {
    flex: 1;
    height: 9px;
    background: rgba(15, 23, 42, 0.07);
    border-radius: 999px;
    overflow: hidden;
  }

  .bar-chart-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #1677ff, #38bdf8);
    transition: width 0.6s ease;
  }

  .bar-chart-val {
    font-size: 0.82rem;
    font-weight: 700;
    color: #0f172a;
    width: 26px;
    text-align: right;
  }

  /* ── Registration status pills ── */
  .status-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 20px;
  }

  .status-mini {
    border-radius: 18px;
    padding: 14px;
    text-align: center;
  }

  .status-mini-label {
    font-size: 0.74rem;
    font-weight: 700;
    margin-bottom: 4px;
  }

  .status-mini-val {
    font-size: 1.8rem;
    font-weight: 900;
    line-height: 1;
  }

  .status-active  { background: rgba(16, 185, 129, 0.10); color: #047857; }
  .status-pending { background: rgba(245, 158, 11, 0.13); color: #92400e; }
  .status-rejected { background: rgba(239, 68, 68, 0.10); color: #b91c1c; }

  /* ── Pending applications ── */
  .application-item {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 20px;
    background: #ffffff;
    padding: 14px 16px;
    transition: 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  .application-item + .application-item { margin-top: 10px; }

  .application-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
  }

  .app-clinic-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(22, 119, 255, 0.10);
    color: #1677ff;
    font-size: 1.1rem;
    flex-shrink: 0;
  }

  .application-name {
    color: #0f172a;
    font-weight: 800;
    margin-bottom: 3px;
    font-size: 0.94rem;
  }

  .application-meta {
    color: #64748b;
    font-size: 0.80rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .app-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border-radius: 999px;
    padding: 5px 12px;
    font-size: 0.74rem;
    font-weight: 800;
    white-space: nowrap;
    flex-shrink: 0;
  }

  .badge-pending  { background: rgba(245, 158, 11, 0.15); color: #92400e; }
  .badge-overdue  { background: rgba(239, 68, 68, 0.12);  color: #b91c1c; }

  .app-actions { display: flex; gap: 6px; flex-shrink: 0; }

  .btn-approve {
    border-radius: 12px;
    padding: 7px 14px;
    font-size: 0.78rem;
    font-weight: 800;
    background: rgba(16, 185, 129, 0.12);
    color: #047857;
    border: 1px solid rgba(16, 185, 129, 0.25);
    transition: 0.18s ease;
    white-space: nowrap;
  }

  .btn-approve:hover {
    background: rgba(16, 185, 129, 0.22);
    color: #047857;
  }

  .btn-reject {
    border-radius: 12px;
    padding: 7px 14px;
    font-size: 0.78rem;
    font-weight: 800;
    background: rgba(239, 68, 68, 0.10);
    color: #b91c1c;
    border: 1px solid rgba(239, 68, 68, 0.22);
    transition: 0.18s ease;
    white-space: nowrap;
  }

  .btn-reject:hover {
    background: rgba(239, 68, 68, 0.18);
    color: #b91c1c;
  }

  /* ── Activity feed ── */
  .activity-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(15, 23, 42, 0.06);
  }

  .activity-item:last-child { border-bottom: none; }

  .activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.88rem;
    flex-shrink: 0;
    margin-top: 1px;
  }

  .activity-text {
    font-size: 0.84rem;
    color: #0f172a;
    line-height: 1.45;
    margin: 0 0 3px;
  }

  .activity-time {
    font-size: 0.75rem;
    color: #94a3b8;
    margin: 0;
  }

  /* ── Donut + legend ── */
  .donut-wrap {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-top: 8px;
  }

  .donut-legend { display: flex; flex-direction: column; gap: 7px; flex: 1; }

  .legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    color: #475569;
  }

  .legend-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  /* ── Trend sparkline area ── */
  .sparkline-wrap { margin: 4px 0 12px; }

  /* ── Responsive ── */
  @media (max-width: 991.98px) {
    .admin-dashboard-hero { padding: 22px; border-radius: 24px; }
    .admin-stat-card, .admin-panel { border-radius: 22px; }
  }

  @media (max-width: 767.98px) {
    .admin-hero-title { font-size: 1.6rem; }
    .admin-hero-icon { width: 58px; height: 58px; font-size: 1.6rem; }
    .status-grid { grid-template-columns: repeat(3, 1fr); }
  }
</style>
@endpush

@section('content')
<div class="admin-dashboard-page">
  <div class="container-fluid px-3 px-md-4 py-4">

    {{-- ── HERO ── --}}
    <div class="admin-dashboard-hero mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="admin-hero-icon">
            <i class="bi bi-shield-check"></i>
          </div>
          <div>
            <h1 class="admin-hero-title">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="admin-hero-text">Platform overview &mdash; manage clinics, services, and registrations.</p>
          </div>
        </div>
      </div>
    </div>

    {{-- ── STAT CARDS ── --}}
    <div class="row g-3 mb-4">

      <div class="col-6 col-lg-3">
        <div class="admin-stat-card">
          <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
            <div>
              <p class="admin-stat-label">
                <i class="bi bi-hospital"></i> Registered clinics
              </p>
              <p class="admin-stat-value">{{ $totalClinics }}</p>
              <p class="admin-stat-desc">
                <span class="admin-stat-trend trend-up">
                  <i class="bi bi-arrow-up-short"></i> {{ $newClinicsThisMonth }} this month
                </span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-lg-3">
        <div class="admin-stat-card">
          <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
            <div>
              <p class="admin-stat-label">
                <i class="bi bi-people"></i> Platform users
              </p>
              <p class="admin-stat-value">{{ $totalUsers }}</p>
              <p class="admin-stat-desc">
                <span class="admin-stat-trend trend-up">
                  <i class="bi bi-arrow-up-short"></i> {{ $newUsersThisWeek }} this week
                </span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-lg-3">
        <div class="admin-stat-card">
          <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
            <div>
              <p class="admin-stat-label">
                <i class="bi bi-activity"></i> </span> Live</span>
                In queue now
              </p>
              <p class="admin-stat-value">{{ $patientsInQueueNow }}</p>
              <p class="admin-stat-desc text-muted" style="font-size:0.80rem">Across all active clinics</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-lg-3">
        <div class="admin-stat-card">
          <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
            <div>
              <p class="admin-stat-label">
                <i class="bi bi-calendar-check"></i> Appointments today
              </p>
              <p class="admin-stat-value">{{ $appointmentsToday }}</p>
              <p class="admin-stat-desc">
                <span class="admin-stat-trend trend-down">
                  <i class="bi bi-arrow-down-short"></i> {{ $cancelledToday }} cancelled
                </span>
              </p>
            </div>
          </div>
        </div>
      </div>

    </div>

    {{-- ── MAIN CONTENT ROW ── --}}
    <div class="row g-3 mb-3">

      {{-- LEFT: Clinic Status + Bar Chart --}}
      <div class="col-lg-5">
        <div class="admin-panel">
          <div class="admin-panel-header">
            <div>
              <h2 class="admin-panel-title">
                Clinic Registration Status
              </h2>
              <p class="admin-panel-subtitle">Clinic status breakdown &amp; activity today</p>
            </div>
          </div>
          <div class="admin-panel-body">

            {{-- Status breakdown --}}
            <div class="status-grid">
              <div class="status-mini status-active">
                <p class="status-mini-label">Active</p>
                <p class="status-mini-val">{{ $activeClinics }}</p>
              </div>
              <div class="status-mini status-pending">
                <p class="status-mini-label">Pending</p>
                <p class="status-mini-val">{{ $pendingClinicsCount }}</p>
              </div>
              <div class="status-mini status-rejected">
                <p class="status-mini-label">Rejected</p>
                <p class="status-mini-val">{{ $rejectedClinics }}</p>
              </div>
            </div>

            {{-- Most active clinics bar chart --}}
            <p class="admin-stat-label mb-3">
              </i> Most active clinics today
              <span class="ms-1" style="font-weight:400;color:#94a3b8">by queue volume</span>
            </p>

            @forelse($topClinicsByQueue as $clinic)
            @php
              $maxQueue = $topClinicsByQueue->max('queue_count');
              $pct = $maxQueue > 0 ? ($clinic->queue_count / $maxQueue) * 100 : 0;
            @endphp
            <div class="bar-chart-row">
              <span class="bar-chart-label" title="{{ $clinic->name }}">{{ $clinic->name }}</span>
              <div class="bar-chart-track">
                <div class="bar-chart-fill" style="width: {{ round($pct) }}%"></div>
              </div>
              <span class="bar-chart-val">{{ $clinic->queue_count }}</span>
            </div>
            @empty
            <p class="text-muted" style="font-size:0.86rem">No queue activity today.</p>
            @endforelse

            {{-- Service catalog donut --}}
            <p class="admin-stat-label mt-4 mb-2">
              <i class="bi bi-grid-1x2"></i> Service catalog breakdown
            </p>
            <div class="donut-wrap">
              <svg viewBox="0 0 80 80" width="80" height="80" style="flex-shrink:0;overflow:visible">
                <circle cx="40" cy="40" r="30" fill="none" stroke="rgba(15,23,42,0.07)" stroke-width="11"/>
                <circle cx="40" cy="40" r="30" fill="none" stroke="#1677ff" stroke-width="11"
                  stroke-dasharray="{{ round(($generalServices / max($totalServices,1)) * 188) }} 188"
                  stroke-dashoffset="0" transform="rotate(-90 40 40)"/>
                <circle cx="40" cy="40" r="30" fill="none" stroke="#10b981" stroke-width="11"
                  stroke-dasharray="{{ round(($specialtyServices / max($totalServices,1)) * 188) }} 188"
                  stroke-dashoffset="-{{ round(($generalServices / max($totalServices,1)) * 188) }}"
                  transform="rotate(-90 40 40)"/>
                <circle cx="40" cy="40" r="30" fill="none" stroke="#f59e0b" stroke-width="11"
                  stroke-dasharray="{{ round(($diagnosticServices / max($totalServices,1)) * 188) }} 188"
                  stroke-dashoffset="-{{ round((($generalServices + $specialtyServices) / max($totalServices,1)) * 188) }}"
                  transform="rotate(-90 40 40)"/>
                <text x="40" y="44" text-anchor="middle" font-size="13" font-weight="900"
                  fill="#0f172a" font-family="inherit">{{ $totalServices }}</text>
              </svg>
              <div class="donut-legend">
                <div class="legend-item">
                  <span class="legend-dot" style="background:#1677ff"></span>
                  General ({{ $generalServices }})
                </div>
                <div class="legend-item">
                  <span class="legend-dot" style="background:#10b981"></span>
                  Specialty ({{ $specialtyServices }})
                </div>
                <div class="legend-item">
                  <span class="legend-dot" style="background:#f59e0b"></span>
                  Diagnostic ({{ $diagnosticServices }})
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      {{-- RIGHT: Growth trend + Activity feed --}}
      <div class="col-lg-7">
        <div class="row g-3 h-100">

          {{-- Growth trend --}}
          <div class="col-12">
            <div class="admin-panel">
              <div class="admin-panel-header">
                <div>
                  <h2 class="admin-panel-title">
                    <i class="bi bi-graph-up-arrow"></i> Clinic registrations
                  </h2>
                  <p class="admin-panel-subtitle">New registrations over the last 6 months</p>
                </div>
              </div>
              <div class="admin-panel-body pb-3">
                <div class="sparkline-wrap">
                  <svg viewBox="0 0 420 80" style="width:100%;overflow:visible" preserveAspectRatio="none">
                    <defs>
                      <linearGradient id="sparkGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#1677ff" stop-opacity="0.15"/>
                        <stop offset="100%" stop-color="#1677ff" stop-opacity="0"/>
                      </linearGradient>
                    </defs>
                    {{-- Grid lines --}}
                    @foreach([0, 20, 40, 60] as $y)
                    <line x1="0" y1="{{ $y }}" x2="420" y2="{{ $y }}"
                      stroke="rgba(15,23,42,0.06)" stroke-width="1"/>
                    @endforeach
                    {{-- Fill area --}}
                    <path d="{{ $sparklineFillPath }}" fill="url(#sparkGrad)"/>
                    {{-- Line --}}
                    <polyline points="{{ $sparklinePoints }}"
                      fill="none" stroke="#1677ff" stroke-width="2.5"
                      stroke-linejoin="round" stroke-linecap="round"/>
                    {{-- Dots + labels --}}
                    @foreach($registrationTrend as $i => $point)
                    @php $x = ($i / (count($registrationTrend) - 1)) * 420; @endphp
                    <circle cx="{{ $x }}" cy="{{ $point['y'] }}" r="4"
                      fill="#ffffff" stroke="#1677ff" stroke-width="2"/>
                    <text x="{{ $x }}" y="76" text-anchor="middle"
                      font-size="10" fill="#94a3b8" font-family="inherit">{{ $point['label'] }}</text>
                    @endforeach
                  </svg>
                </div>
              </div>
            </div>
          </div>

          {{-- Activity feed --}}
          <div class="col-12">
            <div class="admin-panel">
              <div class="admin-panel-header">
                <div>
                  <h2 class="admin-panel-title">
                    <i class="bi bi-clock-history"></i> Recent activity
                  </h2>
                  <p class="admin-panel-subtitle">Latest platform-level events</p>
                </div>
              </div>
              <div class="admin-panel-body">
                @forelse($recentActivity as $event)
                <div class="activity-item">
                  <div class="activity-icon {{ $event['icon_bg'] }}">
                    <i class="{{ $event['icon'] }}" style="color: {{ $event['icon_color'] }}"></i>
                  </div>
                  <div>
                    <p class="activity-text">{{ $event['description'] }}</p>
                    <p class="activity-time">{{ $event['time'] }}</p>
                  </div>
                </div>
                @empty
                <p class="text-muted" style="font-size:0.86rem">No recent activity.</p>
                @endforelse
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>

    {{-- ── PENDING CLINIC APPLICATIONS ── --}}
    <div class="row g-3">
      <div class="col-12">
        <div class="admin-panel">
          <div class="admin-panel-header">
            <div>
              <h2 class="admin-panel-title">
                <i class="bi bi-file-earmark-medical"></i> Pending clinic applications
                @if($pendingClinicsCount > 0)
                <span class="ms-1" style="
                  background: rgba(245,158,11,0.15);
                  color: #92400e;
                  font-size: 0.72rem;
                  padding: 3px 10px;
                  border-radius: 999px;
                  font-weight: 800;
                ">{{ $pendingClinicsCount }}</span>
                @endif
              </h2>
              <p class="admin-panel-subtitle">Clinic registration requests awaiting your review</p>
            </div>
            <a href="{{ route('admin.clinics.index', ['status' => 'pending']) }}"
               class="btn btn-sm btn-outline-primary rounded-pill px-3"
               style="font-size:0.80rem;font-weight:700;white-space:nowrap">
              View all <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
          <div class="admin-panel-body">

            @forelse($pendingClinics as $clinic)
            <div class="application-item">
              <div class="d-flex align-items-center gap-3 flex-1 min-w-0">
                <div class="app-clinic-icon">
                  <i class="bi bi-building-hospital"></i>
                </div>
                <div class="min-w-0">
                  <p class="application-name">{{ $clinic->name }}</p>
                  <p class="application-meta">
                    <i class="bi bi-envelope" style="font-size:0.74rem"></i>
                    @php $clinicEmail = (string) ($clinic->email ?? ''); @endphp
                    {{ strlen($clinicEmail) > 7 ? Str::mask($clinicEmail, '*', 3, max(strlen($clinicEmail) - 7, 0)) : ($clinicEmail ?: 'No email') }}
                    <span>&middot;</span>
                    <i class="bi bi-geo-alt" style="font-size:0.74rem"></i>
                    {{ $clinic->address ?: 'No address listed' }}
                    <span>&middot;</span>
                    <i class="bi bi-clock" style="font-size:0.74rem"></i>
                    {{ $clinic->created_at->diffForHumans() }}
                  </p>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2 flex-shrink-0">
                @if($clinic->created_at->diffInDays(now()) >= 2)
                  <span class="app-badge badge-overdue">
                    <i class="bi bi-exclamation-circle" style="font-size:0.72rem"></i> Overdue
                  </span>
                @else
                  <span class="app-badge badge-pending">
                    <i class="bi bi-hourglass-split" style="font-size:0.72rem"></i> Pending
                  </span>
                @endif
                <div class="app-actions">
                  <form action="{{ route('admin.clinics.approve', $clinic->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn-approve">
                      <i class="bi bi-check-lg me-1"></i> Approve
                    </button>
                  </form>
                  <form action="{{ route('admin.clinics.decline', $clinic->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="decline_reason" value="Declined from the admin dashboard quick action.">
                    <button type="submit" class="btn-reject">
                      <i class="bi bi-x-lg me-1"></i> Reject
                    </button>
                  </form>
                </div>
              </div>
            </div>
            @empty
            <div class="text-center py-5">
              <div style="
                width: 64px; height: 64px; border-radius: 22px;
                background: rgba(16,185,129,0.10); color: #047857;
                font-size: 1.8rem; display: inline-flex;
                align-items: center; justify-content: center;
                margin-bottom: 14px;
              ">
                <i class="bi bi-check2-circle"></i>
              </div>
              <p style="color:#0f172a;font-weight:800;margin-bottom:4px">All clear!</p>
              <p class="text-muted" style="font-size:0.88rem">No pending applications at the moment.</p>
            </div>
            @endforelse

          </div>
        </div>
      </div>
    </div>
    {{-- end pending --}}

  </div>{{-- /container --}}
</div>{{-- /page --}}
@endsection
