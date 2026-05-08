@extends('doctor.layouts.app')

@section('title', 'Doctor Dashboard')

@section('doctor-content')
@php
    use Illuminate\Support\Collection;

    $doctorName = auth()->user()->name ?? 'Doctor';

    /*
    |--------------------------------------------------------------------------
    | Safe Defaults
    |--------------------------------------------------------------------------
    | This prevents errors like:
    | Undefined variable $appointments
    | Undefined variable $queue
    | Undefined variable $clinics
    |--------------------------------------------------------------------------
    */

    $appointments = collect($appointments ?? []);
    $queue = collect($queue ?? []);
    $clinics = collect($clinics ?? []);
    $activeClinic = $activeClinic ?? null;
    $servicesOfferedCount = $servicesOfferedCount ?? 0;

    $formatTime = function ($time) {
        if (!$time) {
            return '—';
        }

        try {
            return \Carbon\Carbon::parse($time)->format('g:i A');
        } catch (\Throwable $e) {
            return $time;
        }
    };

    $todayAppointmentsCount = $appointments->count();
    $activeQueueCount = $queue->count();

    $nowServingCount = $queue->whereIn('status', ['in_progress', 'now_serving'])->count();
    $waitingCount = $queue->whereIn('status', ['waiting', 'called', 'pending'])->count();

    if ($waitingCount === 0 && $nowServingCount === 0 && $activeQueueCount > 0) {
        $waitingCount = $activeQueueCount;
    }

    $todayAppointments = $appointments->sortBy('appointment_time')->take(6);
    $todayQueue = $queue->take(6);

    $nextAppointment = $appointments->sortBy('appointment_time')->first();
@endphp

