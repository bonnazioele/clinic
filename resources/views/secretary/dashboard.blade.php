@extends('layouts.app')

@section('title', 'Secretary Dashboard')

@section('content')
<style>
  .secretary-dashboard-page {
    width: 96%;
    max-width: none;
    margin: 0 auto;
    padding: 0.5rem 0 1.5rem;
  }

  .secretary-welcome {
    border-radius: 24px;
    padding: 26px 30px;
    color: #ffffff;
    background:
      radial-gradient(circle at 90% 30%, rgba(255, 255, 255, 0.16), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37, 99, 235, 0.22);
    overflow: hidden;
    position: relative;
    margin-bottom: 1rem;
  }

  .secretary-welcome h1 {
    margin: 0 0 0.35rem;
    font-size: clamp(1.55rem, 2.4vw, 2.15rem);
    font-weight: 900;
    letter-spacing: -0.045em;
  }

  .secretary-welcome p {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 600;
    opacity: 0.95;
  }

  .secretary-stats-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1.1rem;
  }

  .secretary-stat-card {
    min-height: 150px;
    border-radius: 22px;
    padding: 1.15rem;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
  }

  .secretary-stat-card::after {
    content: "";
    position: absolute;
    right: -24px;
    bottom: -24px;
    width: 118px;
    height: 118px;
    border-radius: 38px;
    background: rgba(255, 255, 255, 0.14);
    transform: rotate(4deg);
  }

  .stat-blue {
    background: linear-gradient(135deg, #0866f2, #2993ff);
  }

  .stat-green {
    background: linear-gradient(135deg, #087b3d, #2bbf6a);
  }

  .stat-yellow {
    background: linear-gradient(135deg, #ffd85a, #ffc107);
    color: #162033;
  }

  .stat-cyan {
    background: linear-gradient(135deg, #78e7ff, #d9f8ff);
    color: #123047;
  }

  .stat-purple {
    background: linear-gradient(135deg, #7c3aed, #a78bfa);
  }

  .stat-gray {
    background: linear-gradient(135deg, #475569, #94a3b8);
  }

  .secretary-stat-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: flex-start;
    gap: 0.9rem;
  }

  .secretary-stat-icon {
    width: 54px;
    height: 54px;
    border-radius: 17px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.18);
    font-size: 1.5rem;
    flex: 0 0 54px;
  }

  .secretary-stat-value {
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.055em;
    margin-bottom: 0.45rem;
  }

  .secretary-stat-title {
    font-size: 0.9rem;
    font-weight: 900;
    margin-bottom: 0.25rem;
  }

  .secretary-stat-sub {
    margin: 0;
    font-size: 0.82rem;
    font-weight: 600;
    opacity: 0.9;
    line-height: 1.35;
  }

  .queue-workspace-card {
    border-radius: 24px;
    border: 1px solid rgba(226, 232, 240, 0.96);
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    padding: 1.35rem;
  }

  .workspace-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 1rem;
  }

  .workspace-title {
    margin: 0;
    color: #0f172a;
    font-size: 1.35rem;
    font-weight: 900;
    letter-spacing: -0.04em;
    display: flex;
    align-items: center;
    gap: 0.65rem;
  }

  .workspace-title i {
    color: #0d6efd;
  }

  .workspace-subtitle {
    margin-top: 0.25rem;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 600;
  }

  .workspace-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border-radius: 999px;
    padding: 0.45rem 0.75rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.78rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .service-tabs {
    gap: 0.5rem;
    border-bottom: 0;
    margin-bottom: 1rem;
  }

  .service-tab {
    border: 1px solid #bfdbfe !important;
    background: #eff6ff !important;
    color: #1d4ed8 !important;
    border-radius: 999px !important;
    padding: 0.55rem 0.9rem !important;
    font-size: 0.86rem;
    font-weight: 900;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
  }

  .service-tab.active {
    background: #0d6efd !important;
    border-color: #0d6efd !important;
    color: #ffffff !important;
    box-shadow: 0 10px 22px rgba(13, 110, 253, 0.2);
  }

  .service-tab .badge {
    background: #bfdbfe !important;
    color: #1e3a8a !important;
    border-radius: 999px;
    padding: 0.28rem 0.5rem;
    font-size: 0.7rem;
  }

  .service-tab.active .badge {
    background: #ffffff !important;
    color: #0d6efd !important;
  }

  .service-panel {
    border-radius: 22px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background:
      linear-gradient(135deg, rgba(248, 250, 252, 0.98), rgba(255, 255, 255, 0.96));
    padding: 1rem;
  }

  .doctor-lane-tabs {
    gap: 0.55rem;
    margin-bottom: 1rem;
  }

  .doctor-lane-tab {
    min-width: 230px;
    text-align: left;
    border: 1px solid #dbe3ef !important;
    background: #ffffff !important;
    color: #1f2937 !important;
    padding: 0.75rem 0.9rem !important;
    border-radius: 18px !important;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
  }

  .doctor-lane-tab.active {
    background: linear-gradient(135deg, #0d6efd, #178bff) !important;
    border-color: #0d6efd !important;
    color: #ffffff !important;
    box-shadow: 0 12px 24px rgba(13, 110, 253, 0.22);
  }

  .doctor-lane-name {
    font-weight: 900;
    font-size: 0.9rem;
    line-height: 1.2;
  }

  .doctor-lane-service {
    font-size: 0.78rem;
    font-weight: 600;
    color: #64748b;
  }

  .doctor-lane-tab.active .doctor-lane-service {
    color: #ffffff;
    opacity: 0.9;
  }

  .lane-board {
    border-radius: 22px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.045);
    overflow: hidden;
  }

  .lane-board-header {
    padding: 1rem 1.15rem;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
  }

  .lane-title {
    margin: 0;
    color: #0f172a;
    font-size: 1.05rem;
    font-weight: 900;
    letter-spacing: -0.025em;
  }

  .lane-subtitle {
    margin-top: 0.2rem;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 600;
  }

  .lane-depth-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.45rem 0.7rem;
    background: #eaf3ff;
    color: #0d6efd;
    border: 1px solid #bfdbfe;
    font-size: 0.76rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .lane-content-grid {
    padding: 1.1rem;
    display: grid;
    grid-template-columns: minmax(260px, 0.85fr) minmax(0, 1.15fr);
    gap: 1rem;
    align-items: start;
  }

  .queue-panel {
    border-radius: 20px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
    overflow: hidden;
  }

  .now-serving-panel {
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 35%),
      linear-gradient(135deg, #f8fbff, #eff6ff);
    border-color: rgba(191, 219, 254, 0.9);
  }

  .queue-panel-header {
    padding: 0.85rem 0.95rem 0.65rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.75);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
  }

  .queue-panel-title {
    margin: 0;
    color: #0f172a;
    font-size: 0.92rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    gap: 0.45rem;
  }

  .queue-panel-title i {
    color: #0d6efd;
  }

  .status-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    border-radius: 999px;
    padding: 0.36rem 0.6rem;
    font-size: 0.72rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .status-chip.active {
    background: #e8fff3;
    color: #0f9f6e;
    border: 1px solid #b7f0cf;
  }

  .status-chip.empty {
    background: #ffffff;
    color: #64748b;
    border: 1px solid #e2e8f0;
  }

  .now-serving-body {
    padding: 1rem;
  }

  .queue-number-large {
    color: #0d6efd;
    font-size: 2.35rem;
    line-height: 1;
    font-weight: 900;
    letter-spacing: -0.06em;
    margin-bottom: 0.5rem;
  }

  .patient-name {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 900;
    line-height: 1.25;
  }

  .patient-meta {
    margin-top: 0.35rem;
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 600;
  }

  .next-up-body {
    padding: 0.85rem;
  }

  .next-up-list {
    display: grid;
    gap: 0.55rem;
  }

  .next-up-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    padding: 0.72rem 0.8rem;
    border-radius: 15px;
    border: 1px solid #edf2f7;
    background: #ffffff;
  }

  .next-up-number {
    color: #0d6efd;
    font-size: 0.86rem;
    font-weight: 900;
  }

  .next-up-name {
    color: #0f172a;
    font-size: 0.86rem;
    font-weight: 800;
    line-height: 1.25;
  }

  .type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    border-radius: 999px;
    padding: 0.34rem 0.55rem;
    background: #ffffff;
    color: #475569;
    border: 1px solid #e2e8f0;
    font-size: 0.7rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .lane-actions {
    grid-column: 1 / -1;
    padding: 0.9rem;
    border-radius: 18px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
  }

  .lane-actions-note {
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 600;
  }

  .lane-action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
  }

  .lane-action-buttons .btn,
  .start-action .btn {
    border-radius: 12px;
    font-size: 0.84rem;
    font-weight: 900;
    padding: 0.55rem 0.9rem;
  }

  .start-action {
    grid-column: 1 / -1;
  }

  .empty-panel {
    border: 1px dashed rgba(13, 110, 253, 0.34);
    border-radius: 18px;
    background:
      linear-gradient(135deg, rgba(13, 110, 253, 0.06), rgba(255, 255, 255, 0.94));
    padding: 1.6rem 1rem;
    text-align: center;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 600;
  }

  .empty-panel-icon {
    width: 54px;
    height: 54px;
    margin: 0 auto 0.75rem;
    display: grid;
    place-items: center;
    border-radius: 17px;
    background: #ffffff;
    color: #0d6efd;
    font-size: 1.5rem;
    box-shadow: 0 10px 28px rgba(13, 110, 253, 0.11);
  }

  @media (max-width: 1200px) {
    .secretary-stats-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .lane-content-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .secretary-dashboard-page {
      width: 100%;
    }

    .secretary-welcome,
    .queue-workspace-card {
      border-radius: 22px;
    }

    .secretary-stats-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .workspace-header,
    .lane-board-header,
    .lane-actions {
      flex-direction: column;
      align-items: stretch;
    }

    .doctor-lane-tab {
      min-width: 100%;
    }

    .lane-action-buttons,
    .lane-action-buttons form,
    .lane-action-buttons .btn,
    .start-action form,
    .start-action .btn {
      width: 100%;
    }

    .lane-action-buttons {
      flex-direction: column;
    }
  }

  @media (max-width: 520px) {
    .secretary-stats-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="secretary-dashboard-page">
  <section class="secretary-welcome">
    <h1>Secretary Dashboard</h1>
    <p>
      Manage today’s appointments and clinic queues.
    </p>
  </section>

  <section class="secretary-stats-grid">
    <div class="secretary-stat-card stat-blue">
      <div class="secretary-stat-content">
        <div class="secretary-stat-icon">
          <i class="bi bi-calendar2-check"></i>
        </div>
        <div>
          <div class="secretary-stat-value">{{ number_format($totalTodayCount ?? 0) }}</div>
          <div class="secretary-stat-title">Total Today</div>
          <p class="secretary-stat-sub">All queue entries today</p>
        </div>
      </div>
    </div>

    <div class="secretary-stat-card stat-yellow">
      <div class="secretary-stat-content">
        <div class="secretary-stat-icon">
          <i class="bi bi-hourglass-split"></i>
        </div>
        <div>
          <div class="secretary-stat-value">{{ number_format($waitingCount ?? 0) }}</div>
          <div class="secretary-stat-title">Waiting</div>
          <p class="secretary-stat-sub">Patients still in queue</p>
        </div>
      </div>
    </div>

    <div class="secretary-stat-card stat-green">
      <div class="secretary-stat-content">
        <div class="secretary-stat-icon">
          <i class="bi bi-check2-circle"></i>
        </div>
        <div>
          <div class="secretary-stat-value">{{ number_format($servedCount ?? 0) }}</div>
          <div class="secretary-stat-title">Served</div>
          <p class="secretary-stat-sub">Completed patients</p>
        </div>
      </div>
    </div>

    <div class="secretary-stat-card stat-gray">
      <div class="secretary-stat-content">
        <div class="secretary-stat-icon">
          <i class="bi bi-person-x"></i>
        </div>
        <div>
          <div class="secretary-stat-value">{{ number_format($noShowCount ?? 0) }}</div>
          <div class="secretary-stat-title">No Show</div>
          <p class="secretary-stat-sub">Marked as no-show</p>
        </div>
      </div>
    </div>

    <div class="secretary-stat-card stat-purple">
      <div class="secretary-stat-content">
        <div class="secretary-stat-icon">
          <i class="bi bi-calendar2-week"></i>
        </div>
        <div>
          <div class="secretary-stat-value">{{ number_format($rescheduledCount ?? 0) }}</div>
          <div class="secretary-stat-title">Rescheduled</div>
          <p class="secretary-stat-sub">Moved schedules</p>
        </div>
      </div>
    </div>

    <div class="secretary-stat-card stat-cyan">
      <div class="secretary-stat-content">
        <div class="secretary-stat-icon">
          <i class="bi bi-person-plus"></i>
        </div>
        <div>
          <div class="secretary-stat-value">{{ number_format($walkInTodayCount ?? 0) }}</div>
          <div class="secretary-stat-title">Walk-in Today</div>
          <p class="secretary-stat-sub">Walk-in queue entries</p>
        </div>
      </div>
    </div>
  </section>

  <section class="queue-workspace-card">
    <div class="workspace-header">
      <div>
        <h2 class="workspace-title">
          <i class="bi bi-people-fill"></i>
          Queue Workstation
        </h2>
        <div class="workspace-subtitle">
          Select a service, choose a doctor lane, then manage the active queue.
        </div>
      </div>

      <span class="workspace-pill">
        <i class="bi bi-layers"></i>
        Service lanes
      </span>
    </div>

    @if(($serviceTabs ?? collect())->count())
      <ul class="nav nav-tabs flex-wrap service-tabs" id="serviceTabs" role="tablist">
        @foreach($serviceTabs as $serviceTab)
          <li class="nav-item" role="presentation">
            <button
              class="nav-link service-tab {{ ($activeServiceTabId ?? null) === $serviceTab['id'] ? 'active' : '' }}"
              id="{{ $serviceTab['id'] }}-tab"
              data-bs-toggle="tab"
              data-bs-target="#{{ $serviceTab['id'] }}"
              type="button"
              role="tab"
              aria-controls="{{ $serviceTab['id'] }}"
              aria-selected="{{ ($activeServiceTabId ?? null) === $serviceTab['id'] ? 'true' : 'false' }}">
              <i class="bi bi-clipboard2-pulse"></i>
              {{ $serviceTab['service_name'] }}
              <span class="badge">{{ $serviceTab['doctor_lanes']->count() }}</span>
            </button>
          </li>
        @endforeach
      </ul>

      <div class="tab-content" id="serviceTabsContent">
        @foreach($serviceTabs as $serviceTab)
          <div
            class="tab-pane fade {{ ($activeServiceTabId ?? null) === $serviceTab['id'] ? 'show active' : '' }}"
            id="{{ $serviceTab['id'] }}"
            role="tabpanel"
            aria-labelledby="{{ $serviceTab['id'] }}-tab">

            <div class="service-panel">
              @if($serviceTab['doctor_lanes']->isEmpty())
                <div class="empty-panel">
                  <div class="empty-panel-icon">
                    <i class="bi bi-person-badge"></i>
                  </div>
                  No doctors are currently assigned to this service.
                </div>
              @else
                <ul class="nav nav-pills flex-wrap doctor-lane-tabs" id="queueLaneTabs-{{ $serviceTab['service_id'] }}" role="tablist">
                  @foreach($serviceTab['doctor_lanes'] as $lane)
                    @php
                      $laneTabId = $lane['id'].'-svc-'.$serviceTab['service_id'];
                    @endphp

                    <li class="nav-item" role="presentation">
                      <button
                        class="nav-link doctor-lane-tab {{ ($serviceTab['active_lane_id'] ?? null) === $lane['id'] ? 'active' : '' }}"
                        id="{{ $laneTabId }}-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#{{ $laneTabId }}"
                        type="button"
                        role="tab"
                        aria-controls="{{ $laneTabId }}"
                        aria-selected="{{ ($serviceTab['active_lane_id'] ?? null) === $lane['id'] ? 'true' : 'false' }}">
                        <div class="doctor-lane-name">
                          <i class="bi bi-person-badge me-1"></i>
                          {{ $lane['doctor_name'] }}
                        </div>
                        <div class="doctor-lane-service">
                          {{ $lane['service_name'] ?: 'General consultation' }}
                        </div>
                      </button>
                    </li>
                  @endforeach
                </ul>

                <div class="tab-content" id="queueLaneTabsContent-{{ $serviceTab['service_id'] }}">
                  @foreach($serviceTab['doctor_lanes'] as $lane)
                    @php
                      $nowServing = $lane['now_serving'];
                      $nextUp = $lane['next_up'];
                      $callNextEntry = $lane['call_next_entry'];
                      $noShowEntry = $lane['no_show_entry'];
                      $lanePaneId = $lane['id'].'-svc-'.$serviceTab['service_id'];
                    @endphp

                    <div
                      class="tab-pane fade {{ ($serviceTab['active_lane_id'] ?? null) === $lane['id'] ? 'show active' : '' }}"
                      id="{{ $lanePaneId }}"
                      role="tabpanel"
                      aria-labelledby="{{ $lanePaneId }}-tab">

                      <div class="lane-board">
                        <div class="lane-board-header">
                          <div>
                            <h3 class="lane-title">{{ $lane['doctor_name'] }}</h3>
                            <div class="lane-subtitle">
                              {{ $lane['service_name'] ?: 'General consultation' }}
                            </div>
                          </div>

                          <span class="lane-depth-pill">
                            <i class="bi bi-people"></i>
                            {{ number_format($lane['queue_depth']) }} in active queue
                          </span>
                        </div>

                        <div class="lane-content-grid">
                          <section class="queue-panel now-serving-panel">
                            <div class="queue-panel-header">
                              <h4 class="queue-panel-title">
                                <i class="bi bi-megaphone"></i>
                                Now Serving
                              </h4>

                              @if($nowServing)
                                <span class="status-chip active">
                                  <i class="bi bi-broadcast"></i>
                                  Active
                                </span>
                              @else
                                <span class="status-chip empty">
                                  <i class="bi bi-pause-circle"></i>
                                  Empty
                                </span>
                              @endif
                            </div>

                            <div class="now-serving-body">
                              @if($nowServing)
                                <div class="queue-number-large">#{{ $nowServing->queue_number }}</div>
                                <div class="patient-name">{{ $nowServing->display_name }}</div>
                                <div class="patient-meta">
                                  <i class="bi bi-clock me-1"></i>
                                  Called at {{ optional($nowServing->updated_at)->format('g:i A') ?? '-' }}
                                  · {{ $nowServing->appointment_id ? 'Appointment' : 'Walk-in' }}
                                </div>
                              @else
                                <div class="patient-name text-muted">
                                  No patient is currently being served.
                                </div>
                                <div class="patient-meta">
                                  Start the next waiting patient when ready.
                                </div>
                              @endif
                            </div>
                          </section>

                          <section class="queue-panel">
                            <div class="queue-panel-header">
                              <h4 class="queue-panel-title">
                                <i class="bi bi-list-ol"></i>
                                Next Up
                              </h4>

                              <span class="status-chip empty">
                                {{ $nextUp->count() }} listed
                              </span>
                            </div>

                            <div class="next-up-body">
                              @if($nextUp->count())
                                <div class="next-up-list">
                                  @foreach($nextUp as $entry)
                                    <div class="next-up-item">
                                      <div>
                                        <div class="next-up-number">#{{ $entry->queue_number }}</div>
                                        <div class="next-up-name">{{ $entry->display_name }}</div>
                                      </div>

                                      <span class="type-badge">
                                        <i class="bi {{ $entry->appointment_id ? 'bi-calendar-check' : 'bi-person-plus' }}"></i>
                                        {{ $entry->appointment_id ? 'Appt' : 'Walk-in' }}
                                      </span>
                                    </div>
                                  @endforeach
                                </div>
                              @else
                                <div class="text-muted small fw-semibold">
                                  No queued patients waiting in this lane.
                                </div>
                              @endif
                            </div>
                          </section>

                          @if(!$nowServing && $callNextEntry)
                            <div class="start-action">
                              <form method="POST" action="{{ route('secretary.queue.call', [$lane['clinic_id'], $callNextEntry->id]) }}">
                                @csrf
                                <button class="btn btn-primary">
                                  <i class="bi bi-play-fill me-1"></i>
                                  Start Next Patient
                                </button>
                              </form>
                            </div>
                          @endif

                          <section class="lane-actions">
                            <div class="lane-actions-note">
                              @if($nowServing)
                                Finish the current patient, then call the next one or mark as no-show.
                              @else
                                No active patient is being served in this lane.
                              @endif
                            </div>

                            <div class="lane-action-buttons">
                              @if($nowServing)
                                <form method="POST" action="{{ route('secretary.queue.done_next', [$lane['clinic_id'], $nowServing->id]) }}">
                                  @csrf
                                  <input type="hidden" name="lane" value="{{ $lane['id'] }}">
                                  <input type="hidden" name="service_tab" value="{{ $serviceTab['id'] }}">
                                  <button class="btn btn-primary">
                                    <i class="bi bi-check2-circle me-1"></i>
                                    Done and Next
                                  </button>
                                </form>

                                @if($noShowEntry)
                                  <form
                                    method="POST"
                                    action="{{ route('secretary.queue.no_show', [$lane['clinic_id'], $noShowEntry->id]) }}"
                                    data-confirm="Mark this patient as no-show?"
                                    data-confirm-title="Mark As No-Show"
                                    data-confirm-btn="Mark No-Show">
                                    @csrf
                                    <button class="btn btn-outline-secondary">
                                      <i class="bi bi-person-x me-1"></i>
                                      Mark No-Show
                                    </button>
                                  </form>
                                @endif
                              @else
                                <button class="btn btn-outline-secondary" disabled>
                                  <i class="bi bi-person-x me-1"></i>
                                  Mark No-Show
                                </button>
                              @endif
                            </div>
                          </section>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="empty-panel">
        <div class="empty-panel-icon">
          <i class="bi bi-layers"></i>
        </div>
        No service tabs with assigned doctors are available yet for your assigned clinics.
      </div>
    @endif
  </section>
</div>
@endsection