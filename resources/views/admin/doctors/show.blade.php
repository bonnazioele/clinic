@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold text-primary mb-1">
          <i class="bi bi-person-badge medical-icon me-2"></i>Dr. {{ $doctor->name }}
        </h2>
        <div class="text-muted">
          <i class="bi bi-envelope me-1"></i>{{ $doctor->email }}
          @if($doctor->phone)
            <span class="ms-3"><i class="bi bi-telephone me-1"></i>{{ $doctor->phone }}</span>
          @endif
        </div>
      </div>
      <div>
        <a class="btn btn-light" href="{{ route('admin.doctors.index') }}">
          <i class="bi bi-arrow-left me-1"></i>Back to list
        </a>
      </div>
    </div>

    <div class="row g-3 mt-3">
      <div class="col-md-4">
        <div class="p-3 border rounded bg-primary text-white">
          <div class="small">Clinics</div>
          <div class="h4 mb-0">{{ $doctor->clinics->count() }}</div>
        </div>
      </div>
      <div class="col-md-4">
        @php $waitingTotal = collect($queueByClinic)->sum('waiting'); @endphp
        <div class="p-3 border rounded bg-warning text-dark">
          <div class="small">Waiting Across Clinics</div>
          <div class="h4 mb-0">{{ $waitingTotal }}</div>
        </div>
      </div>
      <div class="col-md-4">
        @php $servedTotal = collect($queueByClinic)->sum('servedToday'); @endphp
        <div class="p-3 border rounded bg-success text-white">
          <div class="small">Served Today (All Clinics)</div>
          <div class="h4 mb-0">{{ $servedTotal }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="medical-card p-4 h-100">
        <h5 class="mb-3"><i class="bi bi-building me-2"></i>Clinics</h5>
        @if($doctor->clinics->isEmpty())
          <p class="text-muted">No clinics assigned.</p>
        @else
          <ul class="list-group list-group-flush">
            @foreach($doctor->clinics as $c)
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <a href="{{ route('admin.clinics.show', $c) }}" class="text-decoration-none">{{ $c->name }}</a>
                </div>
                <div class="d-flex gap-2">
                  <span class="badge bg-warning text-dark">Waiting: {{ $queueByClinic[$c->id]['waiting'] ?? 0 }}</span>
                  <span class="badge bg-success">Served Today: {{ $queueByClinic[$c->id]['servedToday'] ?? 0 }}</span>
                </div>
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
    <div class="col-lg-6">
      <div class="medical-card p-4 h-100">
        <h5 class="mb-3"><i class="bi bi-clipboard2-pulse me-2"></i>Recent Appointments</h5>
  @php $recent = $recentAppointments; @endphp
  @if($recent->isEmpty())
          <p class="text-muted">No recent appointments.</p>
        @else
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Date</th>
                  <th>Patient</th>
                  <th>Clinic</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($recent as $appt)
                  <tr>
                    <td>{{ \Carbon\Carbon::parse($appt->appointment_date)->format('M d, Y') }}</td>
                    <td>{{ $appt->user?->name }}</td>
                    <td>{{ $appt->clinic?->name }}</td>
                    <td>
                      @php $status = $appt->status ?? 'scheduled'; @endphp
                      <span class="badge bg-{{ $status === 'completed' ? 'success' : ($status === 'cancelled' ? 'secondary' : 'primary') }}">{{ ucfirst($status) }}</span>
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
</div>
@endsection
