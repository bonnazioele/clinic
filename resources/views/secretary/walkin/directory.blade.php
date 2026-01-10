@extends('layouts.app')

@section('title', 'Walk-In Patients')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="medical-card p-4 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
      <div>
        <h1 class="h3 fw-bold text-primary mb-1">
          <i class="bi bi-person-badge me-2"></i>Walk-In Patients
        </h1>
        <div class="text-muted">{{ $clinic->name }}</div>
      </div>
      <div>
        <a href="{{ route('secretary.walkin.index') }}" class="btn btn-outline-primary">
          <i class="bi bi-clipboard-plus me-1"></i>Register Walk-In
        </a>
      </div>
    </div>
  </div>

  <div class="medical-card p-4 mb-4">
    <form method="GET" action="{{ route('secretary.walkin.patients') }}" class="row g-3 align-items-end">
      <div class="col-lg-6">
        <label class="form-label fw-semibold"><i class="bi bi-search me-1"></i>Search walk-in patients</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name, ID, or phone">
      </div>
      <div class="col-lg-3 col-md-4">
        <button class="btn btn-primary w-100">
          <i class="bi bi-funnel me-1"></i>Filter
        </button>
      </div>
      @if(request('q'))
        <div class="col-lg-3 col-md-4">
          <a href="{{ route('secretary.walkin.patients') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-x-circle me-1"></i>Clear
          </a>
        </div>
      @endif
    </form>
  </div>

  <div class="medical-card p-4">
    @if($patients->isEmpty())
      <div class="text-center text-muted py-5">
        <i class="bi bi-person-exclamation display-5 d-block mb-3"></i>
        <p class="mb-2">No walk-in patients for this clinic yet.</p>
        <a href="{{ route('secretary.walkin.index') }}" class="btn btn-primary">
          <i class="bi bi-clipboard-plus me-2"></i>Register a Walk-In Patient
        </a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
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
              @endphp
              <tr>
                <td>
                  <div class="fw-semibold">{{ $patient->full_name }}</div>
                  <div class="small text-muted">Patient ID: {{ $patient->patient_number }}</div>
                </td>
                <td>
                  @if($latestVisit)
                    <div class="fw-semibold">{{ $latestVisit->date_of_visit->format('M d, Y') }}</div>
                    <div class="small text-muted">{{ $latestVisit->requested_service }}</div>
                  @else
                    <span class="text-muted">No visits yet</span>
                  @endif
                </td>
                <td>
                  <span class="badge bg-primary">{{ $patient->clinic_visits_count }} visits</span>
                </td>
                <td>
                  <div class="small"><i class="bi bi-telephone me-1"></i>{{ $patient->mobile_number }}</div>
                  @if($patient->email_address)
                    <div class="small"><i class="bi bi-envelope me-1"></i>{{ $patient->email_address }}</div>
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
@endsection
