@extends('layouts.app')
@section('title','Secretary Dashboard')
@section('content')
<div class="container py-4">
  <!-- Unified Welcome Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="medical-card p-4 text-center">
        <div class="d-flex align-items-center justify-content-center mb-3">
          <i class="bi bi-heart-pulse-fill medical-icon me-3" style="font-size: 3rem;"></i>
          <div>
            <h1 class="mb-1 fw-bold text-primary">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="text-muted mb-0">
              <i class="bi bi-person-badge me-2"></i>Secretary Dashboard
            </p>
          </div>
        </div>
        <div class="row g-3 text-start">
          <div class="col-md-3">
            <div class="p-4 rounded text-white" style="background:#1976ff;">
              <div class="fs-3 fw-bold">{{ $assignedClinics }}</div>
              <div class="mt-1">Assigned Clinics</div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-4 rounded" style="background:#ffc107;">
              <div class="fs-3 fw-bold">{{ $todayAppts }}</div>
              <div class="mt-1">Today's Appointments</div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-4 rounded text-white" style="background:#1f7f56;">
              <div class="fs-3 fw-bold">{{ $totalDoctors }}</div>
              <div class="mt-1">Doctors</div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-4 rounded text-white" style="background:#10c9f4;">
              <div class="fs-3 fw-bold">{{ $availableServices ?? 0 }}</div>
              <div class="mt-1">Available Services</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="medical-card p-4">
    <h5 class="fw-semibold mb-3"><i class="bi bi-gear me-2"></i>Quick Links</h5>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('secretary.queue.overview') }}" class="btn btn-warning"><i class="bi bi-people me-2"></i>Queues</a>
      <a href="{{ route('secretary.doctors.index') }}" class="btn btn-success"><i class="bi bi-person-badge me-2"></i>Doctors</a>
      <a href="{{ route('secretary.services.index') }}" class="btn btn-info"><i class="bi bi-gear me-2"></i>Services</a>
    </div>
  </div>

  <!-- Assigned Clinics -->
  <div class="medical-card p-4 mb-4">
    <h5 class="fw-semibold mb-3"><i class="bi bi-building me-2"></i>Assigned Clinics</h5>
    @if(($clinics ?? collect())->isEmpty())
      <div class="text-muted small">No clinics assigned.</div>
    @else
      <div class="d-flex flex-wrap gap-2">
        @foreach($clinics as $c)
          <span class="badge rounded-pill bg-primary-subtle text-primary border">{{ $c->name }}</span>
        @endforeach
      </div>
    @endif
  </div>

  <!-- Appointments Pane embedded in Dashboard -->
  @include('secretary.appointments._pane')
</div>
@endsection
