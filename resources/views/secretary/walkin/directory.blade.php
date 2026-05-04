@extends('layouts.app')

@section('title', 'Walk-In Patients')

@push('styles')
<style>
  .walkin-directory-shell {
    width: 96%;
    max-width: none;
    margin: 0 auto;
  }

  .directory-hero {
    border-radius: 26px;
    padding: 1.4rem;
    color: #fff;
    background:
      radial-gradient(circle at 88% 18%, rgba(255,255,255,.2), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
    box-shadow: 0 18px 45px rgba(37,99,235,.22);
    margin-bottom: 1rem;
  }

  .directory-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .directory-title-wrap {
    display: flex;
    gap: .9rem;
    align-items: flex-start;
  }

  .directory-icon {
    width: 60px;
    height: 60px;
    border-radius: 20px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.18);
    font-size: 1.55rem;
    flex: 0 0 60px;
  }

  .directory-title {
    margin: 0;
    font-size: clamp(1.5rem, 2.5vw, 2.15rem);
    font-weight: 900;
    letter-spacing: -.045em;
  }

  .directory-subtitle {
    margin: .35rem 0 0;
    opacity: .94;
    font-weight: 650;
  }

  .directory-hero .btn {
    border-radius: 15px;
    font-weight: 900;
  }

  .directory-card {
    border: 1px solid rgba(226,232,240,.96);
    border-radius: 26px;
    background: rgba(255,255,255,.96);
    box-shadow: 0 18px 45px rgba(15,23,42,.08);
    overflow: hidden;
    margin-bottom: 1rem;
  }

  .directory-card-body {
    padding: 1.15rem;
  }

  .directory-card .form-label {
    font-weight: 900;
    color: #334155;
  }

  .directory-card .form-control {
    border-radius: 15px;
    min-height: 46px;
    border-color: #dbe3ef;
    font-weight: 650;
  }

  .directory-card .btn {
    border-radius: 15px;
    font-weight: 900;
    min-height: 46px;
  }

  .directory-table {
    margin: 0;
  }

  .directory-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-weight: 900;
    border-bottom: 1px solid #e2e8f0;
    padding: .85rem;
  }

  .directory-table tbody td {
    padding: 1rem .85rem;
    vertical-align: middle;
    border-color: #edf2f7;
  }

  .patient-avatar {
    width: 44px;
    height: 44px;
    border-radius: 15px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #0d6efd;
    font-weight: 900;
    flex: 0 0 44px;
  }

  .patient-name-wrap {
    display: flex;
    align-items: center;
    gap: .75rem;
  }

  .visit-badge {
    border-radius: 999px;
    padding: .45rem .75rem;
    font-weight: 900;
  }

  .empty-state {
    text-align: center;
    padding: 4rem 1rem;
  }

  .empty-state-icon {
    width: 76px;
    height: 76px;
    margin: 0 auto 1rem;
    border-radius: 24px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #0d6efd;
    font-size: 2rem;
  }

  @media (max-width: 768px) {
    .walkin-directory-shell {
      width: 94%;
    }

    .directory-hero .btn {
      width: 100%;
    }
  }
</style>
@endpush

@section('content')
<div class="walkin-directory-shell py-4">
  @include('partials.alerts')

  <div class="directory-hero">
    <div class="directory-hero-row">
      <div class="directory-title-wrap">
        <div class="directory-icon">
          <i class="bi bi-person-lines-fill"></i>
        </div>
        <div>
          <h1 class="directory-title">Walk-In Patients</h1>
          <p class="directory-subtitle">
            View registered walk-in patients for {{ $clinic->name }}.
          </p>
        </div>
      </div>

      <a href="{{ route('secretary.walkin.index') }}" class="btn btn-light text-primary">
        <i class="bi bi-clipboard-plus me-1"></i>Register Walk-In
      </a>
    </div>
  </div>

  <div class="directory-card">
    <div class="directory-card-body">
      <form method="GET" action="{{ route('secretary.walkin.patients') }}" class="row g-3 align-items-end">
        <div class="col-lg-7">
          <label class="form-label">
            <i class="bi bi-search me-1"></i>Search walk-in patients
          </label>
          <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by name, patient ID, or phone number">
        </div>

        <div class="col-lg-3 col-md-6">
          <button class="btn btn-primary w-100">
            <i class="bi bi-funnel me-1"></i>Filter
          </button>
        </div>

        @if(request('q'))
          <div class="col-lg-2 col-md-6">
            <a href="{{ route('secretary.walkin.patients') }}" class="btn btn-outline-secondary w-100">
              <i class="bi bi-x-circle me-1"></i>Clear
            </a>
          </div>
        @endif
      </form>
    </div>
  </div>

  <div class="directory-card">
    <div class="directory-card-body">
      @if($patients->isEmpty())
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="bi bi-person-exclamation"></i>
          </div>
          <h5 class="fw-bold text-dark mb-2">No walk-in patients found</h5>
          <p class="text-muted mb-3">
            No walk-in patients are recorded for this clinic yet.
          </p>
          <a href="{{ route('secretary.walkin.index') }}" class="btn btn-primary rounded-pill fw-bold">
            <i class="bi bi-clipboard-plus me-2"></i>Register a Walk-In Patient
          </a>
        </div>
      @else
        <div class="table-responsive">
          <table class="table directory-table align-middle">
            <thead>
              <tr>
                <th>Patient</th>
                <th>Last Visit</th>
                <th>Visits Recorded</th>
                <th>Contact</th>
              </tr>
            </thead>

            <tbody>
              @foreach($patients as $patient)
                @php
                  $latestVisit = $patient->visits->first();
                  $initials = collect(explode(' ', $patient->full_name))
                      ->filter()
                      ->take(2)
                      ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                      ->implode('');
                @endphp

                <tr>
                  <td>
                    <div class="patient-name-wrap">
                      <div class="patient-avatar">
                        {{ $initials ?: 'P' }}
                      </div>
                      <div>
                        <div class="fw-bold text-dark">{{ $patient->full_name }}</div>
                        <div class="small text-muted">Patient ID: {{ $patient->patient_number }}</div>
                      </div>
                    </div>
                  </td>

                  <td>
                    @if($latestVisit)
                      <div class="fw-bold text-dark">{{ $latestVisit->date_of_visit->format('M d, Y') }}</div>
                      <div class="small text-muted">{{ $latestVisit->requested_service }}</div>
                    @else
                      <span class="text-muted">No visits yet</span>
                    @endif
                  </td>

                  <td>
                    <span class="badge bg-primary visit-badge">
                      {{ $patient->clinic_visits_count }} visits
                    </span>
                  </td>

                  <td>
                    <div class="small fw-semibold">
                      <i class="bi bi-telephone me-1 text-primary"></i>{{ $patient->mobile_number }}
                    </div>

                    @if($patient->email_address)
                      <div class="small text-muted">
                        <i class="bi bi-envelope me-1"></i>{{ $patient->email_address }}
                      </div>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="mt-3">
          {{ $patients->links('vendor.pagination.compact') }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection