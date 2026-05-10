@extends('layouts.app')

@section('title', 'Queues')

@section('content')
@php
  $today = now()->toDateString();

  /*
  |--------------------------------------------------------------------------
  | Active queue statuses
  |--------------------------------------------------------------------------
  |
  | IMPORTANT:
  | "served" must be active here because after doctor clicks Serve,
  | the secretary still needs to see the patient and click Done & Next.
  |
  */
  $activeStatuses = [
      'waiting',
      'called',
      'in_progress',
      'now_serving',
      'served',
  ];

  $waitingStatuses = [
      'waiting',
      'called',
  ];

  $withDoctorStatuses = [
      'in_progress',
      'now_serving',
  ];

  $isTodayQueueEntry = function ($entry, array $statuses = null) use ($today) {
      if ($statuses !== null && ! in_array($entry->status, $statuses, true)) {
          return false;
      }

      $queueDate = $entry->queue_date
          ?? optional($entry->appointment)->appointment_date
          ?? $entry->scheduled_slot_date
          ?? $entry->created_at
          ?? null;

      if (! $queueDate) {
          return false;
      }

      return \Carbon\Carbon::parse($queueDate)->toDateString() === $today;
  };

  $clinics = $clinics->map(function ($clinic) use ($isTodayQueueEntry, $activeStatuses, $waitingStatuses, $withDoctorStatuses) {
      $todayQueueEntries = $clinic->queueEntries
          ->filter(fn($entry) => $isTodayQueueEntry($entry, $activeStatuses))
          ->values();

      $clinic->setRelation('queueEntries', $todayQueueEntries);

      $clinic->active_count = $todayQueueEntries->count();
      $clinic->waiting_count = $todayQueueEntries->whereIn('status', $waitingStatuses)->count();
      $clinic->with_doctor_count = $todayQueueEntries->whereIn('status', $withDoctorStatuses)->count();
      $clinic->served_pending_count = $todayQueueEntries->where('status', 'served')->count();

      return $clinic;
  });

  $activeClinics = $clinics->filter(fn($c) => $c->active_count > 0);

  $totalActive = $activeClinics->sum(fn($c) => $c->active_count);
  $totalWaiting = $activeClinics->sum(fn($c) => $c->waiting_count);
  $totalWithDoctor = $activeClinics->sum(fn($c) => $c->with_doctor_count);
  $totalServedPending = $activeClinics->sum(fn($c) => $c->served_pending_count);
@endphp

