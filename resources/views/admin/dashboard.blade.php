@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
  
  
  <div class="row mb-4">
    <div class="col-12">
      <div class="medical-card p-4 text-center">
        <div class="d-flex align-items-center justify-content-center mb-3">
          <i class="bi bi-heart-pulse-fill medical-icon me-3" style="font-size: 3rem;"></i>
          <div>
            <h1 class="mb-1 fw-bold text-primary">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="text-muted mb-0">
              <i class="bi bi-shield-check me-2"></i>Administrator Dashboard
            </p>
          </div>
        </div>
        <div class="row g-3 text-start">
          <div class="col-md-3">
            <a href="{{ route('admin.clinics.index') }}" class="text-decoration-none d-block" style="outline:0;">
              <div class="p-4 rounded text-white" style="background:#1976ff;">
                <div class="fs-3 fw-bold">{{ $registeredClinics }}</div>
                <div class="mt-1">Total Registered Clinics</div>
              </div>
            </a>
          </div>
          <div class="col-md-3">
            <a href="{{ route('admin.services.index') }}" class="text-decoration-none d-block" style="outline:0;">
              <div class="p-4 rounded text-white" style="background:#1f7f56;">
                <div class="fs-3 fw-bold">{{ $services }}</div>
                <div class="mt-1">Total Services</div>
              </div>
            </a>
          </div>
          <div class="col-md-3">
            <a href="{{ route('admin.users.index') }}" class="text-decoration-none d-block" style="outline:0;">
              <div class="p-4 rounded" style="background:#ffc107; color:#212529;">
                <div class="fs-3 fw-bold">{{ $users }}</div>
                <div class="mt-1">Total Users</div>
              </div>
            </a>
          </div>
          <div class="col-md-3">
            <a href="#pending-clinic-applicants" class="text-decoration-none d-block" style="outline:0;">
              <div class="p-4 rounded text-white" style="background:#6c757d;">
                <div class="fs-3 fw-bold">{{ $pendingCount }}</div>
                <div class="mt-1">Total Pending Clinics</div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="medical-card p-4 mb-4">
    <h5 class="fw-semibold mb-3 d-flex align-items-center gap-2"><i class="bi bi-lightning-charge"></i>Quick Actions</h5>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bi bi-plus-circle"></i><span>Add Clinic</span>
      </a>
      <a href="{{ route('admin.services.create') }}" class="btn btn-success d-flex align-items-center gap-2">
        <i class="bi bi-gear-wide-connected"></i><span>Add Service</span>
      </a>
      <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-graph-up"></i><span>Reports</span>
      </a>
    </div>
  </div>

  
  <div class="medical-card p-4" id="pending-clinic-applicants">
    <div class="d-flex align-items-center mb-3">
      <h5 class="mb-0"><i class="bi bi-inbox me-2"></i>Pending Clinic Applicants <span class="badge bg-primary ms-2">{{ $pendingCount }}</span></h5>
    </div>
    @if(($pendingClinics ?? collect())->isEmpty())
      <div class="text-center text-muted py-3">
        <i class="bi bi-check2-circle fs-3 d-block mb-2"></i>
        No pending applications.
      </div>
    @else
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Name</th>
              <th>Contact</th>
              <th>Address</th>
              <th>Applied</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($pendingClinics as $pc)
              <tr class="cursor-pointer" onclick="window.location='{{ route('admin.clinics.application', $pc) }}'" style="cursor: pointer;">
                <td>
                  <strong>{{ $pc->name }}</strong>
                  @if($pc->branch_code)
                    <div class="small text-muted">{{ $pc->branch_code }}</div>
                  @endif
                </td>
                <td>
                  @if($pc->contact_first_name || $pc->contact_last_name)
                    <div class="fw-semibold">{{ trim(($pc->contact_first_name ?? '') . ' ' . ($pc->contact_last_name ?? '')) }}</div>
                  @endif
                  @if($pc->contact_person_email)
                    <div class="small text-primary">{{ $pc->contact_person_email }} <i class="bi bi-person-check"></i></div>
                  @endif
                  <div class="small text-muted">{{ $pc->email }}</div>
                  @if($pc->contact_number)
                    <div class="small text-muted">{{ $pc->contact_number }}</div>
                  @endif
                </td>
                <td class="small">{{ Str::limit($pc->address, 50) }}</td>
                <td class="small text-muted">{{ $pc->created_at->diffForHumans() }}</td>
                <td class="text-end">
                  <a href="{{ route('admin.clinics.application', $pc) }}" class="btn btn-sm btn-primary" onclick="event.stopPropagation()">
                    <i class="bi bi-eye me-1"></i>Review
                  </a>
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