<style>
  .doctor-dashboard-page {
    width: 100%;
  }

  .doctor-dashboard-shell {
    max-width: 1380px;
    margin: 0 auto;
  }

  .doctor-dashboard-page .glass-panel {
    border: 1px solid rgba(15, 23, 42, 0.08);
    background: rgba(255, 255, 255, 0.78);
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
  }

  .doctor-hero {
    position: relative;
    overflow: hidden;
    border-radius: 34px;
    padding: 30px;
    background:
      radial-gradient(circle at top left, rgba(37, 99, 235, 0.13), transparent 30%),
      radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.12), transparent 28%),
      linear-gradient(135deg, rgba(255,255,255,0.95), rgba(240,247,255,0.88));
  }

  .doctor-hero::after {
    content: "";
    position: absolute;
    right: -70px;
    bottom: -70px;
    width: 240px;
    height: 240px;
    border-radius: 50%;
    background: rgba(96, 165, 250, 0.14);
    pointer-events: none;
  }

  .doctor-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(37, 99, 235, 0.10);
    color: #2563eb;
    font-size: 0.82rem;
    font-weight: 800;
  }

  .doctor-hero-icon {
    width: 72px;
    height: 72px;
    border-radius: 22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #fff;
    background: linear-gradient(135deg, #2563eb, #06b6d4);
    box-shadow: 0 16px 30px rgba(37, 99, 235, 0.28);
    flex-shrink: 0;
  }

  .doctor-hero-title {
    font-size: clamp(2rem, 3vw, 3rem);
    font-weight: 900;
    color: #0f172a;
    line-height: 1.05;
    letter-spacing: -0.04em;
    margin: 0;
  }

  .doctor-hero-text {
    color: #64748b;
    font-size: 1rem;
    margin-top: 8px;
    margin-bottom: 0;
  }

  .doctor-highlight-card {
    border-radius: 24px;
    padding: 18px 20px;
    background: #ffffff;
    border: 1px solid rgba(15, 23, 42, 0.08);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
  }

  .doctor-highlight-label {
    font-size: 0.8rem;
    font-weight: 800;
    color: #64748b;
    margin-bottom: 4px;
  }

  .doctor-highlight-value {
    font-size: 1.1rem;
    font-weight: 900;
    color: #111827;
    margin-bottom: 0;
  }

  .doctor-highlight-sub {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 4px;
  }

  .doctor-highlight-icon {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    flex-shrink: 0;
  }

  .doctor-quick-btn {
    border-radius: 16px;
    padding: 12px 18px;
    font-weight: 800;
  }

  .overview-card {
    border-radius: 28px;
    padding: 22px;
    height: 100%;
  }

  .overview-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 14px;
  }

  .overview-label {
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 800;
    margin-bottom: 6px;
  }

  .overview-value {
    font-size: 2.35rem;
    line-height: 1;
    font-weight: 900;
    color: #111827;
    margin: 0;
  }

  .overview-desc {
    color: #6b7280;
    font-size: 0.92rem;
    margin-bottom: 0;
  }

  .overview-icon {
    width: 58px;
    height: 58px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    flex-shrink: 0;
  }

  .overview-blue .overview-icon {
    background: rgba(37, 99, 235, 0.12);
    color: #2563eb;
  }

  .overview-yellow .overview-icon {
    background: rgba(245, 158, 11, 0.18);
    color: #a16207;
  }

  .overview-green .overview-icon {
    background: rgba(16, 185, 129, 0.16);
    color: #047857;
  }

  .overview-cyan .overview-icon {
    background: rgba(6, 182, 212, 0.16);
    color: #0891b2;
  }

  .section-card {
    border-radius: 30px;
    overflow: hidden;
    height: 100%;
  }

  .section-card-header {
    padding: 22px 22px 18px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.07);
    background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(248,250,252,0.95));
  }

  .section-card-body {
    padding: 20px;
  }

  .section-title-wrap {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
  }

  .section-title {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #111827;
    font-size: 1.35rem;
    font-weight: 900;
    letter-spacing: -0.02em;
  }

  .section-title i {
    color: #2563eb;
  }

  .section-subtitle {
    margin: 6px 0 0;
    color: #6b7280;
    font-size: 0.95rem;
  }

  .section-count {
    min-width: 38px;
    height: 30px;
    padding: 0 12px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    font-weight: 900;
    color: #1d4ed8;
    background: rgba(37, 99, 235, 0.12);
  }

  .section-count.yellow {
    color: #92400e;
    background: rgba(245, 158, 11, 0.22);
  }

  .section-count.green {
    color: #166534;
    background: rgba(16, 185, 129, 0.18);
  }

  .doctor-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .doctor-item {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 22px;
    background: rgba(248, 250, 252, 0.8);
    padding: 16px;
    transition: 0.2s ease;
  }

  .doctor-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
    background: #ffffff;
  }

  .doctor-item-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }

  .doctor-item-left {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
  }

  .doctor-item-badge {
    min-width: 58px;
    height: 40px;
    border-radius: 14px;
    padding: 0 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    font-weight: 900;
    flex-shrink: 0;
  }

  .doctor-item-badge.blue {
    color: #2563eb;
    background: rgba(37, 99, 235, 0.12);
  }

  .doctor-item-badge.yellow {
    color: #92400e;
    background: rgba(245, 158, 11, 0.20);
  }

  .doctor-item-badge.green {
    color: #166534;
    background: rgba(16, 185, 129, 0.16);
  }

  .doctor-item-title {
    font-size: 1rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .doctor-item-meta {
    color: #6b7280;
    font-size: 0.88rem;
    margin: 0;
  }

  .doctor-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 12px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 900;
    flex-shrink: 0;
  }

  .doctor-status.waiting {
    color: #92400e;
    background: rgba(245, 158, 11, 0.16);
  }

  .doctor-status.serving {
    color: #166534;
    background: rgba(16, 185, 129, 0.18);
  }

  .doctor-status.default {
    color: #475569;
    background: rgba(148, 163, 184, 0.18);
  }

  .doctor-empty {
    border: 1px dashed rgba(148, 163, 184, 0.5);
    border-radius: 24px;
    padding: 34px 20px;
    text-align: center;
    background: rgba(248, 250, 252, 0.75);
  }

  .doctor-empty-icon {
    width: 70px;
    height: 70px;
    border-radius: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    background: rgba(37, 99, 235, 0.10);
    color: #2563eb;
    margin-bottom: 14px;
  }

  .doctor-empty h6 {
    font-size: 1.1rem;
    font-weight: 900;
    color: #111827;
    margin-bottom: 8px;
  }

  .doctor-empty p {
    color: #6b7280;
    font-size: 0.92rem;
    margin: 0;
  }

  .doctor-side-panel {
    border-radius: 28px;
    padding: 22px;
    height: 100%;
  }

  .mini-info {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 20px;
    background: rgba(248, 250, 252, 0.85);
    padding: 16px;
  }

  .mini-info-title {
    font-size: 0.78rem;
    font-weight: 900;
    letter-spacing: 0.03em;
    color: #64748b;
    text-transform: uppercase;
    margin-bottom: 6px;
  }

  .mini-info-value {
    color: #111827;
    font-size: 1rem;
    font-weight: 900;
    margin-bottom: 0;
  }

  .mini-info-text {
    color: #6b7280;
    font-size: 0.88rem;
    margin-top: 4px;
    margin-bottom: 0;
  }

  .clinic-chip {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 18px;
    background: rgba(248, 250, 252, 0.92);
    border: 1px solid rgba(15, 23, 42, 0.07);
  }

  .clinic-chip-icon {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #047857;
    background: rgba(16, 185, 129, 0.14);
    flex-shrink: 0;
  }

  .sticky-actions {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 15;
  }

  .fab-btn {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    border: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2563eb, #0ea5e9);
    color: #ffffff;
    font-size: 1.25rem;
    box-shadow: 0 18px 30px rgba(37, 99, 235, 0.3);
    text-decoration: none;
  }

  @media (max-width: 991.98px) {
    .doctor-hero {
      padding: 24px;
    }

    .section-card,
    .overview-card,
    .doctor-side-panel {
      border-radius: 24px;
    }

    .doctor-item-main {
      flex-direction: column;
      align-items: flex-start;
    }
  }

  @media (max-width: 767.98px) {
    .doctor-dashboard-shell {
      max-width: 100%;
    }

    .doctor-hero {
      padding: 20px;
      border-radius: 24px;
    }

    .overview-card {
      padding: 18px;
    }

    .section-card-header,
    .section-card-body,
    .doctor-side-panel {
      padding: 18px;
    }

    .doctor-hero-icon {
      width: 60px;
      height: 60px;
      font-size: 1.7rem;
      border-radius: 18px;
    }

    .sticky-actions {
      right: 18px;
      bottom: 18px;
    }
  }
