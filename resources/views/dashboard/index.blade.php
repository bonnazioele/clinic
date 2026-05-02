@php use Carbon\Carbon; @endphp

@extends('layouts.patient-dashboard')

<<<<<<< Updated upstream
<!-- @section('title', 'Dashboard') -->

@section('content')
<div class="dashboard-container">

  @include('partials.alerts')

  {{-- STATS --}}
  <div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
      <div class="stat-card stat-blue">
        <div class="stat-content">
          <div class="stat-icon">
            <i class="bi bi-calendar-check"></i>
=======
  @section('content')
  <div class="container py-4">
    @include('partials.alerts')

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
>>>>>>> Stashed changes
          </div>
          <div>
            <div class="stat-value">{{ $upcoming->count() }}</div>
            <div class="stat-title">Upcoming Appointments</div>
            <p class="stat-sub">You have {{ $upcoming->count() }} scheduled appointment(s)</p>
          </div>
        </div>
      </div>
    </div>

<<<<<<< Updated upstream
    <div class="col-xl-3 col-md-6">
      <div class="stat-card stat-green">
        <div class="stat-content">
          <div class="stat-icon">
            <i class="bi bi-clock-history"></i>
          </div>
          <div>
            <div class="stat-value">{{ $past->count() }}</div>
            <div class="stat-title">Past Visits</div>
            <p class="stat-sub">Your completed appointments</p>
=======
    <div class="row g-4">

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
>>>>>>> Stashed changes
          </div>
        </div>
      </div>
    </div>

<<<<<<< Updated upstream
    <div class="col-xl-3 col-md-6">
      <div class="stat-card stat-yellow">
        <div class="stat-content">
          <div class="stat-icon">
            <i class="bi bi-hospital"></i>
=======

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
>>>>>>> Stashed changes
          </div>
          <div>
            <div class="stat-value">{{ \App\Models\Clinic::count() }}</div>
            <div class="stat-title">Available Clinics</div>
            <p class="stat-sub">Clinics ready to serve you</p>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="stat-card stat-cyan">
        <div class="stat-content">
          <div class="stat-icon">
            <i class="bi bi-stethoscope"></i>
          </div>
          <div>
            <div class="stat-value">{{ \App\Models\Service::count() }}</div>
            <div class="stat-title">Available Services</div>
            <p class="stat-sub">Healthcare services available</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- UPCOMING APPOINTMENTS FULL ROW --}}
  <div class="dash-card mb-4">
    <div class="dash-card-title">
      <h2>
        <i class="bi bi-calendar-check title-icon"></i>
        Upcoming Appointments
      </h2>

      <span class="badge bg-primary rounded-pill">{{ $upcoming->count() }}</span>
    </div>

    @if($upcoming->isEmpty())
      <div class="empty-box">
        <i class="bi bi-calendar-x"></i>
        <p class="mb-3">No upcoming appointments scheduled.</p>

        <a href="{{ Route::has('appointments.create') ? route('appointments.create') : url('/appointments/create') }}" class="btn btn-primary px-4">
          <i class="bi bi-plus-circle me-2"></i>Book Appointment
        </a>
      </div>
    @else
      @foreach($upcoming->take(3) as $appointment)
        @php
          $appointmentDate = Carbon::parse($appointment->appointment_date);
        @endphp

        <div class="appointment-row">
          <div class="date-box">
            <div>
              <div class="month">{{ strtoupper($appointmentDate->format('M')) }}</div>
              <div class="day">{{ $appointmentDate->format('d') }}</div>
              <div class="weekday">{{ $appointmentDate->format('D') }}</div>
            </div>
          </div>

          <div>
            <div class="appointment-name">{{ $appointment->clinic->name ?? 'Clinic' }}</div>

            <div class="appointment-meta">
              <span>
                <i class="bi bi-clock me-1"></i>
                {{ Carbon::parse($appointment->appointment_time)->format('g:i A') }}
              </span>

              <span>|</span>

              <span>
                <i class="bi bi-gear me-1"></i>
                {{ $appointment->service->name ?? 'Service' }}
              </span>
            </div>

            <div class="appointment-meta">
              <span>
                <i class="bi bi-geo-alt me-1"></i>
                {{ $appointment->clinic->address ?? 'Clinic address unavailable' }}
              </span>
            </div>
          </div>

          <span class="status-pill">
            <i class="bi bi-dot"></i>
            {{ ucfirst($appointment->status ?? 'Confirmed') }}
          </span>

          <a href="{{ Route::has('appointments.show') ? route('appointments.show', $appointment) : (Route::has('appointments.edit') ? route('appointments.edit', $appointment) : '#') }}"
             class="btn btn-outline-primary">
            View Details
            <i class="bi bi-chevron-right ms-2"></i>
          </a>
        </div>
      @endforeach

      <div class="text-center mt-3">
        <a href="{{ Route::has('appointments.index') ? route('appointments.index') : url('/appointments') }}" class="btn btn-link text-decoration-none fw-bold">
          View All Appointments
          <i class="bi bi-chevron-right ms-2"></i>
        </a>
      </div>
    @endif
  </div>

