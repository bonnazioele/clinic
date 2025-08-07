@extends('admin.layouts.app')

@section('title', 'Services')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Services</h3>
    <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> Add Service
    </a>
  </div>
  
  <div class="card">
    <div class="card-body">
      @if($services->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Service Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($services as $service)
                <tr>
                  <td>{{ $service->id }}</td>
                  <td>
                    <strong>{{ $service->service_name }}</strong>
                  </td>
                  <td>
                    @if($service->description)
                      {{ Str::limit($service->description, 50) }}
                    @else
                      <span class="text-muted">No description</span>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                        <button 
                            type="button"
                            class="btn btn-outline-{{ $service->is_active ? 'success' : 'secondary' }} btn-sm rounded-pill"
                            data-bs-toggle="modal"
                            data-bs-target="#toggleStatusModal"
                            data-service-id="{{ $service->id }}"
                            data-service-name="{{ $service->service_name }}"
                            data-service-active="{{ $service->is_active ? '1' : '0' }}">
                            {{ $service->is_active ? '🟢 On' : '⚪ Off' }}
                        </button>
                        <span class="badge {{ $service->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $service->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                  </td>
                  <td>{{ $service->created_at->format('M d, Y') }}</td>
                  <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('admin.services.edit', $service) }}" 
                            class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteServiceModal" data-service-id="{{ $service->id }}" data-service-name="{{ $service->service_name }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
          {{ $services->links() }}
        </div>
      @else
        <div class="text-center py-5">
          <i class="fas fa-building fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No services found</h5>
          <p class="text-muted">Start by adding your first services.</p>
          <a href="{{ route('admin.clinic-services.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add First Clinic Type
          </a>
        </div>
      @endif
    </div>
  </div>
</div>

<!-- Toggle Status Modal -->
<div class="modal fade" id="toggleStatusModal" tabindex="-1" aria-labelledby="toggleStatusLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content shadow">
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="toggleStatusLabel">Confirm Status Change</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to <span id="toggle-action-label" class="fw-bold text-capitalize"></span> <strong id="service-name-placeholder"></strong>?
      </div>
      <div class="modal-footer">
        <form method="POST" id="toggleStatusForm">
          @csrf
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i> Cancel
          </button>
          <button type="submit" class="btn" id="toggle-action-button">
            <i class="fas me-1" id="toggle-icon"></i> <span id="toggle-action-text"></span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

<!-- Delete Service Modal -->
<div class="modal fade" id="deleteServiceModal" tabindex="-1" aria-labelledby="deleteServiceLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content shadow">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteServiceLabel">Delete Service</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete <strong id="delete-service-name-placeholder"></strong>?
        <p class="text-muted mt-2 mb-0">This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <form method="POST" id="deleteServiceForm">
          @csrf
          @method('DELETE')
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i> Cancel
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash me-1"></i> Delete
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleModal = document.getElementById('toggleStatusModal');
    toggleModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        const serviceId = button.getAttribute('data-service-id');
        const serviceName = button.getAttribute('data-service-name');
        const isActive = button.getAttribute('data-service-active') === '1';

        document.getElementById('service-name-placeholder').textContent = serviceName;
        document.getElementById('toggle-action-label').textContent = isActive ? 'deactivate' : 'activate';
        document.getElementById('toggle-action-text').textContent = isActive ? 'Deactivate' : 'Activate';

        const form = document.getElementById('toggleStatusForm');
        const baseUrl = '{{ route("admin.services.index") }}';
        form.action = `${baseUrl}/${serviceId}/toggle-status`;

        const btn = document.getElementById('toggle-action-button');
        const icon = document.getElementById('toggle-icon');
        if (isActive) {
            btn.className = 'btn btn-warning';
            icon.className = 'fas fa-pause';
        } else {
            btn.className = 'btn btn-success';
            icon.className = 'fas fa-play';
        }
    });

    const deleteModal = document.getElementById('deleteServiceModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        const serviceId = button.getAttribute('data-service-id');
        const serviceName = button.getAttribute('data-service-name');

        document.getElementById('delete-service-name-placeholder').textContent = serviceName;

        const form = document.getElementById('deleteServiceForm');
        const baseUrl = '{{ route("admin.services.index") }}';
        form.action = `${baseUrl}/${serviceId}`;
    });
});
</script>
@endpush


