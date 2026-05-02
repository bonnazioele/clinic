@extends('layouts.patient-dashboard')

@section('title', 'My Appointments')

@push('styles')
@push('styles')
<style>
  .patient-tab-shell,
  .patient-tab-content {
    width: 100%;
    max-width: none;
  }

  .appointments-page {
    width: 96%;
    max-width: none;
    padding: 0.75rem 0 1.25rem;
  }

  .appointments-shell {
    width: 100%;
    border-radius: 22px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: rgba(255, 255, 255, 0.94);
    box-shadow:
      0 16px 42px rgba(15, 23, 42, 0.08),
      inset 0 1px 0 rgba(255, 255, 255, 0.75);
    overflow: hidden;
  }

  .appointments-hero {
    padding: 1.25rem 1.5rem 1rem;
    background:
      radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 32%),
      linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(248, 251, 255, 0.92));
    border-bottom: 1px solid rgba(226, 232, 240, 0.9);
  }

  .appointments-hero-row {
    display: flex;
    justify-content: space-between;
    gap: 0.85rem;
    align-items: flex-start;
  }

  .appointments-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .appointments-title-icon {
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    display: grid;
    place-items: center;
    border-radius: 15px;
    color: #ffffff;
    background: linear-gradient(135deg, #0d6efd, #1287ff);
    box-shadow: 0 10px 24px rgba(13, 110, 253, 0.24);
    font-size: 1.35rem;
  }

  .appointments-title {
    margin: 0;
    color: #071225;
    font-weight: 700;
    letter-spacing: -0.04em;
    font-size: 1.55rem;
    line-height: 1.05;
  }

  .appointments-subtitle {
    margin: 0.35rem 0 0;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
  }

  .book-main-btn {
    border-radius: 13px;
    padding: 0.55rem 0.9rem;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: 0 10px 22px rgba(13, 110, 253, 0.18);
  }

  .appointments-body {
    padding: 1.15rem 1.5rem 1.5rem;
  }

  .section-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.8rem;
    margin-bottom: 0.75rem;
  }

  .section-heading {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    color: #0f172a;
    font-weight: 700;
    letter-spacing: -0.025em;
    font-size: 1.05rem;
  }

  .section-heading i {
    color: #0d6efd;
  }

  .count-pill {
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
    font-weight: 700;
  }

  .queue-panel {
    padding: 0.9rem;
    border-radius: 18px;
    border: 1px solid rgba(125, 211, 252, 0.7);
    background:
      linear-gradient(135deg, rgba(239, 249, 255, 0.95), rgba(224, 247, 255, 0.78));
    margin-bottom: 1.15rem;
  }

  .queue-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
  }

  .queue-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.85rem;
    padding: 0.8rem;
    min-height: 88px;
    border-radius: 17px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
  }

  .queue-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
  }

  .queue-icon {
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: #e8f2ff;
    color: #0d6efd;
    font-size: 1.4rem;
  }

  .queue-title {
    margin: 0 0 0.15rem;
    color: #0f172a;
    font-weight: 700;
    font-size: 0.98rem;
  }

  .queue-meta {
    color: #334155;
    font-weight: 600;
    font-size: 0.86rem;
    line-height: 1.35;
  }

  .queue-meta span {
    color: #0d6efd;
    font-weight: 700;
  }

  .queue-time {
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 500;
  }

  .queue-btn {
    border-radius: 12px;
    padding: 0.48rem 0.75rem;
    font-size: 0.82rem;
    font-weight: 600;
    white-space: nowrap;
  }

  .upcoming-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.8rem;
    margin-bottom: 1.15rem;
  }

  .appointment-card {
    position: relative;
    padding: 0.95rem;
    border-radius: 18px;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
    overflow: hidden;
    transition: 0.22s ease;
  }

  .appointment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.09);
  }

  .appointment-card::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 5px;
    background: linear-gradient(180deg, #0d6efd, #49a4ff);
  }

  .appointment-card.pending::before {
    background: linear-gradient(180deg, #8b5cf6, #a78bfa);
  }

  .appointment-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.85rem;
  }

  .appointment-main {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
  }

  .clinic-icon {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: #e8f2ff;
    color: #0d6efd;
    font-size: 1.45rem;
  }

  .appointment-card.pending .clinic-icon {
    background: #f2eaff;
    color: #8b5cf6;
  }

  .clinic-name {
    margin: 0 0 0.15rem;
    color: #0f172a;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: -0.025em;
  }

  .service-name {
    color: #64748b;
    font-size: 0.83rem;
    font-weight: 600;
  }

  .status-badge-soft {
    border-radius: 999px;
    padding: 0.35rem 0.6rem;
    font-size: 0.72rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .appointment-info-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.6rem;
    padding: 0.75rem 0;
    margin-bottom: 0.75rem;
    border-top: 1px solid #edf2f7;
    border-bottom: 1px solid #edf2f7;
  }

  .info-item {
    display: flex;
    align-items: flex-start;
    gap: 0.45rem;
    color: #334155;
  }

  .info-item i {
    color: #0d6efd;
    font-size: 0.9rem;
    margin-top: 0.08rem;
  }

  .info-label {
    font-size: 0.68rem;
    color: #64748b;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 0.08rem;
  }

  .info-value {
    font-size: 0.82rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.25;
  }

  .info-subvalue {
    font-size: 0.74rem;
    color: #64748b;
    font-weight: 500;
  }

  .appointment-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
  }

  .queue-state {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.85rem;
    font-weight: 700;
    color: #0f9f6e;
  }

  .queue-state.pending-state {
    color: #8b5cf6;
  }

  .action-buttons {
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
  }

  .action-buttons .btn {
    border-radius: 11px;
    font-size: 0.78rem;
    font-weight: 700;
    padding-top: 0.35rem;
    padding-bottom: 0.35rem;
  }

  .empty-state {
    padding: 1.6rem 1rem;
    text-align: center;
    border: 1px dashed rgba(13, 110, 253, 0.34);
    border-radius: 18px;
    background:
      linear-gradient(135deg, rgba(13, 110, 253, 0.06), rgba(255, 255, 255, 0.94));
    margin-bottom: 1.15rem;
  }

  .empty-icon {
    width: 58px;
    height: 58px;
    margin: 0 auto 0.75rem;
    display: grid;
    place-items: center;
    border-radius: 18px;
    background: #ffffff;
    color: #0d6efd;
    font-size: 1.55rem;
    box-shadow: 0 10px 28px rgba(13, 110, 253, 0.11);
  }

  .empty-title {
    margin: 0 0 0.25rem;
    color: #0f172a;
    font-size: 1rem;
    font-weight: 700;
  }

  .empty-text {
    color: #64748b;
    margin-bottom: 0.85rem;
    font-size: 0.88rem;
    font-weight: 500;
  }

  .past-accordion {
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #ffffff;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05);
  }

  .past-accordion .accordion-item {
    border: 0;
  }

  .past-accordion .accordion-button {
    padding: 0.9rem 1rem;
    background: #ffffff;
    box-shadow: none;
    color: #0f172a;
    font-weight: 700;
    font-size: 0.98rem;
    letter-spacing: -0.015em;
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
    padding: 0 1rem 1rem;
  }

  .past-list {
    display: grid;
    gap: 0.55rem;
  }

  .past-item {
    display: grid;
    grid-template-columns: 1.35fr 1.25fr 1.25fr 1fr 0.9fr auto;
    gap: 0.6rem;
    align-items: center;
    padding: 0.75rem 0.85rem;
    border-radius: 14px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
  }

  .past-label {
    display: none;
    color: #64748b;
    font-size: 0.66rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .past-value {
    color: #0f172a;
    font-size: 0.82rem;
    font-weight: 600;
    line-height: 1.25;
  }

  .past-item .badge {
    font-size: 0.72rem;
  }

  .reminder-box {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-top: 0.8rem;
    padding: 0.75rem 0.9rem;
    border-radius: 15px;
    border: 1px solid rgba(191, 219, 254, 0.9);
    background: linear-gradient(135deg, rgba(239, 246, 255, 0.96), rgba(255, 255, 255, 0.9));
    color: #334155;
    font-size: 0.86rem;
    font-weight: 500;
  }

  .reminder-box i {
    color: #0d6efd;
    font-size: 1rem;
  }

  .reminder-box strong {
    color: #0f172a;
    margin-right: 0.2rem;
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
      width: 100%;
      padding-top: 0.75rem;
    }

    .appointments-hero,
    .appointments-body {
      padding-left: 0.85rem;
      padding-right: 0.85rem;
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

    .appointments-title {
      font-size: 1.35rem;
    }

    .appointments-subtitle {
      font-size: 0.82rem;
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

      <div class="appointments-shell">
        <div class="appointments-hero">
          <div class="appointments-hero-row">
            <div class="appointments-title-wrap">
              <div class="appointments-title-icon">
                <i class="bi bi-calendar2-check"></i>
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

                <span class="count-pill">{{ $activeQueues->count() }} Active</span>
              </div>

              <div class="queue-grid">
                @foreach($activeQueues as $queueEntry)
                  <div class="queue-card">
                    <div class="queue-left">
                      <div class="queue-icon">
                        <i class="bi bi-heart-pulse"></i>
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