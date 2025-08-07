@extends('layouts.secretary')
@section('title','Clinic Services')

@section('content')
<div class="container-fluid">
  @include('partials.alerts')

  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="card-title mb-0">
      <i class="bi-clipboard2-pulse"></i> Services List ({{ $services->total() }} total)
    </h3>
    <div>
      <a href="{{ route('secretary.services.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>
        Add New Service
      </a>
    </div>
  </div>

  @if($services->isEmpty())
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center py-5">
        <i class="bi bi-gear display-1 text-muted mb-3"></i>
        <h5 class="text-muted">No Services Available</h5>
        <p class="text-muted mb-3">This clinic doesn't have any services configured yet.</p>
        <small class="text-muted">Contact your administrator to add services to this clinic.</small>
      </div>
    </div>
  @else
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">Service Name</th>
                <th>Description</th>
                <th>Duration</th>
                <th>Date Added</th>
                <th>Status</th>
                <th class="pe-3" width="120">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($services as $service)
              @php
                $clinicService = $service->clinics->first();
              @endphp
              <tr>
                <td class="ps-3">
                  <div class="d-flex align-items-center">
                    <div class="me-3">
                      <i class="bi bi-gear-fill text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                      <div class="fw-medium">{{ $service->service_name }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  @if($service->description)
                    <span title="{{ $service->description }}">
                      {{ \Illuminate\Support\Str::limit($service->description, 60) }}
                    </span>
                  @else
                    <span class="text-muted">No description</span>
                  @endif
                </td>
                <td>
                  @if($clinicService && $clinicService->pivot->duration_minutes)
                    <span class="badge bg-info">
                      {{ $clinicService->pivot->duration_minutes }} min
                    </span>
                  @else
                    <span class="text-muted">Not set</span>
                  @endif
                </td>
                <td>
                  <small class="text-muted">{{ $service->created_at->format('M d, Y') }}</small>
                </td>
                <td>
                  @if($clinicService && $clinicService->pivot->is_active)
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-secondary">Inactive</span>
                  @endif
                </td>
                <td class="pe-3">
                  <div class="btn-group btn-group-sm" role="group">
                    <button type="button" 
                            class="btn btn-outline-danger" 
                            data-bs-toggle="modal" 
                            data-bs-target="#deleteModal{{ $service->id }}"
                            title="Remove Service">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>

                  <!-- Delete Confirmation Modal -->
                  <div class="modal fade" id="deleteModal{{ $service->id }}" tabindex="-1">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Confirm Service Removal</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <p>Are you sure you want to remove <strong>{{ $service->service_name }}</strong> from your clinic?</p>
                          <p class="text-muted small">This will deactivate the service but keep historical records intact.</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <form method="POST" action="{{ route('secretary.services.destroy', $service) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Remove Service</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      
      @if($services->hasPages())
        <div class="card-footer bg-white border-top">
          <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
              Showing {{ $services->firstItem() }} to {{ $services->lastItem() }} of {{ $services->total() }} services
            </div>
            {{ $services->links() }}
          </div>
        </div>
      @endif
    </div>
  @endif
</div>
@endsection
