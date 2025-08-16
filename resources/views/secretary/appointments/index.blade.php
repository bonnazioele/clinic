@extends('layouts.app')

@section('title', 'Manage Appointments')

@section('content')
<div class="container py-4">
  <!-- Page Header -->
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h2 class="fw-bold text-primary mb-1">
          <i class="bi bi-calendar-check medical-icon me-2"></i>Manage Appointments
        </h2>
        <p class="text-muted mb-0">Schedule and manage patient appointments efficiently</p>
      </div>
      <div class="d-flex gap-2">
  <a href="{{ route('secretary.queue.overview') }}" class="btn btn-info">
          <i class="bi bi-people me-2"></i>Manage Queue
        </a>
        <a href="{{ route('secretary.appointments.create') }}" class="btn btn-success">
          <i class="bi bi-plus-circle me-2"></i>New Appointment
        </a>
      </div>
    </div>

    <!-- Statistics Row -->
    <div class="row g-3">
      <div class="col-md-3">
        <div class="text-center p-3 bg-light rounded-3">
          <div class="dashboard-stat">{{ $appointments->total() }}</div>
          <small class="text-muted">Total Appointments</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="text-center p-3 bg-light rounded-3">
          <div class="dashboard-stat">{{ $appointments->where('status', 'scheduled')->count() }}</div>
          <small class="text-muted">Scheduled</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="text-center p-3 bg-light rounded-3">
          <div class="dashboard-stat">{{ $appointments->where('status', 'completed')->count() }}</div>
          <small class="text-muted">Completed</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="text-center p-3 bg-light rounded-3">
          <div class="dashboard-stat">{{ $appointments->where('status', 'cancelled')->count() }}</div>
          <small class="text-muted">Cancelled</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Search and Filter -->
  <div class="medical-card p-4 mb-4">
    <h5 class="mb-3">
      <i class="bi bi-search medical-icon me-2"></i>Search & Filter Appointments
    </h5>
    <form class="row g-3" method="GET" action="{{ route('secretary.appointments.index') }}">
      <div class="col-lg-3">
        <label class="form-label fw-semibold">Patient Name</label>
        <input type="text" name="patient" class="form-control"
               placeholder="Search by patient name..."
               value="{{ request('patient') }}">
      </div>
      <div class="col-lg-2">
        <label class="form-label fw-semibold">Status</label>
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="scheduled" @selected(request('status') == 'scheduled')>Scheduled</option>
          <option value="completed" @selected(request('status') == 'completed')>Completed</option>
          <option value="cancelled" @selected(request('status') == 'cancelled')>Cancelled</option>
        </select>
      </div>
      <div class="col-lg-2">
        <label class="form-label fw-semibold">Date</label>
        <input type="date" name="date" class="form-control"
               value="{{ request('date') }}">
      </div>
      <div class="col-lg-2">
        <label class="form-label fw-semibold">Clinic</label>
        <select name="clinic_id" class="form-select">
          <option value="">All Clinics</option>
          @foreach(\App\Models\Clinic::all() as $clinic)
            <option value="{{ $clinic->id }}" @selected(request('clinic_id') == $clinic->id)>
              {{ $clinic->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-3 d-flex align-items-end">
        <div class="d-grid w-100">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-funnel me-2"></i>Filter
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- Quick Actions -->
  <div class="medical-card p-4 mb-4">
    <h5 class="mb-3">
      <i class="bi bi-lightning medical-icon me-2"></i>Quick Actions
    </h5>
    <div class="row g-3">
      <div class="col-md-3">
        <a href="{{ route('secretary.appointments.create') }}" class="btn btn-success w-100">
          <i class="bi bi-calendar-plus me-2"></i>New Appointment
        </a>
      </div>
      <div class="col-md-3">
  <a href="{{ route('secretary.queue.overview') }}" class="btn btn-info w-100">
          <i class="bi bi-people me-2"></i>Manage Queue
        </a>
      </div>
      <div class="col-md-3">
        <a href="{{ route('secretary.doctors.index') }}" class="btn btn-primary w-100">
          <i class="bi bi-person-badge me-2"></i>Manage Doctors
        </a>
      </div>
      <div class="col-md-3">
        <button class="btn btn-warning w-100" onclick="exportAppointments()">
          <i class="bi bi-download me-2"></i>Export Data
        </button>
      </div>
    </div>
  </div>

  <!-- Appointments Table -->
  <div class="medical-card p-0">
    <div class="d-flex align-items-center justify-content-between p-4 border-bottom">
      <h5 class="mb-0">
        <i class="bi bi-calendar-week medical-icon me-2"></i>Appointments List
      </h5>
      <div class="d-flex align-items-center">
        <span class="badge bg-primary me-3">{{ $appointments->total() }} appointments</span>
        <div class="btn-group btn-group-sm" role="group">
          <button type="button" class="btn btn-outline-primary" id="tableView">
            <i class="bi bi-table"></i>
          </button>
          <button type="button" class="btn btn-outline-primary" id="cardView">
            <i class="bi bi-grid-3x3-gap"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Table View -->
    <div id="tableViewContent">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="border-0 px-4 py-3">
                <i class="bi bi-person me-1"></i>Patient
              </th>
              <th class="border-0 px-4 py-3">
                <i class="bi bi-building me-1"></i>Clinic
              </th>
              <th class="border-0 px-4 py-3">
                <i class="bi bi-gear me-1"></i>Service
              </th>
              <th class="border-0 px-4 py-3">
                <i class="bi bi-person-badge me-1"></i>Doctor
              </th>
              <th class="border-0 px-4 py-3">
                <i class="bi bi-calendar me-1"></i>Date & Time
              </th>
              <th class="border-0 px-4 py-3">
                <i class="bi bi-info-circle me-1"></i>Status
              </th>
              <th class="border-0 px-4 py-3" width="150">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($appointments as $appointment)
              <tr>
                <td class="px-4 py-3">
                  <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                         style="width: 45px; height: 45px;">
                      <i class="bi bi-person-fill text-white"></i>
                    </div>
                    <div>
                      <div class="fw-semibold">{{ $appointment->user->name }}</div>
                      <small class="text-muted">
                        <i class="bi bi-envelope me-1"></i>{{ $appointment->user->email }}
                      </small>
                      @if($appointment->user->phone)
                        <br><small class="text-muted">
                          <i class="bi bi-telephone me-1"></i>{{ $appointment->user->phone }}
                        </small>
                      @endif
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="fw-semibold">{{ $appointment->clinic->name }}</div>
                  <small class="text-muted">
                    <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($appointment->clinic->address, 30) }}
                  </small>
                </td>
                <td class="px-4 py-3">
                  <span class="badge bg-info text-dark">{{ $appointment->service->name }}</span>
                </td>
                <td class="px-4 py-3">
                  @if($appointment->doctor)
                    <div class="d-flex align-items-center">
                      <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-2"
                           style="width: 32px; height: 32px;">
                        <i class="bi bi-person-badge text-white"></i>
                      </div>
                      <span>{{ $appointment->doctor->name }}</span>
                    </div>
                  @else
                    <span class="badge bg-warning text-dark">Unassigned</span>
                  @endif
                </td>
                <td class="px-4 py-3">
                  <div class="fw-semibold">
                    <i class="bi bi-calendar-date me-1"></i>
                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}
                  </div>
                  <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}
                  </small>
                </td>
                <td class="px-4 py-3">
                  @if($appointment->status === 'scheduled')
                    <span class="badge bg-warning text-dark">
                      <i class="bi bi-clock me-1"></i>Scheduled
                    </span>
                  @elseif($appointment->status === 'completed')
                    <span class="badge bg-success text-white">
                      <i class="bi bi-check-circle me-1"></i>Completed
                    </span>
                  @elseif($appointment->status === 'cancelled')
                    <span class="badge bg-danger text-white">
                      <i class="bi bi-x-circle me-1"></i>Cancelled
                    </span>
                  @else
                    <span class="badge bg-secondary text-white">
                      <i class="bi bi-question-circle me-1"></i>{{ ucfirst($appointment->status) }}
                    </span>
                  @endif
                </td>
                <td class="px-4 py-3">
                  <div class="d-flex flex-column gap-2">
                    <a href="{{ route('secretary.appointments.edit', $appointment) }}"
                       class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <form method="POST" action="{{ route('secretary.appointments.destroy', $appointment) }}"
                          class="d-inline" onsubmit="return confirm('Are you sure you want to delete this appointment?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i>Delete
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5">
                  <div class="text-muted">
                    <i class="bi bi-calendar-x display-4 mb-3"></i>
                    <h5 class="fw-semibold mb-2">No appointments found</h5>
                    <p class="mb-3">Start by creating a new appointment for a patient.</p>
                    <a href="{{ route('secretary.appointments.create') }}" class="btn btn-primary">
                      <i class="bi bi-plus-circle me-2"></i>Create First Appointment
                    </a>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Card View (Hidden by default) -->
    <div id="cardViewContent" class="p-4" style="display: none;">
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @forelse($appointments as $appointment)
          <div class="col">
            <div class="clinic-card h-100 p-4">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="d-flex align-items-center">
                  <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                       style="width: 50px; height: 50px;">
                    <i class="bi bi-person-fill text-white"></i>
                  </div>
                  <div>
                    <h6 class="fw-bold text-primary mb-1">{{ $appointment->user->name }}</h6>
                    <small class="text-muted">{{ $appointment->user->email }}</small>
                  </div>
                </div>
                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="{{ route('secretary.appointments.edit', $appointment) }}">
                        <i class="bi bi-pencil me-2"></i>Edit
                      </a>
                    </li>
                    <li>
                      <form method="POST" action="{{ route('secretary.appointments.destroy', $appointment) }}"
                            onsubmit="return confirm('Are you sure you want to delete this appointment?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                          <i class="bi bi-trash me-2"></i>Delete
                        </button>
                      </form>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="mb-3">
                <div class="d-flex align-items-center mb-2">
                  <i class="bi bi-building text-primary me-2"></i>
                  <strong>{{ $appointment->clinic->name }}</strong>
                </div>
                <div class="d-flex align-items-center mb-2">
                  <i class="bi bi-gear text-info me-2"></i>
                  <span>{{ $appointment->service->name }}</span>
                </div>
                @if($appointment->doctor)
                  <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-person-badge text-success me-2"></i>
                    <span>Dr. {{ $appointment->doctor->name }}</span>
                  </div>
                @endif
                <div class="d-flex align-items-center mb-2">
                  <i class="bi bi-calendar-event text-warning me-2"></i>
                  <span>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</span>
                </div>
                <div class="d-flex align-items-center">
                  <i class="bi bi-clock text-info me-2"></i>
                  <span>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</span>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                @if($appointment->status === 'scheduled')
                  <span class="badge bg-warning text-dark">
                    <i class="bi bi-clock me-1"></i>Scheduled
                  </span>
                @elseif($appointment->status === 'completed')
                  <span class="badge bg-success text-white">
                    <i class="bi bi-check-circle me-1"></i>Completed
                  </span>
                @elseif($appointment->status === 'cancelled')
                  <span class="badge bg-danger text-white">
                    <i class="bi bi-x-circle me-1"></i>Cancelled
                  </span>
                @endif
                <a href="{{ route('secretary.appointments.edit', $appointment) }}"
                   class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-pencil me-1"></i>Edit
                </a>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12">
            <div class="text-center py-5">
              <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
              <h5 class="text-muted mt-3">No appointments found</h5>
              <p class="text-muted mb-3">Start by creating a new appointment for a patient.</p>
              <a href="{{ route('secretary.appointments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Create First Appointment
              </a>
            </div>
          </div>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Pagination -->
  @if($appointments->hasPages())
    <div class="d-flex justify-content-center mt-4">
      <nav aria-label="Appointments pagination">
        {{ $appointments->withQueryString()->links() }}
      </nav>
    </div>
  @endif
</div>

@include('partials.alerts')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  initializeViewToggle();
});

function initializeViewToggle() {
  const tableView = document.getElementById('tableView');
  const cardView = document.getElementById('cardView');
  const tableViewContent = document.getElementById('tableViewContent');
  const cardViewContent = document.getElementById('cardViewContent');

  tableView.addEventListener('click', () => {
    tableViewContent.style.display = 'block';
    cardViewContent.style.display = 'none';
    tableView.classList.add('active');
    cardView.classList.remove('active');
  });

  cardView.addEventListener('click', () => {
    cardViewContent.style.display = 'block';
    tableViewContent.style.display = 'none';
    cardView.classList.add('active');
    tableView.classList.remove('active');
  });

  // Set default active state
  tableView.classList.add('active');
}

function exportAppointments() {
  // This would typically make an AJAX call to export appointments
  alert('Export functionality would be implemented here. This could export to CSV, PDF, or Excel format.');
}
</script>
@endpush
