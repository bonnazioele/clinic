@extends('layouts.app')

@section('title', 'Doctor Queue')

@section('content')
@php
  $doctorName = $doctor->name ?: trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? '')) ?: 'Doctor';
  $serviceName = $serviceName ?? ($service->name ?? 'Service Queue');
  $queueRows = collect($queueRows ?? []);
  $dashboardUrl = safe_secretary_route('secretary.dashboard', '/secretary/dashboard');
  $serviceQueueUrl = safe_secretary_route('secretary.services.queue.index', '/secretary/dashboard', [
      'service_id' => $service->id ?? request()->route('service_id'),
  ]);
@endphp

<style>
  .doctor-queue-page {
    width: 96%;
    max-width: none;
    margin: 0 auto;
    padding: 0.5rem 0 1.5rem;
  }
  .doctor-queue-hero {
    border-radius: 24px;
    padding: 1.45rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 90% 25%, rgba(255, 255, 255, 0.16), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37, 99, 235, 0.22);
    margin-bottom: 1rem;
  }

  .doctor-queue-hero-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .doctor-queue-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    min-width: min(100%, 420px);
  }

  .doctor-queue-avatar {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    flex: 0 0 58px;
    object-fit: cover;
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.22);
  }

  .doctor-queue-title {
    margin: 0;
    font-size: clamp(1.5rem, 2.4vw, 2.1rem);
    font-weight: 900;
    letter-spacing: -0.045em;
  }

  .doctor-queue-subtitle {
    margin: 0.3rem 0 0;
    font-size: 0.95rem;
    font-weight: 650;
    opacity: 0.94;
  }

  .doctor-queue-hero-pills {
    display: flex;
    justify-content: flex-end;
    gap: 0.55rem;
    flex-wrap: wrap;
    margin-left: auto;
  }

  .doctor-queue-hero-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border-radius: 999px;
    padding: 0.5rem 0.75rem;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.22);
    color: #ffffff;
    font-size: 0.78rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .queue-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .queue-panel {
    border-radius: 24px;
    border: 1px solid rgba(191, 219, 254, 0.95);
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    overflow: hidden;
  }

  .queue-panel-body {
    padding: 1.25rem;
    position: relative;
    z-index: 1;
  }

  .queue-panel-title {
    margin: 0;
    color: #0f172a;
    font-size: 1.08rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .queue-panel-title i {
    color: #0d6efd;
  }

  .queue-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.42rem 0.72rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.76rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .now-serving-card {
    position: relative;
  }

  .now-serving-card .queue-panel-body {
    min-height: 100%;
    display: flex;
    flex-direction: column;
  }

  .now-serving-card::before {
    content: "";
    position: absolute;
    inset: 0 0 auto;
    height: 5px;
    background: linear-gradient(90deg, #0d6efd, #2bbf6a, #ffc107);
  }

  .now-serving-main {
    display: flex;
    align-items: center;
    gap: 1.15rem;
    margin: 1.45rem 0 1.25rem;
  }

  .queue-number-card {
    min-width: 128px;
    border-radius: 18px;
    padding: 1rem 1.1rem;
    text-align: center;
    background: linear-gradient(135deg, #ffd85a, #ffc107);
    color: #162033;
    font-size: 2.15rem;
    font-weight: 900;
    letter-spacing: -0.055em;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
  }

  .patient-name {
    margin: 0 0 0.3rem;
    color: #0f172a;
    font-size: clamp(1.85rem, 3vw, 2.65rem);
    font-weight: 900;
    letter-spacing: -0.055em;
  }

  .visit-type {
    color: #64748b;
    font-size: 1rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 0.4rem;
  }

  .now-serving-divider {
    height: 1px;
    margin: auto 0 1rem;
    background: linear-gradient(90deg, transparent, #dbeafe, transparent);
  }

  .queue-actions {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.85rem;
  }

  .queue-actions form {
    display: flex;
  }

  .queue-actions .btn,
  .hero-action-btn {
    min-height: 42px;
    border-radius: 12px;
    font-weight: 900;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
  }

  .queue-actions .btn {
    min-height: 58px;
    font-size: 0.96rem;
    border-radius: 16px;
  }

  .queue-actions .btn i {
    font-size: 1.15rem;
  }

  .btn-primary {
    background: linear-gradient(135deg, #0d6efd, #178bff);
    border: 0;
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #0b5ed7, #0d6efd);
  }

  .btn-done {
    background: linear-gradient(135deg, #087b3d, #2bbf6a);
    color: #ffffff;
    border: 0;
  }

  .btn-done:hover {
    background: linear-gradient(135deg, #066a35, #21a95b);
    color: #ffffff;
  }

  .btn-no-show {
    border-color: #ffc107;
    color: #8a5a00;
  }

  .btn-no-show:hover {
    background: #fff7d6;
    border-color: #ffc107;
    color: #654200;
  }

  .queue-status-card {
    min-height: 168px;
    color: #ffffff;
    background:
      radial-gradient(circle at 90% 30%, rgba(255, 255, 255, 0.16), transparent 18%),
      linear-gradient(135deg, #0866f2, #2993ff);
    box-shadow: 0 18px 45px rgba(37, 99, 235, 0.22);
    position: relative;
  }

  .queue-status-card::after {
    content: "";
    position: absolute;
    right: -22px;
    bottom: -28px;
    width: 116px;
    height: 116px;
    border-radius: 36px;
    background: rgba(255, 255, 255, 0.14);
    transform: rotate(4deg);
  }

  .queue-status-value {
    font-size: 2.1rem;
    line-height: 1;
    font-weight: 900;
    letter-spacing: -0.055em;
    margin: 0.55rem 0 0.45rem;
  }

  .queue-status-layout {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
  }

  .queue-pause-btn {
    width: 82px;
    height: 82px;
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.34);
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
    display: grid;
    place-items: center;
    flex: 0 0 82px;
    position: relative;
    z-index: 2;
  }

  .queue-pause-btn i {
    font-size: 2.35rem;
  }

  .queue-pause-btn:hover {
    background: rgba(255, 255, 255, 0.26);
    color: #ffffff;
  }

  .queue-next-card {
    min-height: 132px;
  }

  .queue-number-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 62px;
    border-radius: 14px;
    padding: 0.55rem 0.7rem;
    background: linear-gradient(135deg, #ffd85a, #ffc107);
    color: #162033;
    font-weight: 900;
  }

  .queue-table {
    margin-bottom: 0;
  }

  .queue-table thead th {
    color: #64748b;
    background: #f8fafc;
    border-bottom: 1px solid #edf2f7;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .queue-table tbody td {
    color: #0f172a;
    border-bottom: 1px solid #edf2f7;
    padding: 0.9rem 0.75rem;
    vertical-align: middle;
  }

  .highlight-row {
    background: rgba(13, 110, 253, 0.08);
  }

  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.32rem 0.62rem;
    font-size: 0.72rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .status-badge .dot {
    width: 7px;
    height: 7px;
    border-radius: 999px;
  }

  .status-in-progress {
    background: #dbeafe;
    color: #1d4ed8;
  }

  .status-in-progress .dot {
    background: #1d4ed8;
  }

  .status-waiting {
    background: #fff7d6;
    color: #8a5a00;
  }

  .status-waiting .dot {
    background: #ffc107;
  }

  .filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.42rem 0.72rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.76rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .queue-search {
    position: relative;
    width: min(100%, 320px);
  }

  .queue-search i {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    color: #64748b;
    transform: translateY(-50%);
    pointer-events: none;
  }

  .queue-search-input {
    min-height: 42px;
    border-radius: 999px;
    border: 1px solid #bfdbfe;
    background: #f8fafc;
    padding-left: 2.35rem;
    color: #0f172a;
    font-size: 0.88rem;
    font-weight: 700;
  }

  .row-action-links {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.45rem;
    flex-wrap: wrap;
  }

  .row-action-links form {
    display: inline-flex;
  }

  .row-action-link {
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    background: #eff6ff;
    padding: 0.36rem 0.64rem;
    color: #1d4ed8;
    font-size: 0.82rem;
    font-weight: 900;
    text-decoration: none;
    transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
  }

  .row-action-link:hover {
    background: #ffc107;
    border-color: #ffc107;
    color: #162033;
    text-decoration: none;
  }

  .row-action-link--warning {
    border-color: #fde68a;
    background: #fff7d6;
    color: #8a5a00;
  }

  .row-action-link--warning:hover {
    background: #ffc107;
    border-color: #ffc107;
    color: #162033;
  }

  .row-action-link--danger {
    border-color: #fecaca;
    background: #fff1f2;
    color: #dc2626;
  }

  .row-action-link--danger:hover {
    background: #ffc107;
    border-color: #ffc107;
    color: #162033;
    text-decoration: none;
  }

  .queue-number-text {
    color: #0f172a;
    font-weight: 950;
    letter-spacing: -0.02em;
  }

  .patient-muted {
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 800;
  }

  .icon-btn {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-grid;
    place-items: center;
    padding: 0;
  }

  .fab {
    position: fixed;
    right: 2rem;
    bottom: 2rem;
    width: 56px;
    height: 56px;
    border-radius: 999px;
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #ffffff;
    border: none;
    display: grid;
    place-items: center;
    box-shadow: 0 18px 36px rgba(37, 99, 235, 0.28);
    z-index: 1040;
  }

  .fab:hover {
    color: #ffffff;
    background: linear-gradient(135deg, #0b5ed7, #0d6efd);
  }

  .fab-tooltip {
    position: absolute;
    right: 68px;
    background: rgba(15, 23, 42, 0.9);
    color: #ffffff;
    padding: 0.4rem 0.6rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    white-space: nowrap;
    opacity: 0;
    transform: translateY(6px);
    transition: 0.2s ease;
    pointer-events: none;
  }

  .fab:hover .fab-tooltip {
    opacity: 1;
    transform: translateY(0);
  }

  @media (max-width: 1200px) {
    .queue-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .doctor-queue-page {
      width: 100%;
    }

    .doctor-queue-hero {
      border-radius: 22px;
      padding: 1rem;
    }

    .doctor-queue-hero-pills {
      width: 100%;
      justify-content: flex-start;
      margin-left: 0;
    }

    .now-serving-main {
      align-items: flex-start;
      flex-direction: column;
    }

    .queue-actions {
      grid-template-columns: 1fr 1fr;
    }
  }

  @media (max-width: 520px) {
    .doctor-queue-title-wrap {
      flex-direction: column;
    }

    .queue-actions {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="doctor-queue-page">
  <nav class="mt-2 mb-3" aria-label="Queue navigation">
    <a href="{{ $dashboardUrl }}">Dashboard</a> \ <a href="{{ $serviceQueueUrl }}">{{ $serviceName }}</a>
  </nav>

  <section class="doctor-queue-hero">
    <div class="doctor-queue-hero-row">
      <div class="doctor-queue-title-wrap">
        <img
          class="doctor-queue-avatar"
          src="{{ $doctor->avatar_url ?? $doctor->avatar ?? 'https://placehold.co/120x120?text=DR' }}"
          alt="{{ $doctorName }}">

        <div>
          <h1 class="doctor-queue-title">{{ $doctorName }}</h1>
          <p class="doctor-queue-subtitle">
            Manage the patient queue for this doctor
          </p>
        </div>
      </div>

      <div class="doctor-queue-hero-pills">
        <button class="btn btn-light text-primary hero-action-btn" type="button">
          <i class="bi bi-slash-circle"></i>
          Block Incoming Appointments
        </button>
      </div>
    </div>
  </section>

  <div class="queue-grid">
    <section class="queue-panel now-serving-card">
      <div class="queue-panel-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
          <div>
            <h2 class="queue-panel-title">
              <i class="bi bi-person-check-fill"></i>
              Now Serving
            </h2>
            <div class="text-muted fw-semibold small mt-1">Current active encounter</div>
          </div>
        </div>

        <div class="now-serving-main">
          <div class="queue-number-card">{{ $nowServingNumber ?? '---' }}</div>
          <div>
            <h3 class="patient-name">{{ $nowServingName ?? 'No active patient' }}</h3>
            <div class="visit-type">
              <i class="bi bi-clipboard2-pulse"></i>
              {{ $nowServingType ?? 'Queue is ready' }}
            </div>
          </div>
        </div>

        <div class="now-serving-divider"></div>

        <div class="queue-actions">
          @if ($nowServing)
            <button class="btn btn-primary queue-pause-action" type="button">
              <i class="bi bi-pause-fill"></i>
              Pause Queue
            </button>
          @elseif ($queueNextCallUrl)
            <form method="POST" action="{{ $queueNextCallUrl }}" class="queue-start-form" data-keep-enabled>
              @csrf
              <button class="btn btn-primary w-100 queue-start-button" type="submit">
                <i class="bi bi-play-fill"></i>
                Start Next
              </button>
            </form>
          @else
            <button class="btn btn-primary" type="button" disabled>
              <i class="bi bi-play-fill"></i>
              Start
            </button>
          @endif

          @if ($nowServingDoneNextUrl)
            <form method="POST" action="{{ $nowServingDoneNextUrl }}" data-keep-enabled>
              @csrf
              <button class="btn btn-done w-100" type="submit">
                <i class="bi bi-check2-circle"></i>
                Done and Next
              </button>
            </form>
          @else
            <button class="btn btn-done" type="button" disabled>
              <i class="bi bi-check2-circle"></i>
              Done and Next
            </button>
          @endif

          @if ($nowServingNoShowUrl)
            <form method="POST" action="{{ $nowServingNoShowUrl }}" data-keep-enabled>
              @csrf
              <button class="btn btn-outline-warning btn-no-show w-100" type="submit">
                <i class="bi bi-exclamation-circle"></i>
                No Show
              </button>
            </form>
          @else
            <button class="btn btn-outline-warning btn-no-show" type="button" disabled>
              <i class="bi bi-exclamation-circle"></i>
              No Show
            </button>
          @endif

          <button class="btn btn-outline-secondary" type="button" disabled>
            <i class="bi bi-calendar2-week"></i>
            Reschedule
          </button>
        </div>
      </div>
    </section>

    <div class="d-flex flex-column gap-3">
      <section class="queue-panel queue-status-card">
        <div class="queue-panel-body">
          <div class="queue-status-layout">
            <div>
              <div class="d-flex align-items-center gap-2 fw-bold">
                <i class="bi bi-activity"></i>
                Queue Status
              </div>
              <div class="queue-status-value">{{ number_format($queueStatusCount ?? 0) }} Patients</div>
              <div class="d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-clock"></i>
                Estimated wait: {{ $queueStatusEta ?? 'No wait' }}
              </div>
            </div>

            <button class="queue-pause-btn" type="button" aria-label="Pause queue">
              <i class="bi bi-pause-fill"></i>
            </button>
          </div>
        </div>
      </section>

      <section class="queue-panel queue-next-card">
        <div class="queue-panel-body">
          <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="queue-panel-title">
              <i class="bi bi-arrow-right-circle-fill"></i>
              Queue Next
            </h2>
            <span class="queue-pill">
              <i class="bi bi-hourglass-split"></i>
              Call
            </span>
          </div>

          <div class="d-flex align-items-center gap-3">
            <span class="queue-number-badge">{{ $queueNextNumber ?? '---' }}</span>
            <div>
              <div class="fw-bold text-dark">{{ $queueNextName ?? 'No patient waiting' }}</div>
              <div class="text-muted small">{{ $queueNextType ?? 'Queue is empty' }}</div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <section class="queue-panel">
    <div class="queue-panel-body">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="queue-panel-title">
          <i class="bi bi-list-check"></i>
          Patient Queue
        </h2>

        <div class="queue-search">
          <i class="bi bi-search"></i>
          <input
            type="search"
            class="form-control queue-search-input"
            placeholder="Search patient or queue #"
            aria-label="Search patient queue">
        </div>
      </div>

      <div class="table-responsive">
        <table class="table align-middle queue-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Patient Name</th>
              <th>Visit Type</th>
              <th>Slot Time</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($queueRows as $row)
              <tr class="{{ $row['active'] ? 'highlight-row' : '' }}">
                <td class="queue-number-text">{{ $row['number'] }}</td>
                <td>
                  <div class="fw-bold">{{ $row['name'] }}</div>
                  <div class="patient-muted">
                    {{ $row['id'] }} | {{ $row['visit'] }}
                  </div>
                </td>
                <td>{{ $row['visit'] }}</td>
                <td>{{ $row['time'] }}</td>
                <td>
                  @if (in_array($row['status_key'], ['in_progress', 'now_serving'], true))
                    <span class="status-badge status-in-progress">
                      <span class="dot"></span>
                      {{ $row['status'] }}
                    </span>
                  @else
                    <span class="status-badge status-waiting">
                      <span class="dot"></span>
                      {{ $row['status'] }}
                    </span>
                  @endif
                </td>
                <td class="text-end">
                  <div class="row-action-links">
                    @if (! in_array($row['status_key'], ['in_progress', 'now_serving'], true))
                      <form method="POST" action="{{ $row['call_url'] }}" data-keep-enabled>
                        @csrf
                        <button class="row-action-link" type="submit">Call</button>
                      </form>
                    @endif
                    <form method="POST" action="{{ $row['no_show_url'] }}" data-keep-enabled>
                      @csrf
                      <button class="row-action-link row-action-link--warning" type="submit">No Show</button>
                    </form>
                    <form method="POST" action="{{ $row['cancel_url'] }}" data-keep-enabled>
                      @csrf
                      <button class="row-action-link row-action-link--danger" type="submit">Cancel</button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted fw-semibold py-4">
                  No patients are queued for this doctor and service today.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
        <div class="text-muted small">
          Showing {{ $queueRows->count() }} of {{ $queueRows->count() }} patients in queue
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm icon-btn" type="button" disabled aria-label="Previous page">
            <i class="bi bi-chevron-left"></i>
          </button>
          <button class="btn btn-outline-secondary btn-sm icon-btn" type="button" aria-label="Next page">
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </section>
</div>

<button class="fab" type="button" aria-label="Add Patient to Queue">
  <i class="bi bi-person-plus"></i>
  <span class="fab-tooltip">Add Patient to Queue</span>
</button>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.queue-start-form').forEach(function (form) {
      form.addEventListener('submit', function () {
        const button = form.querySelector('.queue-start-button');

        if (!button) return;

        button.innerHTML = '<i class="bi bi-pause-fill"></i> Pause Queue';
      });
    });

    document.querySelectorAll('.queue-pause-action').forEach(function (button) {
      button.addEventListener('click', function () {
        const isPaused = button.getAttribute('aria-pressed') === 'true';
        button.setAttribute('aria-pressed', isPaused ? 'false' : 'true');
        button.innerHTML = isPaused
          ? '<i class="bi bi-pause-fill"></i> Pause Queue'
          : '<i class="bi bi-play-fill"></i> Resume Queue';
      });
    });
  });
</script>
@endsection
