@extends('layouts.app')

@section('title','Walk-In Registration Confirmation')

@section('content')
<div class="container py-4">
  <div class="mb-3 d-flex justify-content-between gap-2 flex-wrap">
    <a href="{{ route('secretary.patients.create') }}" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left me-2"></i>Back to Registration Page
    </a>
    <div class="d-flex gap-2">
      <a href="{{ route('secretary.walkin.print', $visit->id) }}" class="btn btn-primary" target="_blank">
        <i class="bi bi-printer me-2"></i>Print Slip
      </a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-success text-white">
      <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Registration Successful</h5>
    </div>
    <div class="card-body">
      <div class="row g-4 mb-4">
        <div class="col-md-6">
          <div class="p-3 border rounded h-100 bg-light">
            <div class="text-muted text-uppercase small">Visit Number</div>
            <div class="fs-4 fw-bold text-success">{{ $visit->visit_number }}</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="p-3 border rounded h-100 bg-light">
            <div class="text-muted text-uppercase small">Patient Number</div>
            <div class="fs-4 fw-bold text-primary">{{ $visit->patient->patient_number }}</div>
          </div>
        </div>
      </div>

      <h6 class="text-uppercase text-muted fw-semibold">Patient Information</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="fw-semibold">Full Name</div>
          <div>{{ $visit->patient->full_name }}</div>
        </div>
        <div class="col-md-2">
          <div class="fw-semibold">Sex</div>
          <div>{{ $visit->patient->sex }}</div>
        </div>
        <div class="col-md-3">
          <div class="fw-semibold">Date of Birth</div>
          <div>{{ $visit->patient->date_of_birth->format('F d, Y') }}</div>
        </div>
        <div class="col-md-3">
          <div class="fw-semibold">Age</div>
          <div>{{ $visit->patient->age }} years</div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="fw-semibold">Mobile Number</div>
          <div>{{ $visit->patient->mobile_number }}</div>
        </div>
        <div class="col-md-4">
          <div class="fw-semibold">Email</div>
          <div>{{ $visit->patient->email_address ?? 'N/A' }}</div>
        </div>
        <div class="col-md-4">
          <div class="fw-semibold">Patient Type</div>
          <div>{{ $visit->patient_type }}</div>
        </div>
      </div>

      <h6 class="text-uppercase text-muted fw-semibold">Visit Details</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="fw-semibold">Date of Visit</div>
          <div>{{ $visit->date_of_visit->format('F d, Y') }}</div>
        </div>
        <div class="col-md-3">
          <div class="fw-semibold">Time In</div>
          <div>{{ $visit->time_in->format('h:i A') }}</div>
        </div>
        <div class="col-md-3">
          <div class="fw-semibold">Visit Type</div>
          <div>{{ $visit->visit_type }}</div>
        </div>
        <div class="col-md-3">
          <div class="fw-semibold">Priority Level</div>
          <span class="badge bg-{{ $visit->priority_level === 'Emergency' ? 'danger' : ($visit->priority_level === 'Urgent' ? 'warning text-dark' : 'success') }}">
            {{ $visit->priority_level }}
          </span>
        </div>
        <div class="col-md-6">
          <div class="fw-semibold">Requested Service</div>
          <div>{{ $visit->requested_service }}</div>
        </div>
        @if($visit->assigned_department)
        <div class="col-md-6">
          <div class="fw-semibold">Assigned Department</div>
          <div>{{ $visit->assigned_department }}</div>
        </div>
        @endif
        <div class="col-12">
          <div class="fw-semibold">Reason for Visit</div>
          <div>{{ $visit->reason_for_visit }}</div>
        </div>
      </div>

      <h6 class="text-uppercase text-muted fw-semibold">Emergency Contact</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="fw-semibold">Name</div>
          <div>{{ $visit->patient->emergency_contact_name }}</div>
        </div>
        <div class="col-md-4">
          <div class="fw-semibold">Relationship</div>
          <div>{{ $visit->patient->emergency_contact_relationship }}</div>
        </div>
        <div class="col-md-4">
          <div class="fw-semibold">Contact Number</div>
          <div>{{ $visit->patient->emergency_contact_number }}</div>
        </div>
      </div>

      <h6 class="text-uppercase text-muted fw-semibold">Registration Details</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="fw-semibold">Registered By</div>
          <div>{{ $visit->registrationStaff->name }}</div>
        </div>
        <div class="col-md-6">
          <div class="fw-semibold">Recorded On</div>
          <div>{{ $visit->created_at->format('F d, Y h:i A') }}</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
