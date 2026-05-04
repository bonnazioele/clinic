@extends('layouts.app')

@section('title','Walk-In Registration Confirmation')

@push('styles')
<style>
  .walkin-confirm-shell {
    width: 96%;
    max-width: none;
    margin: 0 auto;
  }

  .confirm-hero {
    border-radius: 26px;
    padding: 1.4rem;
    color: #fff;
    background:
      radial-gradient(circle at 88% 18%, rgba(255,255,255,.2), transparent 18%),
      linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    box-shadow: 0 18px 45px rgba(22,163,74,.22);
    margin-bottom: 1rem;
  }

  .confirm-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .confirm-hero-title-wrap {
    display: flex;
    gap: .9rem;
    align-items: flex-start;
  }

  .confirm-hero-icon {
    width: 60px;
    height: 60px;
    border-radius: 20px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    font-size: 1.65rem;
    flex: 0 0 60px;
  }

  .confirm-hero-title {
    margin: 0;
    font-size: clamp(1.5rem, 2.5vw, 2.15rem);
    font-weight: 900;
    letter-spacing: -.045em;
  }

  .confirm-hero-subtitle {
    margin: .35rem 0 0;
    opacity: .94;
    font-weight: 650;
  }

  .confirm-hero-actions {
    display: flex;
    gap: .65rem;
    flex-wrap: wrap;
  }

  .confirm-hero-actions .btn {
    border-radius: 15px;
    font-weight: 900;
  }

  .confirm-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .confirm-stat-card {
    border-radius: 24px;
    padding: 1rem;
    border: 1px solid rgba(226,232,240,.95);
    background: #fff;
    box-shadow: 0 14px 34px rgba(15,23,42,.08);
    min-height: 132px;
    display: flex;
    gap: .85rem;
    align-items: flex-start;
  }

  .confirm-stat-icon {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #0d6efd;
    font-size: 1.35rem;
    flex: 0 0 54px;
  }

  .confirm-stat-icon.green {
    background: #f0fdf4;
    color: #16a34a;
  }

  .confirm-stat-label {
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-size: .75rem;
    font-weight: 900;
  }

  .confirm-stat-value {
    margin-top: .3rem;
    color: #0f172a;
    font-size: 1.55rem;
    line-height: 1.1;
    font-weight: 900;
  }

  .confirm-panel {
    border: 1px solid rgba(226,232,240,.96);
    border-radius: 26px;
    background: rgba(255,255,255,.96);
    box-shadow: 0 18px 45px rgba(15,23,42,.08);
    overflow: hidden;
  }

  .confirm-panel-head {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    align-items: center;
    gap: .7rem;
  }

  .confirm-panel-head i {
    color: #0d6efd;
  }

  .confirm-panel-title {
    margin: 0;
    font-weight: 900;
    color: #0f172a;
  }

  .confirm-panel-body {
    padding: 1.2rem;
  }

  .confirm-section-title {
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-size: .78rem;
    font-weight: 900;
    margin: 0 0 .85rem;
  }

  .confirm-info-card {
    border: 1px solid #edf2f7;
    border-radius: 18px;
    padding: .85rem;
    height: 100%;
    background: #fff;
  }

  .confirm-info-label {
    color: #64748b;
    font-size: .78rem;
    font-weight: 800;
    margin-bottom: .25rem;
  }

  .confirm-info-value {
    color: #0f172a;
    font-weight: 800;
  }

  .confirm-priority {
    border-radius: 999px;
    padding: .45rem .75rem;
    font-weight: 900;
  }

  @media (max-width: 768px) {
    .walkin-confirm-shell {
      width: 94%;
    }

    .confirm-grid {
      grid-template-columns: 1fr;
    }

    .confirm-hero-actions .btn {
      width: 100%;
    }
  }
</style>
@endpush

