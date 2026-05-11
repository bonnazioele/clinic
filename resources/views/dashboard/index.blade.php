@php use Carbon\Carbon; @endphp

@extends('layouts.patient-dashboard')

<!-- @section('title', 'Dashboard') -->

@section('content')
<div class="dashboard-container">

  @include('partials.alerts')

  {{-- STATS --}}
  <div class="row g-4 mb-4">
    <div class="col-xl-4 col-md-6">
      <a href="{{ Route::has('appointments.index') ? route('appointments.index') : url('/appointments') }}" class="text-decoration-none">
        <div class="stat-card stat-blue">
          <div class="stat-content">
            <div class="stat-icon">
              <i class="bi bi-calendar-check"></i>
            </div>
            <div>
              <div class="stat-value">{{ $upcoming->count() }}</div>
              <div class="stat-title">Upcoming Appointments</div>
              <p class="stat-sub">You have {{ $upcoming->count() }} scheduled appointment(s)</p>
            </div>
          </div>
        </div>
      </a>
    </div>

    <div class="col-xl-4 col-md-6">
      <a href="{{ Route::has('appointments.index') ? route('appointments.index') : url('/appointments') }}" class="text-decoration-none">
        <div class="stat-card stat-green">
          <div class="stat-content">
            <div class="stat-icon">
              <i class="bi bi-clock-history"></i>
            </div>
            <div>
              <div class="stat-value">{{ $past->count() }}</div>
              <div class="stat-title">Past Visits</div>
              <p class="stat-sub">Your completed appointments</p>
            </div>
          </div>
        </div>
      </a>
    </div>

    <div class="col-xl-4 col-md-6">
      <a href="{{ Route::has('clinics.index') ? route('clinics.index') : url('/clinics') }}" class="text-decoration-none">
        <div class="stat-card stat-yellow">
          <div class="stat-content">
            <div class="stat-icon">
              <i class="bi bi-hospital"></i>
            </div>
            <div>
              <div class="stat-value">{{ \App\Models\Clinic::count() }}</div>
              <div class="stat-title">Available Clinics</div>
              <p class="stat-sub">Clinics ready to serve you</p>
            </div>
          </div>
        </div>
      </a>
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

          <a href="{{ Route::has('appointments.show') ? route('appointments.show', $appointment) : (Route::has('appointments.show') ? route('appointments.edit', $appointment) : '#') }}"
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