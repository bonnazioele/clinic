@extends('layouts.app')

@section('title', 'My Appointments')

@section('content')
<div class="container py-4">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-white border-bottom-0 pb-0">
      <h3 class="text-primary fw-bold">
        <i class="bi bi-clipboard2-check-fill me-2"></i>My Appointments
      </h3>
    </div>
    <div class="card-body">

      {{-- Upcoming Appointments --}}
      <h5 class="mt-2 mb-3 fw-semibold">
        <i class="bi bi-clock-history me-2"></i>Upcoming Appointments
      </h5>

      @if($upcoming->isEmpty())
        <div class="alert alert-info text-center rounded-3">
          No upcoming appointments.
          <a href="{{ route('appointments.create') }}" class="text-decoration-none fw-semibold">Book one now</a>.
        </div>
      @else
        <div class="table-responsive mb-5">
          <table class="table align-middle table-hover">
            <thead class="table-light">
              <tr>
                <th>Clinic</th>
                <th>Service</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($upcoming as $a)
                <tr>
                  <td class="fw-semibold">{{ $a->clinic->name }}</td>
                  <td>{{ $a->service->service_name }}</td>
                  <td>{{ $a->doctor ? $a->doctor->first_name . ' ' . $a->doctor->last_name : '—' }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_date)->isoFormat('MMM D, YYYY') }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_time)->format('h:i A') }}</td>
                  <td>
                    <span class="badge rounded-pill
                      {{ $a->status === 'scheduled' ? 'bg-warning text-dark' :
                         ($a->status === 'completed' ? 'bg-success' : 'bg-secondary') }}">
                      {{ ucfirst($a->status) }}
                    </span>
                  </td>
                  <td>
                    @if($a->status === 'scheduled')
                      <form method="POST"
                            action="{{ route('appointments.destroy', $a) }}"
                            onsubmit="return confirm('Cancel this appointment?')"
                            class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3">
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
      @endif

      {{-- Past Appointments --}}
      <h5 class="mt-4 mb-3 fw-semibold">
        <i class="bi bi-archive-fill me-2"></i>Past Appointments
      </h5>

      @if($past->isEmpty())
        <div class="alert alert-secondary text-center rounded-3">
          You have no past appointments.
        </div>
      @else
        <div class="table-responsive">
          <table class="table align-middle table-hover">
            <thead class="table-light">
              <tr>
                <th>Clinic</th>
                <th>Service</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($past as $a)
                <tr>
                  <td class="fw-semibold">{{ $a->clinic->name }}</td>
                  <td>{{ $a->service->service_name }}</td>
                  <td>{{ $a->doctor ? $a->doctor->first_name . ' ' . $a->doctor->last_name : '—' }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_date)->isoFormat('MMM D, YYYY') }}</td>
                  <td>{{ \Carbon\Carbon::parse($a->appointment_time)->format('h:i A') }}</td>
                  <td>
                    <span class="badge rounded-pill
                      {{ $a->status === 'scheduled' ? 'bg-warning text-dark' :
                         ($a->status === 'completed' ? 'bg-success' : 'bg-secondary') }}">
                      {{ ucfirst($a->status) }}
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

    </div>
  </div>
</div>
@endsection
