@extends('layouts.app')
@section('title','Queue Status')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  @if(isset($entry))
    <!-- Specific Queue Entry Status -->
    <div class="medical-card p-4 text-center">
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

        <!-- Estimated Wait Time -->
        @php
          $estimatedMinutes = $ahead * 15; // 15 minutes per person
          $estimatedTime = now()->addMinutes($estimatedMinutes);
        @endphp
        <div class="alert alert-info">
          <i class="bi bi-info-circle me-2"></i>
          <strong>Estimated wait time:</strong> {{ $estimatedMinutes }} minutes<br>
          <small class="text-muted">Expected to be called around {{ $estimatedTime->format('g:i A') }}</small>
        </div>

        <!-- Progress Bar -->
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
          </span>
        </div>
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

      <!-- Actions -->
      <div class="mt-4">
        @if($entry->status === 'waiting')
          <form method="POST" action="{{ route('queue.leave', $entry) }}" class="d-inline me-2">
            @csrf
            <button type="submit" class="btn btn-danger"
                    onclick="return confirm('Are you sure you want to leave the queue?')">
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
      </div>
    </div>
  @elseif(isset($userQueues) && $userQueues->count() > 0)
    <!-- General Queue Status - Multiple Entries -->
    <div class="medical-card p-4 mb-4">
      <h3 class="text-center mb-4">
        <i class="bi bi-people medical-icon me-2"></i>Your Queue Status
      </h3>
      <div class="row g-4">
        @foreach($userQueues as $queueEntry)
          <div class="col-md-6 col-lg-4">
            <div class="clinic-card h-100 p-4 text-center">
              <div class="mb-3">
                <i class="bi bi-building text-primary" style="font-size: 2rem;"></i>
              </div>
              <h5 class="fw-bold text-primary">{{ $queueEntry->clinic->name }}</h5>
              <div class="queue-number display-6 fw-bold text-success mb-3">#{{ $queueEntry->queue_number }}</div>
              <div class="d-flex justify-content-center mb-3">
                <span class="badge bg-warning text-dark px-3 py-2">
                  <i class="bi bi-clock me-1"></i>Waiting
                </span>
              </div>
              @if($queueEntry->appointment)
                <div class="mt-3 mb-3">
                  <small class="text-muted">
                    <i class="bi bi-calendar me-1"></i>
                    {{ $queueEntry->appointment->appointment_date->format('M j, Y') }}
                  </small>
                </div>
              @endif
              <div class="mt-3">
                <a href="{{ route('queue.status.entry', $queueEntry) }}" class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-eye me-1"></i>View Details
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @else
    <!-- No Active Queues -->
    <div class="medical-card p-4 text-center">
      <div class="mb-4">
        <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
      </div>
      <h3 class="text-success mb-3">No Active Queues</h3>
      <p class="lead text-muted mb-4">
        You are not currently in any waiting queues.
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

@if(isset($entry) && $entry->status === 'waiting')
  @push('scripts')
  <script>
    // Auto-refresh queue status every 30 seconds
    setInterval(function() {
      location.reload();
    }, 30000);

  // Placeholder for future live updates (polling/WebSockets)
  </script>
  @endpush
@endif
@endsection
