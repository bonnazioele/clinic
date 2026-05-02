@extends('layouts.patient-dashboard')

@section('title', 'Queue Status')

@push('styles')
<style>
  .patient-tab-shell,
  .patient-tab-content {
    width: 100%;
    max-width: none;
  }

  .queue-page {
    width: 96%;
    max-width: none;
    padding: 0.9rem 0 1.4rem;
  }

  .queue-shell {
    width: 100%;
    border-radius: 24px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: rgba(255, 255, 255, 0.95);
    box-shadow:
      0 16px 42px rgba(15, 23, 42, 0.08),
      inset 0 1px 0 rgba(255, 255, 255, 0.8);
    overflow: hidden;
  }

  .queue-hero {
    padding: 1.25rem 1.5rem 1rem;
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 32%),
      linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
    border-bottom: 1px solid rgba(226, 232, 240, 0.9);
  }

  .queue-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.9rem;
  }

  .queue-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .queue-title-icon {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    display: grid;
    place-items: center;
    border-radius: 16px;
    color: #fff;
    background: linear-gradient(135deg, #0d6efd, #1287ff);
    box-shadow: 0 10px 24px rgba(13, 110, 253, 0.24);
    font-size: 1.4rem;
  }

  .queue-title {
    margin: 0;
    color: #071225;
    font-weight: 800;
    letter-spacing: -0.04em;
    font-size: 1.55rem;
    line-height: 1.05;
  }

  .queue-subtitle {
    margin: 0.35rem 0 0;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
  }

  .queue-back-btn {
    border-radius: 13px;
    padding: 0.55rem 0.9rem;
    font-size: 0.88rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .queue-body {
    padding: 1.1rem 1.5rem 1.5rem;
  }

  .queue-focus-card {
    position: relative;
    border-radius: 22px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    overflow: hidden;
  }

  .queue-focus-card::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 7px;
    background: linear-gradient(180deg, #0d6efd, #49a4ff);
  }

  .queue-focus-card.served::before {
    background: linear-gradient(180deg, #16a34a, #4ade80);
  }

  .queue-status-header {
    padding: 1.1rem 1.15rem 0.9rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    border-bottom: 1px solid #edf2f7;
  }

  .queue-clinic-wrap {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    min-width: 0;
  }

  .queue-clinic-icon {
    width: 54px;
    height: 54px;
    flex: 0 0 54px;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: #e8f2ff;
    color: #0d6efd;
    font-size: 1.55rem;
  }

  .queue-clinic-name {
    margin: 0 0 0.15rem;
    color: #0f172a;
    font-size: 1.05rem;
    font-weight: 800;
    letter-spacing: -0.025em;
  }

  .queue-clinic-sub {
    color: #64748b;
    font-size: 0.84rem;
    font-weight: 600;
  }

  .queue-live-row {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin-top: 0.35rem;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 600;
  }

  .live-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #22c55e;
    box-shadow: 0 0 0 5px rgba(34, 197, 94, 0.12);
  }

  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border-radius: 999px;
    padding: 0.42rem 0.75rem;
    font-size: 0.76rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .status-badge.waiting {
    background: #fff7db;
    color: #8a6300;
    border: 1px solid #ffe7a2;
  }

  .status-badge.served {
    background: #e8fff3;
    color: #0f9f6e;
    border: 1px solid #b7f0cf;
  }

  .visit-date-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    width: fit-content;
    border-radius: 999px;
    padding: 0.42rem 0.7rem;
    font-size: 0.75rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .visit-date-pill.today {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
  }

  .visit-date-pill.tomorrow {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
  }

  .visit-date-pill.this-week {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
  }

  .visit-date-pill.next-week {
    background: #f5f3ff;
    color: #6d28d9;
    border: 1px solid #ddd6fe;
  }

  .visit-date-pill.later {
    background: #f8fafc;
    color: #475569;
    border: 1px solid #e2e8f0;
  }

  .visit-date-detail {
    margin-top: 0.55rem;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 600;
  }

  .queue-main-status {
    padding: 1.3rem 1.15rem;
    display: grid;
    grid-template-columns: 1.1fr 1.9fr;
    gap: 1rem;
    align-items: stretch;
  }

  .queue-number-panel {
    border-radius: 20px;
    border: 1px solid rgba(191, 219, 254, 0.9);
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 35%),
      linear-gradient(135deg, #f8fbff, #eff6ff);
    padding: 1.15rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
  }

  .queue-number-label {
    color: #64748b;
    font-size: 0.74rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.35rem;
  }

  .queue-number {
    color: #0d6efd;
    font-size: 3.4rem;
    line-height: 1;
    font-weight: 900;
    letter-spacing: -0.06em;
  }

  .queue-number-help {
    margin-top: 0.5rem;
    color: #475569;
    font-size: 0.84rem;
    font-weight: 600;
  }

  .queue-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
  }

  .summary-box {
    border-radius: 18px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
    padding: 0.95rem;
  }

  .summary-icon {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    border-radius: 13px;
    background: #ffffff;
    color: #0d6efd;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    font-size: 1.05rem;
    margin-bottom: 0.65rem;
  }

  .summary-label {
    color: #64748b;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.2rem;
  }

  .summary-value {
    color: #0f172a;
    font-size: 1.18rem;
    font-weight: 900;
    line-height: 1.15;
  }

  .summary-sub {
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 600;
    margin-top: 0.22rem;
  }

  .queue-message {
    margin: 0 1.15rem 1rem;
    padding: 0.85rem 0.95rem;
    border-radius: 16px;
    border: 1px solid rgba(125, 211, 252, 0.75);
    background: linear-gradient(135deg, rgba(239, 249, 255, 0.96), rgba(224, 247, 255, 0.78));
    color: #334155;
    font-size: 0.88rem;
    font-weight: 600;
  }

  .queue-message i {
    color: #0d6efd;
  }

  .queue-message.served {
    border-color: rgba(187, 247, 208, 0.9);
    background: linear-gradient(135deg, rgba(240, 253, 244, 0.98), rgba(220, 252, 231, 0.78));
  }

  .queue-message.served i {
    color: #16a34a;
  }

  .nearest-note {
    margin: 0 1.15rem 1rem;
    padding: 0.85rem 0.95rem;
    border-radius: 16px;
    border: 1px solid rgba(191, 219, 254, 0.9);
    background: linear-gradient(135deg, rgba(239, 246, 255, 0.98), rgba(255, 255, 255, 0.95));
    color: #334155;
    font-size: 0.86rem;
    font-weight: 600;
  }

  .nearest-note i {
    color: #0d6efd;
  }

  .queue-detail-strip {
    margin: 0 1.15rem 1rem;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.7rem;
  }

  .queue-detail-note {
    padding: 0.8rem;
    border-radius: 15px;
    border: 1px solid #edf2f7;
    background: #ffffff;
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
  }

  .queue-detail-note i {
    color: #0d6efd;
    font-size: 1rem;
    margin-top: 0.05rem;
  }

  .detail-note-title {
    color: #0f172a;
    font-size: 0.82rem;
    font-weight: 800;
    margin-bottom: 0.12rem;
  }

  .detail-note-text {
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 600;
    line-height: 1.35;
  }

  .queue-progress-wrap {
    margin: 0 1.15rem 1rem;
    padding: 0.85rem 0.95rem;
    border-radius: 16px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
  }

  .queue-progress-meta {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.45rem;
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 700;
  }

  .progress {
    height: 10px;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
  }

  .progress-bar {
    border-radius: 999px;
  }

  .queue-tip-box {
    margin: 0 1.15rem 1rem;
    padding: 0.85rem 0.95rem;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background:
      linear-gradient(135deg, rgba(248, 250, 252, 0.96), rgba(255, 255, 255, 0.95));
  }

  .queue-tip-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0 0 0.45rem;
    color: #0f172a;
    font-size: 0.9rem;
    font-weight: 800;
  }

  .queue-tip-title i {
    color: #0d6efd;
  }

  .queue-tip-list {
    margin: 0;
    padding-left: 1.05rem;
    color: #475569;
    font-size: 0.82rem;
    font-weight: 600;
    line-height: 1.55;
  }

  .queue-actions {
    padding: 0 1.15rem 1.15rem;
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
  }

  .queue-actions .btn {
    border-radius: 12px;
    font-size: 0.84rem;
    font-weight: 800;
    padding: 0.55rem 0.9rem;
  }

  .queue-list-section {
    margin-top: 0.2rem;
  }

  .queue-list-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.8rem;
  }

  .queue-list-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    color: #0f172a;
    font-size: 1.05rem;
    font-weight: 800;
    letter-spacing: -0.025em;
  }

  .queue-list-title i {
    color: #0d6efd;
  }

  .queue-count-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 24px;
    padding: 0 0.55rem;
    border-radius: 999px;
    background: #eaf3ff;
    color: #0d6efd;
    font-size: 0.75rem;
    font-weight: 800;
  }

  .queue-card-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.85rem;
  }

  .queue-mini-card {
    position: relative;
    padding: 1rem;
    border-radius: 18px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    overflow: hidden;
    transition: 0.22s ease;
  }

  .queue-mini-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.09);
  }

  .queue-mini-card::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 5px;
    background: linear-gradient(180deg, #0d6efd, #49a4ff);
  }

  .queue-mini-top {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    margin-bottom: 0.9rem;
  }

  .queue-mini-icon {
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: #e8f2ff;
    color: #0d6efd;
    font-size: 1.25rem;
  }

  .queue-mini-clinic {
    margin: 0;
    color: #0f172a;
    font-size: 0.96rem;
    font-weight: 800;
    letter-spacing: -0.02em;
  }

  .queue-mini-sub {
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 600;
  }

  .queue-mini-number {
    color: #0d6efd;
    font-size: 2rem;
    line-height: 1;
    font-weight: 900;
    letter-spacing: -0.05em;
    margin-bottom: 0.65rem;
  }

  .queue-mini-status {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    width: fit-content;
    border-radius: 999px;
    background: #fff7db;
    color: #8a6300;
    border: 1px solid #ffe7a2;
    padding: 0.42rem 0.65rem;
    font-size: 0.74rem;
    font-weight: 800;
    margin-bottom: 0.85rem;
  }

  .queue-mini-footer {
    padding-top: 0.7rem;
    margin-top: 0.75rem;
    border-top: 1px solid #edf2f7;
    color: #64748b;
    font-size: 0.75rem;
    font-weight: 600;
  }

  .queue-mini-card .btn {
    border-radius: 12px;
    font-size: 0.82rem;
    font-weight: 800;
    padding: 0.48rem 0.8rem;
  }

  .empty-state {
    padding: 2rem 1rem;
    text-align: center;
    border: 1px dashed rgba(13, 110, 253, 0.34);
    border-radius: 18px;
    background:
      linear-gradient(135deg, rgba(13, 110, 253, 0.06), rgba(255, 255, 255, 0.94));
  }

  .empty-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 0.8rem;
    display: grid;
    place-items: center;
    border-radius: 18px;
    background: #ffffff;
    color: #0d6efd;
    font-size: 1.65rem;
    box-shadow: 0 10px 28px rgba(13, 110, 253, 0.11);
  }

  .empty-title {
    margin: 0 0 0.3rem;
    color: #0f172a;
    font-size: 1.05rem;
    font-weight: 800;
  }

  .empty-text {
    color: #64748b;
    margin-bottom: 0.95rem;
    font-size: 0.9rem;
    font-weight: 500;
  }

  .empty-actions {
    display: flex;
    justify-content: center;
    gap: 0.65rem;
    flex-wrap: wrap;
  }

  .empty-actions .btn {
    border-radius: 12px;
    font-size: 0.84rem;
    font-weight: 800;
    padding: 0.55rem 0.9rem;
  }

  @media (max-width: 1200px) {
    .queue-main-status {
      grid-template-columns: 1fr;
    }

    .queue-card-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 900px) {
    .queue-summary-grid,
    .queue-detail-strip {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .queue-page {
      width: 100%;
      padding-top: 0.75rem;
    }

    .queue-hero,
    .queue-body {
      padding-left: 0.85rem;
      padding-right: 0.85rem;
    }

    .queue-hero-row,
    .queue-status-header,
    .queue-list-header {
      flex-direction: column;
      align-items: stretch;
    }

    .queue-title {
      font-size: 1.35rem;
    }

    .queue-subtitle {
      font-size: 0.82rem;
    }

    .queue-back-btn,
    .queue-actions .btn,
    .queue-actions form,
    .queue-actions form button,
    .empty-actions .btn {
      width: 100%;
    }

    .queue-actions,
    .empty-actions {
      flex-direction: column;
    }

    .queue-card-grid {
      grid-template-columns: 1fr;
    }

    .queue-number {
      font-size: 2.9rem;
    }
  }
</style>
@endpush

@section('content')
<div class="patient-tab-shell">
  <div class="patient-tab-content">
    <div class="container-fluid queue-page px-0">
      @include('partials.alerts')

      <div class="queue-shell">
        <div class="queue-hero">
          <div class="queue-hero-row">
            <div class="queue-title-wrap">
              <div class="queue-title-icon">
                <i class="bi bi-people-fill"></i>
              </div>

              <div>
                <h1 class="queue-title">My Queue Status</h1>
                <p class="queue-subtitle">
                  Quickly check your queue number, waiting status, visit date, and estimated call time.
                </p>
              </div>
            </div>

           
          </div>
        </div>

        <div class="queue-body">
          @if(isset($entry))
            @php
              $estimatedMinutes = isset($ahead) ? $ahead * 15 : 0;
              $estimatedTime = now()->addMinutes($estimatedMinutes);

              $progress = isset($ahead) && $ahead > 0
                ? (($entry->queue_number / ($entry->queue_number + $ahead)) * 100)
                : 100;

              $visitDate = optional($entry->appointment)->appointment_date
                ? \Carbon\Carbon::parse($entry->appointment->appointment_date)->startOfDay()
                : null;

              $today = now()->startOfDay();
              $tomorrow = now()->copy()->addDay()->startOfDay();
              $endOfWeek = now()->copy()->endOfWeek()->startOfDay();
              $startOfNextWeek = now()->copy()->addWeek()->startOfWeek()->startOfDay();
              $endOfNextWeek = now()->copy()->addWeek()->endOfWeek()->startOfDay();

              $visitDateLabel = 'No date';
              $visitDateClass = 'later';

              if ($visitDate) {
                if ($visitDate->isSameDay($today)) {
                  $visitDateLabel = 'Today';
                  $visitDateClass = 'today';
                } elseif ($visitDate->isSameDay($tomorrow)) {
                  $visitDateLabel = 'Tomorrow';
                  $visitDateClass = 'tomorrow';
                } elseif ($visitDate->betweenIncluded($today, $endOfWeek)) {
                  $visitDateLabel = 'This Week';
                  $visitDateClass = 'this-week';
                } elseif ($visitDate->betweenIncluded($startOfNextWeek, $endOfNextWeek)) {
                  $visitDateLabel = 'Next Week';
                  $visitDateClass = 'next-week';
                } else {
                  $visitDateLabel = $visitDate->isPast() ? 'Past Date' : 'Later';
                  $visitDateClass = 'later';
                }
              }
            @endphp

            <article class="queue-focus-card {{ $entry->status === 'waiting' ? '' : 'served' }}">
              <div class="queue-status-header">
                <div class="queue-clinic-wrap">
                  <div class="queue-clinic-icon">
                    <i class="bi bi-hospital"></i>
                  </div>

                  <div>
                    <h5 class="queue-clinic-name">{{ $entry->clinic->name }}</h5>

                    <div class="queue-clinic-sub">
                      This is your current queue position.
                    </div>

                    @if($visitDate)
                      <div class="mt-2">
                        <span class="visit-date-pill {{ $visitDateClass }}">
                          <i class="bi bi-calendar-event"></i>
                          {{ $visitDateLabel }}
                        </span>

                        <div class="visit-date-detail">
                          {{ $visitDate->format('M j, Y') }} · {{ $visitDate->format('l') }}
                        </div>
                      </div>
                    @endif

                    <div class="queue-live-row">
                      <span class="live-dot"></span>
                      <span>Status updated just now</span>
                    </div>
                  </div>
                </div>

                @if($entry->status === 'waiting')
                  <span class="status-badge waiting">
                    <i class="bi bi-clock-fill"></i>
                    Waiting
                  </span>
                @else
                  <span class="status-badge served">
                    <i class="bi bi-check-circle-fill"></i>
                    Served
                  </span>
                @endif
              </div>

              <div class="queue-main-status">
                <div class="queue-number-panel">
                  <div class="queue-number-label">Your Queue Number</div>
                  <div class="queue-number">#{{ $entry->queue_number }}</div>

                  @if($entry->status === 'waiting')
                    <div class="queue-number-help">
                      Keep this number visible while waiting at the clinic.
                    </div>
                  @else
                    <div class="queue-number-help">
                      This queue number has already been completed.
                    </div>
                  @endif
                </div>

                @if($entry->status === 'waiting')
                  <div class="queue-summary-grid">
                    <div class="summary-box">
                      <div class="summary-icon">
                        <i class="bi bi-person-lines-fill"></i>
                      </div>
                      <div class="summary-label">People Ahead</div>
                      <div class="summary-value">{{ $ahead }}</div>
                      <div class="summary-sub">
                        {{ $ahead == 1 ? 'person before you' : 'people before you' }}
                      </div>
                    </div>

                    <div class="summary-box">
                      <div class="summary-icon">
                        <i class="bi bi-hourglass-split"></i>
                      </div>
                      <div class="summary-label">Estimated Wait</div>
                      <div class="summary-value">{{ $estimatedMinutes }} mins</div>
                      <div class="summary-sub">Approximate waiting time</div>
                    </div>

                    <div class="summary-box">
                      <div class="summary-icon">
                        <i class="bi bi-bell-fill"></i>
                      </div>
                      <div class="summary-label">Expected Call</div>
                      <div class="summary-value">{{ $estimatedTime->format('g:i A') }}</div>
                      <div class="summary-sub">Estimated only</div>
                    </div>
                  </div>
                @else
                  <div class="queue-summary-grid">
                    <div class="summary-box">
                      <div class="summary-icon">
                        <i class="bi bi-check2-circle"></i>
                      </div>
                      <div class="summary-label">Queue Status</div>
                      <div class="summary-value">Completed</div>
                      <div class="summary-sub">Your visit was already served</div>
                    </div>

                    <div class="summary-box">
                      <div class="summary-icon">
                        <i class="bi bi-clock-history"></i>
                      </div>
                      <div class="summary-label">Served Time</div>
                      <div class="summary-value">{{ $entry->formatted_served_time }}</div>
                      <div class="summary-sub">Time you were served</div>
                    </div>

                    <div class="summary-box">
                      <div class="summary-icon">
                        <i class="bi bi-hospital"></i>
                      </div>
                      <div class="summary-label">Clinic</div>
                      <div class="summary-value">{{ $entry->clinic->name }}</div>
                      <div class="summary-sub">Queue location</div>
                    </div>
                  </div>
                @endif
              </div>

              @if($entry->status === 'waiting')
                <div class="queue-message">
                  <i class="bi bi-info-circle me-2"></i>
                  <strong>You are still waiting.</strong>
                  There {{ $ahead == 1 ? 'is' : 'are' }} {{ $ahead }} {{ $ahead == 1 ? 'person' : 'people' }} ahead of you.
                  Your estimated call time is around {{ $estimatedTime->format('g:i A') }}.
                </div>

                @if($visitDate)
                  <div class="nearest-note">
                    <i class="bi bi-calendar-check me-2"></i>
                    <strong>Visit date:</strong>
                    This queue is for <strong>{{ $visitDateLabel }}</strong>,
                    {{ $visitDate->format('M j, Y') }}.
                    @if($visitDateLabel === 'Today')
                      Please stay ready because this is your nearest queue.
                    @elseif($visitDateLabel === 'Tomorrow')
                      This visit is coming up tomorrow.
                    @elseif($visitDateLabel === 'This Week')
                      This visit is still within this week.
                    @elseif($visitDateLabel === 'Next Week')
                      This visit is scheduled for next week.
                    @endif
                  </div>
                @endif

                <div class="queue-detail-strip">
                  <div class="queue-detail-note">
                    <i class="bi bi-megaphone"></i>
                    <div>
                      <div class="detail-note-title">Listen for your number</div>
                      <div class="detail-note-text">
                        Staff may call your queue number when it is your turn.
                      </div>
                    </div>
                  </div>

                  <div class="queue-detail-note">
                    <i class="bi bi-phone"></i>
                    <div>
                      <div class="detail-note-title">Keep this page open</div>
                      <div class="detail-note-text">
                        Use this screen to quickly check your queue progress.
                      </div>
                    </div>
                  </div>

                  <div class="queue-detail-note">
                    <i class="bi bi-clock-history"></i>
                    <div>
                      <div class="detail-note-title">Time is estimated</div>
                      <div class="detail-note-text">
                        Waiting time may change depending on clinic activity.
                      </div>
                    </div>
                  </div>
                </div>

                <div class="queue-progress-wrap">
                  <div class="queue-progress-meta">
                    <span>Queue Progress</span>
                    <span>{{ $entry->queue_number }} of {{ $entry->queue_number + $ahead }}</span>
                  </div>

                  <div class="progress">
                    <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                  </div>
                </div>

                <div class="queue-tip-box">
                  <h6 class="queue-tip-title">
                    <i class="bi bi-lightbulb"></i>
                    What this means
                  </h6>

                  <ul class="queue-tip-list">
                    <li>Your number is <strong>#{{ $entry->queue_number }}</strong>.</li>
                    <li>{{ $ahead }} {{ $ahead == 1 ? 'person is' : 'people are' }} currently ahead of you.</li>
                    @if($visitDate)
                      <li>This queue is marked as <strong>{{ $visitDateLabel }}</strong>, {{ $visitDate->format('M j, Y') }}.</li>
                    @endif
                    <li>The expected call time is only an estimate and may still change.</li>
                  </ul>
                </div>
              @else
                <div class="queue-message served">
                  <i class="bi bi-check-circle me-2"></i>
                  <strong>Your queue is complete.</strong>
                  You were served at {{ $entry->formatted_served_time }}.
                </div>
              @endif

              <div class="queue-actions">
                @if($entry->status === 'waiting')
                  <form method="POST"
                        action="{{ route('queue.leave', $entry) }}"
                        data-confirm="Leave this queue? Your spot will be forfeited."
                        data-confirm-title="Leave Queue"
                        data-confirm-btn="Leave">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                      <i class="bi bi-x-circle me-2"></i>
                      Leave Queue
                    </button>
                  </form>
                @endif

                <a href="{{ route('clinics.index') }}" class="btn btn-outline-primary">
                  <i class="bi bi-building me-2"></i>
                  Find a Clinic
                </a>
              </div>
            </article>

          @elseif(isset($userQueues) && $userQueues->count() > 0)
            <section class="queue-list-section">
              <div class="queue-list-header">
                <h4 class="queue-list-title">
                  <i class="bi bi-people-fill"></i>
                  Your Active Queues
                </h4>

                <span class="queue-count-pill">
                  {{ $userQueues->count() }} Active
                </span>
              </div>

              <div class="queue-card-grid">
                @foreach($userQueues->sortBy(fn($q) => optional($q->appointment)->appointment_date ?? now()->addYears(10)) as $queueEntry)
                  @php
                    $miniVisitDate = optional($queueEntry->appointment)->appointment_date
                      ? \Carbon\Carbon::parse($queueEntry->appointment->appointment_date)->startOfDay()
                      : null;

                    $miniVisitDateLabel = 'No date';
                    $miniVisitDateClass = 'later';

                    if ($miniVisitDate) {
                      if ($miniVisitDate->isSameDay(now())) {
                        $miniVisitDateLabel = 'Today';
                        $miniVisitDateClass = 'today';
                      } elseif ($miniVisitDate->isSameDay(now()->copy()->addDay())) {
                        $miniVisitDateLabel = 'Tomorrow';
                        $miniVisitDateClass = 'tomorrow';
                      } elseif ($miniVisitDate->betweenIncluded(now()->startOfDay(), now()->copy()->endOfWeek())) {
                        $miniVisitDateLabel = 'This Week';
                        $miniVisitDateClass = 'this-week';
                      } elseif ($miniVisitDate->betweenIncluded(now()->copy()->addWeek()->startOfWeek(), now()->copy()->addWeek()->endOfWeek())) {
                        $miniVisitDateLabel = 'Next Week';
                        $miniVisitDateClass = 'next-week';
                      } else {
                        $miniVisitDateLabel = $miniVisitDate->isPast() ? 'Past Date' : 'Later';
                        $miniVisitDateClass = 'later';
                      }
                    }
                  @endphp

                  <article class="queue-mini-card">
                    <div class="queue-mini-top">
                      <div class="queue-mini-icon">
                        <i class="bi bi-hospital"></i>
                      </div>

                      <div>
                        <h5 class="queue-mini-clinic">{{ $queueEntry->clinic->name }}</h5>
                        <div class="queue-mini-sub">Currently waiting</div>
                      </div>
                    </div>

                    <div class="queue-mini-number">Queue #{{ $queueEntry->queue_number }}</div>

                    @if($miniVisitDate)
                      <div class="mb-2">
                        <span class="visit-date-pill {{ $miniVisitDateClass }}">
                          <i class="bi bi-calendar-event"></i>
                          {{ $miniVisitDateLabel }}
                        </span>

                        <div class="visit-date-detail">
                          {{ $miniVisitDate->format('M j, Y') }} · {{ $miniVisitDate->format('l') }}
                        </div>
                      </div>
                    @endif

                    <div class="queue-mini-status">
                      <i class="bi bi-clock-fill"></i>
                      Waiting
                    </div>

                    <div class="queue-mini-footer">
                      <i class="bi bi-info-circle me-1"></i>
                      Tap “Check My Status” to see people ahead and estimated wait time.
                    </div>

                    <a href="{{ route('queue.status.entry', $queueEntry) }}"
                       class="btn btn-outline-primary w-100 mt-3">
                      <i class="bi bi-eye me-1"></i>
                      Check My Status
                    </a>
                  </article>
                @endforeach
              </div>
            </section>

          @else
            <section class="empty-state">
              <div class="empty-icon">
                <i class="bi bi-check-circle"></i>
              </div>

              <h5 class="empty-title">No Active Queue</h5>
              <p class="empty-text">
                You are not currently waiting in any clinic queue.
              </p>

              <div class="empty-actions">
                <a href="{{ route('clinics.index') }}" class="btn btn-primary">
                  <i class="bi bi-building me-2"></i>
                  Find a Clinic
                </a>
              </div>
            </section>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function handleQueueBack() {
  if (document.referrer && document.referrer !== window.location.href) {
    history.back();
  } else {
    window.location.href = '{{ route('queue.status') }}';
  }
}
</script>
@endpush