@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title','Dashboard')

@section('content')
<div class="container py-4">
  @include('partials.alerts')
  <!-- Welcome Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="medical-card p-4 text-center">
        <div class="d-flex align-items-center justify-content-center mb-3">
          <i class="bi bi-heart-pulse-fill medical-icon me-3" style="font-size: 3rem;"></i>
          <div>
            <h1 class="mb-1 fw-bold text-primary">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="text-muted mb-0">
              @if(Auth::user()->is_admin)
                <i class="bi bi-shield-check me-2"></i>Administrator Dashboard
              @elseif(Auth::user()->is_secretary)
                <i class="bi bi-person-badge me-2"></i>Secretary Dashboard
              @else
                <i class="bi bi-person-heart me-2"></i>Patient Dashboard
              @endif
            </p>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-3">
            <div class="text-center">
              <div class="dashboard-stat">{{ $upcoming->count() }}</div>
              <small class="text-muted">Upcoming Appointments</small>
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-center">
              <div class="dashboard-stat">{{ $past->count() }}</div>
              <small class="text-muted">Past Visits</small>
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-center">
              <div class="dashboard-stat">
                @if(Auth::user()->is_admin)
                  {{ \App\Models\Clinic::count() }}
                @elseif(Auth::user()->is_secretary)
                  {{ \App\Models\User::where('is_doctor', true)->count() }}
                @else
                  {{ \App\Models\Clinic::count() }}
                @endif
              </div>
              <small class="text-muted">
                @if(Auth::user()->is_admin)
                  Total Clinics
                @elseif(Auth::user()->is_secretary)
                  Total Doctors
                @else
                  Available Clinics
                @endif
              </small>
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-center">
              <div class="dashboard-stat">
                @if(Auth::user()->is_admin)
                  {{ \App\Models\Service::count() }}
                @elseif(Auth::user()->is_secretary)
                  {{ \App\Models\Appointment::whereDate('appointment_date', Carbon::today())->count() }}
                @else
                  {{ \App\Models\Service::count() }}
                @endif
              </div>
              <small class="text-muted">
                @if(Auth::user()->is_admin)
                  Total Services
                @elseif(Auth::user()->is_secretary)
                  Today's Appointments
                @else
                  Available Services
                @endif
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    {{-- Upcoming Appointments --}}
    <div class="col-lg-6">
      <div class="dashboard-card">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h5 class="mb-0">
            <i class="bi bi-calendar-check medical-icon me-2"></i>Upcoming Appointments
          </h5>
          <span class="badge bg-primary">{{ $upcoming->count() }}</span>
        </div>

        @if($upcoming->isEmpty())
          <div class="text-center py-4">
            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2 mb-3">No upcoming appointments scheduled.</p>
            <a href="{{ route('appointments.create') }}" class="btn btn-primary">
              <i class="bi bi-plus-circle me-2"></i>Book Appointment
            </a>
          </div>
        @else
          <div class="list-group list-group-flush">
            @foreach($upcoming->take(5) as $appointment)
              <div class="list-group-item border-0 px-0 py-2">
                <div class="d-flex align-items-center">
                  <div class="me-3">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 40px; height: 40px;">
                      <i class="bi bi-calendar-event text-white"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $appointment->clinic->name }}</div>
                    <div class="text-muted small">
                      <i class="bi bi-clock me-1"></i>
                      {{ Carbon::parse($appointment->appointment_date)->format('M j, Y') }} at
                      {{ Carbon::parse($appointment->appointment_time)->format('g:i A') }}
                    </div>
                    <div class="text-muted small">
                      <i class="bi bi-gear me-1"></i>{{ $appointment->service->name }}
                    </div>
                  </div>
                  <div class="ms-2">
                    <a href="{{ route('appointments.edit', $appointment) }}"
                       class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-pencil"></i>
                    </a>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
          <div class="text-center mt-3">
            <a href="{{ route('appointments.index') }}" class="btn btn-outline-primary">
              <i class="bi bi-arrow-right me-2"></i>View All Appointments
            </a>
          </div>
        @endif
      </div>
    </div>

    {{-- Quick Actions --}}
    <div class="col-lg-6">
      <div class="dashboard-card">
        <h5 class="mb-3">
          <i class="bi bi-lightning medical-icon me-2"></i>Quick Actions
        </h5>
        <div class="d-grid gap-3">
          @if(Auth::user()->is_admin)
            <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary">
              <i class="bi bi-building-add me-2"></i>Add New Clinic
            </a>
            <a href="{{ route('admin.services.create') }}" class="btn btn-success">
              <i class="bi bi-gear-add me-2"></i>Add New Service
            </a>
          @elseif(Auth::user()->is_secretary)
            <a href="{{ route('secretary.appointments.create') }}" class="btn btn-primary">
              <i class="bi bi-calendar-plus me-2"></i>Create Appointment
            </a>
            <a href="{{ route('secretary.doctors.create') }}" class="btn btn-success">
              <i class="bi bi-person-plus me-2"></i>Add New Doctor
            </a>
            <a href="{{ route('secretary.queue.overview') }}" class="btn btn-warning">
              <i class="bi bi-people me-2"></i>Manage Queue
            </a>
          @else
            <a href="{{ route('appointments.create') }}" class="btn btn-primary">
              <i class="bi bi-calendar-plus me-2"></i>Book Appointment
            </a>
            <a href="{{ route('queue.status') }}" class="btn btn-warning">
              <i class="bi bi-people me-2"></i>Check Queue Status
            </a>
            <a href="{{ route('clinics.index') }}" class="btn btn-info">
              <i class="bi bi-building me-2"></i>Find Clinics
            </a>
          @endif
          <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
            <i class="bi bi-person-gear me-2"></i>Edit Profile
          </a>
        </div>
      </div>

      {{-- Queue Status for Patients --}}
      @if(!Auth::user()->is_admin && !Auth::user()->is_secretary)
        @php
          $activeQueues = Auth::user()->queueEntries()
            ->where('status', 'waiting')
            ->with('clinic')
            ->orderBy('created_at', 'desc')
            ->get();
        @endphp

        @if($activeQueues->count() > 0)
          <div class="dashboard-card mt-4">
            <h5 class="mb-3">
              <i class="bi bi-clock-history medical-icon me-2"></i>Current Queue Status
            </h5>
            <div class="list-group list-group-flush">
              @foreach($activeQueues as $queueEntry)
                <div class="list-group-item border-0 px-0 py-2">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <div class="fw-semibold">{{ $queueEntry->clinic->name }}</div>
                      <div class="text-muted small">
                        <i class="bi bi-hash me-1"></i>Queue #{{ $queueEntry->queue_number }}
                      </div>
                      <div class="text-muted small">
                        <i class="bi bi-clock me-1"></i>Joined at {{ $queueEntry->formatted_created_time }}
                      </div>
                    </div>
                    <div class="text-end">
                      <span class="badge bg-warning text-dark">
                        <i class="bi bi-clock me-1"></i>Waiting
                      </span>
                      <div class="mt-1">
                        <a href="{{ route('queue.status.entry', $queueEntry) }}"
                           class="btn btn-sm btn-outline-primary">
                          <i class="bi bi-eye me-1"></i>View Details
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
            <div class="text-center mt-3">
              <a href="{{ route('queue.status') }}" class="btn btn-outline-warning">
                <i class="bi bi-arrow-right me-2"></i>View All Queue Status
              </a>
            </div>
          </div>
        @else
          {{-- Queue Information Card --}}
          <div class="dashboard-card mt-4">
            <div class="alert alert-info border-0 mb-0">
              <h6 class="fw-semibold mb-2">
                <i class="bi bi-info-circle me-2"></i>About Appointments & Queues
              </h6>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-2">
                    <strong><i class="bi bi-calendar-check me-1"></i>Appointments:</strong>
                    <small class="text-muted d-block">Scheduled future visits</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-2">
                    <strong><i class="bi bi-people me-1"></i>Queues:</strong>
                    <small class="text-muted d-block">Automatically added when you book an appointment</small>
                  </div>
                </div>
              </div>
              <div class="mt-3">
                <small class="text-muted">
                  <i class="bi bi-lightbulb me-1"></i>
                  <strong>Great news!</strong> You're automatically added to the queue when you book an appointment. No need to manually join!
                </small>
              </div>
            </div>
          </div>
        @endif
      @endif

      <!-- Recent Activity -->
      <div class="dashboard-card mt-4">
        <h5 class="mb-3">
          <i class="bi bi-activity medical-icon me-2"></i>Recent Activity
        </h5>
        <div class="list-group list-group-flush">
          @foreach(auth()->user()->notifications()->latest()->take(3)->get() as $notification)
            <div class="list-group-item border-0 px-0 py-2">
              <div class="d-flex align-items-start">
                <i class="bi bi-info-circle text-primary me-2 mt-1"></i>
                <div class="flex-grow-1">
                  <div class="small">{{ \Illuminate\Support\Str::limit($notification->data['message'], 80) }}</div>
                  <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                  </small>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Past Appointments/Recent Visits --}}
    <div class="col-12">
      <div class="dashboard-card">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h5 class="mb-0">
            <i class="bi bi-clock-history medical-icon me-2"></i>
            @if(Auth::user()->is_admin)
              Recent Clinic Activities
            @elseif(Auth::user()->is_secretary)
              Recent Appointments
            @else
              Recent Visits
            @endif
          </h5>
          <span class="badge bg-secondary">{{ $past->count() }}</span>
        </div>

        @if($past->isEmpty())
          <div class="text-center py-4">
            <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">
              @if(Auth::user()->is_admin)
                No recent clinic activities recorded.
              @elseif(Auth::user()->is_secretary)
                No recent appointments found.
              @else
                No past visits recorded.
              @endif
            </p>
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th><i class="bi bi-calendar me-1"></i>Date</th>
                  <th><i class="bi bi-building me-1"></i>Clinic</th>
                  @if(!Auth::user()->is_admin)
                    <th><i class="bi bi-gear me-1"></i>Service</th>
                  @endif
                  <th><i class="bi bi-clock me-1"></i>Time</th>
                  <th><i class="bi bi-info-circle me-1"></i>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($past->take(10) as $appointment)
                  <tr>
                    <td>
                      <strong>{{ Carbon::parse($appointment->appointment_date)->format('M j, Y') }}</strong>
                    </td>
                    <td>{{ $appointment->clinic->name }}</td>
                    @if(!Auth::user()->is_admin)
                      <td>{{ $appointment->service->name }}</td>
                    @endif
                    <td>{{ Carbon::parse($appointment->appointment_time)->format('g:i A') }}</td>
                    <td>
                      @if($appointment->status === 'completed')
                        <span class="badge bg-success">
                          <i class="bi bi-check-circle me-1"></i>Completed
                        </span>
                      @elseif($appointment->status === 'cancelled')
                        <span class="badge bg-danger">
                          <i class="bi bi-x-circle me-1"></i>Cancelled
                        </span>
                      @else
                        <span class="badge bg-warning">
                          <i class="bi bi-clock me-1"></i>Pending
                        </span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="text-center mt-3">
            <a href="{{ route('appointments.index') }}" class="btn btn-outline-primary">
              <i class="bi bi-arrow-right me-2"></i>View Complete History
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
