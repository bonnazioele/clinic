@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="p-4 rounded text-white d-flex flex-column" style="background:#1976ff;">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-building" style="font-size:1.5rem;"></i>
          <div class="fs-3 fw-bold">{{ $clinics }}</div>
        </div>
        <div class="mt-1">Total Clinics</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="p-4 rounded text-white d-flex flex-column" style="background:#1f7f56;">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-gear" style="font-size:1.5rem;"></i>
          <div class="fs-3 fw-bold">{{ $services }}</div>
        </div>
        <div class="mt-1">Total Services</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="p-4 rounded d-flex flex-column" style="background:#ffc107;">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-people" style="font-size:1.5rem;"></i>
          <div class="fs-3 fw-bold">{{ $users }}</div>
        </div>
        <div class="mt-1">Total Users</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="p-4 rounded text-white d-flex flex-column" style="background:#10c9f4;">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-calendar-check" style="font-size:1.5rem;"></i>
          <div class="fs-3 fw-bold">{{ $appointments }}</div>
        </div>
        <div class="mt-1">Total Appointments</div>
      </div>
    </div>
  </div>

  <div class="medical-card p-4 mb-4">
    <h5 class="fw-semibold mb-3"><i class="bi bi-gear me-2"></i>Quick Links</h5>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('admin.clinics.index') }}" class="btn btn-primary"><i class="bi bi-building me-2"></i>Manage Clinics</a>
      <a href="{{ route('admin.services.index') }}" class="btn btn-success"><i class="bi bi-gear-wide-connected me-2"></i>Manage Services</a>
      <a href="{{ route('admin.users.index') }}" class="btn btn-info"><i class="bi bi-people me-2"></i>Users</a>
    </div>
  </div>

  {{-- Pending Clinic Applicants --}}
  <div class="medical-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h5 class="mb-0"><i class="bi bi-inbox me-2"></i>Pending Clinic Applicants</h5>
      <span class="badge bg-primary">{{ $pendingCount }}</span>
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
              <tr>
                <td>{{ $pc->name }}</td>
                <td>
                  <div class="small text-muted">{{ $pc->email }}</div>
                  <div class="small text-muted">{{ $pc->contact_number }}</div>
                </td>
                <td class="small">{{ $pc->address }}</td>
                <td class="small text-muted">{{ $pc->created_at->diffForHumans() }}</td>
                <td class="text-end">
                  <form action="{{ route('admin.clinics.approve', $pc) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-success"><i class="bi bi-check2 me-1"></i>Approve</button>
                  </form>
                  <form action="{{ route('admin.clinics.decline', $pc) }}" method="POST" class="d-inline ms-1">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x me-1"></i>Decline</button>
                  </form>
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
