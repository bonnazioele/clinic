@extends('layouts.app')

@section('title', 'Secretary Appointments')

@section('content')
@php
  $user = auth()->user();

  $secretaryClinicRouteValue = request()->route('clinic');
  $secretaryClinicId = null;

  if (is_object($secretaryClinicRouteValue) && isset($secretaryClinicRouteValue->id)) {
      $secretaryClinicId = $secretaryClinicRouteValue->id;
  }

  if (!$secretaryClinicId && is_numeric($secretaryClinicRouteValue)) {
      $secretaryClinicId = $secretaryClinicRouteValue;
  }

  if (!$secretaryClinicId && session('active_clinic_id')) {
      $secretaryClinicId = session('active_clinic_id');
  }

  if (!$secretaryClinicId && isset($user->clinic_id)) {
      $secretaryClinicId = $user->clinic_id;
  }

  if (!$secretaryClinicId && isset($user->clinics) && $user->clinics->count()) {
      $secretaryClinicId = $user->clinics->first()->id;
  }

  $secUrl = function ($routeName, $params = [], $fallback = '/secretary/dashboard') use ($secretaryClinicId) {
      if (!\Illuminate\Support\Facades\Route::has($routeName)) {
          return url($fallback);
      }

      try {
          return route($routeName, $params);
      } catch (\Throwable $firstError) {
          if ($secretaryClinicId) {
              try {
                  return route($routeName, array_merge(['clinic' => $secretaryClinicId], (array) $params));
              } catch (\Throwable $secondError) {
                  return url($fallback);
              }
          }

          return url($fallback);
      }
  };

  $totalAppointments = $totalAppointments
      ?? (int) ($summary->total ?? 0);

  $scheduledCount = $scheduledCount
      ?? (int) ($summary->scheduled ?? 0);

  $inProgressCount = $inProgressCount
      ?? (int) ($summary->in_progress ?? 0);

  $cancelledCount = $cancelledCount
      ?? (int) ($summary->cancelled ?? 0);

  $dateMode = request('date_mode') === 'specific' ? 'specific' : 'today';
  $selectedDateLabel = null;

  if ($dateMode === 'specific' && request('date')) {
      try {
          $selectedDateLabel = 'Appointments on ' . \Carbon\Carbon::parse(request('date'))->format('M d, Y');
      } catch (\Throwable $e) {
          $selectedDateLabel = null;
      }
  }

  $periodLabel = $selectedDateLabel ?: "Today's Appointments";
  $activeStatusFilter = request('status');
@endphp

