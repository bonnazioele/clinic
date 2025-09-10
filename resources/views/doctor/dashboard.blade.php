@extends('layouts.app')
@section('title','Doctor Dashboard')
@section('content')
<div class="container py-4">
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h2 class="fw-bold text-primary mb-1 d-flex align-items-center"><i class="bi bi-person-badge medical-icon me-2"></i>Doctor Dashboard</h2>
        <p class="text-muted mb-0">Today's overview</p>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('doctor.queue.index') }}" class="btn btn-primary"><i class="bi bi-list-ol me-2"></i>Queue</a>
        <a href="{{ route('doctor.schedules.index') }}" class="btn btn-secondary"><i class="bi bi-calendar-range me-2"></i>Schedule</a>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-lg-4">
      <div class="dashboard-card h-100">
        <h5 class="fw-semibold mb-3 d-flex align-items-center"><i class="bi bi-calendar-day medical-icon me-2"></i>Today's Appointments</h5>
        @forelse($appointments as $appt)
          <div class="mb-3 small p-2 rounded border bg-light">
            <span class="badge bg-primary me-2">{{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}</span>
            <strong>{{ $appt->user->name }}</strong>
            <div class="text-muted mt-1"><i class="bi bi-gear me-1"></i>{{ $appt->service->name }}</div>
          </div>
        @empty
          <p class="text-muted small mb-0">No appointments today.</p>
        @endforelse
      </div>
    </div>
    <div class="col-lg-4">
      <div class="dashboard-card h-100">
        <h5 class="fw-semibold mb-3 d-flex align-items-center"><i class="bi bi-people medical-icon me-2"></i>Active Queue</h5>
        @php $shown = 0; @endphp
        @forelse($queue as $q)
          @if($shown < 8)
            <div class="d-flex justify-content-between align-items-center mb-2 small p-2 rounded bg-light">
              <div>
                <span class="badge bg-warning text-dark me-2">#{{ $q->queue_number }}</span>
                {{ $q->appointment?->user?->name ?? 'Patient' }}
              </div>
              <span class="text-muted"><i class="bi bi-clock me-1"></i>{{ $q->created_at->diffForHumans(null,true) }}</span>
            </div>
            @php $shown++; @endphp
          @endif
        @empty
          <p class="text-muted small mb-0">No one waiting.</p>
        @endforelse
        @if($queue->count() > 8)
          <p class="text-muted small mb-0">+{{ $queue->count() - 8 }} more…</p>
        @endif
      </div>
    </div>
    <div class="col-lg-4">
      <div class="dashboard-card h-100">
        <h5 class="fw-semibold mb-3 d-flex align-items-center"><i class="bi bi-building medical-icon me-2"></i>Your Clinics</h5>
        @forelse($clinics as $c)
          <div class="mb-2 small p-2 rounded bg-light"><i class="bi bi-hospital me-2"></i>{{ $c->name }}</div>
        @empty
          <p class="text-muted small mb-0">No clinics assigned.</p>
        @endforelse
      </div>
    </div>
  </div>

  <div class="medical-card p-4">
    <h5 class="fw-semibold mb-3 d-flex align-items-center"><i class="bi bi-gear medical-icon me-2"></i>Quick Actions</h5>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('doctor.queue.index') }}" class="btn btn-primary"><i class="bi bi-list-ol me-2"></i>View Queue</a>
      <a href="{{ route('doctor.schedules.index') }}" class="btn btn-secondary"><i class="bi bi-calendar-range me-2"></i>Manage Schedule</a>
    </div>
  </div>
</div>
@endsection
