@extends('layouts.patient-dashboard')

@section('title', 'My Appointments')

@push('styles')
<style>
  .patient-tab-shell,
  .patient-tab-content {
    width: 100%;
    max-width: none;
  }

  .appointments-page {
    width: 95%;
    max-width: none;
    padding: 1.5rem 0 2rem;
  }

  .appointments-shell {
    width: 100%;
    border-radius: 30px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: rgba(255, 255, 255, 0.92);
    box-shadow:
      0 24px 70px rgba(15, 23, 42, 0.10),
      inset 0 1px 0 rgba(255, 255, 255, 0.75);
    overflow: hidden;
  }

  .appointments-hero {
    padding: 2rem 2rem 1.25rem;
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.14), transparent 34%),
      linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(248, 251, 255, 0.92));
    border-bottom: 1px solid rgba(226, 232, 240, 0.9);
  }

  .appointments-hero-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
  }

  .appointments-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
  }

  .appointments-title-icon {
    width: 58px;
    height: 58px;
    flex: 0 0 58px;
    display: grid;
    place-items: center;
    border-radius: 18px;
    color: #ffffff;
    background: linear-gradient(135deg, #0d6efd, #1287ff);
    box-shadow: 0 16px 35px rgba(13, 110, 253, 0.28);
    font-size: 1.65rem;
  }

  .appointments-title {
    margin: 0;
    color: #071225;
    font-weight: 500;
    letter-spacing: -0.055em;
    font-size: 25;
    line-height: 1.05;
  }

  .appointments-subtitle {
    margin: 0.45rem 0 0;
    color: #64748b;
    font-size: 1.02rem;
    font-weight: 500;
  }

  .book-main-btn {
    border-radius: 16px;
    padding: 0.75rem 1.15rem;
    font-weight: 500;
    box-shadow: 0 14px 28px rgba(13, 110, 253, 0.22);
  }

  .appointments-body {
    padding: 1.5rem 2rem 2rem;
  }

  .section-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .section-heading {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin: 0;
    color: #0f172a;
    font-weight: 500;
    letter-spacing: -0.035em;
  }

  .section-heading i {
    color: #0d6efd;
  }

  .count-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 30px;
    padding: 0 0.7rem;
    border-radius: 999px;
    background: #eaf3ff;
    color: #0d6efd;
    font-size: 0.85rem;
    font-weight: 500;
  }

  .queue-panel {
    padding: 1.25rem;
    border-radius: 24px;
    border: 1px solid rgba(125, 211, 252, 0.7);
    background:
      linear-gradient(135deg, rgba(239, 249, 255, 0.95), rgba(224, 247, 255, 0.78));
    margin-bottom: 1.6rem;
  }

  .queue-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .queue-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem;
    min-height: 112px;
    border-radius: 22px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.07);
  }

  .queue-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    min-width: 0;
  }

  .queue-icon {
    width: 58px;
    height: 58px;
    flex: 0 0 58px;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: #e8f2ff;
    color: #0d6efd;
    font-size: 1.75rem;
  }

  .queue-title {
    margin: 0 0 0.2rem;
    color: #0f172a;
    font-weight: 500;
    font-size: 1.08rem;
  }

  .queue-meta {
    color: #334155;
    font-weight: 500;
    line-height: 1.5;
  }

  .queue-meta span {
    color: #0d6efd;
    font-weight: 500;
  }

  .queue-time {
    color: #64748b;
    font-size: 0.92rem;
    font-weight: 500;
  }

  .queue-btn {
    border-radius: 14px;
    padding: 0.6rem 1rem;
    font-weight: 500;
    white-space: nowrap;
  }

  .upcoming-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
  }

  .appointment-card {
    position: relative;
    padding: 1.25rem;
    border-radius: 24px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.07);
    overflow: hidden;
    transition: 0.22s ease;
  }

  .appointment-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 52px rgba(15, 23, 42, 0.11);
  }

  .appointment-card::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 7px;
    background: linear-gradient(180deg, #0d6efd, #49a4ff);
  }

  .appointment-card.pending::before {
    background: linear-gradient(180deg, #8b5cf6, #a78bfa);
  }

  .appointment-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.15rem;
  }

  .appointment-main {
    display: flex;
    align-items: center;
    gap: 1rem;
    min-width: 0;
  }

  .clinic-icon {
    width: 62px;
    height: 62px;
    flex: 0 0 62px;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: #e8f2ff;
    color: #0d6efd;
    font-size: 1.9rem;
  }

  .appointment-card.pending .clinic-icon {
    background: #f2eaff;
    color: #8b5cf6;
  }

  .clinic-name {
    margin: 0 0 0.2rem;
    color: #0f172a;
    font-size: 1.2rem;
    font-weight: 950;
    letter-spacing: -0.035em;
  }

  .service-name {
    color: #64748b;
    font-size: 0.96rem;
    font-weight: 600;
  }

  .status-badge-soft {
    border-radius: 999px;
    padding: 0.45rem 0.78rem;
    font-size: 0.8rem;
    font-weight: 500;
    white-space: nowrap;
  }

  .appointment-info-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
    padding: 1rem 0;
    margin-bottom: 1rem;
    border-top: 1px solid #edf2f7;
    border-bottom: 1px solid #edf2f7;
  }

  .info-item {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
    color: #334155;
  }

  .info-item i {
    color: #0d6efd;
    font-size: 1rem;
    margin-top: 0.12rem;
  }

  .info-label {
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 0.1rem;
  }

  .info-value {
    font-size: 0.92rem;
    font-weight: 500;
    color: #0f172a;
    line-height: 1.35;
  }

  .info-subvalue {
    font-size: 0.82rem;
    color: #64748b;
    font-weight: 500;
  }

  .appointment-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .queue-state {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-weight: 500;
    color: #0f9f6e;
  }

  .queue-state.pending-state {
    color: #8b5cf6;
  }

  .action-buttons {
    display: flex;
    gap: 0.65rem;
    flex-wrap: wrap;
  }

  .action-buttons .btn {
    border-radius: 14px;
    font-weight: 500;
  }

  .empty-state {
    padding: 2.4rem 1rem;
    text-align: center;
    border: 1px dashed rgba(13, 110, 253, 0.34);
    border-radius: 24px;
    background:
      linear-gradient(135deg, rgba(13, 110, 253, 0.07), rgba(255, 255, 255, 0.94));
    margin-bottom: 1.5rem;
  }

  .empty-icon {
    width: 78px;
    height: 78px;
    margin: 0 auto 1rem;
    display: grid;
    place-items: center;
    border-radius: 26px;
    background: #ffffff;
    color: #0d6efd;
    font-size: 2rem;
    box-shadow: 0 16px 38px rgba(13, 110, 253, 0.13);
  }

  .empty-title {
    margin: 0 0 0.35rem;
    color: #0f172a;
    font-weight: 500;
  }

  .empty-text {
    color: #64748b;
    margin-bottom: 1.1rem;
    font-weight: 500;
  }

  .past-accordion {
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 16px 38px rgba(15, 23, 42, 0.06);
  }

  .past-accordion .accordion-item {
    border: 0;
  }

  .past-accordion .accordion-button {
    padding: 1.15rem 1.35rem;
    background: #ffffff;
    box-shadow: none;
    color: #0f172a;
    font-weight: 500;
    font-size: 1.08rem;
    letter-spacing: -0.025em;
  }

  .past-accordion .accordion-button:not(.collapsed) {
    color: #0d6efd;
    background:
      linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(255, 255, 255, 1));
    box-shadow: none;
  }

  .past-accordion .accordion-button:focus {
    box-shadow: none;
  }

  .past-accordion .accordion-body {
    padding: 0 1.35rem 1.35rem;
  }

  .past-list {
    display: grid;
    gap: 0.75rem;
  }

  .past-item {
    display: grid;
    grid-template-columns: 1.35fr 1.25fr 1.25fr 1fr 0.9fr auto;
    gap: 0.75rem;
    align-items: center;
    padding: 0.95rem 1rem;
    border-radius: 18px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
  }

  .past-label {
    display: none;
    color: #64748b;
    font-size: 0.74rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .past-value {
    color: #0f172a;
    font-weight: 500;
    line-height: 1.35;
  }

  .reminder-box {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    margin-top: 1rem;
    padding: 1rem 1.15rem;
    border-radius: 20px;
    border: 1px solid rgba(191, 219, 254, 0.9);
    background: linear-gradient(135deg, rgba(239, 246, 255, 0.96), rgba(255, 255, 255, 0.9));
    color: #334155;
    font-weight: 500;
  }

  .reminder-box i {
    color: #0d6efd;
    font-size: 1.2rem;
  }

  .reminder-box strong {
    color: #0f172a;
    margin-right: 0.25rem;
  }

  @media (max-width: 1200px) {
    .queue-grid,
    .upcoming-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 992px) {
    .appointment-info-grid {
      grid-template-columns: 1fr;
    }

    .past-item {
      grid-template-columns: 1fr 1fr;
    }

    .past-label {
      display: block;
    }
  }

  @media (max-width: 768px) {
    .appointments-page {
      padding-top: 1rem;
    }

    .appointments-hero,
    .appointments-body {
      padding-left: 1rem;
      padding-right: 1rem;
    }

    .appointments-hero-row,
    .appointment-top,
    .queue-card {
      flex-direction: column;
      align-items: stretch;
    }

    .appointments-title-wrap,
    .appointment-main,
    .queue-left {
      align-items: flex-start;
    }

    .book-main-btn,
    .queue-btn {
      width: 100%;
    }

    .appointment-actions {
      align-items: stretch;
      flex-direction: column;
    }

    .action-buttons {
      width: 100%;
    }

    .action-buttons .btn,
    .action-buttons form,
    .action-buttons form button {
      width: 100%;
    }

    .past-item {
      grid-template-columns: 1fr;
    }

    .reminder-box {
      align-items: flex-start;
    }
  }
</style>
@endpush

@section('content')
<div class="patient-tab-shell">
  <div class="patient-tab-content">
    <div class="container-fluid appointments-page px-0">
      @include('partials.alerts')

<<<<<<< Updated upstream
      <div class="appointments-shell">
        <div class="appointments-hero">
          <div class="appointments-hero-row">
            <div class="appointments-title-wrap">
              <div class="appointments-title-icon">
                <i class="bi bi-calendar2-check"></i>
=======

      @php
        $activeQueues = auth()->user()->queueEntries()
          ->whereIn('status', ['waiting', 'now_serving'])
          ->with('clinic')
          ->orderBy('created_at', 'desc')
          ->get();
      @endphp

      @if($activeQueues->count() > 0)
        <div class="alert alert-info border-0 rounded-3 mb-4">
          <h6 class="fw-semibold mb-3">
            <i class="bi bi-people me-2"></i>Current Queue Status
          </h6>
          <div class="row g-3">
            @foreach($activeQueues as $queueEntry)
              <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-between p-3 bg-white rounded">
                  <div>
                    <div class="fw-semibold">{{ $queueEntry->clinic->name }}</div>
                    <div class="text-muted small">
                      <i class="bi bi-hash me-1"></i>Queue #{{ $queueEntry->queue_number }}
                    </div>
                    <div class="text-muted small">
                      <i class="bi bi-clock me-1"></i>Joined at {{ $queueEntry->formatted_created_time }}
                    </div>
                  </div>
                  <div class="text-end">
                    @php($queueIsNowServing = $queueEntry->status === 'now_serving')
                    <span class="badge {{ $queueIsNowServing ? 'bg-primary text-white' : 'bg-warning text-dark' }} mb-2 d-inline-block">
                      <i class="bi {{ $queueIsNowServing ? 'bi-megaphone' : 'bi-clock' }} me-1"></i>{{ $queueIsNowServing ? 'Now Serving' : 'Waiting' }}
                    </span>
                    <br>
                    <a href="{{ route('queue.status.entry', $queueEntry) }}"
                       class="btn btn-sm btn-primary">
                      <i class="bi bi-eye me-1"></i>View Details
                    </a>
                  </div>
                </div>
>>>>>>> Stashed changes
              </div>

              <div>
                <h1 class="appointments-title">My Appointments</h1>
                <p class="appointments-subtitle">
                  View your queue status, upcoming visits, and appointment history in one place.
                </p>
              </div>
            </div>

            <a href="{{ route('appointments.create') }}" class="btn btn-primary book-main-btn">
              <i class="bi bi-plus-lg me-1"></i>
              Book Appointment
            </a>
          </div>
        </div>

        <div class="appointments-body">
          @php
            $activeQueues = auth()->user()->queueEntries()
              ->where('status', 'waiting')
              ->with('clinic')
              ->orderBy('created_at', 'desc')
              ->get();
          @endphp

          @if($activeQueues->count() > 0)
            <section class="queue-panel">
              <div class="section-row">
                <h4 class="section-heading">
                  <i class="bi bi-people-fill"></i>
                  Current Queue Status
                </h4>

<<<<<<< Updated upstream
                <span class="count-pill">{{ $activeQueues->count() }} Active</span>
              </div>

              <div class="queue-grid">
                @foreach($activeQueues as $queueEntry)
                  <div class="queue-card">
                    <div class="queue-left">
                      <div class="queue-icon">
                        <i class="bi bi-heart-pulse"></i>
=======
      @if($upcoming->isEmpty())
        <div class="alert alert-info text-center rounded-3">
          No upcoming appointments.
          <a href="{{ route('appointments.create') }}" class="text-decoration-none fw-semibold">Book one now</a>.
        </div>
      @else
        <div class="table-responsive mb-5">
          <table class="table align-middle table-hover">
            <thead class="table-light">
              <tr>
                <th>Clinic</th>
                <th>Service</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($upcoming as $a)
                <tr>
                  <td class="fw-semibold">{{ $a->clinic->name }}</td>
                  <td>{{ $a->service->name }}</td>
                  <td>{{ $a->doctor ? ('Dr. ' . $a->doctor->name) : '—' }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_date)->isoFormat('MMM D, YYYY') }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_time)->format('h:i A') }}</td>
                  <td>
                    <span class="badge rounded-pill {{ $a->status_badge_class }}">
                      {{ $a->status_label }}
                    </span>
                  </td>
                  <td>
                    @if($a->status === 'scheduled')
                      <div class="d-flex gap-2">
                        @php
                          // Check if user is in queue for this appointment
                          $inQueue = \App\Models\QueueEntry::where('appointment_id', $a->id)
                            ->whereIn('status', ['waiting', 'now_serving'])
                            ->first();
                        @endphp

                        @if($inQueue)
                          @php($inQueueNowServing = $inQueue->status === 'now_serving')
                          <span class="badge {{ $inQueueNowServing ? 'bg-primary text-white' : 'bg-success text-white' }}">
                            <i class="bi {{ $inQueueNowServing ? 'bi-megaphone' : 'bi-check-circle' }} me-1"></i>{{ $inQueueNowServing ? 'Now Serving' : 'In Queue' }} #{{ $inQueue->queue_number }}
                          </span>
                          <a href="{{ route('queue.status.entry', $inQueue) }}"
                             class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-eye me-1"></i>View Status
                          </a>
                        @else
                          <span class="badge bg-secondary text-white">
                            <i class="bi bi-clock me-1"></i>Queue Pending
                          </span>
                        @endif

                        <form method="POST"
                          action="{{ route('appointments.destroy', $a) }}"
                          class="d-inline"
                          data-confirm="Cancel this appointment? You will also be removed from the queue."
                          data-confirm-title="Cancel Appointment"
                          data-confirm-btn="Cancel Appointment">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger rounded-pill px-3">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                          </button>
                        </form>