<style>
  .secretary-appointments-page {
    width: 96%;
    max-width: none;
    margin: 0 auto;
    padding: 0.5rem 0 1.5rem;
  }

  .sec-shell {
    border-radius: 24px;
    border: 1px solid rgba(226, 232, 240, 0.96);
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    overflow: hidden;
    margin-bottom: 1rem;
  }

  .sec-hero {
    padding: 1.35rem 1.45rem;
    color: #ffffff;
    background:
      radial-gradient(circle at 90% 30%, rgba(255, 255, 255, 0.16), transparent 18%),
      linear-gradient(135deg, #0d6efd 0%, #1d4ed8 100%);
  }

  .sec-hero-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .sec-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
  }

  .sec-title-icon {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
    font-size: 1.55rem;
    flex: 0 0 56px;
  }

  .sec-title {
    margin: 0;
    font-size: clamp(1.45rem, 2.4vw, 2.05rem);
    font-weight: 900;
    letter-spacing: -0.045em;
  }

  .sec-subtitle {
    margin: 0.3rem 0 0;
    font-size: 0.95rem;
    font-weight: 650;
    opacity: 0.94;
  }

  .sec-hero .btn {
    border-radius: 14px;
    font-weight: 900;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.16);
  }

  .sec-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
    padding: 1.2rem;
  }

  .sec-stat-card {
    min-height: 138px;
    border-radius: 22px;
    padding: 1rem;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
    display: block;
    text-decoration: none;
    transition: transform 0.18s ease, box-shadow 0.18s ease, outline-color 0.18s ease;
  }

  .sec-stat-card:hover {
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.12);
  }

  .sec-stat-yellow:hover {
    color: #162033;
  }

  .sec-stat-card.active {
    outline: 4px solid rgba(13, 110, 253, 0.28);
    outline-offset: 3px;
  }

  .sec-stat-card::after {
    content: "";
    position: absolute;
    right: -24px;
    bottom: -24px;
    width: 110px;
    height: 110px;
    border-radius: 34px;
    background: rgba(255, 255, 255, 0.14);
    transform: rotate(4deg);
  }

  .sec-stat-blue {
    background: linear-gradient(135deg, #0866f2, #2993ff);
  }

  .sec-stat-yellow {
    background: linear-gradient(135deg, #ffd85a, #ffc107);
    color: #162033;
  }

  .sec-stat-green {
    background: linear-gradient(135deg, #087b3d, #2bbf6a);
  }

  .sec-stat-gray {
    background: linear-gradient(135deg, #475569, #94a3b8);
  }

  .sec-stat-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
  }

  .sec-stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 17px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.18);
    font-size: 1.45rem;
    flex: 0 0 52px;
  }

  .sec-stat-value {
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.055em;
    margin-bottom: 0.4rem;
  }

  .sec-stat-label {
    font-size: 0.88rem;
    font-weight: 900;
  }

  .sec-stat-help {
    margin-top: 0.15rem;
    font-size: 0.78rem;
    font-weight: 650;
    opacity: 0.9;
  }

  .sec-panel {
    border-radius: 24px;
    border: 1px solid rgba(226, 232, 240, 0.96);
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    overflow: hidden;
    margin-bottom: 1rem;
  }

  .sec-card-head {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
  }

  .sec-card-title {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 900;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .sec-card-title i {
    color: #0d6efd;
  }

  .sec-card-body {
    padding: 1.2rem;
  }

  .sec-filter-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr)) auto;
    gap: 0.85rem;
    align-items: end;
  }

  .specific-date-field.is-hidden {
    display: none;
  }

  .form-label {
    font-size: 0.82rem;
    font-weight: 850;
    color: #334155;
  }

  .form-control,
  .form-select {
    border-radius: 14px !important;
    border-color: #dbe3ef !important;
    font-weight: 650;
    box-shadow: none !important;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: rgba(13, 110, 253, 0.55) !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1) !important;
  }

  .sec-list-toolbar {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-wrap: wrap;
  }

  .sec-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.42rem 0.72rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.76rem;
    font-weight: 900;
  }

  .sec-table {
    margin: 0;
  }

  .sec-table thead th {
    background: #f8fafc !important;
    color: #475569;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    border-bottom: 1px solid #e2e8f0 !important;
  }

  .sec-avatar {
    width: 44px;
    height: 44px;
    border-radius: 999px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #0d6efd;
    font-weight: 900;
    flex: 0 0 44px;
  }

  .sec-table .btn,
  .sec-action-buttons .btn {
    border-radius: 12px;
    font-weight: 850;
  }

  .sec-doctor-detail {
    display: grid;
    gap: 0.25rem;
    color: #475569;
    font-size: 0.82rem;
    font-weight: 650;
  }

  .sec-status-badge {
    border-radius: 999px;
    padding: 0.42rem 0.68rem;
    font-weight: 900;
  }

  .sec-empty {
    text-align: center;
    padding: 2.4rem 1rem;
    color: #64748b;
  }

  .sec-empty i {
    font-size: 3rem;
    color: #94a3b8;
  }

  .pagination-wrap {
    display: flex;
    justify-content: center;
    padding: 1rem 1.2rem 1.2rem;
    margin-top: 1rem;
  }

  .pagination-wrap nav {
    width: 100%;
  }

  .pagination-wrap .pagination {
    justify-content: center;
    gap: 0.35rem;
    flex-wrap: wrap;
    margin-bottom: 0;
  }

  .pagination-wrap .page-link {
    border-radius: 12px;
    min-width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-color: #dbe3ef;
    color: #0d6efd;
    font-weight: 800;
    box-shadow: none;
  }

  .pagination-wrap .page-item.active .page-link {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #ffffff;
  }

  .pagination-wrap .page-item.disabled .page-link {
    color: #94a3b8;
    background: #f8fafc;
  }

  @media (max-width: 1200px) {
    .sec-stats-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sec-filter-grid {
      grid-template-columns: 1fr 1fr;
    }
  }

  @media (max-width: 768px) {
    .secretary-appointments-page {
      width: 100%;
    }

    .sec-stats-grid,
    .sec-card-body {
      padding-left: 0.85rem;
      padding-right: 0.85rem;
    }

    .sec-hero {
      padding: 0.95rem;
    }

    .sec-filter-grid {
      grid-template-columns: 1fr;
    }

    .sec-card-head {
      align-items: stretch;
    }

    .sec-table-wrap {
      display: none !important;
    }
  }
