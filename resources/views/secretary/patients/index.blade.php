@extends('layouts.app')

@section('title', 'Patients')

@section('content')
<style>
  .patients-page {
    width: 96%;
    max-width: 1380px;
    margin: 0 auto;
    padding: 1rem 0 2rem;
  }

  .patients-page .medical-card {
    border: 1px solid rgba(226, 232, 240, 0.96) !important;
    border-radius: 24px !important;
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08) !important;
  }

  .patients-hero {
    color: #ffffff;
    background:
      radial-gradient(circle at 90% 28%, rgba(255, 255, 255, 0.18), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%) !important;
  }

  .patients-hero h1,
  .patients-hero .text-muted {
    color: #ffffff !important;
  }

  .patients-hero .badge {
    background: rgba(255, 255, 255, 0.16) !important;
    color: #ffffff !important;
    border-color: rgba(255, 255, 255, 0.22) !important;
  }

  .patients-page .form-control {
    min-height: 46px;
    border-radius: 14px;
    border-color: #dbe3ef;
    background: #f8fafc;
    font-weight: 650;
    box-shadow: none;
  }

  .patients-page .btn {
    border-radius: 14px;
    font-weight: 900;
  }

  .patients-page table thead th {
    background: #f8fafc !important;
    color: #475569;
    letter-spacing: 0.06em;
  }

  .patients-page table tbody tr:hover {
    background: #f8fafc;
  }
</style>

<div class="container py-4 patients-page">
  @include('partials.alerts')

  <div class="medical-card patients-hero p-4 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
      <div>
        <h1 class="h3 fw-bold text-primary mb-1">
          <i class="bi bi-people me-2"></i>Patients
        </h1>
        <div class="d-flex flex-wrap align-items-center gap-2 text-muted">
          <span class="badge rounded-pill bg-light text-primary border border-primary-subtle px-3 py-2">
            <i class="bi bi-hospital me-1"></i>{{ $clinic->name }}
          </span>
          @if($clinic->branch_code)
            <span class="badge rounded-pill bg-light text-muted border px-3 py-2">{{ $clinic->branch_code }}</span>
          @endif
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
          <i class="bi bi-clipboard-data me-1"></i>{{ $patients->total() }} total
        </span>
        <a href="{{ route('secretary.patients.create') }}" class="btn btn-primary">
          <i class="bi bi-person-plus me-2"></i>Register Patient
        </a>
      </div>
    </div>
  </div>

  <div class="medical-card p-4 mb-4">
    <form method="GET" action="{{ route('secretary.patients.index') }}" class="row g-3 align-items-end">
      <div class="col-lg-6">
        <label class="form-label fw-semibold"><i class="bi bi-search me-1"></i>Search patients</label>
        <input type="text"
               name="q"
               value="{{ request('q') }}"
               class="form-control"
               placeholder="Name, email, or phone">
      </div>
      <div class="col-lg-3 col-md-4">
        <button class="btn btn-primary w-100">
          <i class="bi bi-funnel me-1"></i>Filter
        </button>
      </div>
      @if(request('q'))
        <div class="col-lg-3 col-md-4">
          <a href="{{ route('secretary.patients.index') }}" class="btn btn-outline-secondary w-100">
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
        <p class="mb-2">No patients found for this clinic yet.</p>
        <a href="{{ route('secretary.patients.create') }}" class="btn btn-primary">
          <i class="bi bi-person-plus me-2"></i>Register a Patient
        </a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th class="text-uppercase small fw-semibold">
                <span class="d-inline-flex align-items-center gap-2">
                  <i class="bi bi-person-badge"></i>
                  Patient
                </span>
              </th>
              <th class="text-uppercase small fw-semibold">
                <span class="d-inline-flex align-items-center gap-2">
                  <i class="bi bi-calendar-event"></i>
                  Last Visit
                </span>
              </th>
              <th class="text-uppercase small fw-semibold">
                <span class="d-inline-flex align-items-center gap-2">
                  <i class="bi bi-bar-chart"></i>
                  Activity
                </span>
              </th>
              <th class="text-uppercase small fw-semibold">
                <span class="d-inline-flex align-items-center gap-2">
                  <i class="bi bi-chat-dots"></i>
                  Contact
                </span>
              </th>
              <th class="text-uppercase small fw-semibold text-end">
                <span class="d-inline-flex align-items-center gap-2 justify-content-end">
                  <i class="bi bi-gear"></i>
                  Actions
                </span>
              </th>
            </tr>
          </thead>
          <tbody>
            @foreach($patients as $patient)
              @php
                $lastVisit = $patient->last_appointment_date ?? $patient->last_queue_activity;
                $lastVisitFormatted = $lastVisit
                    ? \Carbon\Carbon::parse($lastVisit)->format('M d, Y')
                    : null;
                $appointmentsCount = $patient->clinic_appointments_count ?? 0;
                $queueCount = $patient->clinic_queue_entries_count ?? 0;
              @endphp
              <tr>
                <td>
                  <div class="fw-semibold">{{ $patient->name }}</div>
                  <div class="small text-muted">Account ID: #{{ $patient->id }}</div>
                </td>
                <td>
                  @if($lastVisitFormatted)
                    <span class="fw-semibold">{{ $lastVisitFormatted }}</span>
                    <div class="small text-muted">
                      {{ $patient->last_appointment_date ? 'Appointment' : 'Walk-In' }}
                    </div>
                  @else
                    <span class="text-muted">No interactions yet</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-primary text-white px-3">
                      <i class="bi bi-clipboard-check me-1"></i>{{ $appointmentsCount }} appt
                    </span>
                    <span class="badge bg-info text-dark px-3">
                      <i class="bi bi-people me-1"></i>{{ $queueCount }} queue
                    </span>
                  </div>
                </td>
                <td>
                  <div class="small">
                    <i class="bi bi-envelope me-1"></i>
                    @if($patient->email)
                      <a href="mailto:{{ $patient->email }}" class="text-decoration-none">{{ $patient->email }}</a>
                    @else
                      <span class="text-muted">No email</span>
                    @endif
                  </div>
                  <div class="small mt-1">
                    <i class="bi bi-telephone me-1"></i>
                    @if($patient->phone)
                      <a href="tel:{{ $patient->phone }}" class="text-decoration-none">{{ $patient->phone }}</a>
                    @else
                      <span class="text-muted">No phone</span>
                    @endif
                  </div>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2">
                    <a href="{{ route('secretary.patients.edit', $patient) }}" class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <form method="POST" action="{{ route('secretary.patients.destroy', $patient) }}" onsubmit="return confirm('Delete {{ $patient->name }}? This cannot be undone.');">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>Delete
                      </button>
                    </form>
                  </div>
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
