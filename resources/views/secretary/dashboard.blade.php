@extends('layouts.app')
@section('title','Secretary Dashboard')

@section('content')
<div class="container py-4">

  <div class="row mb-4">
    <div class="col-12">
      <div class="medical-card p-4 text-center">
        <div class="d-flex align-items-center justify-content-center mb-3">
          <i class="bi bi-heart-pulse-fill medical-icon me-3" style="font-size: 3rem;"></i>
          <div>
            <h1 class="mb-1 fw-bold text-primary">Welcome, {{ auth()->user()->name }}!</h1>
            <p class="text-muted mb-0">
              <i class="bi bi-person-badge me-2"></i>Secretary Dashboard
            </p>
          </div>
        </div>

        @php
          $assignedClinics = auth()->user()
            ->secretaryClinics()
            ->select('clinics.id', 'clinics.name', 'clinics.branch_code')
            ->get();
        @endphp

        <div class="row g-3 text-start justify-content-center">
          @if($assignedClinics->count())
            <div class="col-md-3">
              <div class="p-4 rounded text-white h-100" style="background:#0d6efd;">
                @php $firstClinic = $assignedClinics->first(); @endphp
                <div class="fs-3 fw-bold">
                  {{ $firstClinic ? \Illuminate\Support\Str::limit($firstClinic->name, 18) : '—' }}
                </div>
                <div class="mt-1">Assigned Clinic{{ $assignedClinics->count() > 1 ? 's' : '' }}</div>
                @if($assignedClinics->count() > 1)
                  <div class="small opacity-75 mt-2">+{{ $assignedClinics->count() - 1 }} more</div>
                @endif
              </div>
            </div>
          @endif

          <div class="col-md-3">
            <a href="{{ route('secretary.appointments.index') }}" class="text-decoration-none">
              <div class="p-4 rounded h-100" style="background:#ffc107; color:#000;">
                <div class="fs-3 fw-bold">{{ $todayAppts }}</div>
                <div class="mt-1">Today's Appointments</div>
              </div>
            </a>
          </div>

          <div class="col-md-3">
            <a href="{{ route('secretary.doctors.index') }}" class="text-decoration-none">
              <div class="p-4 rounded text-white h-100" style="background:#1f7f56;">
                <div class="fs-3 fw-bold">{{ $totalDoctors }}</div>
                <div class="mt-1">Doctors</div>
              </div>
            </a>
          </div>

          <div class="col-md-3">
            <a href="{{ route('secretary.services.index') }}" class="text-decoration-none">
              <div class="p-4 rounded text-white h-100" style="background:#10c9f4;">
                <div class="fs-3 fw-bold">{{ $availableServices ?? 0 }}</div>
                <div class="mt-1">Available Services</div>
              </div>
            </a>
          </div>

          <div class="col-md-3">
            <a href="{{ route('secretary.patients.index') }}" class="text-decoration-none">
              <div class="p-4 rounded text-white h-100" style="background:#28a745;">
                <div class="fs-3 fw-bold">{{ number_format($clinicPatients ?? 0) }}</div>
                <div class="mt-1">Patients</div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="medical-card p-0 overflow-hidden mb-4">
    <div class="p-4 border-bottom">
      <div class="d-flex align-items-center mb-4">
        <i class="bi bi-search text-primary me-3" style="font-size: 2rem;"></i>
        <h3 class="mb-0">Search &amp; Filter Appointments</h3>
      </div>

      <form method="GET" action="{{ route('secretary.dashboard') }}" class="row g-3 align-items-end">
        <div class="col-md-5">
          <label class="form-label fw-semibold">Patient Name</label>
          <input
            type="text"
            name="patient"
            class="form-control"
            placeholder="Search by patient name..."
            value="{{ request('patient') }}"
          >
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Date</label>
          <input
            type="date"
            name="date"
            class="form-control"
            value="{{ request('date', $selectedDate ?? now()->toDateString()) }}"
          >
        </div>

        <input type="hidden" name="status" value="{{ $selectedStatus ?? 'pending' }}">

        <div class="col-md-3">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-funnel me-2"></i>Apply
          </button>
        </div>
      </form>
    </div>

    <div class="d-flex justify-content-between align-items-center p-4 border-bottom flex-wrap gap-3">
      <div class="d-flex align-items-center">
        <i class="bi bi-calendar-week text-primary me-3" style="font-size: 1.8rem;"></i>
        <div>
          <h3 class="mb-0">Appointments List</h3>
          <small class="text-muted">Click any row to open its queue list.</small>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary rounded-pill px-3 py-2">
          {{ $appointments->total() }} appointments
        </span>
      </div>
    </div>

    <div class="p-4 border-bottom bg-light">
      <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('secretary.dashboard', array_filter(['date' => $selectedDate, 'patient' => request('patient'), 'status' => 'pending'])) }}"
           class="btn {{ ($selectedStatus ?? 'pending') === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
          Pending
          <span class="ms-1 badge text-dark bg-light">{{ $statusCounts['pending'] ?? 0 }}</span>
        </a>

        <a href="{{ route('secretary.dashboard', array_filter(['date' => $selectedDate, 'patient' => request('patient'), 'status' => 'scheduled'])) }}"
           class="btn {{ ($selectedStatus ?? '') === 'scheduled' ? 'btn-primary' : 'btn-outline-primary' }}">
          Scheduled
          <span class="ms-1 badge bg-light text-dark">{{ $statusCounts['scheduled'] ?? 0 }}</span>
        </a>

        <a href="{{ route('secretary.dashboard', array_filter(['date' => $selectedDate, 'patient' => request('patient'), 'status' => 'completed'])) }}"
           class="btn {{ ($selectedStatus ?? '') === 'completed' ? 'btn-success' : 'btn-outline-success' }}">
          Completed
          <span class="ms-1 badge bg-light text-dark">{{ $statusCounts['completed'] ?? 0 }}</span>
        </a>

        <a href="{{ route('secretary.dashboard', array_filter(['date' => $selectedDate, 'patient' => request('patient'), 'status' => 'all'])) }}"
           class="btn {{ ($selectedStatus ?? '') === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">
          All
          <span class="ms-1 badge bg-light text-dark">{{ $statusCounts['all'] ?? 0 }}</span>
        </a>
      </div>
    </div>

    @if($appointments->count())
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead style="background:#f3f6f9;">
            <tr>
              <th class="px-4 py-3">Patient</th>
              <th class="px-4 py-3">Service</th>
              <th class="px-4 py-3">Doctor</th>
              <th class="px-4 py-3">Date &amp; Time</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Document</th>
              <th class="px-4 py-3">Action</th>
            </tr>
          </thead>

          <tbody>
            @foreach($appointments as $appointment)
              <tr
                class="appointment-row"
                data-href="{{ route('secretary.queue.index', ['clinic' => $appointment->clinic_id, 'date' => \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d')]) }}"
                style="cursor:pointer;"
              >
                <td class="px-4 py-4">
                  <div class="d-flex align-items-center gap-3">
                    <div
                      class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                      style="width:56px;height:56px;"
                    >
                      <i class="bi bi-person-fill"></i>
                    </div>

                    <div>
                      <div class="fw-bold fs-5">{{ $appointment->user->name ?? 'N/A' }}</div>

                      @if(!empty($appointment->user->email))
                        <div class="text-muted small">
                          <i class="bi bi-envelope me-1"></i>{{ $appointment->user->email }}
                        </div>
                      @endif

                      @if(!empty($appointment->user->phone))
                        <div class="text-muted small">
                          <i class="bi bi-telephone me-1"></i>{{ $appointment->user->phone }}
                        </div>
                      @endif
                    </div>
                  </div>
                </td>

                <td class="px-4 py-4">
                  @if($appointment->service)
                    <span class="badge rounded-pill text-bg-info px-3 py-2">
                      {{ $appointment->service->name }}
                    </span>
                  @else
                    —
                  @endif
                </td>

                <td class="px-4 py-4">
                  @if($appointment->doctor)
                    <div class="d-flex align-items-center gap-2">
                      <div
                        class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                        style="width:40px;height:40px;"
                      >
                        <i class="bi bi-person-badge"></i>
                      </div>
                      <span>{{ $appointment->doctor->name }}</span>
                    </div>
                  @else
                    —
                  @endif
                </td>

                <td class="px-4 py-4">
                  <div class="fw-bold">
                    <i class="bi bi-calendar-event me-1"></i>
                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}
                  </div>
                  <div class="text-muted">
                    <i class="bi bi-clock me-1"></i>{{ $appointment->appointment_time }}
                  </div>
                </td>

                <td class="px-4 py-4">
                  @php
                    $badgeClass = match($appointment->status) {
                      'pending' => 'bg-warning text-dark',
                      'scheduled' => 'bg-primary',
                      'completed' => 'bg-success',
                      default => 'bg-light text-dark'
                    };
                  @endphp

                  <span class="badge {{ $badgeClass }}">
                    {{ ucfirst($appointment->status) }}
                  </span>
                </td>

                <td class="px-4 py-4">
                  @if(!empty($appointment->document_path))
                    <a href="{{ asset('storage/' . $appointment->document_path) }}"
                       target="_blank"
                       class="btn btn-sm btn-outline-secondary stop-row-click">
                      View
                    </a>
                  @else
                    —
                  @endif
                </td>

                <td class="px-4 py-4">
                  <a
                    href="{{ route('secretary.queue.index', ['clinic' => $appointment->clinic_id, 'date' => \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d')]) }}"
                    class="btn btn-sm btn-primary stop-row-click"
                  >
                    <i class="bi bi-list-ul me-1"></i>Queue List
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="p-4">
        {{ $appointments->links() }}
      </div>
    @else
      <div class="p-5 text-center">
        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3 mb-1">No appointments found</h5>
        <p class="text-muted mb-0">Try changing the date or the selected tab.</p>
      </div>
    @endif
  </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.appointment-row').forEach(function (row) {
    row.addEventListener('click', function (e) {
      if (e.target.closest('.stop-row-click')) return;
      const href = row.getAttribute('data-href');
      if (href) window.location.href = href;
    });
  });
});
</script>
@endpush
@endsection