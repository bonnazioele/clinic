@extends('layouts.app')

@section('title', 'Manage Appointments')

@section('content')
<div class="container py-4">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">
          <i class="bi bi-calendar-check me-2"></i>Manage Appointments
        </h2>
        <a href="{{ route('appointments.create') }}" class="btn btn-success rounded-pill">
          <i class="bi bi-plus-circle me-2"></i>New Appointment
        </a>
      </div>

      @include('partials.alerts')

      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Patient</th>
                  <th>Clinic</th>
                  <th>Service</th>
                  <th>Doctor</th>
                  <th>Date & Time</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($appointments as $appointment)
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="bg-light text-dark rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 40px; height: 40px;">
                          <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                          <div class="fw-semibold">{{ $appointment->user->first_name }} {{ $appointment->user->last_name }}</div>
                          <small class="text-muted">{{ $appointment->user->email }}</small>
                        </div>
                      </div>
                    </td>
                    <td>{{ $appointment->clinic->name }}</td>
                    <td>{{ $appointment->service->service_name }}</td>
                    <td>
                      @if($appointment->doctor)
                        {{ $appointment->doctor->first_name }} {{ $appointment->doctor->last_name }}
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td>
                      <div class="fw-semibold">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</div>
                      <small class="text-muted">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</small>
                    </td>
                    <td>
                      @if($appointment->status === 'scheduled')
                        <span class="badge bg-warning text-dark rounded-pill">Scheduled</span>
                      @elseif($appointment->status === 'completed')
                        <span class="badge bg-success text-white rounded-pill">Completed</span>
                      @elseif($appointment->status === 'cancelled')
                        <span class="badge bg-danger text-white rounded-pill">Cancelled</span>
                      @else
                        <span class="badge bg-secondary text-white rounded-pill">{{ ucfirst($appointment->status) }}</span>
                      @endif
                    </td>
                    <td>
                      <div class="btn-group" role="group">
                        <a href="{{ route('secretary.appointments.edit', $appointment) }}"
                           class="btn btn-sm btn-outline-primary rounded-pill">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('secretary.appointments.destroy', $appointment) }}"
                              class="d-inline" onsubmit="return confirm('Delete this appointment?')">
                          @csrf @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center py-5">
                      <div class="text-muted">
                        <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No appointments found</h5>
                        <p>Start by creating a new appointment for a patient.</p>
                      </div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      @if($appointments->hasPages())
        <div class="mt-4 d-flex justify-content-center">
          {{ $appointments->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