@section('content')
<div class="walkin-confirm-shell py-4">
  <div class="confirm-hero">
    <div class="confirm-hero-row">
      <div class="confirm-hero-title-wrap">
        <div class="confirm-hero-icon">
          <i class="bi bi-check2-circle"></i>
        </div>
        <div>
          <h1 class="confirm-hero-title">Registration Successful</h1>
          <p class="confirm-hero-subtitle">
            The walk-in patient has been registered and added to the clinic queue.
          </p>
        </div>
      </div>

      <div class="confirm-hero-actions">
        <a href="{{ route('secretary.patients.create') }}" class="btn btn-outline-light">
          <i class="bi bi-arrow-left me-2"></i>Back to Registration
        </a>

        <a href="{{ route('secretary.walkin.print', $visit->id) }}" class="btn btn-light text-success" target="_blank">
          <i class="bi bi-printer me-2"></i>Print Slip
        </a>
      </div>
    </div>
  </div>

  <div class="confirm-grid">
    <div class="confirm-stat-card">
      <div class="confirm-stat-icon green">
        <i class="bi bi-receipt-cutoff"></i>
      </div>
      <div>
        <div class="confirm-stat-label">Visit Number</div>
        <div class="confirm-stat-value">{{ $visit->visit_number }}</div>
      </div>
    </div>

    <div class="confirm-stat-card">
      <div class="confirm-stat-icon">
        <i class="bi bi-person-badge"></i>
      </div>
      <div>
        <div class="confirm-stat-label">Patient Number</div>
        <div class="confirm-stat-value">{{ $visit->patient->patient_number }}</div>
      </div>
    </div>
  </div>

  <div class="confirm-panel">
    <div class="confirm-panel-head">
      <i class="bi bi-file-medical fs-5"></i>
      <h2 class="confirm-panel-title h5">Walk-In Registration Details</h2>
    </div>

    <div class="confirm-panel-body">
      <h6 class="confirm-section-title">Patient Information</h6>
      <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Full Name</div>
            <div class="confirm-info-value">{{ $visit->patient->full_name }}</div>
          </div>
        </div>

        <div class="col-lg-2 col-md-6">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Sex</div>
            <div class="confirm-info-value">{{ $visit->patient->sex }}</div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Date of Birth</div>
            <div class="confirm-info-value">{{ $visit->patient->date_of_birth->format('F d, Y') }}</div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Age</div>
            <div class="confirm-info-value">{{ $visit->patient->age }} years</div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Mobile Number</div>
            <div class="confirm-info-value">{{ $visit->patient->mobile_number }}</div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Email</div>
            <div class="confirm-info-value">{{ $visit->patient->email_address ?? 'N/A' }}</div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Patient Type</div>
            <div class="confirm-info-value">{{ $visit->patient_type }}</div>
          </div>
        </div>
      </div>

      <h6 class="confirm-section-title">Visit Details</h6>
      <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Date of Visit</div>
            <div class="confirm-info-value">{{ $visit->date_of_visit->format('F d, Y') }}</div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Time In</div>
            <div class="confirm-info-value">{{ $visit->time_in->format('h:i A') }}</div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Visit Type</div>
            <div class="confirm-info-value">{{ $visit->visit_type }}</div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Priority Level</div>
            <span class="badge confirm-priority bg-{{ $visit->priority_level === 'Emergency' ? 'danger' : ($visit->priority_level === 'Urgent' ? 'warning text-dark' : 'success') }}">
              {{ $visit->priority_level }}
            </span>
          </div>
        </div>

        <div class="col-md-6">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Requested Service</div>
            <div class="confirm-info-value">{{ $visit->requested_service }}</div>
          </div>
        </div>

        @if($visit->assigned_department)
          <div class="col-md-6">
            <div class="confirm-info-card">
              <div class="confirm-info-label">Assigned Department</div>
              <div class="confirm-info-value">{{ $visit->assigned_department }}</div>
            </div>
          </div>
        @endif

        <div class="col-12">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Reason for Visit</div>
            <div class="confirm-info-value">{{ $visit->reason_for_visit }}</div>
          </div>
        </div>
      </div>

      <h6 class="confirm-section-title">Emergency Contact</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Name</div>
            <div class="confirm-info-value">{{ $visit->patient->emergency_contact_name }}</div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Relationship</div>
            <div class="confirm-info-value">{{ $visit->patient->emergency_contact_relationship }}</div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Contact Number</div>
            <div class="confirm-info-value">{{ $visit->patient->emergency_contact_number }}</div>
          </div>
        </div>
      </div>

      <h6 class="confirm-section-title">Registration Details</h6>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Registered By</div>
            <div class="confirm-info-value">{{ $visit->registrationStaff->name }}</div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="confirm-info-card">
            <div class="confirm-info-label">Recorded On</div>
            <div class="confirm-info-value">{{ $visit->created_at->format('F d, Y h:i A') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection