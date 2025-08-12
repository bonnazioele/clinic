@extends('admin.layouts.app')
@section('title','Clinics')
@section('content')
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="h3 fw-bold text-dark mb-1">
        <i class="bi bi-hospital me-2 text-primary"></i>Clinics Management
      </h2>
      <p class="text-muted mb-0">Manage and monitor all registered clinics</p>
    </div>
    <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary rounded-pill">
      <i class="bi bi-plus-circle me-2"></i> New Clinic
    </a>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr class="bg-light">
              <th class="border-0 px-4 py-3 fw-semibold">Logo</th>
              <th class="border-0 px-4 py-3 fw-semibold">Clinic Details</th>
              <th class="border-0 px-4 py-3 fw-semibold">Contact</th>
              <th class="border-0 px-4 py-3 fw-semibold">Type</th>
              <th class="border-0 px-4 py-3 fw-semibold">Services</th>
              <th class="border-0 px-4 py-3 fw-semibold">Location</th>
              <th class="border-0 px-4 py-3 fw-semibold" width="150">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($clinics as $clinic)
            <tr>
              <td class="px-4 py-3">
                @if($clinic->logo)
                  <img src="{{ asset('storage/' . $clinic->logo) }}"
                       alt="Logo"
                       class="rounded-3"
                       style="width: 45px; height: 45px; object-fit: cover;">
                @else
                  <div class="bg-light rounded-3 d-flex align-items-center justify-content-center"
                       style="width: 45px; height: 45px;">
                    <i class="bi bi-image text-muted"></i>
                  </div>
                @endif
              </td>
              <td class="px-4 py-3">
                <div>
                  <h6 class="fw-semibold text-dark mb-1">{{ $clinic->name }}</h6>
                  <small class="text-muted d-block">{{ $clinic->branch_code }}</small>
                  <small class="text-muted">{{ Str::limit($clinic->address, 30) }}</small>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="small">
                  @if($clinic->contact_number)
                    <div class="d-flex align-items-center mb-1">
                      <i class="bi bi-telephone text-muted me-2"></i>
                      <span>{{ $clinic->contact_number }}</span>
                    </div>
                  @endif
                  @if($clinic->email)
                    <div class="d-flex align-items-center">
                      <i class="bi bi-envelope text-muted me-2"></i>
                      <span>{{ $clinic->email }}</span>
                    </div>
                  @endif
                </div>
              </td>
              <td class="px-4 py-3">
                @if($clinic->type)
                  <span class="badge bg-info rounded-pill">{{ $clinic->type->type_name }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td class="px-4 py-3">
                @if($clinic->services->count() > 0)
                  <div class="small">
                    @foreach($clinic->services->take(2) as $service)
                      <span class="badge bg-secondary rounded-pill me-1 mb-1">{{ $service->service_name }}</span>
                    @endforeach
                    @if($clinic->services->count() > 2)
                      <span class="text-muted">+{{ $clinic->services->count() - 2 }} more</span>
                    @endif
                  </div>
                @else
                  <span class="text-muted small">No services</span>
                @endif
              </td>
              <td class="px-4 py-3 small">
                @if($clinic->gps_latitude && $clinic->gps_longitude)
                  <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-geo-alt text-muted me-2"></i>
                    <span>{{ number_format($clinic->gps_latitude, 4) }}</span>
                  </div>
                  <div class="d-flex align-items-center">
                    <i class="bi bi-geo-alt text-muted me-2"></i>
                    <span>{{ number_format($clinic->gps_longitude, 4) }}</span>
                  </div>
                @else
                  <span class="text-muted">No location</span>
                @endif
              </td>
              <td class="px-4 py-3">
                <div class="d-flex flex-column gap-2">
                  <a href="{{ route('admin.clinics.edit', $clinic) }}"
                     class="btn btn-sm btn-outline-primary rounded-pill">
                    <i class="bi bi-pencil me-1"></i> Edit
                  </a>
                  <form method="POST"
                        action="{{ route('admin.clinics.destroy', $clinic) }}"
                        class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this clinic?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger rounded-pill w-100">
                      <i class="bi bi-trash me-1"></i> Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-5">
                <div class="py-4">
                  <i class="bi bi-hospital display-4 text-muted mb-3"></i>
                  <h5 class="text-muted fw-semibold mb-2">No clinics found</h5>
                  <p class="text-muted mb-3">Get started by creating your first clinic.</p>
                  <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary rounded-pill">
                    <i class="bi bi-plus-circle me-2"></i> Create First Clinic
                  </a>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @if($clinics->hasPages())
    <div class="mt-4">
      {{ $clinics->links() }}
    </div>
  @endif

  <div id="map" class="rounded-4 shadow-sm mb-5" style="height: 300px;"></div>
@endsection

