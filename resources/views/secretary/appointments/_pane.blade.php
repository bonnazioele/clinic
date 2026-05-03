@php
  $user = auth()->user();
  $secretaryClinic = request()->route('clinic');
  if ($secretaryClinic instanceof \App\Models\Clinic) {
      $secretaryClinicId = $secretaryClinic->id;
  } elseif (is_numeric($secretaryClinic)) {
      $secretaryClinicId = $secretaryClinic;
  } else {
      $secretaryClinicId = null;
  }
  $secretaryClinicId = $secretaryClinicId
      ?? session('active_clinic_id')
      ?? $user?->clinic_id
      ?? optional($user?->clinics?->first())->id
      ?? null;

  $secUrl = function ($routeName, $params = [], $fallback = '/secretary/dashboard') use ($secretaryClinicId) {
      if (!\Illuminate\Support\Facades\Route::has($routeName)) return url($fallback);
      try {
          return route($routeName, $params);
      } catch (\Throwable $e) {
          try {
              if ($secretaryClinicId) {
                  return route($routeName, array_merge(['clinic' => $secretaryClinicId], (array) $params));
              }
          } catch (\Throwable $e2) {}
          return url($fallback);
      }
  };

  $totalAppointments = method_exists($appointments, 'total') ? $appointments->total() : $appointments->count();
  $scheduledCount = $appointments->where('status', 'scheduled')->count();
  $completedCount = $appointments->where('status', 'completed')->count();
  $cancelledCount = $appointments->where('status', 'cancelled')->count();
@endphp

