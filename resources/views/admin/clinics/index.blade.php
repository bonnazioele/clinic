@extends('admin.layouts.app')
@section('title','Clinics')

@push('styles')
<style>
  .admin-clinics-page { width: 100%; }

  .admin-clinics-hero {
    position: relative;
    overflow: hidden;
    border-radius: 34px;
    padding: 32px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    background:
      radial-gradient(circle at top left, rgba(22, 119, 255, 0.16), transparent 30%),
      radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.12), transparent 28%),
      rgba(255, 255, 255, 0.86);
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
  }

  .admin-clinics-hero::after {
    content: "";
    position: absolute;
    right: -80px;
    bottom: -90px;
    width: 260px;
    height: 260px;
    border-radius: 999px;
    background: rgba(22, 119, 255, 0.10);
    pointer-events: none;
  }

  .admin-hero-icon {
    width: 76px;
    height: 76px;
    border-radius: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    background: linear-gradient(135deg, #1677ff, #06b6d4);
    font-size: 2.1rem;
    box-shadow: 0 18px 34px rgba(22, 119, 255, 0.28);
    flex-shrink: 0;
  }

  .admin-hero-title {
    margin: 0;
    color: #0f172a;
    font-size: clamp(1.75rem, 3vw, 2.6rem);
    font-weight: 900;
    line-height: 1.05;
  }

  .admin-hero-text {
    color: #64748b;
    margin: 8px 0 0;
    font-size: 0.97rem;
  }

  .admin-panel {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.90);
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.07);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    overflow: hidden;
  }

  .admin-panel-header {
    padding: 20px 24px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.07);
    background: linear-gradient(180deg, #ffffff, #f8fafc);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }

  .admin-panel-title {
    color: #0f172a;
    font-size: 1.1rem;
    font-weight: 900;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .admin-panel-title i { color: #1677ff; }

  .admin-panel-subtitle {
    color: #64748b;
    font-size: 0.86rem;
    margin: 5px 0 0;
  }

  .admin-panel-body { padding: 20px 24px; }

  .admin-count-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 3px 10px;
    background: rgba(22, 119, 255, 0.10);
    color: #1677ff;
    font-size: 0.72rem;
    font-weight: 800;
  }

  .admin-primary-action {
    border: 0;
    border-radius: 999px;
    padding: 10px 18px;
    color: #ffffff;
    background: linear-gradient(135deg, #1677ff, #06b6d4);
    box-shadow: 0 14px 28px rgba(22, 119, 255, 0.22);
    font-size: 0.86rem;
    font-weight: 800;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    white-space: nowrap;
  }

  .admin-primary-action:hover {
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 18px 36px rgba(22, 119, 255, 0.28);
  }

  .filter-label {
    color: #475569;
    font-size: 0.78rem;
    font-weight: 800;
    margin-bottom: 7px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .admin-filter-control {
    min-height: 48px;
    border-radius: 16px;
    border: 1px solid rgba(15, 23, 42, 0.10);
    color: #0f172a;
    font-size: 0.92rem;
    box-shadow: none;
  }

  .admin-filter-control:focus {
    border-color: rgba(22, 119, 255, 0.45);
    box-shadow: 0 0 0 4px rgba(22, 119, 255, 0.10);
  }

  .btn-filter {
    min-height: 48px;
    border: 0;
    border-radius: 16px;
    background: #1677ff;
    color: #ffffff;
    font-weight: 800;
  }

  .btn-filter:hover { background: #0f63d8; color: #ffffff; }

  .btn-clear-filter {
    min-height: 48px;
    border-radius: 16px;
    border: 1px solid rgba(100, 116, 139, 0.22);
    color: #475569;
    font-weight: 800;
  }

  .clinics-table-wrapper {
    border-radius: 22px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    background: #ffffff;
    overflow-x: auto;
    overflow-y: visible;
  }

  .clinics-table { margin-bottom: 0; }

  .clinics-table thead th {
    background: #f8fafc;
    border: none;
    border-bottom: 1px solid rgba(15, 23, 42, 0.07);
    color: #64748b;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 1rem 1.25rem;
    white-space: nowrap;
  }

  .clinics-table tbody tr {
    background: #ffffff;
    border-bottom: 1px solid rgba(15, 23, 42, 0.07);
    cursor: pointer;
    position: relative;
    z-index: 0;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
  }

  .clinics-table tbody tr:last-child { border-bottom: none; }

  .clinics-table tbody tr:hover {
    background: #f8fafc;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    transform: translateY(-2px);
    z-index: 5;
  }

  .clinics-table td {
    padding: 0.95rem 1.25rem !important;
    vertical-align: middle;
  }

  .clinic-avatar,
  .clinic-avatar-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    object-fit: cover;
    flex-shrink: 0;
  }

  .clinic-cell { min-width: 240px; }

  .clinic-avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(22, 119, 255, 0.10);
    color: #1677ff;
    font-size: 1.25rem;
  }

  .clinic-name {
    color: #0f172a;
    font-size: 0.97rem;
    font-weight: 800;
    margin-bottom: 0.12rem;
  }

  .clinic-branch {
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 700;
  }

  .clinic-subtext {
    color: #94a3b8;
    font-size: 0.70rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .count-value {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 900;
    line-height: 1.1;
  }

  .count-label {
    display: block;
    color: #94a3b8;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .location-text {
    color: #475569;
    font-size: 0.82rem;
    max-width: 270px;
  }

  .location-secondary {
    color: #94a3b8;
    font-size: 0.72rem;
    margin-top: 0.2rem;
  }

  .status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 0.45rem 0.9rem;
    line-height: 1;
    white-space: nowrap;
  }

  .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0.7;
  }

  .status-success { background: rgba(16, 185, 129, 0.12); color: #047857; }
  .status-warning { background: rgba(245, 158, 11, 0.15); color: #92400e; }
  .status-danger { background: rgba(239, 68, 68, 0.11); color: #b91c1c; }
  .status-neutral { background: rgba(100, 116, 139, 0.13); color: #475569; }

  .action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 14px;
    background: #ffffff;
    color: #64748b;
    cursor: pointer;
    padding: 0;
    text-decoration: none;
    transition: 0.18s ease;
  }

  .action-btn:hover {
    background: rgba(22, 119, 255, 0.10);
    color: #1677ff;
    border-color: rgba(22, 119, 255, 0.20);
  }

  .action-btn-delete:hover {
    background: rgba(239, 68, 68, 0.10);
    color: #b91c1c;
    border-color: rgba(239, 68, 68, 0.18);
  }

  .empty-state {
    padding: 56px 18px;
    text-align: center;
  }

  .empty-state-icon {
    width: 72px;
    height: 72px;
    border-radius: 24px;
    background: rgba(100, 116, 139, 0.11);
    color: #64748b;
    font-size: 2rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
  }

  @media (max-width: 991.98px) {
    .admin-clinics-hero { padding: 22px; border-radius: 24px; }
    .admin-panel { border-radius: 22px; }
    .clinics-table thead { display: none; }
    .clinics-table-wrapper { border: 0; background: transparent; }
    .clinics-table tbody tr {
      display: block;
      border: 1px solid rgba(15, 23, 42, 0.08);
      border-radius: 20px;
      margin-bottom: 1rem;
      padding: 1rem;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }
    .clinics-table td {
      display: flex;
      justify-content: space-between;
      gap: 18px;
      padding: 0.45rem 0 !important;
      border-bottom: none;
    }
    .clinics-table td::before {
      content: attr(data-label);
      color: #94a3b8;
      font-size: 0.70rem;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }
    .action-cell { justify-content: flex-start; }
    .clinic-cell {
      flex-direction: column;
      align-items: flex-start !important;
    }
  }

  @media (max-width: 767.98px) {
    .admin-hero-icon { width: 58px; height: 58px; font-size: 1.6rem; }
    .admin-hero-title { font-size: 1.6rem; }
    .admin-panel-header {
      flex-direction: column;
      align-items: stretch;
    }
    .admin-primary-action { width: 100%; }
  }
</style>
@endpush

@section('content')
<div class="admin-clinics-page">
  <div class="container-fluid px-3 px-md-4 py-4">

    <div class="admin-clinics-hero mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 position-relative" style="z-index:1">
        <div class="d-flex align-items-center gap-3">
          <div class="admin-hero-icon">
            <i class="bi bi-building-fill-gear"></i>
          </div>
          <div>
            <h1 class="admin-hero-title">Clinics Management</h1>
            <p class="admin-hero-text">Manage registered healthcare facilities, staff assignments, and clinic status.</p>
          </div>
        </div>
        <a href="{{ route('admin.clinics.create') }}" class="admin-primary-action">
          <i class="bi bi-plus-circle"></i>
          New Clinic
        </a>
      </div>
    </div>

    <div class="admin-panel mb-4">
      <div class="admin-panel-header">
        <div>
          <h2 class="admin-panel-title">
            <i class="bi bi-funnel"></i> Filters
          </h2>
          <p class="admin-panel-subtitle">Find clinics by name or registration status</p>
        </div>
      </div>
      <div class="admin-panel-body">
        <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.clinics.index') }}">
          <div class="col-md-5 col-lg-4">
            <label class="filter-label"><i class="bi bi-search"></i> Search</label>
            <input type="text" name="name" class="form-control admin-filter-control" placeholder="Search clinic name..." value="{{ request('name') }}">
          </div>
          <div class="col-md-4 col-lg-3">
            <label class="filter-label"><i class="bi bi-activity"></i> Status</label>
            <select name="status" class="form-select admin-filter-control">
              <option value="">All Status</option>
              <option value="pending" @selected(request('status') == 'pending')>Pending</option>
              <option value="approved" @selected(request('status') == 'approved')>Approved</option>
              <option value="active" @selected(request('status') == 'active')>Active</option>
              <option value="rejected" @selected(request('status') == 'rejected')>Rejected</option>
              <option value="suspended" @selected(request('status') == 'suspended')>Suspended</option>
            </select>
          </div>
          <div class="col-md-2 col-lg-2">
            <button type="submit" class="btn btn-filter w-100">
              <i class="bi bi-funnel me-1"></i>Apply
            </button>
          </div>
          <div class="col-md-2 col-lg-2">
            @if(request()->hasAny(['name','status']) && (request('name') || request('status')))
              <a href="{{ route('admin.clinics.index') }}" class="btn btn-clear-filter w-100">
                <i class="bi bi-x-circle me-1"></i>Clear
              </a>
            @endif
          </div>
        </form>
      </div>
    </div>

    <div class="admin-panel">
      <div class="admin-panel-header">
        <div>
          <h2 class="admin-panel-title">
            <i class="bi bi-hospital"></i> Registered Clinics
            <span class="admin-count-badge">{{ $clinics->total() }}</span>
          </h2>
          <p class="admin-panel-subtitle">Click a clinic row to open its profile and operational details</p>
        </div>
      </div>

      <div class="admin-panel-body">
        <div class="table-responsive clinics-table-wrapper">
          <table class="table clinics-table align-middle">
            <thead>
              <tr>
                <th>Clinic</th>
                <th class="text-center"><i class="bi bi-person-badge me-1"></i>Doctors</th>
                <th class="text-center"><i class="bi bi-clipboard-check me-1"></i>Services</th>
                <th><i class="bi bi-geo-alt me-1"></i>Location</th>
                <th><i class="bi bi-activity me-1"></i>Status</th>
                <th class="pe-4 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($clinics as $clinic)
              <tr class="clinic-row" onclick="window.location='{{ route('admin.clinics.show', $clinic) }}'">
                <td class="clinic-cell" data-label="Clinic">
                  <div class="d-flex align-items-center gap-3">
                    @if($clinic->logo)
                      <img src="{{ asset('storage/' . $clinic->logo) }}" alt="Clinic logo" class="clinic-avatar">
                    @else
                      <div class="clinic-avatar-placeholder">
                        <i class="bi bi-hospital"></i>
                      </div>
                    @endif
                    <div>
                      <div class="clinic-name">
                        <a href="{{ route('admin.clinics.show', $clinic) }}" class="text-decoration-none text-reset" onclick="event.stopPropagation();">{{ $clinic->name }}</a>
                      </div>
                      @if($clinic->branch_code)
                        <div class="clinic-branch">{{ $clinic->branch_code }}</div>
                      @endif
                      @if($clinic->contact_email)
                        <div class="clinic-subtext">{{ $clinic->contact_email }}</div>
                      @endif
                    </div>
                  </div>
                </td>
                <td class="text-center" data-label="Doctors">
                  <div class="count-value">{{ $clinic->doctor_count }}</div>
                  <span class="count-label">{{ Str::plural('Doctor', $clinic->doctor_count) }}</span>
                </td>
                <td class="text-center" data-label="Services">
                  @php $servicesCount = $clinic->services->count(); @endphp
                  <div class="count-value">{{ $servicesCount }}</div>
                  <span class="count-label">{{ Str::plural('Service', $servicesCount) }}</span>
                </td>
                <td data-label="Location">
                  <div class="location-text">{{ Str::limit($clinic->address, 60) }}</div>
                  <div class="location-secondary">Registered {{ $clinic->created_at->format('M d, Y') }}</div>
                </td>
                <td data-label="Status">
                  @php
                    $rawStatus = strtolower((string)($clinic->status ?? ''));
                    switch ($rawStatus) {
                      case 'active':
                        $statusLabel = 'Active';
                        $statusClass = 'status-success';
                        break;
                      case 'approved':
                        $statusLabel = 'Approved';
                        $statusClass = 'status-warning';
                        break;
                      case 'pending':
                        $statusLabel = 'Pending';
                        $statusClass = 'status-warning';
                        break;
                      case 'rejected':
                        $statusLabel = 'Rejected';
                        $statusClass = 'status-danger';
                        break;
                      case 'suspended':
                        $statusLabel = 'Suspended';
                        $statusClass = 'status-danger';
                        break;
                      case 'deleted':
                        $statusLabel = 'Deleted';
                        $statusClass = 'status-danger';
                        break;
                      default:
                        $statusLabel = $clinic->status ? ucfirst((string)$clinic->status) : 'Unknown';
                        $statusClass = 'status-neutral';
                    }
                  @endphp
                  <span class="status-pill {{ $statusClass }}">
                    <span class="status-dot"></span>
                    {{ $statusLabel }}
                  </span>
                </td>
                <td class="pe-4 text-end action-cell" data-label="Actions">
                  <div class="d-flex gap-2 justify-content-end" onclick="event.stopPropagation();">
                    <form method="POST"
                          action="{{ route('admin.clinics.destroy', $clinic) }}"
                          class="d-inline"
                          data-confirm="Delete this clinic? This action cannot be undone."
                          data-confirm-title="Delete Clinic"
                          data-confirm-btn="Delete">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                              class="action-btn action-btn-delete"
                              title="Delete Clinic"
                              aria-label="Delete Clinic">
                        <i class="bi bi-trash3"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6">
                  <div class="empty-state">
                    <div class="empty-state-icon">
                      <i class="bi bi-building-x"></i>
                    </div>
                    <h5 class="mb-2" style="color:#0f172a;font-weight:900">No clinics found</h5>
                    <p class="text-muted mb-4">Create a clinic or adjust your filters to broaden the results.</p>
                    <a href="{{ route('admin.clinics.create') }}" class="admin-primary-action">
                      <i class="bi bi-plus-circle"></i>Create First Clinic
                    </a>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($clinics->hasPages())
          <div class="d-flex justify-content-center pt-4">
            {{ $clinics->links() }}
          </div>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  const actionButtons = document.querySelectorAll('.action-btn[title]');
  actionButtons.forEach(button => {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
      new bootstrap.Tooltip(button);
    }
  });


});
</script>
@endpush
