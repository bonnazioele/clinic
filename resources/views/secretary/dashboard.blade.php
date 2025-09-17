@extends('layouts.app')
@section('title','Secretary Dashboard')
@section('content')
<div class="container py-4">
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="p-4 rounded text-white" style="background:#1976ff;">
        <div class="fs-3 fw-bold">{{ $assignedClinics }}</div>
        <div class="mt-1">Assigned Clinics</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="p-4 rounded" style="background:#ffc107;">
        <div class="fs-3 fw-bold">{{ $todayAppts }}</div>
        <div class="mt-1">Today's Appointments</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="p-4 rounded text-white" style="background:#1f7f56;">
        <div class="fs-3 fw-bold">{{ $totalDoctors }}</div>
        <div class="mt-1">Doctors</div>
      </div>
    </div>
  </div>

  <div class="medical-card p-4">
    <h5 class="fw-semibold mb-3"><i class="bi bi-gear me-2"></i>Quick Links</h5>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('secretary.appointments.index') }}" class="btn btn-primary"><i class="bi bi-calendar-check me-2"></i>Appointments</a>
      <a href="{{ route('secretary.queue.overview') }}" class="btn btn-warning"><i class="bi bi-people me-2"></i>Queues</a>
      <a href="{{ route('secretary.doctors.index') }}" class="btn btn-success"><i class="bi bi-person-badge me-2"></i>Doctors</a>
      <a href="{{ route('secretary.services.index') }}" class="btn btn-info"><i class="bi bi-gear me-2"></i>Services</a>
    </div>
  </div>
</div>
@endsection