<style>
  .secretary-appointments-pane { width: 96%; max-width: none; margin: 0 auto; }
  .sec-panel { border-radius: 24px; border: 1px solid rgba(226,232,240,.96); background: rgba(255,255,255,.94); box-shadow: 0 18px 45px rgba(15,23,42,.08); overflow: hidden; }
  .sec-panel + .sec-panel { margin-top: 1rem; }
  .sec-hero { padding: 1.35rem 1.45rem; background: radial-gradient(circle at top left, rgba(13,110,253,.14), transparent 34%), linear-gradient(135deg,#fff,#f8fbff); }
  .sec-hero-row { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; }
  .sec-title-wrap { display:flex; align-items:flex-start; gap:.85rem; }
  .sec-title-icon { width:54px; height:54px; border-radius:18px; display:grid; place-items:center; background:linear-gradient(135deg,#0d6efd,#178bff); color:#fff; font-size:1.45rem; box-shadow:0 14px 28px rgba(13,110,253,.25); }
  .sec-title { margin:0; font-size:1.55rem; font-weight:900; letter-spacing:-.045em; color:#0f172a; }
  .sec-subtitle { margin:.25rem 0 0; color:#64748b; font-weight:650; }
  .sec-stats-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.9rem; padding:0 1.45rem 1.35rem; }
  .sec-stat-card { min-height:126px; border-radius:20px; padding:1rem; color:#fff; position:relative; overflow:hidden; box-shadow:0 14px 30px rgba(15,23,42,.08); }
  .sec-stat-card::after { content:""; position:absolute; right:-24px; bottom:-24px; width:110px; height:110px; border-radius:36px; background:rgba(255,255,255,.14); }
  .sec-stat-blue{background:linear-gradient(135deg,#0866f2,#2993ff)} .sec-stat-green{background:linear-gradient(135deg,#087b3d,#2bbf6a)} .sec-stat-yellow{background:linear-gradient(135deg,#ffd85a,#ffc107); color:#162033} .sec-stat-gray{background:linear-gradient(135deg,#475569,#94a3b8)}
  .sec-stat-content{position:relative; z-index:2; display:flex; gap:.8rem; align-items:flex-start;} .sec-stat-icon{width:48px;height:48px;border-radius:16px;display:grid;place-items:center;background:rgba(255,255,255,.18);font-size:1.35rem;flex:0 0 48px}.sec-stat-value{font-size:1.9rem;font-weight:900;line-height:1;letter-spacing:-.05em}.sec-stat-label{font-weight:900;font-size:.86rem;margin-top:.25rem}.sec-stat-help{font-size:.78rem;font-weight:650;opacity:.9;margin-top:.15rem}
  .sec-card-head{padding:1rem 1.2rem;border-bottom:1px solid #edf2f7;display:flex;justify-content:space-between;align-items:center;gap:.8rem;flex-wrap:wrap}.sec-card-title{margin:0;font-size:1.05rem;font-weight:900;color:#0f172a;display:flex;align-items:center;gap:.5rem}.sec-card-title i{color:#0d6efd}.sec-card-body{padding:1.2rem}.sec-actions-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.75rem}.sec-action-card{border-radius:18px;border:1px solid #e2e8f0;background:#fff;padding:.9rem;color:#0f172a;font-weight:850;text-align:left;display:flex;align-items:center;gap:.7rem;transition:.18s ease}.sec-action-card:hover{transform:translateY(-2px);border-color:#bfdbfe;background:#eff6ff;color:#0d6efd}.sec-action-icon{width:40px;height:40px;border-radius:14px;display:grid;place-items:center;background:#eff6ff;color:#0d6efd;flex:0 0 40px}
  .sec-filter-grid{display:grid;grid-template-columns:1.4fr .8fr .8fr auto;gap:.85rem;align-items:end}.form-label{font-size:.82rem;font-weight:850;color:#334155}.form-control,.form-select{border-radius:14px!important;border-color:#dbe3ef!important;font-weight:650;box-shadow:none!important}.form-control:focus,.form-select:focus{border-color:rgba(13,110,253,.55)!important;box-shadow:0 0 0 .2rem rgba(13,110,253,.1)!important}
  .sec-list-toolbar{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap}.sec-pill{display:inline-flex;align-items:center;gap:.35rem;border-radius:999px;padding:.42rem .72rem;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:.76rem;font-weight:900}.sec-view-toggle{display:inline-flex;padding:.18rem;border-radius:12px;border:1px solid #dbe3ef;background:#f8fafc}.sec-view-toggle button{border:0;background:transparent;color:#64748b;width:34px;height:30px;border-radius:10px;display:grid;place-items:center}.sec-view-toggle button.active{background:#0d6efd;color:#fff}
  .sec-table{margin:0}.sec-table thead th{background:#f8fafc!important;color:#475569;font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid #e2e8f0!important}.sec-avatar{width:44px;height:44px;border-radius:999px;display:grid;place-items:center;background:#eff6ff;color:#0d6efd;font-weight:900;flex:0 0 44px}.sec-table .btn,.sec-action-buttons .btn{border-radius:12px;font-weight:850}.sec-cards-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}.sec-appointment-card{border:1px solid #e2e8f0;border-radius:20px;background:#fff;padding:1rem;box-shadow:0 10px 26px rgba(15,23,42,.045)}.sec-meta-list{display:grid;gap:.5rem;margin:.85rem 0}.sec-meta-item{display:flex;gap:.5rem;color:#475569;font-weight:650}.sec-meta-item i{color:#0d6efd}.sec-empty{text-align:center;padding:2.4rem 1rem;color:#64748b}.sec-empty i{font-size:3rem;color:#94a3b8}.pagination-wrap{display:flex;justify-content:center;margin-top:1rem}
  @media(max-width:1200px){.sec-stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.sec-actions-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.sec-filter-grid{grid-template-columns:1fr 1fr}.sec-cards-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media(max-width:768px){.secretary-appointments-pane{width:100%}.sec-stats-grid,.sec-card-body{padding-left:.85rem;padding-right:.85rem}.sec-hero{padding:.95rem}.sec-filter-grid,.sec-actions-grid,.sec-cards-grid{grid-template-columns:1fr}.sec-card-head{align-items:stretch}.sec-view-toggle{width:max-content}.sec-table-wrap{display:none!important}}
</style>

<div class="secretary-appointments-pane">
  <section class="sec-panel">
    <div class="sec-hero">
      <div class="sec-hero-row">
        <div class="sec-title-wrap">
          <div class="sec-title-icon"><i class="bi bi-calendar-check"></i></div>
          <div>
            <h1 class="sec-title">Manage Appointments</h1>
            <p class="sec-subtitle">Schedule, update, and track patient appointments for your clinic.</p>
          </div>
        </div>
        <a href="{{ $secUrl('secretary.appointments.create') }}" class="btn btn-primary fw-bold rounded-4"><i class="bi bi-calendar-plus me-2"></i>New Appointment</a>
      </div>
    </div>
    <div class="sec-stats-grid">
      <div class="sec-stat-card sec-stat-blue"><div class="sec-stat-content"><div class="sec-stat-icon"><i class="bi bi-calendar-week"></i></div><div><div class="sec-stat-value">{{ number_format($totalAppointments) }}</div><div class="sec-stat-label">Total</div><div class="sec-stat-help">All matching appointments</div></div></div></div>
      <div class="sec-stat-card sec-stat-yellow"><div class="sec-stat-content"><div class="sec-stat-icon"><i class="bi bi-clock-history"></i></div><div><div class="sec-stat-value">{{ number_format($scheduledCount) }}</div><div class="sec-stat-label">Scheduled</div><div class="sec-stat-help">Upcoming or active</div></div></div></div>
      <div class="sec-stat-card sec-stat-green"><div class="sec-stat-content"><div class="sec-stat-icon"><i class="bi bi-check2-circle"></i></div><div><div class="sec-stat-value">{{ number_format($completedCount) }}</div><div class="sec-stat-label">Completed</div><div class="sec-stat-help">Finished visits</div></div></div></div>
      <div class="sec-stat-card sec-stat-gray"><div class="sec-stat-content"><div class="sec-stat-icon"><i class="bi bi-x-circle"></i></div><div><div class="sec-stat-value">{{ number_format($cancelledCount) }}</div><div class="sec-stat-label">Cancelled</div><div class="sec-stat-help">Cancelled records</div></div></div></div>
    </div>
  </section>

  <section class="sec-panel">
    <div class="sec-card-head"><h2 class="sec-card-title"><i class="bi bi-lightning"></i>Quick Actions</h2></div>
    <div class="sec-card-body">
      <div class="sec-actions-grid">
        <a href="{{ $secUrl('secretary.patients.create') }}" class="sec-action-card"><span class="sec-action-icon"><i class="bi bi-person-plus"></i></span><span>Register Patient</span></a>
        <a href="{{ $secUrl('secretary.appointments.create') }}" class="sec-action-card"><span class="sec-action-icon"><i class="bi bi-calendar-plus"></i></span><span>New Appointment</span></a>
        <a href="{{ $secUrl('secretary.queue.overview') }}" class="sec-action-card"><span class="sec-action-icon"><i class="bi bi-people"></i></span><span>Manage Queue</span></a>
        <a href="{{ $secUrl('secretary.doctors.index') }}" class="sec-action-card"><span class="sec-action-icon"><i class="bi bi-person-badge"></i></span><span>Manage Doctors</span></a>
        <button type="button" class="sec-action-card" onclick="exportAppointments()"><span class="sec-action-icon"><i class="bi bi-download"></i></span><span>Export Data</span></button>
      </div>
    </div>
  </section>

  <section class="sec-panel">
    <div class="sec-card-head"><h2 class="sec-card-title"><i class="bi bi-search"></i>Search & Filter</h2></div>
    <div class="sec-card-body">
      <form method="GET" action="{{ $secUrl('secretary.appointments.index', [], '/secretary/dashboard') }}" class="sec-filter-grid">
        <div><label class="form-label">Patient Name</label><input type="text" name="patient" class="form-control" placeholder="Search by patient name..." value="{{ request('patient') }}"></div>
        <div><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All Status</option><option value="scheduled" @selected(request('status') == 'scheduled')>Scheduled</option><option value="completed" @selected(request('status') == 'completed')>Completed</option><option value="cancelled" @selected(request('status') == 'cancelled')>Cancelled</option></select></div>
        <div><label class="form-label">Date</label><input type="date" name="date" class="form-control" value="{{ request('date') }}"></div>
        <div class="d-grid"><button type="submit" class="btn btn-primary fw-bold rounded-4"><i class="bi bi-funnel me-2"></i>Filter</button></div>
      </form>
    </div>
  </section>

  <section class="sec-panel">
    <div class="sec-card-head">
      <h2 class="sec-card-title"><i class="bi bi-calendar-week"></i>Appointments List</h2>
      <div class="sec-list-toolbar"><span class="sec-pill"><i class="bi bi-check2-circle"></i>{{ number_format($totalAppointments) }} appointments</span><div class="sec-view-toggle"><button type="button" id="tableView" class="active" title="Table view"><i class="bi bi-table"></i></button><button type="button" id="cardView" title="Card view"><i class="bi bi-grid-3x3-gap"></i></button></div></div>
    </div>
    <div id="tableViewContent" class="sec-table-wrap table-responsive">
      <table class="table sec-table align-middle">
        <thead><tr><th class="px-4 py-3">Patient</th><th class="px-4 py-3">Service</th><th class="px-4 py-3">Doctor</th><th class="px-4 py-3">Date & Time</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Document</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody>
          @forelse($appointments as $appointment)
            <tr>
              <td class="px-4 py-3"><div class="d-flex align-items-center gap-3"><div class="sec-avatar">{{ strtoupper(substr($appointment->user->name ?? 'P', 0, 1)) }}</div><div><div class="fw-bold text-dark">{{ $appointment->user->name }}</div><small class="text-muted">{{ $appointment->user->email }}</small>@if($appointment->user->phone)<br><small class="text-muted">{{ $appointment->user->phone }}</small>@endif</div></div></td>
              <td class="px-4 py-3"><span class="sec-pill">{{ $appointment->service->name ?? '—' }}</span></td>
              <td class="px-4 py-3">@if($appointment->doctor)<span class="fw-bold">Dr. {{ $appointment->doctor->name }}</span>@else<span class="badge bg-warning text-dark">Unassigned</span>@endif</td>
              <td class="px-4 py-3"><div class="fw-bold">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</div><small class="text-muted">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</small></td>
              <td class="px-4 py-3"><span class="badge {{ $appointment->status_badge_class }}">{{ $appointment->status_label }}</span></td>
              <td class="px-4 py-3">@if($appointment->medical_document)<a href="{{ asset('storage/' . $appointment->medical_document) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-medical me-1"></i>View</a>@else<span class="text-muted">—</span>@endif</td>
              <td class="px-4 py-3"><div class="d-flex flex-column gap-2 sec-action-buttons"><a href="{{ $secUrl('secretary.appointments.edit', [$appointment]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a><form method="POST" action="{{ $secUrl('secretary.appointments.destroy', [$appointment]) }}" data-confirm="Delete this appointment? Any queue entry linked to it will also be cancelled." data-confirm-title="Delete Appointment" data-confirm-btn="Delete">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Delete</button></form></div></td>
            </tr>
          @empty
            <tr><td colspan="7"><div class="sec-empty"><i class="bi bi-calendar-x"></i><h5 class="fw-bold mt-3">No appointments found</h5><p>Start by creating a new appointment for a patient.</p><a href="{{ $secUrl('secretary.appointments.create') }}" class="btn btn-primary rounded-4 fw-bold"><i class="bi bi-plus-circle me-2"></i>Create First Appointment</a></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div id="cardViewContent" class="sec-card-body" style="display:none;">
      <div class="sec-cards-grid">
        @forelse($appointments as $appointment)
          <article class="sec-appointment-card"><div class="d-flex justify-content-between gap-3"><div class="d-flex gap-3"><div class="sec-avatar">{{ strtoupper(substr($appointment->user->name ?? 'P', 0, 1)) }}</div><div><h3 class="h6 fw-black mb-1">{{ $appointment->user->name }}</h3><small class="text-muted">{{ $appointment->user->email }}</small></div></div><span class="badge {{ $appointment->status_badge_class }} h-25">{{ $appointment->status_label }}</span></div><div class="sec-meta-list"><div class="sec-meta-item"><i class="bi bi-building"></i>{{ $appointment->clinic->name ?? '—' }}</div><div class="sec-meta-item"><i class="bi bi-gear"></i>{{ $appointment->service->name ?? '—' }}</div>@if($appointment->doctor)<div class="sec-meta-item"><i class="bi bi-person-badge"></i>Dr. {{ $appointment->doctor->name }}</div>@endif<div class="sec-meta-item"><i class="bi bi-calendar-event"></i>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</div><div class="sec-meta-item"><i class="bi bi-clock"></i>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</div></div><div class="d-flex gap-2 flex-wrap"><a href="{{ $secUrl('secretary.appointments.edit', [$appointment]) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>@if($appointment->medical_document)<a href="{{ asset('storage/' . $appointment->medical_document) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-medical me-1"></i>Document</a>@endif</div></article>
        @empty
          <div class="sec-empty"><i class="bi bi-calendar-x"></i><h5 class="fw-bold mt-3">No appointments found</h5></div>
        @endforelse
      </div>
    </div>
  </section>

  @if($appointments->hasPages())<div class="pagination-wrap">{{ $appointments->withQueryString()->links() }}</div>@endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () { initializeViewToggle(); });
function initializeViewToggle() { const tableView = document.getElementById('tableView'); const cardView = document.getElementById('cardView'); const tableViewContent = document.getElementById('tableViewContent'); const cardViewContent = document.getElementById('cardViewContent'); if (!tableView || !cardView || !tableViewContent || !cardViewContent) return; tableView.addEventListener('click', () => { tableViewContent.style.display = 'block'; cardViewContent.style.display = 'none'; tableView.classList.add('active'); cardView.classList.remove('active'); }); cardView.addEventListener('click', () => { cardViewContent.style.display = 'block'; tableViewContent.style.display = 'none'; cardView.classList.add('active'); tableView.classList.remove('active'); }); tableView.classList.add('active'); }
function exportAppointments() { alert('Export functionality would be implemented here.'); }
</script>
@endpush
