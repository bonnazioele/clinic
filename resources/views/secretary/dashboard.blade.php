@extends('layouts.secretary')

@section('title', 'Secretary Dashboard')

@section('content')
<div class="container-fluid page-container">
  {{-- Page Header with aligned content --}}
  <div class="page-header">
    <h2 class="page-title">
      <i class="bi bi-speedometer2 me-2"></i>
      {{ session('current_clinic_name', 'Central Medical Clinic') }}
    </h2>

    <div class="toolbar">
      {{-- Search (Left side of toolbar) --}}
      <div class="input-icon toolbar-search">
        <i class="bi bi-search icon"></i>
        <input type="text" class="form-control" placeholder="Search patients, appointments…">
      </div>

      {{-- Quick Actions (Right side) --}}
      <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" id="quickActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-lightning-fill me-2"></i> Quick Actions
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="quickActionsDropdown">
          <li>
            <a class="dropdown-item" href="{{ route('secretary.appointments.index') }}">
              <i class="bi bi-calendar-plus me-2"></i> Add Appointment
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('secretary.doctors.index') }}">
              <i class="bi bi-person-walking me-2"></i> Add Walk-in Patient
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('secretary.doctors.create') }}">
              <i class="bi bi-person-plus me-2"></i> Create Patient Account
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item" href="{{ route('secretary.services.index') }}">
              <i class="bi bi-clipboard2-pulse me-2"></i> Manage Services
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  </div>

  {{-- Stat Cards --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-card__icon"><i class="bi bi-calendar-check"></i></div>
        <div class="stat-card__label">Today’s Appointments</div>
        <div class="stat-card__value">{{ $todayAppointments ?? 0 }}</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-card__icon"><i class="bi bi-people"></i></div>
        <div class="stat-card__label">Queue (Waiting)</div>
        <div class="stat-card__value">{{ $queueCount ?? 0 }}</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-card__icon"><i class="bi bi-person-badge"></i></div>
        <div class="stat-card__label">Active Doctors</div>
        <div class="stat-card__value">{{ $activeDoctors ?? 0 }}</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-card__icon"><i class="bi bi-gear"></i></div>
        <div class="stat-card__label">Services</div>
        <div class="stat-card__value">{{ $servicesCount ?? 0 }}</div>
      </div>
    </div>
  </div>

  {{-- Two-up tables --}}
  <div class="row g-3">
    {{-- Today’s Queue --}}
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="section-title mb-0">
            <i class="bi bi-list-ol me-2"></i> Today’s Queue
          </h5>
          <a href="#" class="btn btn-soft btn-soft-primary btn-sm disabled" tabindex="-1" aria-disabled="true">
            Live Queue Dashboard <i class="bi bi-broadcast ms-2"></i>
          </a>
        </div>
        <div class="card-body">
          @if(!empty($queue) && count($queue))
            <div class="table-responsive">
              <table class="table table-clean align-middle mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($queue as $item)
                  <tr>
                    <td>{{ $item->ticket_no ?? '—' }}</td>
                    <td>{{ $item->patient_name ?? 'Guest' }}</td>
                    <td>{{ $item->service_name ?? '—' }}</td>
                    <td>
                      @php $st = strtolower($item->status ?? 'waiting'); @endphp
                      <span class="badge text-bg-{{ $st === 'serving' ? 'primary' : ($st === 'waiting' ? 'secondary' : ($st === 'completed' ? 'success' : 'warning')) }}">
                        {{ ucfirst($st) }}
                      </span>
                    </td>
                    <td class="text-end">
                      <div class="btn-group btn-group-sm">
                        <a href="#" class="btn btn-soft btn-soft-success" title="Serve"><i class="bi bi-play-fill"></i></a>
                        <a href="#" class="btn btn-soft btn-soft-warning" title="Requeue"><i class="bi bi-arrow-repeat"></i></a>
                        <a href="#" class="btn btn-soft btn-soft-danger" title="Remove"><i class="bi bi-x-lg"></i></a>
                      </div>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center text-muted py-5">
              <i class="bi bi-inboxes display-6 d-block mb-2"></i>
              No queue entries yet.
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Today’s Appointments --}}
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="section-title mb-0">
            <i class="bi bi-calendar-event me-2"></i> Today’s Appointments
          </h5>
          <a href="{{ route('secretary.appointments.index') }}" class="btn btn-soft btn-soft-primary btn-sm">
            Manage <i class="bi bi-arrow-right ms-2"></i>
          </a>
        </div>
        <div class="card-body">
          @if(!empty($appointments) && count($appointments))
            <div class="table-responsive">
              <table class="table table-clean align-middle mb-0">
                <thead>
                  <tr>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Service</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($appointments as $appt)
                  <tr>
                    <td>{{ \Carbon\Carbon::parse($appt->scheduled_at ?? now())->format('h:i A') }}</td>
                    <td>{{ $appt->patient_name ?? '—' }}</td>
                    <td>{{ $appt->doctor_name ?? '—' }}</td>
                    <td>{{ $appt->service_name ?? '—' }}</td>
                    <td class="text-end">
                      <div class="btn-group btn-group-sm">
                        <a href="#" class="btn btn-soft btn-soft-success" title="Check-in"><i class="bi bi-check2-circle"></i></a>
                        <a href="#" class="btn btn-soft btn-soft-danger" title="Cancel"><i class="bi bi-x-lg"></i></a>
                      </div>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center text-muted py-5">
              <i class="bi bi-calendar-x display-6 d-block mb-2"></i>
              No appointments scheduled yet.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Overview --}}
  <div class="row g-3 mt-1">
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header">
          <h5 class="section-title mb-0">
            <i class="bi bi-graph-up me-2"></i> Dashboard Overview
          </h5>
        </div>
        <div class="card-body">
          <div class="text-center py-5 text-muted">
            <i class="bi bi-speedometer2 display-6 d-block mb-2"></i>
            Analytics & charts coming soon.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
