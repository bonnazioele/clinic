@extends('layouts.app')
@section('title', 'Queues')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">
      <i class="bi bi-people-fill text-primary me-3" style="font-size: 2rem;"></i>Queue Overview
    </h1>
    <a href="{{ route('secretary.appointments.index') }}" class="btn btn-outline-primary">
      <i class="bi bi-arrow-left me-2"></i>Back to Appointments
    </a>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="p-4 border rounded bg-warning text-dark">
        <h4 class="fw-semibold">{{ $totalWaiting }}</h4>
        <small>Total Waiting</small>
      </div>
    </div>
    <div class="col-md-6">
      <div class="p-4 border rounded bg-success text-white">
        <h4 class="fw-semibold">{{ $totalServedToday }}</h4>
        <small>Served Today</small>
      </div>
    </div>
  </div>

  <div class="medical-card p-4">
  <h5 class="mb-4"><i class="bi bi-building me-2"></i>Queue</h5>
  @php $activeClinics = $clinics->filter(fn($c) => $c->waiting_count > 0); @endphp
  @if($activeClinics->count() > 0)
      <div class="row g-4">
    <div class="mb-3">
      @php $recent = auth()->user()->notifications()->where('type',\App\Notifications\DoctorServedQueue::class)->latest()->take(3)->get(); @endphp
      @if($recent->count())
        <div class="alert alert-info">
          <strong><i class="bi bi-bell me-1"></i>Recent Activity:</strong>
          <ul class="small mb-0">
            @foreach($recent as $n)
              <li>{{ $n->data['message'] }} <span class="text-muted">{{ $n->created_at->diffForHumans() }}</span></li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
    @foreach($activeClinics as $clinic)
            <div class="col-md-6 col-lg-4">
              <div class="clinic-card h-100 p-4">
                <div class="d-flex justify-content-end mb-3">
                  <span class="badge bg-warning text-dark">{{ $clinic->waiting_count }} waiting</span>
                </div>

                <div class="mb-3">
                  <h6 class="fw-semibold mb-2">Current Queue:</h6>
                  <div class="list-group list-group-flush">
                    @foreach($clinic->queueEntries->take(3) as $entry)
                      <div class="list-group-item border-0 px-0 py-2">
                        <div class="d-flex align-items-center justify-content-between">
                          <div>
                            <span class="fw-semibold">#{{ $entry->queue_number }}</span>
                            <small class="text-muted d-block">{{ $entry->display_name }}</small>
                          </div>
                          <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $entry->formatted_created_time }}</small>
                        </div>
                      </div>
                    @endforeach
                    @if($clinic->waiting_count > 3)
                      <div class="text-center">
                        <small class="text-muted">+{{ $clinic->waiting_count - 3 }} more</small>
                      </div>
                    @endif
                  </div>
                </div>

                <div class="d-grid">
                  <a href="{{ route('secretary.queue.index', $clinic) }}" class="btn btn-primary btn-sm">
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
  <p class="text-muted mb-0">No active queue entries.</p>
      </div>
    @endif
  </div>
  @include('partials.alerts')
</div>
@endsection
