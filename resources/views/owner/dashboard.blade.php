@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Owner Dashboard</h3>
    <span class="badge bg-success ms-2">{{ $clinic->name }}</span>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card text-white" style="background: linear-gradient(135deg,#4e73df,#224abe)">
        <div class="card-body">
          <div class="small text-white-50">Doctors</div>
          <div class="fs-3 fw-bold">{{ $metrics['doctors'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-white" style="background: linear-gradient(135deg,#1cc88a,#13855c)">
        <div class="card-body">
          <div class="small text-white-50">Secretaries</div>
          <div class="fs-3 fw-bold">{{ $metrics['secretaries'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-white" style="background: linear-gradient(135deg,#36b9cc,#258391)">
        <div class="card-body">
          <div class="small text-white-50">Services</div>
          <div class="fs-3 fw-bold">{{ $metrics['services'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-white" style="background: linear-gradient(135deg,#f6c23e,#c69500)">
        <div class="card-body">
          <div class="small text-white-50">Appointments Today</div>
          <div class="fs-3 fw-bold">{{ $metrics['appointments_today'] }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-body d-flex flex-wrap gap-2">
      <a href="{{ route('owner.staff.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-people me-1"></i> Manage Staff
      </a>
    </div>
  </div>
</div>
@endsection
