@extends('layouts.app')

@section('title', 'My Appointments')

@section('content')
<div class="container py-4">
  @include('partials.alerts')
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-white border-bottom-0 pb-0">
      <h3 class="text-primary fw-bold">
        <i class="bi bi-clipboard2-check-fill me-2"></i>My Appointments
      </h3>
    </div>
    <div class="card-body">

      {{-- Current Queue Status --}}
      @php
        $activeQueues = auth()->user()->queueEntries()
          ->where('status', 'waiting')
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
                    <a href="{{ route('queue.status.entry', $queueEntry) }}"
                       class="btn btn-sm btn-primary">
                      <i class="bi bi-eye me-1"></i>View Details
                    </a>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Upcoming Appointments --}}
      <h5 class="mt-2 mb-3 fw-semibold">
        <i class="bi bi-clock-history me-2"></i>Upcoming Appointments
      </h5>

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
                  <td>{{ $a->doctor ? $a->doctor->name : '—' }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_date)->isoFormat('MMM D, YYYY') }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_time)->format('h:i A') }}</td>
                  <td>
                    <span class="badge rounded-pill
                      {{ $a->status === 'scheduled' ? 'bg-warning text-dark' :
                         ($a->status === 'completed' ? 'bg-success' : 'bg-secondary') }}">
                      {{ ucfirst($a->status) }}
                    </span>
                  </td>
                  <td>
                    @if($a->status === 'scheduled')
                      <div class="d-flex gap-2">
                        @php
                          // Check if user is in queue for this appointment
                          $inQueue = \App\Models\QueueEntry::where('appointment_id', $a->id)
                            ->where('status', 'waiting')
                            ->first();
                        @endphp

                        @if($inQueue)
                          <span class="badge bg-success text-white">
                            <i class="bi bi-check-circle me-1"></i>In Queue #{{ $inQueue->queue_number }}
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
                              onsubmit="return confirm('Cancel this appointment? You will also be removed from the queue.')"
                              class="d-inline">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger rounded-pill px-3">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                          </button>
                        </form>
                      </div>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

      {{-- Past Appointments --}}
      <h5 class="mt-4 mb-3 fw-semibold">
        <i class="bi bi-archive-fill me-2"></i>Past Appointments
      </h5>

      @if($past->isEmpty())
        <div class="alert alert-secondary text-center rounded-3">
          You have no past appointments.
        </div>
      @else
        <div class="table-responsive">
          <table class="table align-middle table-hover">
            <thead class="table-light">
              <tr>
                <th>Clinic</th>
                <th>Service</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($past as $a)
                <tr>
                  <td class="fw-semibold">{{ $a->clinic->name }}</td>
                  <td>{{ $a->service->name }}</td>
                  <td>{{ $a->doctor ? $a->doctor->name : '—' }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_date)->isoFormat('MMM D, YYYY') }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_time)->format('h:i A') }}</td>
                  <td>
                    <span class="badge rounded-pill
                      {{ $a->status === 'scheduled' ? 'bg-warning text-dark' :
                         ($a->status === 'completed' ? 'bg-success' : 'bg-secondary') }}">
                      {{ ucfirst($a->status) }}
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

    </div>
  </div>
</div>
@endsection