>>>>>>> Stashed changes
                      </div>

                      <div>
                        <h5 class="queue-title">{{ $queueEntry->clinic->name }}</h5>

                        <div class="queue-meta">
                          Queue <span>#{{ $queueEntry->queue_number }}</span>
                        </div>

                        <div class="queue-time">
                          <i class="bi bi-clock me-1"></i>
                          Joined at {{ $queueEntry->formatted_created_time }}
                        </div>
                      </div>
                    </div>

                    <a href="{{ route('queue.status.entry', $queueEntry) }}" class="btn btn-primary queue-btn">
                      <i class="bi bi-eye me-1"></i>
                      View Details
                    </a>
                  </div>
                @endforeach
              </div>
            </section>
          @endif

          <section>
            <div class="section-row">
              <h4 class="section-heading">
                <i class="bi bi-calendar-week"></i>
                Upcoming Appointments
                <span class="count-pill">{{ $upcoming->count() }}</span>
              </h4>


            </div>

            @if($upcoming->isEmpty())
              <div class="empty-state">
                <div class="empty-icon">
                  <i class="bi bi-calendar-plus"></i>
                </div>

                <h5 class="empty-title">No upcoming appointments</h5>
                <p class="empty-text">You do not have any scheduled appointment right now.</p>


              </div>
            @else
              <div class="upcoming-grid">
                @foreach($upcoming as $a)
                  @php
                    $inQueue = \App\Models\QueueEntry::where('appointment_id', $a->id)
                      ->where('status', 'waiting')
                      ->first();

                    $isPendingQueue = !$inQueue;
                  @endphp

                  <article class="appointment-card {{ $isPendingQueue ? 'pending' : '' }}">
                    <div class="appointment-top">
                      <div class="appointment-main">
                        <div class="clinic-icon">
                          <i class="bi bi-heart-pulse"></i>
                        </div>

                        <div>
                          <h5 class="clinic-name">{{ $a->clinic->name }}</h5>
                          <div class="service-name">{{ $a->service->name }}</div>
                        </div>
                      </div>

                      <span class="badge status-badge-soft {{ $a->status_badge_class }}">
                        {{ $a->status_label }}
                      </span>
                    </div>

                    <div class="appointment-info-grid">
                      <div class="info-item">
                        <i class="bi bi-person"></i>
                        <div>
                          <div class="info-label">Doctor</div>
                          <div class="info-value">
                            {{ $a->doctor ? ('Dr. ' . $a->doctor->name) : '—' }}
                          </div>
                        </div>
                      </div>

                      <div class="info-item">
                        <i class="bi bi-calendar3"></i>
                        <div>
                          <div class="info-label">Date</div>
                          <div class="info-value">
                            {{ \Carbon\Carbon::parse($a->appointment_date)->isoFormat('MMM D, YYYY') }}
                          </div>
                          <div class="info-subvalue">
                            {{ \Carbon\Carbon::parse($a->appointment_date)->isoFormat('dddd') }}
                          </div>
                        </div>
                      </div>

                      <div class="info-item">
                        <i class="bi bi-clock"></i>
                        <div>
                          <div class="info-label">Time</div>
                          <div class="info-value">
                            {{ \Carbon\Carbon::parse($a->appointment_time)->format('h:i A') }}
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="appointment-actions">
                      @if($inQueue)
                        <div class="queue-state">
                          <i class="bi bi-check-circle-fill"></i>
                          In Queue #{{ $inQueue->queue_number }}
                        </div>
                      @else
                        <div class="queue-state pending-state">
                          <i class="bi bi-clock-fill"></i>
                          Queue Pending
                        </div>
                      @endif

                      @if($a->status === 'scheduled')
                        <div class="action-buttons">
                          @if($inQueue)
                            <a href="{{ route('queue.status.entry', $inQueue) }}"
                               class="btn btn-sm btn-outline-primary px-3">
                              <i class="bi bi-eye me-1"></i>
                              View Status
                            </a>
                          @endif

                          <form method="POST"
                                action="{{ route('appointments.destroy', $a) }}"
                                class="d-inline"
                                data-confirm="Cancel this appointment? You will also be removed from the queue."
                                data-confirm-title="Cancel Appointment"
                                data-confirm-btn="Cancel Appointment">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-sm btn-outline-danger px-3">
                              <i class="bi bi-trash3 me-1"></i>
                              Cancel
                            </button>
                          </form>
                        </div>
                      @endif
                    </div>
                  </article>
                @endforeach
              </div>
            @endif
          </section>

          <div class="accordion past-accordion mt-4" id="pastAppointmentsAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header" id="pastAppointmentsHeading">
                <button class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#pastAppointmentsCollapse"
                        aria-expanded="false"
                        aria-controls="pastAppointmentsCollapse">
                  <i class="bi bi-archive-fill me-2"></i>
                  Past Appointments
                  <span class="count-pill ms-2">{{ $past->count() }}</span>
                </button>
              </h2>

              <div id="pastAppointmentsCollapse"
                   class="accordion-collapse collapse"
                   aria-labelledby="pastAppointmentsHeading"
                   data-bs-parent="#pastAppointmentsAccordion">
                <div class="accordion-body">
                  @if($past->isEmpty())
                    <div class="empty-state mb-0">
                      <div class="empty-icon">
                        <i class="bi bi-archive"></i>
                      </div>

                      <h5 class="empty-title">No past appointments</h5>
                      <p class="empty-text mb-0">
                        Your completed and previous appointments will appear here.
                      </p>
                    </div>
                  @else
                    <div class="past-list">
                      @foreach($past as $a)
                        <div class="past-item">
                          <div>
                            <span class="past-label">Clinic</span>
                            <div class="past-value">{{ $a->clinic->name }}</div>
                          </div>

                          <div>
                            <span class="past-label">Service</span>
                            <div class="past-value">{{ $a->service->name }}</div>
                          </div>

                          <div>
                            <span class="past-label">Doctor</span>
                            <div class="past-value">
                              {{ $a->doctor ? ('Dr. ' . $a->doctor->name) : '—' }}
                            </div>
                          </div>

                          <div>
                            <span class="past-label">Date</span>
                            <div class="past-value">
                              {{ \Carbon\Carbon::parse($a->appointment_date)->isoFormat('MMM D, YYYY') }}
                            </div>
                          </div>

                          <div>
                            <span class="past-label">Time</span>
                            <div class="past-value">
                              {{ \Carbon\Carbon::parse($a->appointment_time)->format('h:i A') }}
                            </div>
                          </div>

                          <div>
                            <span class="past-label">Status</span>
                            <span class="badge rounded-pill {{ $a->status_badge_class }}">
                              {{ $a->status_label }}
                            </span>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>

          <div class="reminder-box">
            <i class="bi bi-info-circle-fill"></i>
            <div>
              <strong>Reminder:</strong>
              Please arrive 30 minutes before your scheduled appointment.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection