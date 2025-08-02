<div>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Services</h3>
    <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> Add Service
    </a>
  </div>

  @if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('status') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ $errors->first('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

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
                        <button wire:click="confirmToggle({{ $service->id }})"
                                class="btn btn-outline-{{ $service->is_active ? 'success' : 'secondary' }} btn-sm rounded-pill">
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
                      <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteServiceModal{{ $service->id }}">
                        <i class="fas fa-trash"></i>
                      </button>
                      <!-- Delete Modal -->
                      <div class="modal fade" id="deleteServiceModal{{ $service->id }}" tabindex="-1" aria-labelledby="deleteServiceModalLabel{{ $service->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="deleteServiceModalLabel{{ $service->id }}">Delete Service</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              Are you sure you want to delete the service <strong>{{ $service->service_name }}</strong>?
                            </div>
                            <div class="modal-footer">
                              <form method="POST" action="{{ route('admin.services.destroy', $service) }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Delete</button>
                              </form>
                            </div>
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

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
          {{ $services->links() }}
        </div>
      @else
        <div class="text-center py-5">
          <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No services found</h5>
          <p class="text-muted">Start by adding your first service.</p>
          <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add First Service
          </a>
        </div>
      @endif
    </div>
  </div>
</div>

<!-- Status Toggle Confirmation Modal -->
@if($confirmingToggle && $serviceToToggle)
<div class="modal" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" aria-labelledby="toggleStatusModalLabel" aria-modal="true" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="toggleStatusModalLabel">Confirm Status Change</h5>
        <button type="button" class="btn-close" wire:click="cancelToggle" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to {{ $serviceToToggle->is_active ? 'deactivate' : 'activate' }} the service "{{ $serviceToToggle->service_name }}"?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" wire:click="cancelToggle">Cancel</button>
        <button type="button" class="btn btn-primary" wire:click="toggleStatus">Confirm</button>
      </div>
    </div>
  </div>
</div>
@endif
</div>
