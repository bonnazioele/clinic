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
            <h1 class="mb-1 fw-bold text-primary">Welcome, {{ auth()->user()->name }}!</h1>
            <p class="text-muted mb-0">
              <i class="bi bi-person-badge me-2"></i>Secretary Dashboard
            </p>
          </div>
        </div>
        @php $assignedClinics = auth()->user()->secretaryClinics()->select('clinics.id','clinics.name','clinics.branch_code')->get(); @endphp
        <div class="row g-3 text-start justify-content-center">
          @if($assignedClinics->count())
          <!-- Assigned Clinics Column -->
          <div class="col-md-3">
            <div class="p-4 rounded text-white h-100" style="background:#0d6efd;">
              @php $firstClinic = $assignedClinics->first(); @endphp
              <div class="fs-3 fw-bold">{{ $firstClinic ? Str::limit($firstClinic->name, 18) : '—' }}</div>
              <div class="mt-1">Assigned Clinic{{ $assignedClinics->count()>1 ? 's' : '' }}</div>
              @if($assignedClinics->count() > 1)
                <div class="small opacity-75 mt-2">+{{ $assignedClinics->count() - 1 }} more</div>
              @endif
            </div>
          </div>
          @endif

          <!-- Today's Appointments -->
          <div class="col-md-3">
            <a href="{{ route('secretary.appointments.index') }}" class="text-decoration-none">
              <div class="p-4 rounded h-100" style="background:#ffc107; color:#000;">
                <div class="fs-3 fw-bold">{{ $todayAppts }}</div>
                <div class="mt-1">Today's Appointments</div>
              </div>
            </a>
          </div>
          <!-- Doctors -->
          <div class="col-md-3">
            <a href="{{ route('secretary.doctors.index') }}" class="text-decoration-none">
              <div class="p-4 rounded text-white h-100" style="background:#1f7f56;">
                <div class="fs-3 fw-bold">{{ $totalDoctors }}</div>
                <div class="mt-1">Doctors</div>
              </div>
            </a>
          </div>
          <!-- Services -->
          <div class="col-md-3">
            <a href="{{ route('secretary.services.index') }}" class="text-decoration-none">
              <div class="p-4 rounded text-white h-100" style="background:#10c9f4;">
                <div class="fs-3 fw-bold">{{ $availableServices ?? 0 }}</div>
                <div class="mt-1">Available Services</div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>





  <!-- Appointments Pane embedded in Dashboard -->
  @include('secretary.appointments._pane')
</div>
@endsection