</style>

<div class="secretary-appointments-page">
  <section class="sec-shell">
    <div class="sec-hero">
      <div class="sec-hero-row">
        <div class="sec-title-wrap">
          <div class="sec-title-icon">
            <i class="bi bi-calendar-check"></i>
          </div>

          <div>
            <h1 class="sec-title">Manage Appointments</h1>
            <p class="sec-subtitle">
              Schedule, update, and track patient appointments for your clinic.
            </p>
          </div>
        </div>

        <a href="{{ $secUrl('secretary.appointments.create') }}" class="btn btn-light text-primary">
          <i class="bi bi-calendar-plus me-2"></i>
          New Appointment
        </a>
      </div>
    </div>

    <div class="sec-stats-grid">
      <div class="sec-stat-card sec-stat-blue">
        <div class="sec-stat-content">
          <div class="sec-stat-icon">
            <i class="bi bi-calendar-week"></i>
          </div>
          <div>
            <div class="sec-stat-value">{{ number_format($totalAppointments) }}</div>
            <div class="sec-stat-label">Total</div>
            <div class="sec-stat-help">All matching appointments</div>
          </div>
        </div>
      </div>

      <div class="sec-stat-card sec-stat-yellow">
        <div class="sec-stat-content">
          <div class="sec-stat-icon">
            <i class="bi bi-clock-history"></i>
          </div>
          <div>
            <div class="sec-stat-value">{{ number_format($scheduledCount) }}</div>
            <div class="sec-stat-label">Scheduled</div>
            <div class="sec-stat-help">Upcoming or active</div>
          </div>
        </div>
      </div>

      <div class="sec-stat-card sec-stat-green">
        <div class="sec-stat-content">
          <div class="sec-stat-icon">
            <i class="bi bi-activity"></i>
          </div>
          <div>
            <div class="sec-stat-value">{{ number_format($inProgressCount) }}</div>
            <div class="sec-stat-label">In Progress</div>
            <div class="sec-stat-help">Being served now</div>
          </div>
        </div>
      </div>

      <div class="sec-stat-card sec-stat-gray">
        <div class="sec-stat-content">
          <div class="sec-stat-icon">
            <i class="bi bi-x-circle"></i>
          </div>
          <div>
            <div class="sec-stat-value">{{ number_format($cancelledCount) }}</div>
            <div class="sec-stat-label">Cancelled</div>
            <div class="sec-stat-help">Cancelled records</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="sec-panel">
    <div class="sec-card-head">
      <h2 class="sec-card-title">
        <i class="bi bi-search"></i>
        Search & Filter
      </h2>
    </div>

    <div class="sec-card-body">
      <form method="GET"
            action="{{ $secUrl('secretary.appointments.index', [], '/secretary/dashboard') }}"
            class="sec-filter-grid">
        <div>
          <label class="form-label">Patient Search</label>
          <input type="text"
                 name="patient"
                 class="form-control"
                 placeholder="Name, email, or phone..."
                 value="{{ request('patient') }}">
        </div>

        <div>
          <label class="form-label">Doctor</label>
          <select name="doctor_id" class="form-select">
            <option value="">All doctors</option>
            @foreach($doctors as $doctor)
              <option value="{{ $doctor->id }}" @selected((string) request('doctor_id') === (string) $doctor->id)>
                Dr. {{ $doctor->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="form-label">Service</label>
          <select name="service_id" class="form-select">
            <option value="">All services</option>
            @foreach($services as $service)
              <option value="{{ $service->id }}" @selected((string) request('service_id') === (string) $service->id)>
                {{ $service->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="">All statuses</option>
            <option value="scheduled" @selected($activeStatusFilter === 'scheduled')>Scheduled</option>
            <option value="in_progress" @selected($activeStatusFilter === 'in_progress')>In Progress</option>
            <option value="completed" @selected($activeStatusFilter === 'completed')>Completed</option>
            <option value="cancelled" @selected($activeStatusFilter === 'cancelled')>Cancelled</option>
            <option value="no_show" @selected($activeStatusFilter === 'no_show')>No Show</option>
            <option value="rescheduled" @selected($activeStatusFilter === 'rescheduled')>Rescheduled</option>
          </select>
        </div>

        <div>
          <label class="form-label">Date</label>
          <select name="date_mode" id="appointmentDateMode" class="form-select">
            <option value="today" @selected($dateMode === 'today')>Today</option>
            <option value="specific" @selected($dateMode === 'specific')>Specific date</option>
          </select>
        </div>

        <div class="specific-date-field {{ $dateMode === 'specific' ? '' : 'is-hidden' }}" id="specificDateField">
          <label class="form-label">Specific Date</label>
          <input type="date"
                 name="date"
                 id="appointmentSpecificDate"
                 class="form-control"
                 min="{{ now()->toDateString() }}"
                 value="{{ request('date') }}">
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary fw-bold rounded-4">
            <i class="bi bi-funnel me-2"></i>
            Filter
          </button>
          <a href="{{ $secUrl('secretary.appointments.index', [], '/secretary/dashboard') }}"
             class="btn btn-outline-secondary fw-bold rounded-4 mt-2">
            Clear
          </a>
        </div>
      </form>
    </div>
  </section>

  <section class="sec-panel">
    <div class="sec-card-head">
      <h2 class="sec-card-title">
        <i class="bi bi-calendar-week"></i>
        {{ $periodLabel }}
      </h2>
    </div>

    <div class="sec-table-wrap table-responsive">
      <table class="table sec-table align-middle">
        <thead>
          <tr>
            <th class="px-4 py-3">Patient</th>
            <th class="px-4 py-3">Service</th>
            <th class="px-4 py-3">Doctor</th>
            <th class="px-4 py-3">Date & Time</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Document</th>
          </tr>
        </thead>

        <tbody>
          @forelse($appointments as $appointment)
            <tr>
              <td class="px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="sec-avatar">
                    {{ strtoupper(substr($appointment->user->name ?? 'P', 0, 1)) }}
                  </div>

                  <div>
                    <div class="fw-bold text-dark">
                      {{ $appointment->user->name ?? 'Unknown Patient' }}
                    </div>

                    <small class="text-muted">
                      {{ $appointment->user->email ?? 'No email' }}
                    </small>

                    @if($appointment->user && $appointment->user->phone)
                      <br>
                      <small class="text-muted">{{ $appointment->user->phone }}</small>
                    @endif
                  </div>
                </div>
              </td>

              <td class="px-4 py-3">
                <span class="sec-pill">
                  {{ $appointment->service->name ?? 'N/A' }}
                </span>
              </td>

              <td class="px-4 py-3">
                @if($appointment->doctor)
                  <div class="fw-bold text-dark">Dr. {{ $appointment->doctor->name }}</div>
                  <div class="sec-doctor-detail">
                    <span>{{ $appointment->doctor->email ?? 'No email' }}</span>
                    @if($appointment->doctor->phone)
                      <span>{{ $appointment->doctor->phone }}</span>
                    @endif
                  </div>
                @else
                  <span class="badge bg-warning text-dark">Unassigned</span>
                @endif
              </td>

              <td class="px-4 py-3">
                <div class="fw-bold">
                  {{ optional($appointment->appointment_date)->format('M d, Y') ?? 'N/A' }}
                </div>

                <small class="text-muted">
                  {{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') : 'N/A' }}
                </small>
              </td>

              <td class="px-4 py-3">
                @php
                  $status = strtolower($appointment->status ?? 'scheduled');

                  $badgeClass = 'bg-primary';

                  if ($status === 'completed') {
                      $badgeClass = 'bg-success';
                  }

                  if ($status === 'in_progress') {
                      $badgeClass = 'bg-info text-dark';
                  }

                  if ($status === 'cancelled') {
                      $badgeClass = 'bg-danger';
                  }

                  if ($status === 'no_show') {
                      $badgeClass = 'bg-secondary';
                  }
                @endphp

                <span class="badge sec-status-badge {{ $badgeClass }}">
                  {{ ucfirst(str_replace('_', ' ', $status)) }}
                </span>
              </td>

              <td class="px-4 py-3">
                @if($appointment->medical_document)
                  <a href="{{ Storage::url($appointment->medical_document) }}"
                     target="_blank"
                     class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-file-earmark-medical me-1"></i>
                    View
                  </a>
                @else
                  <span class="text-muted small">No document</span>
                @endif
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="6">
                <div class="sec-empty">
                  <i class="bi bi-calendar-x d-block mb-3"></i>
                  <h5 class="fw-bold">No appointments found</h5>
                  <p class="mb-0">Try adjusting your filters or create a new appointment.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if(method_exists($appointments, 'hasPages') && $appointments->hasPages())
      <div class="pagination-wrap">
        {{ $appointments->appends(request()->except(['page', 'export']))->links() }}
      </div>
    @endif
  </section>
</div>

@endsection

@push('scripts')
<script>
  function exportAppointments() {
    const query = new URLSearchParams(window.location.search);
    query.delete('page');
    query.delete('export');
    const exportUrl = @json($secUrl('secretary.appointments.export', [], '/secretary/appointments/export'));

    let exportFrame = document.getElementById('appointmentsExportFrame');

    if (!exportFrame) {
      exportFrame = document.createElement('iframe');
      exportFrame.id = 'appointmentsExportFrame';
      exportFrame.name = 'appointmentsExportFrame';
      exportFrame.style.display = 'none';
      document.body.appendChild(exportFrame);
    }

    exportFrame.src = exportUrl + (query.toString() ? '?' + query.toString() : '');
  }

  document.addEventListener('DOMContentLoaded', function () {
    const currentUrl = new URL(window.location.href);
    if (currentUrl.searchParams.has('export')) {
      currentUrl.searchParams.delete('export');
      window.history.replaceState({}, '', currentUrl.toString());
    }

    const dateMode = document.getElementById('appointmentDateMode');
    const specificDateField = document.getElementById('specificDateField');
    const specificDate = document.getElementById('appointmentSpecificDate');

    if (dateMode && specificDateField) {
      const toggleSpecificDate = () => {
        const usesSpecificDate = dateMode.value === 'specific';
        specificDateField.classList.toggle('is-hidden', !usesSpecificDate);

        if (!usesSpecificDate && specificDate) {
          specificDate.value = '';
        }
      };

      dateMode.addEventListener('change', toggleSpecificDate);
      toggleSpecificDate();
    }
  });
</script>
@endpush
