@extends('admin.layouts.app')
@section('title','Clinics')

@push('styles')
<style>
  .clinics-table-wrapper {
    border-radius: 18px;
    border: 1px solid #E2E8F0;
    background: #ffffff;
    overflow-x: auto;
    overflow-y: visible;
  }

  .clinics-table {
    margin-bottom: 0;
  }

  .clinics-table thead th {
    background: #F1F5F9;
    border: none;
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #475569;
    font-weight: 600;
    padding: 0.9rem 1.25rem;
  }

  .clinics-table thead th:first-child {
    width: 54px;
  }

  .clinics-table tbody tr {
    background: #ffffff;
    border-bottom: 1px solid #E2E8F0;
    cursor: pointer;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    position: relative;
    z-index: 0;
  }

  .clinics-table tbody tr:last-child {
    border-bottom: none;
  }

  .clinics-table tbody tr:hover {
    background: #F8FAFC;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    transform: translateY(-2px);
    z-index: 5;
  }

  .clinics-table td {
    padding: 0.95rem 1.25rem !important;
    vertical-align: middle;
  }

  .select-cell {
    padding-left: 1.5rem !important;
  }

  .clinic-checkbox {
    width: 16px;
    height: 16px;
    border-radius: 6px;
    border-color: #CBD5F5;
  }

  .clinic-checkbox:focus {
    box-shadow: none;
  }

  .clinic-cell {
    min-width: 220px;
  }

  .clinic-avatar,
  .clinic-avatar-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
  }

  .clinic-avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #E0F2FE;
    color: #0284C7;
    font-size: 1.25rem;
  }

  .clinic-meta .clinic-name {
    font-size: 0.975rem;
    font-weight: 600;
    color: #0F172A;
    margin-bottom: 0.1rem;
  }

  .clinic-meta .clinic-branch {
    font-size: 0.75rem;
    color: #64748B;
  }

  .clinic-meta .clinic-subtext {
    font-size: 0.7rem;
    color: #94A3B8;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .count-value {
    font-weight: 600;
    color: #0F172A;
    font-size: 0.95rem;
    line-height: 1.1;
  }

  .count-label {
    display: block;
    font-size: 0.68rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #94A3B8;
  }

  .location-text {
    font-size: 0.8rem;
    color: #475569;
  }

  .location-secondary {
    font-size: 0.7rem;
    color: #94A3B8;
    margin-top: 0.2rem;
  }

  .status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.35rem 0.85rem;
    line-height: 1;
  }

  .status-pill .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0.7;
  }

  .status-success {
    background: #DCFCE7;
    color: #15803D;
  }

  .status-warning {
    background: #FEF9C3;
    color: #B45309;
  }

  .status-danger {
    background: #FEE2E2;
    color: #B91C1C;
  }

  .status-neutral {
    background: #E2E8F0;
    color: #475569;
  }

  .action-dropdown {
    position: relative;
  }

  .action-dropdown .btn-light {
    border-radius: 12px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    color: #475569;
  }

  .action-dropdown .btn-light:hover {
    background: #E2E8F0;
    color: #0F172A;
  }

  .action-dropdown .dropdown-menu {
    min-width: 190px;
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
    border: 1px solid #E2E8F0;
    border-radius: 12px;
  }

  .action-dropdown .dropdown-item {
    padding: 0.55rem 1rem;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .action-dropdown .dropdown-item i {
    width: 16px;
    font-size: 0.875rem;
  }

  .action-dropdown .dropdown-divider {
    margin: 0.5rem 0;
  }

  @media (max-width: 992px) {
    .clinics-table thead {
      display: none;
    }

    .clinics-table tbody tr {
      display: block;
      border-radius: 16px;
      margin-bottom: 1rem;
      padding: 1rem;
    }

    .clinics-table td {
      display: flex;
      justify-content: space-between;
      padding: 0.35rem 0 !important;
      border-bottom: none;
    }

    .clinics-table td::before {
      content: attr(data-label);
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #94A3B8;
    }

    .select-cell,
    .action-cell {
      justify-content: flex-start;
    }

    .clinic-cell {
      flex-direction: column;
      align-items: flex-start !important;
    }

    .clinics-table-wrapper {
      border: none;
    }
  }
</style>
@endpush

@section('content')
<div class="container py-4">
  
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold text-primary mb-1">
          <i class="bi bi-building-fill-gear me-2"></i>Clinics Management
        </h2>
        <p class="text-muted mb-0">Manage and monitor all registered healthcare facilities</p>
      </div>
      <div class="d-none d-md-block">
        <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary">
          <i class="bi bi-plus-circle me-2"></i>New Clinic
        </a>
      </div>
    </div>
    <div class="mt-3 d-md-none">
      <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary w-100">
        <i class="bi bi-plus-circle me-2"></i>New Clinic
      </a>
    </div>
  </div>

  
  <div class="medical-card p-4 mb-4">
    <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.clinics.index') }}">
      <div class="col-md-5 col-lg-4">
        <label class="form-label fw-semibold"><i class="bi bi-search me-1"></i>Search</label>
        <input type="text" name="name" class="form-control form-control-lg" placeholder="Search clinic name..." value="{{ request('name') }}">
      </div>
      <div class="col-md-4 col-lg-3">
        <label class="form-label fw-semibold"><i class="bi bi-filter me-1"></i>Status</label>
        <select name="status" class="form-select form-select-lg">
          <option value="">All Status</option>
          <option value="active" @selected(request('status') == 'active')>Active</option>
          <option value="inactive" @selected(request('status') == 'inactive')>Inactive</option>
        </select>
      </div>
      <div class="col-md-2 col-lg-2">
        <button type="submit" class="btn btn-primary btn-lg w-100">
          <i class="bi bi-funnel me-1"></i>Apply
        </button>
      </div>
      <div class="col-md-2 col-lg-2">
        @if(request()->hasAny(['name','status']) && (request('name') || request('status')))
          <a href="{{ route('admin.clinics.index') }}" class="btn btn-outline-secondary btn-lg w-100">
            <i class="bi bi-x-circle me-1"></i>Clear
          </a>
        @endif
      </div>
    </form>
  </div>

  
  <div class="medical-card p-4">
    <div class="mb-4">
      <h4 class="mb-0 d-flex align-items-center gap-2">
        Registered Clinics
        <span class="badge bg-primary" style="font-size:.75rem;">{{ $clinics->total() }}</span>
      </h4>
    </div>

    <div class="table-responsive clinics-table-wrapper">
      <table class="table clinics-table align-middle">
        <thead>
          <tr>
            <th class="ps-4">
              <input type="checkbox" class="form-check-input clinic-checkbox" id="select-all-clinics">
            </th>
            <th>Clinic</th>
            <th class="text-center">Staff</th>
            <th class="text-center">Doctors</th>
            <th class="text-center">Services</th>
            <th>Location</th>
            <th>Status</th>
            <th class="pe-4 text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($clinics as $clinic)
          <tr class="clinic-row" onclick="window.location='{{ route('admin.clinics.show', $clinic) }}'">
            <td class="select-cell" data-label="Select">
              <input type="checkbox" class="form-check-input clinic-checkbox row-checkbox" onclick="event.stopPropagation();">
            </td>
            <td class="clinic-cell" data-label="Clinic">
              <div class="d-flex align-items-center gap-3">
                @if($clinic->logo)
                  <img src="{{ asset('storage/' . $clinic->logo) }}" alt="Clinic logo" class="clinic-avatar">
                @else
                  <div class="clinic-avatar-placeholder">
                    <i class="bi bi-hospital"></i>
                  </div>
                @endif
                <div class="clinic-meta">
                  <div class="clinic-name">
                    <a href="{{ route('admin.clinics.show', $clinic) }}" class="text-decoration-none text-reset" onclick="event.stopPropagation();">{{ $clinic->name }}</a>
                  </div>
                  @if($clinic->branch_code)
                    <div class="clinic-branch">{{ $clinic->branch_code }}</div>
                  @endif
                  @if($clinic->contact_email)
                    <div class="clinic-subtext">{{ Str::limit($clinic->contact_email, 28) }}</div>
                  @endif
                </div>
              </div>
            </td>
            <td class="text-center" data-label="Staff">
              <div class="count-value">{{ $clinic->secretary_count }}</div>
              <span class="count-label">{{ Str::plural('Staff', $clinic->secretary_count) }}</span>
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
                    // Approved but not yet activated by first login
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
              <div class="dropdown action-dropdown" onclick="event.stopPropagation();">
                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="{{ route('admin.clinics.edit', $clinic) }}">
                      <i class="bi bi-pencil"></i>
                      <span>Edit</span>
                    </a>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <button class="dropdown-item text-warning" onclick="suspendClinic({{ $clinic->id }})">
                      <i class="bi bi-pause-circle"></i>
                      <span>Suspend</span>
                    </button>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <button class="dropdown-item text-danger" onclick="deleteClinic({{ $clinic->id }})">
                      <i class="bi bi-trash3"></i>
                      <span>Delete</span>
                    </button>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center py-5">
              <div class="medical-card p-4 mt-4">
                <i class="bi bi-building-x display-4 text-muted mb-3"></i>
                <h5 class="text-muted mb-3">No Clinics Found</h5>
                <p class="text-muted mb-4">Get started by creating your first clinic.</p>
                <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary">
                  <i class="bi bi-plus-circle me-2"></i>Create First Clinic
                </a>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($clinics->hasPages())
      <div class="d-flex justify-content-center p-4">
        {{ $clinics->links() }}
      </div>
    @endif
  </div>

  
</div>
@endsection

@push('scripts')
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const selectAllCheckbox = document.getElementById('select-all-clinics');
    const rowCheckboxes = Array.from(document.querySelectorAll('.row-checkbox'));

    if (selectAllCheckbox && rowCheckboxes.length) {
      selectAllCheckbox.addEventListener('change', (event) => {
        rowCheckboxes.forEach(cb => {
          cb.checked = event.target.checked;
        });
      });

      rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
          if (!cb.checked) {
            selectAllCheckbox.checked = false;
            return;
          }

          const allChecked = rowCheckboxes.every(item => item.checked);
          selectAllCheckbox.checked = allChecked;
        });
      });
    }
  });

  function suspendClinic(clinicId) {
    if (!confirm('Are you sure you want to suspend this clinic? The clinic will be temporarily disabled.')) return;
    // TODO: Implement suspend functionality
    alert('Suspend functionality to be implemented. Clinic ID: ' + clinicId);
  }

  function deleteClinic(clinicId) {
    if (!confirm('Are you sure you want to delete this clinic? This action cannot be undone.')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/clinics/${clinicId}`;
    form.innerHTML = `
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="_method" value="DELETE">
    `;
    document.body.appendChild(form);
    form.submit();
  }
  </script>
@endpush
