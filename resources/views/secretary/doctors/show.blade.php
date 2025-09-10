@extends('layouts.app')
@section('title','Doctor Profile')
@section('content')
<div class="container py-4">
  @include('partials.alerts')
  <div class="medical-card p-4 mb-4 d-flex justify-content-between align-items-center">
    <div>
      <h2 class="fw-bold text-primary mb-1"><i class="bi bi-person-badge me-2"></i>{{ $doctor->name }}</h2>
      <p class="text-muted mb-0">Doctor Profile & Credentials</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('secretary.doctors.edit',$doctor) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
      <a href="{{ route('secretary.doctors.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
  </div>

  <div class="row g-4">
    <!-- Basic Info -->
    <div class="col-lg-4">
      <div class="medical-card p-4 h-100">
        <h5 class="fw-semibold mb-3"><i class="bi bi-person-lines-fill me-2"></i>Basic Information</h5>
        <dl class="row mb-0 small">
          <dt class="col-5">Name</dt><dd class="col-7">{{ $doctor->name }}</dd>
          <dt class="col-5">Email</dt><dd class="col-7">{{ $doctor->email }}</dd>
          <dt class="col-5">Phone</dt><dd class="col-7">{{ $doctor->phone ?: '—' }}</dd>
          <dt class="col-5">Address</dt><dd class="col-7">{{ $doctor->address ?: '—' }}</dd>
          <dt class="col-5">Clinics</dt>
          <dd class="col-7">
            @forelse($doctor->clinics as $c)
              <span class="badge bg-primary mb-1">{{ $c->name }}</span>
            @empty <span class="text-muted">None</span> @endforelse
          </dd>
          <dt class="col-5">Services</dt>
          <dd class="col-7">
            @forelse($doctor->services as $s)
              <span class="badge bg-info text-dark mb-1">{{ $s->name }}</span>
            @empty <span class="text-muted">None</span> @endforelse
          </dd>
        </dl>
      </div>
    </div>

    <!-- Schedule -->
    <div class="col-lg-8">
      <div class="medical-card p-4 h-100">
        <h5 class="fw-semibold mb-3"><i class="bi bi-calendar-range me-2"></i>Weekly Availability</h5>
        @if($scheduleByDay->isEmpty())
          <p class="text-muted small mb-0">No availability set.</p>
        @else
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Day</th><th>Clinic</th><th>Time Window</th>
                </tr>
              </thead>
              <tbody>
              @foreach($scheduleByDay as $day=>$entries)
                @foreach($entries as $entry)
                <tr>
                  <td>{{ ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$day] }}</td>
                  <td>{{ $entry->clinic->name }}</td>
                  <td>{{ substr($entry->start_time,0,5) }} - {{ substr($entry->end_time,0,5) }}</td>
                </tr>
                @endforeach
              @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Recent Appointments (optional) -->
  <div class="medical-card p-4 mt-4">
    <h5 class="fw-semibold mb-3"><i class="bi bi-clock-history me-2"></i>Recent Appointments</h5>
    @php
      $recent = \App\Models\Appointment::with('user','clinic','service')
          ->where('doctor_id',$doctor->id)
          ->latest('appointment_date')
          ->latest('appointment_time')
          ->take(10)->get();
    @endphp
    @if($recent->isEmpty())
      <p class="text-muted small mb-0">No recent appointments.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th><th>Time</th><th>Patient</th><th>Clinic</th><th>Service</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recent as $a)
              <tr>
                <td>{{ \Carbon\Carbon::parse($a->appointment_date)->format('Y-m-d') }}</td>
                <td>{{ \Carbon\Carbon::parse($a->appointment_time)->format('H:i') }}</td>
                <td>{{ $a->user->name }}</td>
                <td>{{ $a->clinic->name }}</td>
                <td>{{ $a->service->name }}</td>
                <td>
                  <span class="badge {{ $a->status==='scheduled' ? 'bg-warning text-dark' : ($a->status==='completed' ? 'bg-success' : 'bg-secondary') }}">{{ ucfirst($a->status) }}</span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
