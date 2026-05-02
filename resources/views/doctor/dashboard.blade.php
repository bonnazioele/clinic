@extends('layouts.app')
@section('title','Doctor Dashboard')
@section('content')
<div class="container py-4">

  <div class="row mb-4">
    <div class="col-12">
      <div class="medical-card p-4 text-center">
        <div class="d-flex align-items-center justify-content-center mb-3">
          <i class="bi bi-heart-pulse-fill medical-icon me-3" style="font-size: 3rem;"></i>
          <div>
            <h1 class="mb-1 fw-bold text-primary">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="text-muted mb-0">
              <i class="bi bi-person-badge me-2"></i>Doctor Dashboard
            </p>
          </div>
        </div>
        <div class="row g-3 text-start">
          <div class="col-md-3">
            <div class="p-4 rounded text-white" style="background:#1976ff;">
              <div class="fs-3 fw-bold">{{ $appointments->count() }}</div>
              <div class="mt-1">Today's Appointments</div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-4 rounded" style="background:#ffc107;">
              <div class="fs-3 fw-bold">{{ $totalQueueCount }}</div>
              <div class="mt-1">People in Queue</div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-4 rounded text-white" style="background:#1f7f56;">
              <div class="fs-5 fw-bold text-truncate">{{ $activeClinic->name ?? 'Clinic' }}</div>
              <div class="mt-1">Active Clinic</div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-4 rounded text-white" style="background:#10c9f4;">
              <div class="fs-3 fw-bold">{{ $servicesOfferedCount }}</div>
              <div class="mt-1">Services Offered</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-lg-4">
      <div class="dashboard-card h-100">
        <h5 class="fw-semibold mb-3 d-flex align-items-center"><i class="bi bi-calendar-day medical-icon me-2"></i>Today's Appointments</h5>
        @forelse($appointments as $appt)
          <div class="mb-3 small p-2 rounded border bg-light">
            <span class="badge bg-primary me-2">{{ time12($appt->appointment_time) }}</span>
            <strong>{{ $appt->user->name }}</strong>
            <div class="text-muted mt-1"><i class="bi bi-gear me-1"></i>{{ $appt->service->name }}</div>
            @if($appt->medical_document)
              <div class="mt-1">
                <a class="small" href="{{ asset('storage/' . $appt->medical_document) }}" target="_blank"><i class="bi bi-file-earmark-medical me-1"></i>View document</a>
              </div>
            @endif
          </div>
        @empty
          <p class="text-muted small mb-0">No appointments today.</p>
        @endforelse
      </div>
    </div>
    <div class="col-lg-4">
      <div class="dashboard-card h-100">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
          <h5 class="fw-semibold mb-0 d-flex align-items-center"><i class="bi bi-people medical-icon me-2"></i>Active Queue</h5>
          <form method="GET" action="{{ route('doctor.dashboard') }}" class="d-flex align-items-center gap-2">
            <select name="clinic_id" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="0" @selected(empty($selectedClinicId))>All assigned clinics</option>
              @foreach($clinics as $clinic)
                <option value="{{ $clinic->id }}" @selected((int) $selectedClinicId === (int) $clinic->id)>{{ $clinic->name }}</option>
              @endforeach
            </select>
          </form>
        </div>
        @php $shown = 0; @endphp
        @forelse($queue as $q)
          @if($shown < 8)
            <div class="d-flex justify-content-between align-items-center mb-2 small p-2 rounded bg-light">
              <div>
                <span class="badge bg-warning text-dark me-2">#{{ $q->queue_number }}</span>
                {{ $q->appointment?->user?->name ?? 'Patient' }}
                <div class="text-muted mt-1"><i class="bi bi-building me-1"></i>{{ $q->clinic?->name ?? 'Clinic' }}</div>
              </div>
              <div class="text-end">
                <span class="badge bg-{{ $q->status_badge_class }} {{ in_array($q->status_badge_class,['warning','info']) ? 'text-dark' : '' }}">{{ $q->status_label }}</span>
                <div class="text-muted mt-1"><i class="bi bi-clock me-1"></i>{{ $q->created_at->diffForHumans(null,true) }}</div>
              </div>
            </div>
            @php $shown++; @endphp
          @endif
        @empty
          <p class="text-muted small mb-0">No active queue in your assigned clinic(s).</p>
        @endforelse
        @if($queue->count() > 8)
          <p class="text-muted small mb-0">+{{ $queue->count() - 8 }} more…</p>
        @endif
        <div class="mt-3">
          <a href="{{ route('doctor.queue.index', ['clinic_id' => $selectedClinicId ?: null]) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-list-ol me-1"></i>Open full queue
          </a>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="dashboard-card h-100">
        <h5 class="fw-semibold mb-3 d-flex align-items-center"><i class="bi bi-building medical-icon me-2"></i>Active Clinic</h5>
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
