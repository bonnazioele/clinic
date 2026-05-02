@extends('layouts.patient-dashboard')

@section('title','Queue Status')

@push('styles')
  @vite('resources/css/queue-layout.css')
@endpush

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <style>
    .queue-hero {
      background: linear-gradient(145deg, #e6f0ff 0%, #f8fbff 55%, #ffffff 100%);
      border: 1px solid #d5e7ff;
      border-radius: 18px;
      padding: 1.5rem;
    }
    .queue-pill {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      border-radius: 999px;
      padding: .45rem .9rem;
      font-size: .85rem;
      font-weight: 600;
    }
    .queue-pill.waiting { background: #fff3cd; color: #7a5a00; }
    .queue-pill.now-serving { background: #dbeafe; color: #1d4ed8; }
    .queue-pill.served { background: #d1fae5; color: #065f46; }
    .queue-stat {
      border: 1px solid #e8edf4;
      border-radius: 14px;
      padding: 1rem;
      background: #fff;
      height: 100%;
    }
    .queue-stat-label { font-size: .8rem; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; }
    .queue-stat-value { font-size: 1.4rem; font-weight: 700; color: #0f172a; }
    .queue-action-panel {
      border: 1px solid #e8edf4;
      border-radius: 14px;
      padding: 1rem;
      background: #ffffff;
    }
    .queue-list-card {
      border: 1px solid #e5e7eb;
      border-radius: 16px;
      padding: 1rem;
      background: #fff;
      transition: transform .15s ease, box-shadow .15s ease;
      height: 100%;
    }
    .queue-list-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
    }
  </style>

  @if(isset($entry))
<<<<<<< Updated upstream
    <div class="medical-card p-4 text-center position-relative">
      <div class="position-absolute" style="right:1rem; top:1rem;">
        <button type="button" class="btn btn-outline-secondary px-3 py-2" style="font-weight:500;" onclick="handleQueueBack()">
          <i class="bi bi-arrow-left me-2"></i>Back
        </button>
      </div>

      <div class="mb-4">
        <i class="bi bi-clock-history medical-icon" style="font-size: 4rem;"></i>
      </div>

      <h2 class="queue-number display-4 fw-bold text-primary">#{{ $entry->queue_number }}</h2>
      <h5 class="text-primary mb-3">{{ $entry->clinic->name }}</h5>

      @if($entry->status === 'waiting')
        <div class="mb-4">
          <span class="badge bg-warning text-dark fs-6 px-4 py-3">
            <i class="bi bi-clock me-2"></i>{{ $ahead }} people ahead of you
          </span>
        </div>

        @php
          $estimatedMinutes = $ahead * 15;
          $estimatedTime = now()->addMinutes($estimatedMinutes);
        @endphp

        <div class="alert alert-info">
          <i class="bi bi-info-circle me-2"></i>
          <strong>Estimated wait time:</strong> {{ $estimatedMinutes }} minutes<br>
          <small class="text-muted">Expected to be called around {{ $estimatedTime->format('g:i A') }}</small>
        </div>

        <div class="mb-4">
          <div class="d-flex justify-content-between mb-2">
            <small class="text-muted">Queue Progress</small>
            <small class="text-muted">{{ $entry->queue_number }} of {{ $entry->queue_number + $ahead }}</small>
          </div>

          <div class="progress" style="height: 10px;">
            @php
              $progress = $ahead > 0 ? (($entry->queue_number / ($entry->queue_number + $ahead)) * 100) : 100;
            @endphp
            <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
          </div>
        </div>
      @else
        <div class="mb-4">
          <span class="badge bg-success text-white fs-6 px-4 py-3">
            <i class="bi bi-check-circle me-2"></i>Served at {{ $entry->formatted_served_time }}
=======
    @php
      $isNowServing = $entry->status === 'now_serving';
      $isWaiting = $entry->status === 'waiting';
      $isActive = in_array($entry->status, ['waiting', 'now_serving'], true);
      $estimatedMinutes = $isWaiting ? ($ahead * 15) : 0;
      $estimatedTime = $isWaiting ? now()->addMinutes($estimatedMinutes) : null;
      $positionTotal = $isActive ? ($ahead + 1) : 1;
      $readiness = $isWaiting ? round((1 / max($positionTotal, 1)) * 100) : 100;
      $statusClass = $isWaiting ? 'waiting' : ($isNowServing ? 'now-serving' : 'served');
      $statusIcon = $isWaiting ? 'bi-hourglass-split' : ($isNowServing ? 'bi-megaphone' : 'bi-check2-circle');
      $statusText = $isWaiting ? 'Waiting' : ($isNowServing ? 'Now Serving' : 'Served');
    @endphp

    <div class="queue-hero mb-4">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <div class="text-muted small mb-1">Live Queue Status</div>
          <h2 class="mb-2 fw-bold text-primary d-flex align-items-center gap-2">
            <i class="bi bi-clock-history"></i>
            Queue #{{ $entry->queue_number }}
          </h2>
          <div class="text-dark fw-semibold">
            <i class="bi bi-building me-1"></i>{{ $entry->clinic->name }}
          </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-2">
          <span class="queue-pill {{ $statusClass }}">
            <i class="bi {{ $statusIcon }}"></i>
            {{ $statusText }}
>>>>>>> Stashed changes
          </span>
          <button type="button" class="btn btn-outline-secondary btn-sm" onclick="handleQueueBack()">
            <i class="bi bi-arrow-left me-1"></i>Back
          </button>
        </div>
<<<<<<< Updated upstream
      @endif

      @if($entry->appointment)
        <div class="mt-4 p-3 bg-light rounded">
          <h6 class="fw-semibold mb-2">
            <i class="bi bi-calendar me-2"></i>Related Appointment
          </h6>
          <p class="mb-1">
            <strong>Date:</strong> {{ $entry->appointment->appointment_date->format('M j, Y') }}
          </p>
          <p class="mb-1">
            <strong>Time:</strong> {{ $entry->appointment->appointment_time->format('g:i A') }}
          </p>
          <p class="mb-0">
            <strong>Service:</strong> {{ $entry->appointment->service->name }}
          </p>
        </div>
      @endif

      <div class="mt-4">
        @if($entry->status === 'waiting')
          <form method="POST"
                action="{{ route('queue.leave', $entry) }}"
                class="d-inline me-2"
                data-confirm="Leave this queue? Your spot will be forfeited."
                data-confirm-title="Leave Queue"
                data-confirm-btn="Leave">
            @csrf
            <button type="submit" class="btn btn-danger">
              <i class="bi bi-x-circle me-2"></i>Leave Queue
            </button>
          </form>
        @endif

        <a href="{{ route('clinics.index') }}" class="btn btn-primary me-2">
          <i class="bi bi-building me-2"></i>Find Another Clinic
        </a>

        <a href="{{ route('appointments.create') }}" class="btn btn-success">
          <i class="bi bi-calendar-plus me-2"></i>Book Appointment
        </a>
=======
>>>>>>> Stashed changes
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="queue-stat">
          <div class="queue-stat-label">People Ahead</div>
          <div class="queue-stat-value">{{ $isWaiting ? $ahead : 0 }}</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="queue-stat">
          <div class="queue-stat-label">Your Position</div>
          <div class="queue-stat-value">{{ $isActive ? ($ahead + 1) : 1 }}{{ $isActive ? ' / '.$positionTotal : '' }}</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="queue-stat">
          <div class="queue-stat-label">Estimated Wait</div>
          <div class="queue-stat-value">{{ $isWaiting ? $estimatedMinutes.' min' : ($isNowServing ? 'Now' : 'Done') }}</div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="queue-stat">
          <div class="queue-stat-label">Joined Queue</div>
          <div class="queue-stat-value" style="font-size:1rem;">{{ $entry->created_at->format('M j, g:i A') }}</div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="medical-card p-4 h-100">
          <h5 class="fw-semibold mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-speedometer2"></i>
            Queue Readiness
          </h5>
          @if($isActive)
            <div class="d-flex justify-content-between small text-muted mb-2">
              <span>Progress to your turn</span>
              <span>{{ $readiness }}%</span>
            </div>
            <div class="progress mb-3" style="height: 12px; border-radius: 999px;">
              <div class="progress-bar bg-success" style="width: {{ $readiness }}%"></div>
            </div>
            @if($isNowServing)
              <div class="alert alert-primary mb-0">
                <div class="fw-semibold mb-1"><i class="bi bi-megaphone me-1"></i>Your Turn</div>
                <div>You are now being served. Please proceed to the clinic desk.</div>
              </div>
            @else
              <div class="alert alert-info mb-0">
                <div class="fw-semibold mb-1"><i class="bi bi-info-circle me-1"></i>Status Insight</div>
                <div>
                  {{ $ahead > 0 ? $ahead.' patient'.($ahead > 1 ? 's' : '').' ahead of you.' : 'You are next in line.' }}
                  @if($estimatedTime)
                    Expected call around <strong>{{ $estimatedTime->format('g:i A') }}</strong>.
                  @endif
                </div>
              </div>
            @endif
          @else
            <div class="alert alert-success mb-0">
              <div class="fw-semibold mb-1"><i class="bi bi-check-circle me-1"></i>Visit Completed</div>
              <div>Served at {{ $entry->formatted_served_time }}.</div>
            </div>
          @endif

          @if($entry->appointment)
            <div class="queue-action-panel mt-3">
              <h6 class="fw-semibold mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-calendar2-check"></i> Related Appointment
              </h6>
              <div class="small text-muted mb-1">Date: {{ $entry->appointment->appointment_date->format('M j, Y') }}</div>
              <div class="small text-muted mb-1">Time: {{ $entry->appointment->appointment_time->format('g:i A') }}</div>
              <div class="small text-muted">Service: {{ $entry->appointment->service->name }}</div>
            </div>
          @endif
        </div>
      </div>

      <div class="col-lg-4">
        <div class="medical-card p-4 h-100">
          <h5 class="fw-semibold mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-lightning-charge"></i>
            Quick Actions
          </h5>
          <div class="d-grid gap-2">
            @if($isWaiting)
              <form method="POST" action="{{ route('queue.leave', $entry) }}"
                    data-confirm="Leave this queue? Your spot will be forfeited."
                    data-confirm-title="Leave Queue"
                    data-confirm-btn="Leave">
                @csrf
                <button type="submit" class="btn btn-danger w-100">
                  <i class="bi bi-x-circle me-1"></i>Leave Queue
                </button>
              </form>
            @endif
            <a href="{{ route('queue.status') }}" class="btn btn-outline-primary w-100">
              <i class="bi bi-list-ul me-1"></i>View All My Queues
            </a>
            <a href="{{ route('clinics.index') }}" class="btn btn-primary w-100">
              <i class="bi bi-building me-1"></i>Find Another Clinic
            </a>
            <a href="{{ route('appointments.create') }}" class="btn btn-success w-100">
              <i class="bi bi-calendar-plus me-1"></i>Book Appointment
            </a>
          </div>
        </div>
      </div>
    </div>

  @elseif(isset($userQueues) && $userQueues->count() > 0)
    <div class="medical-card p-4 mb-4">
<<<<<<< Updated upstream
      <h3 class="text-center mb-4">
        <i class="bi bi-people medical-icon me-2"></i>Your Queue Status
      </h3>

=======
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h3 class="mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-people medical-icon"></i>Your Active Queues
        </h3>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">{{ $userQueues->count() }} active</span>
      </div>
>>>>>>> Stashed changes
      <div class="row g-4">
        @foreach($userQueues as $queueEntry)
          <div class="col-md-6 col-lg-4">
            <div class="queue-list-card">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <div class="small text-muted">Clinic</div>
                  <h6 class="fw-bold text-primary mb-0">{{ $queueEntry->clinic->name }}</h6>
                </div>
                @php
                  $listNowServing = $queueEntry->status === 'now_serving';
                  $listClass = $listNowServing ? 'now-serving' : 'waiting';
                  $listIcon = $listNowServing ? 'bi-megaphone' : 'bi-hourglass-split';
                  $listText = $listNowServing ? 'Now Serving' : 'Waiting';
                @endphp
                <span class="queue-pill {{ $listClass }}"><i class="bi {{ $listIcon }}"></i>{{ $listText }}</span>
              </div>

<<<<<<< Updated upstream
              <h5 class="fw-bold text-primary">{{ $queueEntry->clinic->name }}</h5>
              <div class="queue-number display-6 fw-bold text-success mb-3">#{{ $queueEntry->queue_number }}</div>

              <div class="d-flex justify-content-center mb-3">
                <span class="badge bg-warning text-dark px-3 py-2">
                  <i class="bi bi-clock me-1"></i>Waiting
                </span>
              </div>

=======
              <div class="mb-2">
                <div class="small text-muted">Queue Number</div>
                <div class="display-6 fw-bold text-success mb-0">#{{ $queueEntry->queue_number }}</div>
              </div>

              <div class="small text-muted mb-1"><i class="bi bi-clock me-1"></i>Joined {{ $queueEntry->created_at->diffForHumans() }}</div>
>>>>>>> Stashed changes
              @if($queueEntry->appointment)
                <div class="small text-muted mb-3">
                  <i class="bi bi-calendar-event me-1"></i>{{ $queueEntry->appointment->appointment_date->format('M j, Y') }}
                </div>
              @endif

<<<<<<< Updated upstream
              <div class="mt-3">
                <a href="{{ route('queue.status.entry', $queueEntry) }}" class="btn btn-outline-primary btn-sm">
=======
              <div class="d-grid mt-3">
                <a href="{{ route('queue.status.entry', $queueEntry) }}" class="btn btn-outline-primary">
>>>>>>> Stashed changes
                  <i class="bi bi-eye me-1"></i>View Details
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @else
    <div class="medical-card p-4 text-center">
      <div class="mb-4">
        <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
      </div>
<<<<<<< Updated upstream

      <h3 class="text-success mb-3">No Active Queues</h3>
=======
      <h3 class="text-success mb-2">No Active Queues</h3>
>>>>>>> Stashed changes
      <p class="lead text-muted mb-4">
        You are not currently waiting in any clinic queue.
      </p>

      <div class="d-flex justify-content-center gap-3">
        <a href="{{ route('clinics.index') }}" class="btn btn-primary">
          <i class="bi bi-building me-2"></i>Find a Clinic
        </a>

        <a href="{{ route('appointments.create') }}" class="btn btn-success">
          <i class="bi bi-calendar-plus me-2"></i>Book Appointment
        </a>
      </div>
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
function handleQueueBack(){
  if (document.referrer && document.referrer !== window.location.href) {
    history.back();
  } else {
    window.location.href = '{{ route('queue.status') }}';
  }
}
</script>
@endpush