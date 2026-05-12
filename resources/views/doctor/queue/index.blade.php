@extends('doctor.layouts.app')

@section('title', 'Doctor Queue')

@section('doctor-content')
@php
  $nowServingEntry = $nowServingEntry ?? null;
  $waitingPage = $waitingPage ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 8);
  $completedQueue = $completedQueue ?? collect();

  $activeCount = $waitingPage->total() + ($nowServingEntry ? 1 : 0);
  $completedCount = $completedQueue->count();

  $completedSorted = $completedQueue->sortByDesc(function ($entry) {
    return $entry->doctor_completed_at
      ?? $entry->served_at
      ?? $entry->updated_at
      ?? $entry->created_at;
  });

  $formatCompletedTime = function ($entry) {
    $time = $entry->doctor_completed_at
      ?? $entry->served_at
      ?? $entry->updated_at
      ?? $entry->created_at;

    return $time ? \Carbon\Carbon::parse($time)->format('h:i A') : 'N/A';
  };
@endphp
<style>
  .doctor-queue-page {
    width: 100%;
  }

  .doctor-queue-shell {
    max-width: 1380px;
    margin: 0 auto;
  }

  .queue-hero {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 30px;
    background:
      radial-gradient(circle at top left, rgba(37, 99, 235, 0.14), transparent 30%),
      radial-gradient(circle at bottom right, rgba(245, 158, 11, 0.12), transparent 28%),
      rgba(255, 255, 255, 0.84);
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    padding: 28px;
    overflow: hidden;
  }

  .queue-hero-icon {
    width: 70px;
    height: 70px;
    border-radius: 22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2563eb, #06b6d4);
    color: #ffffff;
    font-size: 2rem;
    box-shadow: 0 16px 30px rgba(37, 99, 235, 0.26);
    flex-shrink: 0;
  }

  .queue-hero-title {
    font-size: clamp(2rem, 3vw, 2.8rem);
    font-weight: 900;
    letter-spacing: -0.04em;
    color: #0f172a;
    margin: 0;
  }

  .queue-hero-text {
    color: #64748b;
    margin: 8px 0 0;
    font-size: 1rem;
  }

  .queue-summary-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 24px;
    background: #ffffff;
    padding: 18px 20px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
  }

  .queue-summary-label {
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 800;
    margin-bottom: 4px;
  }

  .queue-summary-value {
    color: #111827;
    font-size: 2.2rem;
    font-weight: 900;
    line-height: 1;
    margin: 0;
  }

  .queue-board {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.82);
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    overflow: hidden;
  }

  .queue-board-header {
    padding: 22px 24px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.07);
    background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.95));
  }

  .queue-board-title {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #111827;
    font-size: 1.4rem;
    font-weight: 900;
    margin: 0;
  }

  .queue-board-title i {
    color: #2563eb;
  }

  .queue-board-subtitle {
    color: #64748b;
    margin: 6px 0 0;
    font-size: 0.95rem;
  }

  .queue-count-pill {
    min-width: 42px;
    height: 32px;
    padding: 0 14px;
    border-radius: 999px;
    background: rgba(245, 158, 11, 0.18);
    color: #92400e;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 0.85rem;
  }

  .queue-row {
    padding: 18px 24px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.07);
    transition: 0.2s ease;
  }

  .queue-row:last-child {
    border-bottom: 0;
  }

  .queue-row:hover {
    background: rgba(248, 250, 252, 0.9);
  }

  .queue-row.now-serving {
    background: rgba(16, 185, 129, 0.08);
  }

  .queue-row.completed {
    background: rgba(34, 197, 94, 0.06);
  }

  .queue-number {
    min-width: 64px;
    height: 46px;
    border-radius: 16px;
    padding: 0 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #92400e;
    background: rgba(245, 158, 11, 0.2);
    font-weight: 900;
    font-size: 1rem;
  }

  .patient-avatar {
    width: 50px;
    height: 50px;
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(37, 99, 235, 0.12);
    color: #2563eb;
    font-size: 1.25rem;
    flex-shrink: 0;
  }

  .patient-name {
    color: #111827;
    font-size: 1rem;
    font-weight: 900;
    margin: 0 0 4px;
  }

  .queue-meta {
    color: #64748b;
    font-size: 0.88rem;
    margin: 0;
  }

  .queue-label {
    color: #94a3b8;
    font-size: 0.72rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 5px;
  }

  .status-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border-radius: 999px;
    padding: 8px 12px;
    font-size: 0.78rem;
    font-weight: 900;
  }

  .action-btn {
    border-radius: 15px;
    padding: 10px 14px;
    font-weight: 800;
    white-space: nowrap;
  }

  .empty-queue {
    padding: 56px 24px;
    text-align: center;
  }

  .empty-queue-icon {
    width: 76px;
    height: 76px;
    border-radius: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(37, 99, 235, 0.1);
    color: #2563eb;
    font-size: 2rem;
    margin-bottom: 16px;
  }

  .empty-queue h5 {
    color: #111827;
    font-weight: 900;
    margin-bottom: 6px;
  }

  .empty-queue p {
    color: #64748b;
    margin: 0;
  }

  .serve-modal-content {
    border: 0;
    border-radius: 26px;
    overflow: hidden;
    box-shadow: 0 28px 70px rgba(15, 23, 42, 0.24);
  }

  .serve-modal-header {
    border-bottom: 0;
    background: linear-gradient(135deg, #0d6efd, #2563eb);
    color: #ffffff;
    padding: 20px 22px;
  }

  .serve-modal-header .btn-close {
    filter: invert(1);
    opacity: 0.9;
  }

  .serve-modal-body {
    padding: 30px 24px 22px;
    text-align: center;
  }

  .serve-modal-icon {
    width: 78px;
    height: 78px;
    border-radius: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(37, 99, 235, 0.14);
    color: #16a34a;
    font-size: 2.3rem;
    margin-bottom: 16px;
  }

  .serve-modal-title {
    font-size: 1.25rem;
    font-weight: 900;
    color: #111827;
    margin-bottom: 6px;
  }

  .serve-modal-text {
    color: #64748b;
    margin-bottom: 0;
  }

  .serve-patient-box {
    margin-top: 18px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 18px;
    background: #f8fafc;
    padding: 14px 16px;
  }

  .serve-patient-name {
    font-weight: 900;
    color: #111827;
    margin-bottom: 3px;
  }

  .serve-queue-number {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 0;
  }

  .serve-modal-footer {
    border-top: 0;
    padding: 0 24px 24px;
    justify-content: center;
    gap: 10px;
  }

  .serve-modal-btn {
    border-radius: 16px;
    padding: 11px 18px;
    font-weight: 900;
  }

  @media (max-width: 991.98px) {
    .queue-hero {
      padding: 22px;
      border-radius: 24px;
    }

    .queue-row {
      padding: 18px;
    }

    .queue-number {
      min-width: 56px;
      height: 42px;
    }
  }
</style>

<div class="doctor-queue-page">
  <div class="doctor-queue-shell">

    <div class="queue-hero mb-4">
      <div class="row align-items-center g-4">
        <div class="col-lg-8">
          <div class="d-flex align-items-center gap-3 gap-md-4">
            <div class="queue-hero-icon">
              <i class="bi bi-list-ol"></i>
            </div>

            <div>
              <h1 class="queue-hero-title">Today's Queue</h1>
              <p class="queue-hero-text">
                View appointment patients and guest walk-ins assigned to you. Click <strong>Complete</strong> only after the consultation is finished.
              </p>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="queue-summary-card">
            <div class="d-flex align-items-center justify-content-between gap-3">
              <div>
                <div class="queue-summary-label">Active Queue</div>
                <h2 class="queue-summary-value">{{ $activeCount }}</h2>
              </div>

              <span class="patient-avatar" style="background:rgba(245,158,11,0.18);color:#92400e;">
                <i class="bi bi-people"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="queue-board">
      <div class="queue-board-header">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
          <div>
            <h3 class="queue-board-title">
              <i class="bi bi-activity"></i>
              Queue Board
            </h3>
            <p class="queue-board-subtitle">
              Patients waiting, called, currently in consultation, or completed today.
            </p>
          </div>

          <span class="queue-count-pill">
            {{ $activeCount }}
          </span>
        </div>
      </div>

      @if($nowServingEntry)
        @php
          $patientName = $nowServingEntry->display_name;
          $patientEmail = $nowServingEntry->display_email;
          $patientPhone = $nowServingEntry->display_phone;
          $isWalkIn = $nowServingEntry->is_walk_in;
          $appointmentTime = $nowServingEntry->appointment?->appointment_time;
          $slotTime = $nowServingEntry->formatted_scheduled_slot_time;
          $isServed = $nowServingEntry->status === 'served';
          $isCompleted = $nowServingEntry->status === 'completed';
          $canServe = in_array($nowServingEntry->status, ['in_progress', 'now_serving'], true);
        @endphp

        <div class="queue-row now-serving">
          <div class="row align-items-center g-3">
            <div class="col-12 col-lg-1">
              <span class="queue-number">
                #{{ $nowServingEntry->queue_number }}
              </span>
            </div>

            <div class="col-12 col-lg-4">
              <div class="d-flex align-items-center gap-3">
                <span class="patient-avatar">
                  <i class="bi {{ $isWalkIn ? 'bi-person-plus' : 'bi-person' }}"></i>
                </span>

                <div class="min-w-0">
                  <h6 class="patient-name text-truncate">
                    {{ $patientName }}
                  </h6>

                  <p class="queue-meta">
                    @if($isWalkIn)
                      <span class="badge bg-info text-dark rounded-pill me-1">Guest Walk-In</span>
                    @else
                      <span class="badge bg-primary rounded-pill me-1">Appointment</span>
                    @endif

                    @if($patientEmail)
                      {{ $patientEmail }}
                    @elseif($patientPhone)
                      {{ $patientPhone }}
                    @else
                      {{ $nowServingEntry->created_at ? $nowServingEntry->created_at->diffForHumans() : 'Queue entry' }}
                    @endif
                  </p>
                </div>
              </div>
            </div>

            <div class="col-6 col-lg-2">
              <div class="queue-label">Schedule</div>

              <div class="fw-bold text-dark">
                @if($slotTime)
                  <i class="bi bi-clock text-primary me-1"></i>
                  {{ $slotTime }}
                @elseif($appointmentTime)
                  <i class="bi bi-clock text-primary me-1"></i>
                  {{ function_exists('time12') ? time12($appointmentTime) : \Carbon\Carbon::parse($appointmentTime)->format('h:i A') }}
                @else
                  Walk-in
                @endif
              </div>
            </div>

            <div class="col-6 col-lg-2">
              <div class="queue-label">Status</div>

              <span class="status-pill bg-info text-dark">
                <i class="bi bi-megaphone-fill"></i>
                Now Serving
              </span>
            </div>

            <div class="col-6 col-lg-1">
              <div class="queue-label">Document</div>

              @if($nowServingEntry->appointment?->medical_document)
                <a href="{{ asset('storage/' . $nowServingEntry->appointment->medical_document) }}"
                   target="_blank"
                   class="btn btn-sm btn-outline-secondary action-btn">
                  <i class="bi bi-file-earmark-medical"></i>
                </a>
              @else
                <span class="text-muted fw-bold">N/A</span>
              @endif
            </div>

            <div class="col-6 col-lg-2 text-lg-end">
              @if($isCompleted)
                <span class="status-pill bg-success text-white">
                  <i class="bi bi-check2-circle"></i>
                  Completed
                </span>
              @elseif($isServed)
                <span class="btn btn-outline-primary action-btn disabled">
                  <i class="bi bi-hourglass-split me-1"></i>
                  Awaiting Secretary
                </span>
              @elseif($canServe)
                <button type="button"
                        class="btn btn-primary action-btn serve-btn"
                        data-action-url="{{ route('doctor.queue.serve', $nowServingEntry) }}"
                        data-patient="{{ $patientName }}"
                        data-queue="#{{ $nowServingEntry->queue_number }}">
                  <i class="bi bi-person-check me-1"></i>
                  Serve
                </button>
              @else
                <span class="btn btn-outline-secondary action-btn disabled">
                  <i class="bi bi-lock me-1"></i>
                  Locked
                </span>
              @endif
            </div>
          </div>
        </div>
      @else
        <div class="empty-queue">
          <div class="empty-queue-icon">
            <i class="bi bi-people"></i>
          </div>

          <h5>No patient is being served</h5>
          <p>Call the next patient to begin the consultation.</p>
        </div>
      @endif

      @if($waitingPage->count())
        @foreach($waitingPage as $entry)
          @php
            $patientName = $entry->display_name;
            $patientEmail = $entry->display_email;
            $patientPhone = $entry->display_phone;
            $isWalkIn = $entry->is_walk_in;
            $appointmentTime = $entry->appointment?->appointment_time;
            $slotTime = $entry->formatted_scheduled_slot_time;
            $isServed = $entry->status === 'served';
            $isCompleted = $entry->status === 'completed';
            $canServe = in_array($entry->status, ['in_progress', 'now_serving'], true);
            $statusLabel = $entry->status === 'called' ? 'Called' : 'Waiting';
            $statusIcon = $entry->status === 'called' ? 'bi-megaphone-fill' : 'bi-hourglass-split';
            $statusClass = $entry->status === 'called' ? 'bg-primary text-white' : 'bg-warning text-dark';
          @endphp

          <div class="queue-row">
            <div class="row align-items-center g-3">
              <div class="col-12 col-lg-1">
                <span class="queue-number">
                  #{{ $entry->queue_number }}
                </span>
              </div>

              <div class="col-12 col-lg-4">
                <div class="d-flex align-items-center gap-3">
                  <span class="patient-avatar">
                    <i class="bi {{ $isWalkIn ? 'bi-person-plus' : 'bi-person' }}"></i>
                  </span>

                  <div class="min-w-0">
                    <h6 class="patient-name text-truncate">
                      {{ $patientName }}
                    </h6>

                    <p class="queue-meta">
                      @if($isWalkIn)
                        <span class="badge bg-info text-dark rounded-pill me-1">Guest Walk-In</span>
                      @else
                        <span class="badge bg-primary rounded-pill me-1">Appointment</span>
                      @endif

                      @if($patientEmail)
                        {{ $patientEmail }}
                      @elseif($patientPhone)
                        {{ $patientPhone }}
                      @else
                        {{ $entry->created_at ? $entry->created_at->diffForHumans() : 'Queue entry' }}
                      @endif
                    </p>
                  </div>
                </div>
              </div>

              <div class="col-6 col-lg-2">
                <div class="queue-label">Schedule</div>

                <div class="fw-bold text-dark">
                  @if($slotTime)
                    <i class="bi bi-clock text-primary me-1"></i>
                    {{ $slotTime }}
                  @elseif($appointmentTime)
                    <i class="bi bi-clock text-primary me-1"></i>
                    {{ function_exists('time12') ? time12($appointmentTime) : \Carbon\Carbon::parse($appointmentTime)->format('h:i A') }}
                  @else
                    Walk-in
                  @endif
                </div>
              </div>

              <div class="col-6 col-lg-2">
                <div class="queue-label">Status</div>

                <span class="status-pill {{ $statusClass }}">
                  <i class="bi {{ $statusIcon }}"></i>
                  {{ $statusLabel }}
                </span>
              </div>

              <div class="col-6 col-lg-1">
                <div class="queue-label">Document</div>

                @if($entry->appointment?->medical_document)
                  <a href="{{ asset('storage/' . $entry->appointment->medical_document) }}"
                     target="_blank"
                     class="btn btn-sm btn-outline-secondary action-btn">
                    <i class="bi bi-file-earmark-medical"></i>
                  </a>
                @else
                  <span class="text-muted fw-bold">N/A</span>
                @endif
              </div>

              <div class="col-6 col-lg-2 text-lg-end">
                @if($isCompleted)
                  <span class="status-pill bg-success text-white">
                    <i class="bi bi-check2-circle"></i>
                    Completed
                  </span>
                @elseif($isServed)
                  <span class="btn btn-outline-primary action-btn disabled">
                    <i class="bi bi-hourglass-split me-1"></i>
                    Awaiting Secretary
                  </span>
                @elseif($canServe)
                  <button type="button"
                          class="btn btn-primary action-btn serve-btn"
                          data-action-url="{{ route('doctor.queue.serve', $entry) }}"
                          data-patient="{{ $patientName }}"
                          data-queue="#{{ $entry->queue_number }}">
                    <i class="bi bi-person-check me-1"></i>
                    Serve
                  </button>
                @else
                  <span class="btn btn-outline-secondary action-btn disabled">
                    <i class="bi bi-lock me-1"></i>
                    Locked
                  </span>
                @endif
              </div>
            </div>
          </div>
        @endforeach

        @if($waitingPage->hasPages())
          <div class="px-4 py-3">
            {{ $waitingPage->withQueryString()->links() }}
          </div>
        @endif
      @endif
    </div>

  </div>
</div>

<details class="queue-board mt-4">
  <summary class="queue-board-header" style="cursor:pointer;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
      <div>
        <h3 class="queue-board-title">
          <i class="bi bi-check2-circle"></i>
          Today's Completed
        </h3>
        <p class="queue-board-subtitle">Patients already completed today.</p>
      </div>

      <span class="queue-count-pill">
        {{ $completedCount }}
      </span>
    </div>
  </summary>

  <div>
    @if($completedSorted->count())
      @foreach($completedSorted as $entry)
        <div class="queue-row completed">
          <div class="row align-items-center g-3">
            <div class="col-12 col-lg-2">
              <span class="queue-number">
                #{{ $entry->queue_number }}
              </span>
            </div>

            <div class="col-12 col-lg-6">
              <h6 class="patient-name text-truncate">
                {{ $entry->display_name }}
              </h6>
            </div>

            <div class="col-12 col-lg-4">
              <div class="queue-label">Completed At</div>
              <div class="fw-bold text-dark">
                <i class="bi bi-clock-history text-success me-1"></i>
                {{ $formatCompletedTime($entry) }}
              </div>
            </div>
          </div>
        </div>
      @endforeach
    @else
      <div class="empty-queue">
        <div class="empty-queue-icon">
          <i class="bi bi-check2-circle"></i>
        </div>
        <h5>No completed patients yet</h5>
        <p>Finish a consultation to see it logged here.</p>
      </div>
    @endif
  </div>
</details>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = @json(csrf_token());

    document.querySelectorAll('.serve-btn').forEach(function (button) {
      button.addEventListener('click', function () {
        const actionUrl = button.getAttribute('data-action-url');
        const patientName = button.getAttribute('data-patient') || 'Patient';
        const queueNumber = button.getAttribute('data-queue') || 'Queue';

        if (!actionUrl) {
          window.showDoctorToast?.('Missing serve action for this patient.', 'danger');
          return;
        }

        window.confirmDoctorToast({
          title: 'Mark Patient as Served',
          message: `${queueNumber} - ${patientName}. The secretary will finalize and advance the queue after this.`,
          confirmText: 'Mark Served',
          cancelText: 'Cancel',
          type: 'warning',
          onConfirm: function () {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = actionUrl;
            form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">`;
            document.body.appendChild(form);
            form.submit();
          }
        });
      });
    });
  });
</script>
@endsection