</style>

<div class="doctor-dashboard-page" id="top">
  <div class="doctor-dashboard-shell">

    <div class="doctor-hero glass-panel mb-4">
      <div class="row g-4 align-items-center">
        <div class="col-xl-8">
          <div class="d-flex align-items-start gap-3 gap-md-4">
            <div class="doctor-hero-icon">
              <i class="bi bi-heart-pulse-fill"></i>
            </div>

            <div class="flex-grow-1">
              <div class="doctor-hero-badge mb-3">
                <i class="bi bi-clipboard2-pulse"></i>
                Doctor Dashboard
              </div>

              <h1 class="doctor-hero-title">Good day, {{ $doctorName }}!</h1>

              <p class="doctor-hero-text">
                Here’s your quick view of <strong>today’s queue</strong>, <strong>today’s appointments</strong>, and your assigned clinic so you can work faster without switching pages.
              </p>

              <div class="d-flex flex-wrap gap-2 mt-4">
                <a href="{{ Route::has('doctor.queue.index') ? route('doctor.queue.index') : '#' }}" class="btn btn-primary doctor-quick-btn">
                  <i class="bi bi-list-ol me-2"></i>Open Queue
                </a>

                <a href="{{ Route::has('doctor.schedules.index') ? route('doctor.schedules.index') : '#' }}" class="btn btn-outline-primary doctor-quick-btn">
                  <i class="bi bi-calendar2-week me-2"></i>Manage Schedule
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-4">
          <div class="doctor-highlight-card">
            <div class="d-flex align-items-center justify-content-between gap-3">
              <div class="min-w-0">
                <div class="doctor-highlight-label">Current Clinic</div>
                <p class="doctor-highlight-value text-truncate">{{ $activeClinic->name ?? 'No clinic assigned' }}</p>
                <div class="doctor-highlight-sub">
                  @if($nextAppointment)
                    Next appointment:
                    <strong>{{ $formatTime($nextAppointment->appointment_time ?? null) }}</strong>
                  @else
                    No more appointments scheduled today
                  @endif
                </div>
              </div>

              <span class="doctor-highlight-icon" style="background: rgba(16,185,129,0.14); color:#047857;">
                <i class="bi bi-hospital"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 g-lg-4 mb-4">
      <div class="col-sm-6 col-xl-3">
        <div class="overview-card glass-panel overview-blue">
          <div class="overview-top">
            <div>
              <div class="overview-label">Today’s Appointments</div>
              <h2 class="overview-value">{{ $todayAppointmentsCount }}</h2>
            </div>
            <span class="overview-icon">
              <i class="bi bi-calendar2-check"></i>
            </span>
          </div>
          <p class="overview-desc">Scheduled consultations for today.</p>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="overview-card glass-panel overview-yellow">
          <div class="overview-top">
            <div>
              <div class="overview-label">Today’s Queue</div>
              <h2 class="overview-value">{{ $activeQueueCount }}</h2>
            </div>
            <span class="overview-icon">
              <i class="bi bi-people"></i>
            </span>
          </div>
          <p class="overview-desc">Patients currently waiting or being served.</p>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="overview-card glass-panel overview-green">
          <div class="overview-top">
            <div>
              <div class="overview-label">Now Serving</div>
              <h2 class="overview-value">{{ $nowServingCount }}</h2>
            </div>
            <span class="overview-icon">
              <i class="bi bi-megaphone"></i>
            </span>
          </div>
          <p class="overview-desc">Patients currently in consultation.</p>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="overview-card glass-panel overview-cyan">
          <div class="overview-top">
            <div>
              <div class="overview-label">Services Offered</div>
              <h2 class="overview-value">{{ $servicesOfferedCount }}</h2>
            </div>
            <span class="overview-icon">
              <i class="bi bi-clipboard2-pulse"></i>
            </span>
          </div>
          <p class="overview-desc">Available services in your assignment.</p>
        </div>
      </div>
    </div>

    <div class="row g-4 align-items-stretch">
      <div class="col-xl-5">
        <div class="section-card glass-panel h-100">
          <div class="section-card-header">
            <div class="section-title-wrap">
              <div>
                <h3 class="section-title">
                  <i class="bi bi-list-ol"></i>
                  Today’s Queue
                </h3>
                <p class="section-subtitle">See who is waiting and who is currently being served.</p>
              </div>

              <span class="section-count yellow">{{ $activeQueueCount }}</span>
            </div>
          </div>

          <div class="section-card-body">
            @if($todayQueue->count())
              <div class="doctor-list">
                @foreach($todayQueue as $q)
                  @php
                    $status = $q->status ?? 'waiting';
                    $statusClass = 'default';
                    $statusLabel = ucfirst(str_replace('_', ' ', $status));

                    if (in_array($status, ['in_progress', 'now_serving'], true)) {
                        $statusClass = 'serving';
                        $statusLabel = 'In Progress';
                    } elseif (in_array($status, ['waiting', 'called', 'pending'])) {
                        $statusClass = 'waiting';
                        $statusLabel = $status === 'called' ? 'Called' : 'Waiting';
                    }

                    $queueNumber = $q->queue_number ?? $q->number ?? '—';

                    $queuePatientName =
                        $q->appointment?->user?->name
                        ?? $q->patient?->name
                        ?? $q->user?->name
                        ?? 'Patient';
                  @endphp

                  <div class="doctor-item">
                    <div class="doctor-item-main">
                      <div class="doctor-item-left">
                        <span class="doctor-item-badge yellow">#{{ $queueNumber }}</span>

                        <div class="min-w-0">
                          <h6 class="doctor-item-title">{{ $queuePatientName }}</h6>
                          <p class="doctor-item-meta">
                            <i class="bi bi-clock me-1"></i>
                            {{ $q->created_at ? $q->created_at->diffForHumans() : 'Queue entry' }}
                          </p>
                        </div>
                      </div>

                      <span class="doctor-status {{ $statusClass }}">
                        <i class="bi {{ in_array($status, ['in_progress', 'now_serving'], true) ? 'bi-play-circle-fill' : 'bi-hourglass-split' }}"></i>
                        {{ $statusLabel }}
                      </span>
                    </div>
                  </div>
                @endforeach
              </div>

              @if($activeQueueCount > 6)
                <div class="text-center mt-3">
                  <a href="{{ Route::has('doctor.queue.index') ? route('doctor.queue.index') : '#' }}" class="btn btn-outline-warning doctor-quick-btn">
                    View Full Queue
                  </a>
                </div>
              @endif
            @else
              <div class="doctor-empty">
                <div class="doctor-empty-icon">
                  <i class="bi bi-people"></i>
                </div>
                <h6>No patients in queue</h6>
                <p>The queue is clear for today.</p>
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="col-xl-4">
        <div class="section-card glass-panel h-100">
          <div class="section-card-header">
            <div class="section-title-wrap">
              <div>
                <h3 class="section-title">
                  <i class="bi bi-calendar-day"></i>
                  Today’s Appointments
                </h3>
                <p class="section-subtitle">Your scheduled consultations for today.</p>
              </div>

              <span class="section-count">{{ $todayAppointmentsCount }}</span>
            </div>
          </div>

          <div class="section-card-body">
            @if($todayAppointments->count())
              <div class="doctor-list">
                @foreach($todayAppointments as $appt)
                  @php
                    $appointmentPatientName =
                        $appt->user?->name
                        ?? $appt->patient?->name
                        ?? 'Patient';

                    $appointmentServiceName =
                        $appt->service?->name
                        ?? 'Consultation';

                    $appointmentDocument = $appt->medical_document ?? null;
                  @endphp

                  <div class="doctor-item">
                    <div class="doctor-item-main">
                      <div class="doctor-item-left">
                        <span class="doctor-item-badge blue">
                          {{ $formatTime($appt->appointment_time ?? null) }}
                        </span>

                        <div class="min-w-0">
                          <h6 class="doctor-item-title">{{ $appointmentPatientName }}</h6>
                          <p class="doctor-item-meta">
                            <i class="bi bi-gear me-1"></i>
                            {{ $appointmentServiceName }}
                          </p>
                        </div>
                      </div>

                      @if($appointmentDocument)
                        <a href="{{ asset('storage/' . $appointmentDocument) }}"
                           target="_blank"
                           class="btn btn-sm btn-outline-primary doctor-quick-btn"
                           style="padding:8px 12px;">
                          <i class="bi bi-file-earmark-medical"></i>
                        </a>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="doctor-empty">
                <div class="doctor-empty-icon">
                  <i class="bi bi-calendar-x"></i>
                </div>
                <h6>No appointments today</h6>
                <p>You currently have no scheduled consultations for today.</p>
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="col-xl-3">
        <div class="doctor-side-panel glass-panel h-100">
          <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
            <div>
              <h3 class="section-title mb-0" style="font-size:1.2rem;">
                <i class="bi bi-building"></i>
                Clinic Overview
              </h3>
              <p class="section-subtitle mb-0" style="margin-top:6px;">Quick reference for your assignment.</p>
            </div>

            <span class="section-count green">{{ $clinics->count() }}</span>
          </div>

          <div class="d-grid gap-3">
            <div class="mini-info">
              <div class="mini-info-title">Active Clinic</div>
              <p class="mini-info-value">{{ $activeClinic->name ?? 'No clinic assigned' }}</p>
              <p class="mini-info-text">This is the clinic currently linked to your dashboard.</p>
            </div>

            <div class="mini-info">
              <div class="mini-info-title">Queue Summary</div>
              <p class="mini-info-value">{{ $waitingCount }} waiting · {{ $nowServingCount }} serving</p>
              <p class="mini-info-text">Helpful for checking today’s actual workload quickly.</p>
            </div>

            <div>
              <div class="mini-info-title mb-2">Assigned Clinic List</div>

              <div class="d-grid gap-2">
                @forelse($clinics as $c)
                  <div class="clinic-chip">
                    <span class="clinic-chip-icon">
                      <i class="bi bi-hospital"></i>
                    </span>

                    <div class="min-w-0">
                      <div class="fw-bold text-dark text-truncate">{{ $c->name ?? 'Clinic' }}</div>
                      <div class="text-muted small">Assigned clinic</div>
                    </div>
                  </div>
                @empty
                  <div class="doctor-empty" style="padding:24px 16px;">
                    <div class="doctor-empty-icon" style="width:58px;height:58px;font-size:1.6rem;margin-bottom:10px;">
                      <i class="bi bi-building"></i>
                    </div>
                    <h6 style="font-size:1rem;">No clinics assigned</h6>
                    <p style="font-size:0.86rem;">No clinic is currently attached to this doctor account.</p>
                  </div>
                @endforelse
              </div>
            </div>

            <div class="pt-2">
              <a href="{{ Route::has('doctor.queue.index') ? route('doctor.queue.index') : '#' }}" class="btn btn-primary w-100 doctor-quick-btn mb-2">
                <i class="bi bi-list-ol me-2"></i>Go to Queue
              </a>

              <a href="{{ Route::has('doctor.schedules.index') ? route('doctor.schedules.index') : '#' }}" class="btn btn-outline-primary w-100 doctor-quick-btn">
                <i class="bi bi-calendar2-week me-2"></i>Go to Schedules
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="sticky-actions">
      <a href="#top" class="fab-btn" aria-label="Back to top">
        <i class="bi bi-arrow-up"></i>
      </a>
    </div>

  </div>
</div>
@endsection
