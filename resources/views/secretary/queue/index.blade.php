@extends('layouts.app')

@section('title', "Queue — {$clinic->name}")

@section('content')
@php
  $today = now()->toDateString();

  $activeStatuses = [
      'waiting',
      'called',
      'in_progress',
      'now_serving',
      'served',
  ];

  $waiting = $waiting->filter(function ($queueEntry) use ($today, $activeStatuses) {
      if (! in_array($queueEntry->status, $activeStatuses, true)) {
          return false;
      }

      $queueDate = $queueEntry->queue_date
          ?? optional($queueEntry->appointment)->appointment_date
          ?? $queueEntry->scheduled_slot_date
          ?? $queueEntry->created_at
          ?? null;

      if (! $queueDate) {
          return false;
      }

      return \Carbon\Carbon::parse($queueDate)->toDateString() === $today;
  })->values();

  $waitingCount = $waiting->count();
  $servedCount = $waiting->where('status', 'served')->count();
  $nowServingCount = $waiting->whereIn('status', ['in_progress', 'now_serving'])->count();
  $waitingOnlyCount = $waiting->whereIn('status', ['waiting', 'called'])->count();

  $currentServed = $waiting->firstWhere('status', 'served');

  $currentInProgress = $waiting->first(function ($queueEntry) {
      return in_array($queueEntry->status, ['in_progress', 'now_serving'], true);
  });
@endphp

