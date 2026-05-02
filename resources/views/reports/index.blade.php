@extends('layouts.app')
@section('title', 'My Reports')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <style>
    .report-kpi {
      border-radius: 10px;
      padding: 1.2rem;
      color: #ffffff;
      min-height: 118px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .report-kpi-value {
      font-size: 2.15rem;
      line-height: 1;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    .report-kpi-title {
      font-size: 1.05rem;
      opacity: 0.98;
      margin: 0;
    }
    .report-kpi.kpi-blue { background: #2d79ea; }
    .report-kpi.kpi-green { background: #268657; }
    .report-kpi.kpi-yellow { background: #f4bc07; color: #111827; }
    .report-kpi.kpi-cyan { background: #25b8d8; }
    .table thead th {
      font-weight: 600;
      background: #f3f4f6;
      border-bottom: 2px solid #2d79ea;
    }
  </style>

  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-bar-chart-line me-2"></i>My Reports</h2>
        <p class="text-muted mb-0">Review your appointments and queue history in one place.</p>
      </div>
    </div>

    <form method="GET" class="row g-3">
      <div class="col-md-3">
        <label class="form-label">From</label>
        <input type="date" name="from" value="{{ $from }}" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">To</label>
        <input type="date" name="to" value="{{ $to }}" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Appointment Status</label>
        <select name="appointment_status" class="form-select">
          <option value="all" @selected($appointmentStatus === 'all')>All</option>
          <option value="scheduled" @selected($appointmentStatus === 'scheduled')>Scheduled</option>
          <option value="completed" @selected($appointmentStatus === 'completed')>Completed</option>
          <option value="cancelled" @selected($appointmentStatus === 'cancelled')>Cancelled</option>
          <option value="no_show" @selected($appointmentStatus === 'no_show')>No Show</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Queue Status</label>
        <select name="queue_status" class="form-select">
          <option value="all" @selected($queueStatus === 'all')>All</option>
          <option value="waiting" @selected($queueStatus === 'waiting')>Waiting</option>
          <option value="now_serving" @selected($queueStatus === 'now_serving')>Now Serving</option>
          <option value="served" @selected($queueStatus === 'served')>Served</option>
          <option value="cancelled" @selected($queueStatus === 'cancelled')>Cancelled</option>
          <option value="no_show" @selected($queueStatus === 'no_show')>No Show</option>
        </select>
      </div>
      <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Apply Filters</button>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="report-kpi kpi-blue h-100">
        <div class="report-kpi-value">{{ $summary['total_appointments'] }}</div>
        <p class="report-kpi-title">Total Appointments</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="report-kpi kpi-green h-100">
        <div class="report-kpi-value">{{ $summary['completed_appointments'] }}</div>
        <p class="report-kpi-title">Past Visits</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="report-kpi kpi-yellow h-100">
        <div class="report-kpi-value">{{ $summary['active_queues'] }}</div>
        <p class="report-kpi-title">Active Queue</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="report-kpi kpi-cyan h-100">
        <div class="report-kpi-value">{{ $summary['served_queues'] }}</div>
        <p class="report-kpi-title">Served Queue</p>
      </div>
    </div>
  </div>

  <div class="medical-card p-0 mb-4">
    <div class="p-3 border-bottom">
      <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Appointment History</h5>
    </div>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th class="px-3 py-2"><i class="bi bi-hospital me-1"></i>Clinic</th>
            <th class="px-3 py-2"><i class="bi bi-gear me-1"></i>Service</th>
            <th class="px-3 py-2"><i class="bi bi-person-badge me-1"></i>Doctor</th>
            <th class="px-3 py-2"><i class="bi bi-calendar-event me-1"></i>Date</th>
            <th class="px-3 py-2"><i class="bi bi-clock me-1"></i>Time</th>
            <th class="px-3 py-2"><i class="bi bi-info-circle me-1"></i>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($appointments as $a)
            <tr>
              <td class="px-3 py-2">{{ $a->clinic?->name ?? 'Clinic' }}</td>
              <td class="px-3 py-2">{{ $a->service?->name ?? '-' }}</td>
              <td class="px-3 py-2">{{ $a->doctor ? ('Dr. ' . $a->doctor->name) : '-' }}</td>
              <td class="px-3 py-2">{{ optional($a->appointment_date)->format('M j, Y') }}</td>
              <td class="px-3 py-2">{{ $a->appointment_time ? $a->appointment_time->format('g:i A') : '-' }}</td>
              <td class="px-3 py-2"><span class="badge {{ $a->status_badge_class }}">{{ $a->status_label }}</span></td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No appointments found for selected filters.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-3">{{ $appointments->links() }}</div>
  </div>

  <div class="medical-card p-0">
    <div class="p-3 border-bottom">
      <h5 class="mb-0"><i class="bi bi-list-ol me-2"></i>Queue History</h5>
    </div>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th class="px-3 py-2"><i class="bi bi-hospital me-1"></i>Clinic</th>
            <th class="px-3 py-2"><i class="bi bi-hash me-1"></i>Queue #</th>
            <th class="px-3 py-2"><i class="bi bi-box-arrow-in-right me-1"></i>Joined</th>
            <th class="px-3 py-2"><i class="bi bi-check2-circle me-1"></i>Served At</th>
            <th class="px-3 py-2"><i class="bi bi-info-circle me-1"></i>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($queueEntries as $q)
            <tr>
              <td class="px-3 py-2">{{ $q->clinic?->name ?? 'Clinic' }}</td>
              <td class="px-3 py-2">#{{ $q->queue_number }}</td>
              <td class="px-3 py-2">{{ $q->created_at?->format('M j, Y g:i A') }}</td>
              <td class="px-3 py-2">{{ $q->served_at?->format('M j, Y g:i A') ?? '-' }}</td>
              <td class="px-3 py-2">
                <span class="badge bg-{{ $q->status_badge_class }} {{ in_array($q->status_badge_class, ['warning','info','light']) ? 'text-dark' : '' }}">{{ $q->status_label }}</span>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No queue entries found for selected filters.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-3">{{ $queueEntries->links() }}</div>
  </div>
</div>
@endsection
