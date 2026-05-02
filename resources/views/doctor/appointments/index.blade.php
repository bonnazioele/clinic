@extends('layouts.app')

@section('title', 'Upcoming Appointments')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h2 class="fw-bold text-primary mb-1 d-flex align-items-center">
          <i class="bi bi-calendar-check medical-icon me-2"></i>Upcoming Appointments
        </h2>
        <p class="text-muted mb-0">Your scheduled appointments from today onward.</p>
      </div>
      <span class="badge bg-primary">{{ $appointments->total() }} total</span>
    </div>
  </div>

  <div class="medical-card p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label fw-semibold">Clinic</label>
        <select name="clinic_id" class="form-select">
          <option value="">All assigned clinics</option>
          @foreach($clinics as $clinic)
            <option value="{{ $clinic->id }}" @selected((string) request('clinic_id') === (string) $clinic->id)>{{ $clinic->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Date</label>
        <input type="date" name="date" value="{{ request('date') }}" class="form-control">
      </div>
      <div class="col-md-3 d-grid">
        <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Apply</button>
      </div>
      <div class="col-md-2 d-grid">
        <a href="{{ route('doctor.appointments.index') }}" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>
  </div>

  <div class="medical-card p-0">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th class="px-4 py-3">Date</th>
            <th class="px-4 py-3">Time</th>
            <th class="px-4 py-3">Patient</th>
            <th class="px-4 py-3">Clinic</th>
            <th class="px-4 py-3">Service</th>
            <th class="px-4 py-3">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($appointments as $appointment)
            <tr>
              <td class="px-4 py-3">{{ optional($appointment->appointment_date)->format('M d, Y') }}</td>
              <td class="px-4 py-3">{{ $appointment->appointment_time ? time12($appointment->appointment_time) : '—' }}</td>
              <td class="px-4 py-3">{{ $appointment->user?->name ?? 'Patient' }}</td>
              <td class="px-4 py-3">{{ $appointment->clinic?->name ?? '—' }}</td>
              <td class="px-4 py-3">{{ $appointment->service?->name ?? '—' }}</td>
              <td class="px-4 py-3"><span class="badge bg-primary">Scheduled</span></td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-5 text-muted">No upcoming appointments found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-3">
      {{ $appointments->links() }}
    </div>
  </div>
</div>
@endsection