<style>
  .queue-overview-page {
    width: 96%;
    max-width: none;
    margin: 0 auto;
    padding: 0.5rem 0 1.5rem;
  }

  .overview-hero {
    border-radius: 24px;
    padding: 1.45rem;
    color: #fff;
    background:
      radial-gradient(circle at 90% 25%, rgba(255,255,255,.16), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37,99,235,.22);
    margin-bottom: 1rem;
  }

  .overview-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .overview-title-wrap {
    display: flex;
    gap: .85rem;
    align-items: flex-start;
  }

  .overview-title-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    font-size: 1.55rem;
    flex: 0 0 58px;
  }

  .overview-title {
    margin: 0;
    font-size: clamp(1.5rem, 2.4vw, 2.1rem);
    font-weight: 900;
    letter-spacing: -.045em;
  }

  .overview-subtitle {
    margin: .3rem 0 0;
    font-size: .95rem;
    font-weight: 650;
    opacity: .94;
  }

  .overview-hero .btn {
    border-radius: 14px;
    font-weight: 900;
  }

  .overview-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .overview-stat-card {
    min-height: 145px;
    border-radius: 22px;
    padding: 1rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 14px 34px rgba(15,23,42,.08);
  }

  .overview-stat-card::after {
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

  .overview-stat-blue {
    background: linear-gradient(135deg, #0866f2, #2993ff);
  }

  .overview-stat-yellow {
    background: linear-gradient(135deg, #ffd85a, #ffc107);
    color: #162033;
  }

  .overview-stat-green {
    background: linear-gradient(135deg, #087b3d, #2bbf6a);
  }

  .overview-stat-purple {
    background: linear-gradient(135deg, #6d28d9, #8b5cf6);
  }

  .overview-stat-content {
    position: relative;
    z-index: 2;
    display: flex;
    gap: .85rem;
    align-items: flex-start;
  }

  .overview-stat-icon {
    width: 54px;
    height: 54px;
    border-radius: 17px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    font-size: 1.45rem;
    flex: 0 0 54px;
  }

  .overview-stat-value {
    font-size: 2.1rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -.055em;
    margin-bottom: .4rem;
  }

  .overview-stat-label {
    font-size: .9rem;
    font-weight: 900;
  }

  .overview-stat-help {
    font-size: .78rem;
    font-weight: 700;
    opacity: .85;
    margin-top: .2rem;
  }

  .overview-panel {
    border-radius: 24px;
    border: 1px solid rgba(226,232,240,.96);
    background: rgba(255,255,255,.94);
    box-shadow: 0 18px 45px rgba(15,23,42,.08);
    overflow: hidden;
  }

  .overview-panel-head {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
  }

  .overview-panel-title {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 900;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: .5rem;
  }

  .overview-panel-title i {
    color: #0d6efd;
  }

  .recent-box {
    padding: 1rem 1.2rem 0;
  }

  .recent-alert {
    border-radius: 18px;
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    color: #1e3a8a;
    padding: .9rem;
  }

  .clinic-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
    padding: 1.2rem;
  }

  .clinic-queue-card {
    border: 1px solid #e2e8f0;
    border-radius: 22px;
    background: #fff;
    padding: 1rem;
    box-shadow: 0 10px 26px rgba(15,23,42,.045);
    height: 100%;
  }

  .clinic-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: .75rem;
    margin-bottom: .9rem;
  }

  .clinic-icon {
    width: 50px;
    height: 50px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #0d6efd;
    font-size: 1.35rem;
  }

  .clinic-name {
    margin: 0;
    color: #0f172a;
    font-weight: 900;
    letter-spacing: -.025em;
  }

  .queue-badge {
    border-radius: 999px;
    padding: .4rem .65rem;
    background: #fff7db;
    color: #8a6300;
    border: 1px solid #ffe7a2;
    font-size: .74rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .queue-badge-served {
    background: #dcfce7;
    color: #166534;
    border-color: #bbf7d0;
  }

  .queue-preview {
    border-radius: 18px;
    background: #f8fafc;
    border: 1px solid #edf2f7;
    padding: .8rem;
    margin-bottom: .9rem;
  }

  .queue-preview-title {
    font-weight: 900;
    color: #0f172a;
    margin-bottom: .55rem;
  }

  .queue-preview-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .65rem;
    padding: .55rem 0;
    border-top: 1px solid #e2e8f0;
  }

  .queue-preview-item:first-of-type {
    border-top: 0;
  }

  .queue-num {
    color: #0d6efd;
    font-weight: 900;
  }

  .queue-status-pill {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    border-radius: 999px;
    padding: .25rem .5rem;
    font-size: .7rem;
    font-weight: 900;
    margin-top: .25rem;
  }

  .queue-status-waiting,
  .queue-status-called {
    background: #fff7db;
    color: #8a6300;
  }

  .queue-status-in_progress,
  .queue-status-now_serving {
    background: #dbeafe;
    color: #1d4ed8;
  }

  .queue-status-served {
    background: #dcfce7;
    color: #166534;
  }

  .empty-overview {
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
  }

  .empty-overview-icon {
    width: 78px;
    height: 78px;
    margin: 0 auto 1rem;
    border-radius: 24px;
    display: grid;
    place-items: center;
    background: #ecfdf5;
    color: #16a34a;
    font-size: 2.25rem;
  }

  @media (max-width: 1200px) {
    .overview-stats-grid,
    .clinic-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 768px) {
    .queue-overview-page {
      width: 100%;
    }

    .overview-hero {
      padding: 1rem;
      border-radius: 22px;
    }

    .overview-stats-grid,
    .clinic-grid {
      grid-template-columns: 1fr;
    }

    .clinic-grid {
      padding: .85rem;
    }
  }
</style>

<div class="queue-overview-page">
  <section class="overview-hero">
    <div class="overview-hero-row">
      <div class="overview-title-wrap">
        <div class="overview-title-icon">
          <i class="bi bi-people-fill"></i>
        </div>

        <div>
          <h1 class="overview-title">Queue Overview</h1>
          <p class="overview-subtitle">
            Monitor today's active queues across your assigned clinics. Served patients stay visible until Done &amp; Next is clicked.
          </p>
        </div>
      </div>

      <a href="{{ Route::has('secretary.appointments.index') ? route('secretary.appointments.index') : url('/secretary/dashboard') }}"
         class="btn btn-light text-primary">
        <i class="bi bi-arrow-left me-2"></i>
        Back to Appointments
      </a>
    </div>
  </section>

  <section class="overview-stats-grid">
    <div class="overview-stat-card overview-stat-blue">
      <div class="overview-stat-content">
        <div class="overview-stat-icon">
          <i class="bi bi-list-check"></i>
        </div>
        <div>
          <div class="overview-stat-value">{{ number_format($totalActive) }}</div>
          <div class="overview-stat-label">Active Today</div>
          <div class="overview-stat-help">Waiting, with doctor, and served</div>
        </div>
      </div>
    </div>

    <div class="overview-stat-card overview-stat-yellow">
      <div class="overview-stat-content">
        <div class="overview-stat-icon">
          <i class="bi bi-hourglass-split"></i>
        </div>
        <div>
          <div class="overview-stat-value">{{ number_format($totalWaiting) }}</div>
          <div class="overview-stat-label">Waiting / Called</div>
          <div class="overview-stat-help">Ready to be processed</div>
        </div>
      </div>
    </div>

    <div class="overview-stat-card overview-stat-green">
      <div class="overview-stat-content">
        <div class="overview-stat-icon">
          <i class="bi bi-activity"></i>
        </div>
        <div>
          <div class="overview-stat-value">{{ number_format($totalWithDoctor) }}</div>
          <div class="overview-stat-label">With Doctor</div>
          <div class="overview-stat-help">Currently in progress</div>
        </div>
      </div>
    </div>

    <div class="overview-stat-card overview-stat-purple">
      <div class="overview-stat-content">
        <div class="overview-stat-icon">
          <i class="bi bi-check2-circle"></i>
        </div>
        <div>
          <div class="overview-stat-value">{{ number_format($totalServedPending) }}</div>
          <div class="overview-stat-label">Served Pending</div>
          <div class="overview-stat-help">Needs Done &amp; Next</div>
        </div>
      </div>
    </div>
  </section>

  <section class="overview-panel">
    <div class="overview-panel-head">
      <h2 class="overview-panel-title">
        <i class="bi bi-building"></i>
        Today's Clinic Queues
      </h2>
    </div>

    @php
      $recent = auth()->user()->notifications()
        ->where('type', \App\Notifications\DoctorServedQueue::class)
        ->latest()
        ->take(3)
        ->get();
    @endphp

    @if($recent->count())
      <div class="recent-box">
        <div class="recent-alert">
          <strong>
            <i class="bi bi-bell me-1"></i>
            Recent Activity:
          </strong>

          <ul class="small mb-0 mt-2">
            @foreach($recent as $n)
              <li>
                {{ $n->data['message'] ?? 'A patient was served by the doctor.' }}
                <span class="text-muted">{{ $n->created_at->diffForHumans() }}</span>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif

    @if($activeClinics->count() > 0)
      <div class="clinic-grid">
        @foreach($activeClinics as $clinic)
          <article class="clinic-queue-card">
            <div class="clinic-card-top">
              <div class="d-flex gap-3 align-items-start">
                <div class="clinic-icon">
                  <i class="bi bi-hospital"></i>
                </div>

                <div>
                  <h5 class="clinic-name">{{ $clinic->name ?? 'Clinic' }}</h5>
                  <small class="text-muted">{{ $clinic->address ?? 'No address listed' }}</small>
                </div>
              </div>

              <div class="d-flex flex-column gap-2 align-items-end">
                <span class="queue-badge">
                  {{ $clinic->active_count }} active
                </span>

                @if($clinic->served_pending_count > 0)
                  <span class="queue-badge queue-badge-served">
                    {{ $clinic->served_pending_count }} served
                  </span>
                @endif
              </div>
            </div>

            <div class="queue-preview">
              <div class="queue-preview-title">Today's Queue</div>

              @foreach($clinic->queueEntries->take(3) as $entry)
                <div class="queue-preview-item">
                  <div>
                    <div class="queue-num">#{{ $entry->queue_number }}</div>
                    <small class="text-muted">{{ $entry->display_name }}</small>

                    <div>
                      <span class="queue-status-pill queue-status-{{ $entry->status }}">
                        {{ $entry->status_label ?? ucfirst(str_replace('_', ' ', $entry->status)) }}
                      </span>
                    </div>
                  </div>

                  <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    {{ $entry->formatted_created_time }}
                  </small>
                </div>
              @endforeach

              @if($clinic->active_count > 3)
                <div class="text-center pt-2">
                  <small class="text-muted">
                    +{{ $clinic->active_count - 3 }} more
                  </small>
                </div>
              @endif
            </div>

            <div class="d-grid">
              <a href="{{ route('secretary.queue.index', $clinic) }}"
                 class="btn btn-primary btn-sm">
                <i class="bi bi-eye me-2"></i>
                Manage Queue
              </a>
            </div>
          </article>
        @endforeach
      </div>
    @else
      <div class="empty-overview">
        <div class="empty-overview-icon">
          <i class="bi bi-check-circle"></i>
        </div>

        <h5 class="text-success fw-bold">No Active Queues</h5>
        <p class="text-muted mb-0">No queue entries for today.</p>
      </div>
    @endif
  </section>
</div>
@endsection