<style>
  .queue-page {
    width: 96%;
    max-width: none;
    margin: 0 auto;
    padding: 0.5rem 0 1.5rem;
  }

  .queue-hero {
    border-radius: 24px;
    padding: 1.45rem;
    color: #fff;
    background:
      radial-gradient(circle at 90% 25%, rgba(255,255,255,.16), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37, 99, 235, .22);
    margin-bottom: 1rem;
  }

  .queue-hero-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .queue-title-wrap {
    display: flex;
    gap: .85rem;
    align-items: flex-start;
  }

  .queue-title-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    font-size: 1.55rem;
    flex: 0 0 58px;
  }

  .queue-title {
    margin: 0;
    font-size: clamp(1.5rem, 2.4vw, 2.1rem);
    font-weight: 900;
    letter-spacing: -0.045em;
  }

  .queue-subtitle {
    margin: .3rem 0 0;
    font-size: .95rem;
    font-weight: 650;
    opacity: .94;
  }

  .queue-hero-pills {
    display: flex;
    gap: .55rem;
    flex-wrap: wrap;
  }

  .queue-hero-pill {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    border-radius: 999px;
    padding: .5rem .75rem;
    background: rgba(255,255,255,.16);
    border: 1px solid rgba(255,255,255,.22);
    color: #fff;
    font-size: .78rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .queue-current-alert {
    border-radius: 22px;
    padding: 1rem 1.15rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    box-shadow: 0 12px 30px rgba(15,23,42,.06);
  }

  .queue-current-alert.served {
    border: 1px solid #bbf7d0;
    background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
  }

  .queue-current-alert.progress {
    border: 1px solid #bfdbfe;
    background: linear-gradient(135deg, #eff6ff, #f8fbff);
  }

  .queue-current-title {
    font-weight: 900;
    margin: 0;
  }

  .queue-current-alert.served .queue-current-title {
    color: #14532d;
  }

  .queue-current-alert.progress .queue-current-title {
    color: #1d4ed8;
  }

  .queue-current-text {
    margin: .15rem 0 0;
    font-size: .88rem;
    font-weight: 650;
  }

  .queue-current-alert.served .queue-current-text {
    color: #166534;
  }

  .queue-current-alert.progress .queue-current-text {
    color: #1e40af;
  }

  .queue-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .queue-stat-card {
    min-height: 140px;
    border-radius: 22px;
    padding: 1rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 14px 34px rgba(15,23,42,.08);
  }

  .queue-stat-card::after {
    content: "";
    position: absolute;
    right: -24px;
    bottom: -24px;
    width: 112px;
    height: 112px;
    border-radius: 36px;
    background: rgba(255,255,255,.14);
    transform: rotate(4deg);
  }

  .queue-stat-blue { background: linear-gradient(135deg, #0866f2, #2993ff); }
  .queue-stat-yellow { background: linear-gradient(135deg, #ffd85a, #ffc107); color: #162033; }
  .queue-stat-green { background: linear-gradient(135deg, #087b3d, #2bbf6a); }
  .queue-stat-purple { background: linear-gradient(135deg, #6d28d9, #8b5cf6); }

  .queue-stat-content {
    position: relative;
    z-index: 2;
    display: flex;
    gap: .85rem;
    align-items: flex-start;
  }

  .queue-stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 17px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    font-size: 1.45rem;
    flex: 0 0 52px;
  }

  .queue-stat-value {
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -.055em;
    margin-bottom: .4rem;
  }

  .queue-stat-label {
    font-size: .88rem;
    font-weight: 900;
  }

  .queue-stat-help {
    margin-top: .15rem;
    font-size: .78rem;
    font-weight: 650;
    opacity: .9;
  }

  .queue-panel {
    border-radius: 24px;
    border: 1px solid rgba(226,232,240,.96);
    background: rgba(255,255,255,.94);
    box-shadow: 0 18px 45px rgba(15,23,42,.08);
    overflow: hidden;
  }

  .queue-panel-head {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
  }

  .queue-panel-title {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 900;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: .5rem;
  }

  .queue-panel-title i {
    color: #0d6efd;
  }

  .queue-pill {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border-radius: 999px;
    padding: .42rem .72rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: .76rem;
    font-weight: 900;
  }

  .queue-table {
    margin: 0;
  }

  .queue-table thead th {
    background: #f8fafc !important;
    color: #475569;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 1rem;
  }

  .queue-table tbody td {
    padding: 1rem;
    vertical-align: middle;
  }

  .queue-number-badge {
    width: 54px;
    height: 54px;
    border-radius: 17px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #0d6efd;
    font-weight: 900;
    font-size: 1rem;
  }

  .queue-patient-name {
    font-weight: 900;
    color: #0f172a;
  }

  .queue-contact {
    color: #64748b;
    font-size: .8rem;
    font-weight: 650;
    margin-top: .15rem;
  }

  .queue-type {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    border-radius: 999px;
    padding: .35rem .6rem;
    background: #ecfdf5;
    color: #047857;
    font-size: .72rem;
    font-weight: 900;
    margin-top: .4rem;
  }

  .queue-action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
  }

  .queue-action-buttons .btn {
    border-radius: 12px;
    font-weight: 850;
  }

  .queue-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
  }

  .queue-empty-icon {
    width: 76px;
    height: 76px;
    margin: 0 auto 1rem;
    border-radius: 24px;
    display: grid;
    place-items: center;
    background: #ecfdf5;
    color: #16a34a;
    font-size: 2.2rem;
  }

  .queue-cards {
    display: none;
    padding: 1rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .queue-card {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    background: #fff;
    padding: 1rem;
    box-shadow: 0 10px 26px rgba(15,23,42,.045);
  }

  .queue-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: .75rem;
    margin-bottom: .85rem;
  }

  .resched-modal .modal-content {
    border: 0;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(15,23,42,.18);
  }

  .resched-modal .modal-header {
    background: linear-gradient(135deg, #0d6efd, #178bff);
    color: #fff;
  }

  .resched-modal .btn-close {
    filter: invert(1);
  }

  @media (max-width: 992px) {
    .queue-stats-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .queue-table-wrap {
      display: none;
    }

    .queue-cards {
      display: grid;
    }
  }

  @media (max-width: 768px) {
    .queue-page {
      width: 100%;
    }

    .queue-hero {
      padding: 1rem;
      border-radius: 22px;
    }

    .queue-stats-grid {
      grid-template-columns: 1fr;
    }

    .queue-cards {
      grid-template-columns: 1fr;
      padding: .85rem;
    }

    .queue-action-buttons,
    .queue-action-buttons form,
    .queue-action-buttons .btn {
      width: 100%;
    }
  }
</style>

<div class="queue-page">
  <section class="queue-hero">
    <div class="queue-hero-row">
      <div class="queue-title-wrap">
        <div class="queue-title-icon">
          <i class="bi bi-clock-history"></i>
        </div>

        <div>
          <h1 class="queue-title">Queue Management</h1>
          <p class="queue-subtitle">
            {{ $clinic->name }} — {{ $clinic->address }}
          </p>
        </div>
      </div>

      <div class="queue-hero-pills">
        <span class="queue-hero-pill">
          <i class="bi bi-building"></i>
          {{ $clinic->name }}
        </span>
        <span class="queue-hero-pill">
          <i class="bi bi-people"></i>
          {{ $waitingCount }} active
        </span>
      </div>
    </div>
  </section>

  @if($currentServed)
    <section class="queue-current-alert served">
      <div>
        <h5 class="queue-current-title">
          <i class="bi bi-check-circle me-1"></i>
          Patient is served
        </h5>
        <p class="queue-current-text">
          {{ $currentServed->display_name }} is done with the doctor. You can now click Done &amp; Next.
        </p>
      </div>

      <form method="POST" action="{{ route('secretary.queue.done_next', [$clinic, $currentServed]) }}">
        @csrf
        <button class="btn btn-success fw-bold rounded-pill px-4">
          <i class="bi bi-check2-circle me-1"></i>
          Done &amp; Next
        </button>
      </form>
    </section>
  @elseif($currentInProgress)
    <section class="queue-current-alert progress">
      <div>
        <h5 class="queue-current-title">
          <i class="bi bi-activity me-1"></i>
          Patient is with the doctor
        </h5>
        <p class="queue-current-text">
          {{ $currentInProgress->display_name }} is still in progress. Done &amp; Next will be available after the doctor clicks Serve.
        </p>
      </div>

      <button type="button" class="btn btn-success fw-bold rounded-pill px-4" disabled>
        <i class="bi bi-check2-circle me-1"></i>
        Done &amp; Next
      </button>
    </section>
  @endif

  <section class="queue-stats-grid">
    <div class="queue-stat-card queue-stat-blue">
      <div class="queue-stat-content">
        <div class="queue-stat-icon">
          <i class="bi bi-people"></i>
        </div>
        <div>
          <div class="queue-stat-value">{{ number_format($waitingCount) }}</div>
          <div class="queue-stat-label">Active Entries</div>
          <div class="queue-stat-help">Today's queue records</div>
        </div>
      </div>
    </div>

    <div class="queue-stat-card queue-stat-yellow">
      <div class="queue-stat-content">
        <div class="queue-stat-icon">
          <i class="bi bi-hourglass-split"></i>
        </div>
        <div>
          <div class="queue-stat-value">{{ number_format($waitingOnlyCount) }}</div>
          <div class="queue-stat-label">Waiting</div>
          <div class="queue-stat-help">Ready to be called</div>
        </div>
      </div>
    </div>

    <div class="queue-stat-card queue-stat-green">
      <div class="queue-stat-content">
        <div class="queue-stat-icon">
          <i class="bi bi-activity"></i>
        </div>
        <div>
          <div class="queue-stat-value">{{ number_format($nowServingCount) }}</div>
          <div class="queue-stat-label">With Doctor</div>
          <div class="queue-stat-help">Currently in progress</div>
        </div>
      </div>
    </div>

    <div class="queue-stat-card queue-stat-purple">
      <div class="queue-stat-content">
        <div class="queue-stat-icon">
          <i class="bi bi-check2-circle"></i>
        </div>
        <div>
          <div class="queue-stat-value">{{ number_format($servedCount) }}</div>
          <div class="queue-stat-label">Served</div>
          <div class="queue-stat-help">Needs Done &amp; Next</div>
        </div>
      </div>
    </div>
  </section>

  <section class="queue-panel">
    <div class="queue-panel-head">
      <h2 class="queue-panel-title">
        <i class="bi bi-list-ol"></i>
        Today's Active Queue
      </h2>

      <span class="queue-pill">
        <i class="bi bi-clock"></i>
        {{ $waitingCount }} today
      </span>
    </div>

    @if($waiting->isEmpty())
      <div class="queue-empty">
        <div class="queue-empty-icon">
          <i class="bi bi-check-circle"></i>
        </div>
        <h5 class="fw-bold text-success">Queue is Empty</h5>
        <p class="mb-0">No queue entries for today.</p>
      </div>
    @else
      <div class="queue-table-wrap table-responsive">
        <table class="table queue-table table-hover">
          <thead>
            <tr>
              <th>Queue #</th>
              <th>Patient</th>
              <th>Appointment</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            @foreach($waiting as $queueEntry)
              @php
                $status = $queueEntry->status;
              @endphp

              <tr>
                <td>
                  <div class="queue-number-badge">
                    #{{ $queueEntry->queue_number }}
                  </div>
                </td>

                <td>
                  <div class="queue-patient-name">{{ $queueEntry->display_name }}</div>

                  @if($queueEntry->display_email)
                    <div class="queue-contact">
                      <i class="bi bi-envelope me-1"></i>{{ $queueEntry->display_email }}
                    </div>
                  @endif

                  @if($queueEntry->display_phone)
                    <div class="queue-contact">
                      <i class="bi bi-telephone me-1"></i>{{ $queueEntry->display_phone }}
                    </div>
                  @endif

                  @if($queueEntry->is_walk_in)
                    <span class="queue-type">
                      <i class="bi bi-person-plus"></i>
                      Walk-In
                    </span>
                  @endif

                  @if($queueEntry->isPriority())
                    <span class="queue-type bg-warning text-dark">
                      <i class="bi bi-star-fill"></i>
                      Priority
                    </span>
                  @endif
                </td>

                <td>
                  @if($queueEntry->scheduled_slot_date || $queueEntry->formatted_scheduled_slot_time)
                    <div class="fw-bold">
                      {{ $queueEntry->scheduledSlotDateString() ? \Carbon\Carbon::parse($queueEntry->scheduledSlotDateString())->format('M j, Y') : 'Today' }}
                    </div>
                    <small class="text-muted">
                      {{ $queueEntry->formatted_scheduled_slot_time ?? 'Slot pending' }}
                    </small>
                  @elseif($queueEntry->appointment)
                    <div class="fw-bold">
                      {{ $queueEntry->appointment->appointment_date->format('M j, Y') }}
                    </div>
                    <small class="text-muted">
                      {{ $queueEntry->appointment->appointment_time }}
                    </small>
                  @else
                    <span class="queue-pill">Walk-in</span>
                  @endif
                </td>

                <td>
                  <span class="badge bg-{{ $queueEntry->status_badge_class }}">
                    {{ $queueEntry->status_label }}
                  </span>
                </td>

                <td>
                  <div class="queue-action-buttons">
                    @if(in_array($status, ['waiting', 'called'], true))
                      <form method="POST" action="{{ route('secretary.queue.call', [$clinic, $queueEntry]) }}">
                        @csrf
                        <button class="btn btn-sm btn-primary">
                          <i class="bi bi-megaphone me-1"></i>Call
                        </button>
                      </form>
                    @elseif(in_array($status, ['in_progress', 'now_serving'], true))
                      <span class="badge bg-primary fs-6 px-3 py-2">
                        <i class="bi bi-activity me-1"></i>With Doctor
                      </span>
                    @elseif($status === 'served')
                      <form method="POST" action="{{ route('secretary.queue.done_next', [$clinic, $queueEntry]) }}">
                        @csrf
                        <button class="btn btn-sm btn-success">
                          <i class="bi bi-check-circle me-1"></i>Done &amp; Next
                        </button>
                      </form>
                    @endif

                    @if(in_array($status, ['waiting', 'called', 'in_progress', 'now_serving'], true))
                      <form method="POST"
                            action="{{ route('secretary.queue.no_show', [$clinic, $queueEntry]) }}"
                            data-confirm="Mark this patient as NO-SHOW? They will be removed from the queue."
                            data-confirm-title="Mark As No-Show"
                            data-confirm-btn="Mark No-Show">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary">
                          <i class="bi bi-person-x me-1"></i>No-Show
                        </button>
                      </form>
                    @endif

                    @if(in_array($status, ['waiting', 'called'], true))
                      @unless($queueEntry->isPriority())
                        <form method="POST"
                              action="{{ route('secretary.queue.priority', [$clinic, $queueEntry]) }}"
                              data-confirm="Mark this patient as priority and update queue slots?"
                              data-confirm-title="Mark As Priority"
                              data-confirm-btn="Mark Priority">
                          @csrf
                          <button class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-star-fill me-1"></i>Priority
                          </button>
                        </form>
                      @endunless

                      <button type="button"
                              class="btn btn-sm btn-warning"
                              data-bs-toggle="modal"
                              data-bs-target="#reschedModal"
                              data-action-url="{{ route('secretary.queue.reschedule', [$clinic, $queueEntry]) }}">
                        <i class="bi bi-calendar-event me-1"></i>Resched
                      </button>

                      <form method="POST"
                            action="{{ route('secretary.queue.cancel', [$clinic, $queueEntry]) }}"
                            data-confirm="Cancel this queue entry? The patient will be notified."
                            data-confirm-title="Cancel Queue Entry"
                            data-confirm-btn="Cancel">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger">
                          <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="queue-cards">
        @foreach($waiting as $queueEntry)
          @php
            $status = $queueEntry->status;
          @endphp

          <article class="queue-card">
            <div class="queue-card-top">
              <div>
                <div class="queue-number-badge mb-2">
                  #{{ $queueEntry->queue_number }}
                </div>
                <div class="queue-patient-name">{{ $queueEntry->display_name }}</div>
              </div>

              <span class="badge bg-{{ $queueEntry->status_badge_class }}">
                {{ $queueEntry->status_label }}
              </span>
            </div>

            @if($queueEntry->isPriority())
              <span class="queue-type bg-warning text-dark mb-2">
                <i class="bi bi-star-fill"></i>
                Priority
              </span>
            @endif

            @if($queueEntry->display_email)
              <div class="queue-contact">
                <i class="bi bi-envelope me-1"></i>{{ $queueEntry->display_email }}
              </div>
            @endif

            @if($queueEntry->display_phone)
              <div class="queue-contact">
                <i class="bi bi-telephone me-1"></i>{{ $queueEntry->display_phone }}
              </div>
            @endif

            <div class="mt-3 mb-3">
              @if($queueEntry->scheduled_slot_date || $queueEntry->formatted_scheduled_slot_time)
                <div class="fw-bold">
                  {{ $queueEntry->scheduledSlotDateString() ? \Carbon\Carbon::parse($queueEntry->scheduledSlotDateString())->format('M j, Y') : 'Today' }}
                </div>
                <small class="text-muted">
                  {{ $queueEntry->formatted_scheduled_slot_time ?? 'Slot pending' }}
                </small>
              @elseif($queueEntry->appointment)
                <div class="fw-bold">
                  {{ $queueEntry->appointment->appointment_date->format('M j, Y') }}
                </div>
                <small class="text-muted">
                  {{ $queueEntry->appointment->appointment_time }}
                </small>
              @else
                <span class="queue-pill">Walk-in</span>
              @endif
            </div>

            <div class="queue-action-buttons">
              @if(in_array($status, ['waiting', 'called'], true))
                <form method="POST" action="{{ route('secretary.queue.call', [$clinic, $queueEntry]) }}">
                  @csrf
                  <button class="btn btn-sm btn-primary">
                    <i class="bi bi-megaphone me-1"></i>Call
                  </button>
                </form>
              @elseif(in_array($status, ['in_progress', 'now_serving'], true))
                <span class="badge bg-primary fs-6 px-3 py-2">
                  <i class="bi bi-activity me-1"></i>With Doctor
                </span>
              @elseif($status === 'served')
                <form method="POST" action="{{ route('secretary.queue.done_next', [$clinic, $queueEntry]) }}">
                  @csrf
                  <button class="btn btn-sm btn-success">
                    <i class="bi bi-check-circle me-1"></i>Done &amp; Next
                  </button>
                </form>
              @endif

              @if(in_array($status, ['waiting', 'called', 'in_progress', 'now_serving'], true))
                <form method="POST"
                      action="{{ route('secretary.queue.no_show', [$clinic, $queueEntry]) }}"
                      data-confirm="Mark this patient as NO-SHOW?"
                      data-confirm-title="Mark As No-Show"
                      data-confirm-btn="Mark No-Show">
                  @csrf
                  <button class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-person-x me-1"></i>No-Show
                  </button>
                </form>
              @endif

              @if(in_array($status, ['waiting', 'called'], true))
                @unless($queueEntry->isPriority())
                  <form method="POST"
                        action="{{ route('secretary.queue.priority', [$clinic, $queueEntry]) }}"
                        data-confirm="Mark this patient as priority and update queue slots?"
                        data-confirm-title="Mark As Priority"
                        data-confirm-btn="Mark Priority">
                    @csrf
                    <button class="btn btn-sm btn-outline-warning">
                      <i class="bi bi-star-fill me-1"></i>Priority
                    </button>
                  </form>
                @endunless

                <button type="button"
                        class="btn btn-sm btn-warning"
                        data-bs-toggle="modal"
                        data-bs-target="#reschedModal"
                        data-action-url="{{ route('secretary.queue.reschedule', [$clinic, $queueEntry]) }}">
                  <i class="bi bi-calendar-event me-1"></i>Resched
                </button>

                <form method="POST"
                      action="{{ route('secretary.queue.cancel', [$clinic, $queueEntry]) }}"
                      data-confirm="Cancel this queue entry?"
                      data-confirm-title="Cancel Queue Entry"
                      data-confirm-btn="Cancel">
                  @csrf
                  <button class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                  </button>
                </form>
              @endif
            </div>
          </article>
        @endforeach
      </div>
    @endif
  </section>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const reschedModalEl = document.getElementById('reschedModal');

  if (!reschedModalEl) return;

  const reschedModal = new bootstrap.Modal(reschedModalEl);

  document.querySelectorAll('[data-bs-target="#reschedModal"][data-action-url]').forEach(btn => {
    btn.addEventListener('click', () => {
      const url = btn.getAttribute('data-action-url');

      if (url) {
        try {
          localStorage.setItem('lastReschedActionUrl', url);
        } catch(e) {}
      }
    });
  });

  reschedModalEl.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;

    if (!button) return;

    const actionUrl = button.getAttribute('data-action-url');
    const form = reschedModalEl.querySelector('form');

    if (form && actionUrl) {
      form.setAttribute('action', actionUrl);
    }
  });

  const hasErrors = reschedModalEl.querySelector('.text-danger');

  if (hasErrors) {
    let stored = null;

    try {
      stored = localStorage.getItem('lastReschedActionUrl');
    } catch(e) {}

    const form = reschedModalEl.querySelector('form');

    if (stored && form) {
      form.setAttribute('action', stored);
    }

    reschedModal.show();
  }

  if (typeof Echo !== 'undefined' && typeof Swal !== 'undefined') {
    Echo.private('user.notifications.{{ auth()->id() }}')
      .listen('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', (e) => {
        if(e.notification.role === 'secretary') {
          Swal.fire({
            title: 'Update',
            text: e.notification.message,
            icon: 'info',
            confirmButtonText: 'OK'
          });
        }
      });
  }
});
</script>
@endpush
