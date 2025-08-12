@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="container py-4">
  <div class="row">
    <div class="col-12">
      <h2 class="fw-bold text-primary mb-4">
        <i class="bi bi-speedometer2 me-2"></i>My Dashboard
      </h2>
    </div>
  </div>

  {{-- Health Activity Summary --}}
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center">
          <i class="bi bi-calendar-check text-success fs-1 mb-2"></i>
          <h4 class="fw-bold">{{ $upcoming->count() }}</h4>
          <p class="text-muted mb-0">Upcoming Appointments</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center">
          <i class="bi bi-hospital text-primary fs-1 mb-2"></i>
          <h4 class="fw-bold">{{ $past->count() }}</h4>
          <p class="text-muted mb-0">Past Visits</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center">
          <i class="bi bi-clock-history text-warning fs-1 mb-2"></i>
          <h4 class="fw-bold">{{ $upcoming->where('status', 'scheduled')->count() }}</h4>
          <p class="text-muted mb-0">Pending</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center">
          <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
          <h4 class="fw-bold">{{ $past->where('status', 'completed')->count() }}</h4>
          <p class="text-muted mb-0">Completed</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Quick Actions --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
          <h5 class="fw-semibold mb-3">
            <i class="bi bi-lightning-charge me-2"></i>Quick Actions
          </h5>
          <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('appointments.create') }}" class="btn btn-primary rounded-pill">
              <i class="bi bi-calendar-plus me-2"></i>Book New Appointment
            </a>
            <a href="{{ route('clinics.index') }}" class="btn btn-outline-primary rounded-pill">
              <i class="bi bi-search me-2"></i>Find Clinics
            </a>
            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary rounded-pill">
              <i class="bi bi-list-ul me-2"></i>View All Appointments
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Upcoming Appointments --}}
  @if($upcoming->isNotEmpty())
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-header bg-white border-bottom-0">
            <h5 class="fw-semibold mb-0">
              <i class="bi bi-clock me-2"></i>Upcoming Appointments
            </h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Date & Time</th>
                    <th>Clinic</th>
                    <th>Service</th>
                    <th>Doctor</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($upcoming->take(5) as $appointment)
                    <tr>
                      <td>
                        <div class="fw-semibold">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</div>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</small>
                      </td>
                      <td>{{ $appointment->clinic->name }}</td>
                      <td>{{ $appointment->service->service_name }}</td>
                      <td>{{ $appointment->doctor ? $appointment->doctor->first_name . ' ' . $appointment->doctor->last_name : '—' }}</td>
                      <td>
                        <span class="badge bg-warning text-dark rounded-pill">{{ ucfirst($appointment->status) }}</span>
                      </td>
                      <td>
                        @if($appointment->status === 'scheduled')
                          <form method="POST" action="{{ route('appointments.destroy', $appointment) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill"
                                    onclick="return confirm('Cancel this appointment?')">
                              <i class="bi bi-x-circle me-1"></i>Cancel
                            </button>
                          </form>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @if($upcoming->count() > 5)
              <div class="text-center mt-3">
                <a href="{{ route('appointments.index') }}" class="btn btn-link text-primary">
                  View All Appointments
                </a>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- Recent Clinic Visits --}}
  @if($past->isNotEmpty())
    <div class="row">
      <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-header bg-white border-bottom-0">
            <h5 class="fw-semibold mb-0">
              <i class="bi bi-clock-history me-2"></i>Recent Clinic Visits
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              @foreach($past->take(4) as $appointment)
                <div class="col-md-6 col-lg-3 mb-3">
                  <div class="d-flex align-items-center p-3 border rounded-3">
                    <div class="flex-shrink-0 me-3">
                      <i class="bi bi-hospital text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="fw-semibold mb-1">{{ $appointment->clinic->name }}</h6>
                      <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</p>
                      <span class="badge bg-success rounded-pill">{{ ucfirst($appointment->status) }}</span>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

</div>
@endsection
