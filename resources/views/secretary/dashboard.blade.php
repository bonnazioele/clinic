@extends('layouts.app')

@section('title', 'Secretary Dashboard')

@section('content')
@php
  $doctorCount = (int) ($totalActiveDoctors ?? 0);
  $hasDoctors = $doctorCount > 0;
  $canShowAddDoctorPrompt = ! $hasDoctors && Route::has('secretary.doctors.create');
@endphp

<style>
  .secretary-dashboard-page {
    width: 96%;
    max-width: none;
    margin: 0 auto;
    padding: 0.5rem 0 1.5rem;
  }

  .dashboard-summary-shell {
    border-radius: 24px;
    border: 1px solid rgba(226, 232, 240, 0.96);
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    overflow: hidden;
    margin-bottom: 1.25rem;
  }

  .secretary-welcome {
    padding: 1.35rem 1.45rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 90% 30%, rgba(255, 255, 255, 0.16), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
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

  .first-doctor-card {
    border-radius: 24px;
    border: 1px solid rgba(191, 219, 254, 0.95);
    background:
      radial-gradient(circle at 92% 18%, rgba(13, 110, 253, 0.11), transparent 20%),
      linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
    box-shadow: 0 18px 45px rgba(37, 99, 235, 0.13);
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .first-doctor-left {
    display: flex;
    align-items: flex-start;
    gap: 0.9rem;
  }

  .first-doctor-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #ffffff;
    display: grid;
    place-items: center;
    font-size: 1.6rem;
    box-shadow: 0 12px 24px rgba(13, 110, 253, 0.2);
    flex: 0 0 58px;
  }

  .first-doctor-title {
    margin: 0;
    color: #0f172a;
    font-size: 1.2rem;
    font-weight: 950;
    letter-spacing: -0.03em;
  }

  .first-doctor-text {
    margin: 0.25rem 0 0;
    color: #475569;
    font-size: 0.92rem;
    font-weight: 650;
    line-height: 1.45;
  }

  .first-doctor-steps {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-top: 0.75rem;
  }

  .first-doctor-step {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.38rem 0.65rem;
    background: #ffffff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.76rem;
    font-weight: 900;
  }

  .first-doctor-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
  }

  .first-doctor-actions .btn {
    border-radius: 14px;
    font-weight: 900;
    padding: 0.7rem 1rem;
  }

  .secretary-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    padding: 1.2rem;
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
    border: 1px solid rgba(191, 219, 254, 0.95);
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 24px 60px rgba(37, 99, 235, 0.13);
    padding: 1.6rem;
    position: relative;
    overflow: hidden;
  }

  .queue-workspace-card::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 5px;
    background: linear-gradient(90deg, #0d6efd, #2bbf6a, #ffc107);
  }

  .queue-workspace-card > * {
    position: relative;
    z-index: 1;
  }

  .workspace-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 1.25rem;
  }

  .workspace-title {
    margin: 0;
    color: #0f172a;
    font-size: clamp(1.5rem, 2.2vw, 2rem);
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
    font-size: 0.98rem;
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

  .cliniq-service-card {
    min-height: 172px;
    border-radius: 20px;
    border: 1px solid rgba(226, 232, 240, 0.96);
    background: rgba(255, 255, 255, 0.95);
    padding: 1.25rem 1.3rem;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .cliniq-service-card-link {
    display: block;
    text-decoration: none;
    color: inherit;
  }

  .cliniq-service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
  }

  .cliniq-service-card__header {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 1rem;
  }

  .cliniq-service-card__title {
    margin: 0;
    font-size: 1.12rem;
    font-weight: 900;
    color: #0f172a;
  }

  .cliniq-service-card__stats {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
  }

  .cliniq-service-card__stat {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
  }

  .cliniq-service-card__stat--right {
    align-items: flex-end;
    text-align: right;
  }

  .cliniq-service-card__stat-label {
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    color: #64748b;
  }

  .cliniq-service-card__stat-value {
    font-size: 1.75rem;
    font-weight: 900;
    color: #0f172a;
  }

  .cliniq-service-card__stat-value--primary {
    color: #0d6efd;
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
    .queue-workspace-card,
    .first-doctor-card {
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

    .first-doctor-card {
      align-items: stretch;
    }

    .first-doctor-actions,
    .first-doctor-actions .btn {
      width: 100%;
    }
  }

  @media (max-width: 520px) {
    .secretary-stats-grid {
      grid-template-columns: 1fr;
    }

    .first-doctor-left {
      flex-direction: column;
    }
  }
</style>

<div class="secretary-dashboard-page">
  @include('partials.alerts')

  @if($canShowAddDoctorPrompt)
    <section class="first-doctor-card">
      <div class="first-doctor-left">
        <div class="first-doctor-icon">
          <i class="bi bi-person-plus-fill"></i>
        </div>

        <div>
          <h2 class="first-doctor-title">Add your first doctor</h2>
          <p class="first-doctor-text">
            Your clinic is ready. Add at least one doctor so services, schedules, appointments, and queues can start working properly.
          </p>

          <div class="first-doctor-steps">
            <span class="first-doctor-step">
              <i class="bi bi-check-circle"></i>
              Password changed
            </span>
            <span class="first-doctor-step">
              <i class="bi bi-check-circle"></i>
              Operational hours configured
            </span>
            <span class="first-doctor-step">
              <i class="bi bi-person-badge"></i>
              Next: Add doctor
            </span>
          </div>
        </div>
      </div>

      <div class="first-doctor-actions">
        <a href="{{ route('secretary.doctors.create') }}" class="btn btn-primary">
          <i class="bi bi-person-plus me-1"></i>
          Add Doctor
        </a>
      </div>
    </section>
  @endif

  <section class="dashboard-summary-shell">
    <div class="secretary-welcome">
      <h1>Secretary Dashboard</h1>
      <p>
        Manage today's appointments and clinic queues.
      </p>
    </div>

    <div class="secretary-stats-grid">
      <div class="secretary-stat-card stat-yellow">
        <div class="secretary-stat-content">
          <div class="secretary-stat-icon">
            <i class="bi bi-hourglass-split"></i>
          </div>
          <div>
            <div class="secretary-stat-value">{{ number_format($totalWaiting ?? 0) }}</div>
            <div class="secretary-stat-title">Total Waiting</div>
            <p class="secretary-stat-sub">Patients waiting in queue</p>
          </div>
        </div>
      </div>

      <div class="secretary-stat-card stat-blue">
        <div class="secretary-stat-content">
          <div class="secretary-stat-icon">
            <i class="bi bi-person-check"></i>
          </div>
          <div>
            <div class="secretary-stat-value">{{ number_format($nowServingCount ?? 0) }}</div>
            <div class="secretary-stat-title">Now Serving</div>
            <p class="secretary-stat-sub">Active patient encounters</p>
          </div>
        </div>
      </div>

      <div class="secretary-stat-card stat-green">
        <div class="secretary-stat-content">
          <div class="secretary-stat-icon">
            <i class="bi bi-person-badge"></i>
          </div>
          <div>
            <div class="secretary-stat-value">
              {{ number_format($onDutyDoctors ?? 0) }}/{{ number_format($totalActiveDoctors ?? 0) }}
            </div>
            <div class="secretary-stat-title">Doctors</div>
            <p class="secretary-stat-sub">Serving / active</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="queue-workspace-card">
    <div class="workspace-header">
      <div>
        <h2 class="workspace-title">
          <i class="bi bi-people-fill"></i>
          Service Statuses
        </h2>
        <div class="workspace-subtitle">
          Select a service to open its browser-like doctor queue lanes.
        </div>
      </div>

      <span class="workspace-pill">
        <i class="bi bi-layers"></i>
        Service lanes
      </span>
    </div>

    <div class="row g-4">
      @forelse ($serviceStatusCards ?? [] as $serviceCard)
        @php
          $serviceId = $serviceCard['route_key'] ?? $serviceCard['id'] ?? null;

          $serviceLink = Route::has('secretary.services.queue.index')
              ? route('secretary.services.queue.index', ['service_id' => $serviceId])
              : url('/secretary/services/' . $serviceId);
        @endphp

        <div class="col-md-6 col-xl-4 mb-4">
          <a class="cliniq-service-card-link" href="{{ $serviceLink }}">
            <div class="cliniq-service-card">
              <div class="cliniq-service-card__header">
                <h3 class="cliniq-service-card__title">{{ $serviceCard['name'] }}</h3>
              </div>

              <div class="cliniq-service-card__stats">
                <div class="cliniq-service-card__stat">
                  <span class="cliniq-service-card__stat-label">WAITING</span>
                  <span class="cliniq-service-card__stat-value">{{ number_format($serviceCard['waiting_count'] ?? 0) }}</span>
                </div>

                <div class="cliniq-service-card__stat cliniq-service-card__stat--right">
                  <span class="cliniq-service-card__stat-label">SERVING</span>
                  <span class="cliniq-service-card__stat-value cliniq-service-card__stat-value--primary">
                    {{ number_format($serviceCard['serving_doctors'] ?? 0) }}
                  </span>
                </div>
              </div>
            </div>
          </a>
        </div>
      @empty
        <div class="col-12">
          <div class="empty-panel">
            <div class="empty-panel-icon">
              <i class="bi bi-layers"></i>
            </div>

            @if($canShowAddDoctorPrompt)
              Add your first doctor to start setting up services and queue lanes.
            @else
              No services are assigned to this clinic yet.
            @endif
          </div>
        </div>
      @endforelse
    </div>
  </section>
</div>
@endsection