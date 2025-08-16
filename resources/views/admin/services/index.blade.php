@extends('admin.layouts.app')

@section('title', 'Services')

@section('content')
<div class="container py-4">
  {{-- Header --}}
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h2 fw-bold text-primary mb-2">
          <i class="bi bi-gear-wide-connected medical-icon me-3" style="font-size: 2.5rem;"></i>Services Management
        </h1>
        <p class="text-muted mb-0 fs-5">Manage and configure available medical services</p>
      </div>
      <a href="{{ route('admin.services.create') }}" class="btn btn-success px-4">
        <i class="bi bi-plus-circle me-2"></i>Add Service
      </a>
    </div>

    <!-- Statistics Row -->
    <div class="row g-4">
      <div class="col-md-4">
        <div class="text-center p-4 bg-light rounded-3 border">
          <div class="dashboard-stat text-primary">{{ $services->total() }}</div>
          <small class="text-muted fw-semibold">Total Services</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="text-center p-4 bg-light rounded-3 border">
          <div class="dashboard-stat text-success">{{ \App\Models\Clinic::count() }}</div>
          <small class="text-muted fw-semibold">Active Clinics</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="text-center p-4 bg-light rounded-3 border">
          <div class="dashboard-stat text-info">{{ \App\Models\User::where('is_doctor', true)->count() }}</div>
          <small class="text-muted fw-semibold">Healthcare Providers</small>
        </div>
      </div>
    </div>
  </div>

  {{-- Optional Toast --}}
  @include('partials.alerts')

  {{-- Table --}}
  <div class="medical-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">
        <i class="bi bi-gear medical-icon me-2"></i>Available Services
      </h4>
      <span class="badge bg-primary fs-6">{{ $services->total() }} Services</span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr class="bg-light">
            <th class="border-0 px-4 py-3 fw-semibold">
              <i class="bi bi-gear me-1"></i>Service Name
            </th>
            <th class="border-0 px-4 py-3 fw-semibold">
              <i class="bi bi-info-circle me-1"></i>Description
            </th>
            <th class="border-0 px-4 py-3 fw-semibold text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($services as $service)
            <tr>
              <td class="px-4 py-4">
                <h6 class="fw-semibold text-dark mb-0">{{ $service->name }}</h6>
              </td>
              <td class="px-4 py-4">
                <span class="text-muted">{{ Str::limit($service->description, 50) ?: 'No description provided' }}</span>
              </td>
              <td class="px-4 py-4 text-end">
                <div class="d-flex gap-2 justify-content-end">
                  <a href="{{ route('admin.services.edit', $service) }}"
                     class="btn btn-sm btn-outline-primary rounded-pill">
                    <i class="bi bi-pencil me-1"></i>Edit
                  </a>
                  <form method="POST"
                        action="{{ route('admin.services.destroy', $service) }}"
                        class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this service?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger rounded-pill">
                      <i class="bi bi-trash me-1"></i>Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center py-5">
                <div class="medical-card p-4 mt-4">
                  <i class="bi bi-gear-x display-4 text-muted mb-3"></i>
                  <h5 class="text-muted mb-3">No Services Found</h5>
                  <p class="text-muted mb-4">Get started by adding your first medical service.</p>
                  <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add First Service
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    @if($services->hasPages())
      <div class="d-flex justify-content-center p-4">
        {{ $services->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
