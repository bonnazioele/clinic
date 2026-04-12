@extends('layouts.app')
@section('title', 'Queues')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <h1 class="h2 mb-0">
      <i class="bi bi-people-fill text-primary me-3" style="font-size: 2rem;"></i>Queue Overview
    </h1>

    <div class="d-flex gap-2 flex-wrap">
      <form method="GET" action="{{ route('secretary.queue.overview') }}" class="d-flex gap-2 align-items-end">
        <div>
          <label class="form-label mb-1">Queue Date</label>
          <input
            type="date"
            name="date"
            class="form-control"
            value="{{ $selectedDate ?? now()->toDateString() }}"
          >
        </div>
        <div>
          <button class="btn btn-outline-primary">
            <i class="bi bi-funnel me-1"></i>Apply
          </button>
        </div>
      </form>

      <a href="{{ route('secretary.dashboard') }}" class="btn btn-outline-primary align-self-end">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
      </a>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="p-4 border rounded bg-warning text-dark h-100">
        <h4 class="fw-semibold mb-1">{{ $totalWaiting }}</h4>
        <small>Total Waiting</small>
      </div>
    </div>

    <div class="col-md-6">
      <div class="p-4 border rounded bg-success text-white h-100">
        <h4 class="fw-semibold mb-1">{{ $totalServedToday }}</h4>
        <small>Served on Selected Date</small>
      </div>
    </div>
  </div>

  <div class="medical-card p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
      <h5 class="mb-0">
        <i class="bi bi-building me-2"></i>
        Queue for {{ \Carbon\Carbon::parse($selectedDate ?? now()->toDateString())->format('M j, Y') }}
      </h5>
      <small class="text-muted">Only clinics with active queues are shown.</small>
    </div>

    @php $activeClinics = $clinics->filter(fn($c) => $c->waiting_count > 0); @endphp

    @if($activeClinics->count() > 0)
      <div class="mb-3">
        @php
          $recent = auth()->user()
            ->notifications()
            ->where('type', \App\Notifications\DoctorServedQueue::class)
            ->latest()
            ->take(3)
            ->get();
        @endphp

        @if($recent->count())
          <div class="alert alert-info">
            <strong><i class="bi bi-bell me-1"></i>Recent Activity:</strong>
            <ul class="small mb-0 mt-2">
              @foreach($recent as $n)
                <li>
                  {{ $n->data['message'] }}
                  <span class="text-muted">{{ $n->created_at->diffForHumans() }}</span>
                </li>
              @endforeach
            </ul>
          </div>
        @endif
      </div>

      <div class="row g-4">
        @foreach($activeClinics as $clinic)
          <div class="col-md-6 col-lg-4">
            <div class="clinic-card h-100 p-4 border rounded bg-white shadow-sm">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <h6 class="fw-bold mb-1">{{ $clinic->name }}</h6>
                  @if(!empty($clinic->address))
                    <small class="text-muted d-block">{{ $clinic->address }}</small>
                  @endif
                </div>
                <span class="badge bg-warning text-dark">{{ $clinic->waiting_count }} waiting</span>
              </div>

              <div class="mb-3">
                <h6 class="fw-semibold mb-2">Current Queue</h6>
                <div class="list-group list-group-flush">
                  @foreach($clinic->queueEntries->take(3) as $entry)
                    <div class="list-group-item border-0 px-0 py-2">
                      <div class="d-flex align-items-center justify-content-between">
                        <div>
                          <span class="fw-semibold">#{{ $entry->queue_number }}</span>
                          <small class="text-muted d-block">{{ $entry->display_name }}</small>
                        </div>
                        <small class="text-muted">
                          <i class="bi bi-clock me-1"></i>{{ $entry->formatted_created_time }}
                        </small>
                      </div>
                    </div>
                  @endforeach

                  @if($clinic->waiting_count > 3)
                    <div class="text-center pt-2">
                      <small class="text-muted">+{{ $clinic->waiting_count - 3 }} more</small>
                    </div>
                  @endif
                </div>
              </div>

              <div class="d-grid">
                <a
                  href="{{ route('secretary.queue.index', ['clinic' => $clinic->id, 'date' => $selectedDate ?? now()->toDateString()]) }}"
                  class="btn btn-primary btn-sm"
                >
                  <i class="bi bi-eye me-2"></i>Manage Queue
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="text-center py-5">
        <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
        <h5 class="text-success mt-3">No Active Queues</h5>
        <p class="text-muted mb-0">No active queue entries for the selected date.</p>
      </div>
    @endif
  </div>

  @include('partials.alerts')
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  setInterval(function () {
    const params = new URLSearchParams(window.location.search);
    const date = params.get('date');
    const url = date
      ? `${window.location.pathname}?date=${encodeURIComponent(date)}`
      : window.location.pathname;
    window.location.replace(url);
  }, 30000);
});
</script>
@endpush
@endsection