{{-- ACTIVITY + PAST APPOINTMENTS --}}
<div class="row g-4 mb-4">

  <!-- Recent Activity (Left) -->
  <div class="col-lg-6">
    <div class="dash-card dashboard-pair-card recent-activity-card">
      <div class="dash-card-title">
        <h2>
          <i class="bi bi-activity title-icon"></i>
          Recent Activity
        </h2>
      </div>

      <div class="activity-list">
      @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notification)
        <div class="activity-item">
          <div class="activity-icon">
            <i class="bi bi-info-circle"></i>
          </div>

          <div>
            <div>{{ \Illuminate\Support\Str::limit($notification->data['message'] ?? 'New notification', 90) }}</div>
            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
          </div>
        </div>
      @empty
        <p class="text-muted mb-0">No recent activity.</p>
      @endforelse
      </div>
    </div>
  </div>

  <!-- Past Appointments (Right) -->
  <div class="col-lg-6">
    <div class="dash-card dashboard-pair-card past-appointments-card">
      <div class="dash-card-title">
        <h2>
          <i class="bi bi-clock-history title-icon"></i>
          Past Appointments
        </h2>
        <span class="badge bg-secondary rounded-pill">{{ $past->count() }}</span>
      </div>

      <div class="past-appointments-list">
      @if($past->isEmpty())
        <div class="empty-box">
          <i class="bi bi-clock-history"></i>
          <p>No past visits recorded.</p>
        </div>
      @else
        @foreach($past->take(3) as $appointment)
          @php
            $appointmentDate = Carbon::parse($appointment->appointment_date);
          @endphp
          <div class="appointment-row mb-3">
            <div class="date-box">
              <div>
                <div class="month">{{ strtoupper($appointmentDate->format('M')) }}</div>
                <div class="day">{{ $appointmentDate->format('d') }}</div>
                <div class="weekday">{{ $appointmentDate->format('D') }}</div>
              </div>
            </div>

            <div>
              <div class="appointment-name">{{ $appointment->clinic->name ?? 'Clinic' }}</div>

              <div class="appointment-meta">
                <span>
                  <i class="bi bi-gear me-1"></i>
                  {{ $appointment->service->name ?? 'Service' }}
                </span>

                <span>|</span>

                <span>
                  <i class="bi bi-clock me-1"></i>
                  {{ Carbon::parse($appointment->appointment_time)->format('g:i A') }}
                </span>
              </div>
            </div>

            <span class="status-pill">
              @if($appointment->status === 'completed')
                <span class="badge bg-success">Completed</span>
              @elseif($appointment->status === 'cancelled')
                <span class="badge bg-danger">Cancelled</span>
              @else
                <span class="badge bg-warning text-dark">{{ ucfirst($appointment->status ?? 'Pending') }}</span>
              @endif
            </span>
          </div>
        @endforeach

      @endif
      </div>

      @if(!$past->isEmpty())
        <div class="history-link-wrap">
          <a href="{{ Route::has('appointments.index') ? route('appointments.index') : url('/appointments') }}" class="btn btn-outline-primary btn-sm">
            View Complete History
          </a>
        </div>
      @endif
    </div>
  </div>

</div>

</div>
@endsection