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

  $cancelTodayUrl = route('secretary.services.doctors.queue.cancel_today', [
      'service_id' => $service->id ?? request()->route('service_id'),
      'doctor_id' => $doctor->id ?? request()->route('doctor_id'),
  ]);

  $nowServingStatus = strtolower(trim((string) ($nowServing?->status ?? '')));

  $canDoneNext = $nowServing
      && $nowServingStatus === 'served'
      && $nowServingDoneNextUrl;

  $canNoShow = $nowServing
      && in_array($nowServingStatus, ['in_progress', 'now_serving'], true)
      && $nowServingNoShowUrl;

  $canReschedule = $nowServing
      && in_array($nowServingStatus, ['in_progress', 'now_serving'], true);

  $nowServingRescheduleUrl = $canReschedule
      ? route('secretary.queue.reschedule', [
          'clinic' => $clinicId,
          'entry' => $nowServing->id,
      ])
      : null;

  /*
    Browser-like doctor tabs.
    The controller may pass any of these: $doctorTabs, $serviceDoctors, or $doctors.
    If none is passed yet, the page still works and shows the current doctor as the only tab.
  */
  $doctorTabs = collect($doctorTabs ?? $serviceDoctors ?? $doctors ?? ($service->doctors ?? []));

  if ($doctorTabs->isEmpty() && isset($doctor)) {
      $doctorTabs = collect([$doctor]);
  }

  $currentDoctorId = (string) ($doctor->id ?? $doctor->doctor_id ?? request()->route('doctor_id'));
  $serviceIdForTabs = $service->id ?? request()->route('service_id');
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

  .doctor-queue-hero-pills form {
    display: inline-flex;
  }

  .hero-action-btn--danger {
    border-color: rgba(255, 255, 255, 0.85);
    background: rgba(220, 38, 38, 0.92);
    color: #ffffff;
  }

  .hero-action-btn--danger:hover {
    background: #b91c1c;
    color: #ffffff;
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

  .queue-pill-served {
    background: #dcfce7;
    color: #166534;
    border-color: #bbf7d0;
  }

  .queue-pill-progress {
    background: #dbeafe;
    color: #1d4ed8;
    border-color: #bfdbfe;
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

  .highlight-row-served {
    background: rgba(34, 197, 94, 0.10);
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

  .status-served {
    background: #dcfce7;
    color: #166534;
  }

  .status-served .dot {
    background: #16a34a;
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

  .row-action-link--muted {
    border-color: #e2e8f0;
    background: #f8fafc;
    color: #64748b;
    cursor: default;
  }

  .row-action-link--muted:hover {
    border-color: #e2e8f0;
    background: #f8fafc;
    color: #64748b;
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

  .resched-modal .modal-content {
    border: 0;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
  }

  .resched-modal .modal-header {
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #ffffff;
  }

  .resched-modal .btn-close {
    filter: invert(1);
  }



  /* ── Browser-like shell for doctor queue management ── */
  .dq-browser {
    border-radius: 24px;
    border: 1px solid rgba(191, 219, 254, 0.95);
    background: #ffffff;
    box-shadow: 0 24px 60px rgba(37, 99, 235, 0.13);
    overflow: hidden;
  }

  .dq-browser-chrome {
    background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);
    border-bottom: 1px solid #bfdbfe;
    padding-top: 0.85rem;
  }

  .dq-browser-topbar {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.75rem 1rem 0;
  }

  .dq-browser-lights {
    display: flex;
    align-items: center;
    gap: 0.42rem;
    flex: 0 0 auto;
  }

  .dq-browser-light {
    width: 12px;
    height: 12px;
    border-radius: 999px;
    box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.08);
  }

  .dq-browser-light.red { background: #ff5f57; }
  .dq-browser-light.yellow { background: #ffbd2e; }
  .dq-browser-light.green { background: #28c840; }

  .dq-addressbar {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 0.45rem;
    border: 1px solid #bfdbfe;
    background: rgba(255, 255, 255, 0.86);
    border-radius: 999px;
    padding: 0.42rem 0.85rem;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 700;
  }

  .dq-addressbar span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .dq-browser-tabs {
    display: flex;
    align-items: flex-end;
    gap: 0.8rem;
    padding: 0 1.25rem 0;
    overflow-x: auto;
    scrollbar-width: none;
  }

  .dq-browser-tabs::-webkit-scrollbar { display: none; }

  .dq-browser-tab {
    min-width: 245px;
    max-width: 310px;
    min-height: 86px;
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 1rem 1.2rem;
    border-radius: 22px 22px 0 0;
    border: 1px solid transparent;
    border-bottom: 0;
    color: #475569;
    text-decoration: none;
    transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
  }

  .dq-browser-tab:hover {
    background: rgba(255, 255, 255, 0.82);
    color: #0f172a;
    transform: translateY(-2px);
  }

  .dq-browser-tab.active {
    background: #ffffff;
    border-color: #93c5fd;
    color: #0f172a;
    margin-bottom: -1px;
    box-shadow: 0 -6px 22px rgba(37, 99, 235, 0.12);
  }

  .dq-browser-tab-icon {
    width: 52px;
    height: 52px;
    border-radius: 17px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 1rem;
    font-weight: 950;
    flex: 0 0 52px;
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.1);
  }

  .dq-browser-tab.active .dq-browser-tab-icon {
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #ffffff;
  }

  .dq-browser-tab-text { min-width: 0; }

  .dq-browser-tab-name {
    display: block;
    font-size: 1.08rem;
    font-weight: 950;
    line-height: 1.15;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .dq-browser-tab-sub {
    display: block;
    margin-top: 0.28rem;
    font-size: 0.84rem;
    font-weight: 850;
    color: #64748b;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .dq-browser-tab.active .dq-browser-tab-sub { color: #2563eb; }

  .dq-browser-body { padding: 1.25rem; }

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
  <div class="dq-browser">
    <div class="dq-browser-chrome">
      <div class="dq-browser-tabs" role="tablist" aria-label="Doctor queue lanes">
        @forelse($doctorTabs as $doctorTab)
          @php
            $tabId = (string) (data_get($doctorTab, 'id') ?? data_get($doctorTab, 'doctor_id') ?? '');
            $tabName = data_get($doctorTab, 'name')
                ?? trim((data_get($doctorTab, 'first_name') ?? '') . ' ' . (data_get($doctorTab, 'last_name') ?? ''))
                ?: 'Doctor';
            $tabInitials = collect(explode(' ', str_replace('Dr.', '', $tabName)))
                ->filter()
                ->take(2)
                ->map(fn($part) => strtoupper(mb_substr($part, 0, 1)))
                ->implode('') ?: 'DR';
            $isCurrentDoctorTab = $tabId === $currentDoctorId;
            $tabUrl = Route::has('secretary.services.doctors.queue')
                ? route('secretary.services.doctors.queue', ['service_id' => $serviceIdForTabs, 'doctor_id' => $tabId])
                : url('/secretary/services/' . $serviceIdForTabs . '/doctors/' . $tabId . '/queue');
          @endphp

          <a
            class="dq-browser-tab {{ $isCurrentDoctorTab ? 'active' : '' }}"
            href="{{ $tabUrl }}"
            role="tab"
            aria-selected="{{ $isCurrentDoctorTab ? 'true' : 'false' }}">
            <span class="dq-browser-tab-icon">{{ $tabInitials }}</span>
            <span class="dq-browser-tab-text">
              <span class="dq-browser-tab-name"> Dr. {{ $tabName }}</span>
              <span class="dq-browser-tab-sub">Queue management</span>
            </span>
          </a>
        @empty
          <span class="dq-browser-tab active" role="tab" aria-selected="true">
            <span class="dq-browser-tab-icon">DR</span>
            <span class="dq-browser-tab-text">
              <span class="dq-browser-tab-name">No doctors</span>
              <span class="dq-browser-tab-sub">No queue lane available</span>
            </span>
          </span>
        @endforelse
      </div>
    </div>

    <div class="dq-browser-body">
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
          <h1 class="doctor-queue-title">Dr. {{ $doctorName }}</h1>
          <p class="doctor-queue-subtitle">
            Manage the patient queue for this doctor
          </p>
        </div>
      </div>

      <div class="doctor-queue-hero-pills">
        @if($nowServingStatus === 'served')
          <span class="doctor-queue-hero-pill">
            <i class="bi bi-check2-circle"></i>
            Patient served — Done &amp; Next available
          </span>
        @elseif(in_array($nowServingStatus, ['in_progress', 'now_serving'], true))
          <span class="doctor-queue-hero-pill">
            <i class="bi bi-activity"></i>
            Patient with doctor
          </span>
        @endif

        <button class="btn btn-light text-primary hero-action-btn" type="button">
          <i class="bi bi-slash-circle"></i>
          Block Incoming Appointments
        </button>

        <form
          method="POST"
          action="{{ $cancelTodayUrl }}"
          data-confirm="Cancel all active appointments and walk-ins in this doctor queue for today?"
          data-confirm-title="Cancel Today's Queue"
          data-confirm-btn="Cancel Queue">
          @csrf
          <button class="btn hero-action-btn hero-action-btn--danger" type="submit" {{ $queueRows->isEmpty() ? 'disabled' : '' }}>
            <i class="bi bi-x-octagon"></i>
            Cancel Today's Queue
          </button>
        </form>
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
            <div class="text-muted fw-semibold small mt-1">
              @if($nowServingStatus === 'served')
                Doctor has served this patient. Secretary can now finish the queue entry.
              @elseif(in_array($nowServingStatus, ['in_progress', 'now_serving'], true))
                Current active encounter
              @else
                Current active encounter
              @endif
            </div>
          </div>

          @if($nowServingStatus === 'served')
            <span class="queue-pill queue-pill-served">
              <i class="bi bi-check-circle"></i>
              Served
            </span>
          @elseif(in_array($nowServingStatus, ['in_progress', 'now_serving'], true))
            <span class="queue-pill queue-pill-progress">
              <i class="bi bi-activity"></i>
              With Doctor
            </span>
          @endif
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
            <button class="btn btn-primary queue-pause-action" type="button" {{ $nowServingStatus === 'served' ? 'disabled' : '' }}>
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

          @if ($canDoneNext)
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

          @if ($canNoShow)
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

          @if ($canReschedule && $nowServingRescheduleUrl)
            <button
              class="btn btn-outline-secondary"
              type="button"
              data-bs-toggle="modal"
              data-bs-target="#reschedModal"
              data-action-url="{{ $nowServingRescheduleUrl }}">
              <i class="bi bi-calendar2-week"></i>
              Reschedule
            </button>
          @else
            <button class="btn btn-outline-secondary" type="button" disabled>
              <i class="bi bi-calendar2-week"></i>
              Reschedule
            </button>
          @endif
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
              @php
                $statusKey = strtolower(trim((string) ($row['status_key'] ?? '')));

                $isWaiting = in_array($statusKey, ['waiting', 'called'], true);
                $isWithDoctor = in_array($statusKey, ['in_progress', 'now_serving'], true);
                $isServed = $statusKey === 'served';

                $rowClass = $isServed
                    ? 'highlight-row-served'
                    : ($isWithDoctor ? 'highlight-row' : '');
              @endphp

              <tr class="{{ $rowClass }}">
                <td class="queue-number-text">{{ $row['number'] }}</td>

                <td>
                  <div class="fw-bold">{{ $row['name'] }}</div>
                  <div class="patient-muted">
                    {{ $row['id'] }} | {{ $row['visit'] }}
                    @if(! empty($row['is_priority']))
                      | Priority
                    @endif
                  </div>
                </td>

                <td>{{ $row['visit'] }}</td>

                <td>{{ $row['time'] }}</td>

                <td>
                  @if ($isServed)
                    <span class="status-badge status-served">
                      <span class="dot"></span>
                      {{ $row['status'] }}
                    </span>
                  @elseif ($isWithDoctor)
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
                    @if ($isWaiting || $isWithDoctor)
                      <form method="POST" action="{{ $row['call_url'] }}" data-keep-enabled>
                        @csrf
                        <button class="row-action-link" type="submit">Notify</button>
                      </form>

                      @if($isWaiting && empty($row['is_priority']))
                        <form
                          method="POST"
                          action="{{ $row['priority_url'] }}"
                          data-confirm="Mark this patient as priority and update queue slots?"
                          data-confirm-title="Mark As Priority"
                          data-confirm-btn="Mark Priority"
                          data-keep-enabled>
                          @csrf
                          <button class="row-action-link" type="submit">Priority</button>
                        </form>
                      @endif

                      <form method="POST" action="{{ $row['cancel_url'] }}" data-keep-enabled>
                        @csrf
                        <button class="row-action-link row-action-link--danger" type="submit">Cancel</button>
                      </form>
                    @else
                      <span class="row-action-link row-action-link--muted">
                        —
                      </span>
                    @endif
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
  </div>
</div>

<div class="modal fade resched-modal" id="reschedModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="#" class="modal-content" id="reschedForm">
      @csrf

      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-calendar-event me-2"></i>
          Reschedule Appointment
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="mb-3">
          <label class="form-label fw-bold">New Date</label>
          <input type="date" name="new_date" class="form-control" value="{{ old('new_date') }}" required>
          @error('new_date')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-0">
          <label class="form-label fw-bold">New Time</label>
          <input type="time" name="new_time" class="form-control" value="{{ old('new_time') }}" required>
          @error('new_time')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary">
          <i class="bi bi-save me-1"></i>
          Save
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Cancel
        </button>
      </div>
    </form>
  </div>
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
        if (button.disabled) return;

        const isPaused = button.getAttribute('aria-pressed') === 'true';

        button.setAttribute('aria-pressed', isPaused ? 'false' : 'true');

        button.innerHTML = isPaused
          ? '<i class="bi bi-pause-fill"></i> Pause Queue'
          : '<i class="bi bi-play-fill"></i> Resume Queue';
      });
    });

    const reschedModalEl = document.getElementById('reschedModal');

    if (reschedModalEl) {
      reschedModalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const actionUrl = button ? button.getAttribute('data-action-url') : null;
        const form = reschedModalEl.querySelector('form');

        if (form && actionUrl) {
          form.setAttribute('action', actionUrl);
        }
      });
    }
  });
</script>
@endsection

@push('scripts')
  @include('partials.secretary-doctor-served-toast')
@endpush
