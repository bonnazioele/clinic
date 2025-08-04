<div>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Services</h3>
    <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> Add Service
    </a>
  </div>
  @if($error || $message)
    <div class="alert alert-{{ $error ? 'danger' : 'success' }} alert-dismissible fade show auto-dismiss" role="alert" data-auto-dismiss="2000">
      {{ $error ?? $message }}
      <button type="button" class="btn-close" wire:click="$set($error ? 'error' : 'message', null)" aria-label="Close"></button>
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
                    <div class="d-flex align-items-center gap-2">
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
                        <button type="button" class="btn btn-outline-danger" wire:click="confirmDelete({{ $service->id }})">
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
        <x-empty-state icon="fas fa-concierge-bell" title="No services found" subtitle="Start by adding your first service to get started." buttonLabel="Add First Service" :buttonRoute="route('admin.services.create')" buttonIcon="fas fa-plus"/>
      @endif
    </div>
  </div>
</div>

<x-status-toggle-modal :itemName="$serviceToToggleName" :isActive="$serviceToToggleStatus" :confirming="$confirmingToggle" :closing="$closing"/>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('close-modal', (event) => {
        const delay = event.delay || 300;
        setTimeout(() => {
            @this.call('actuallyCancel');
        }, delay);
    });
});
</script>

@if($confirmingDelete && $serviceToDelete)
<div class="modal" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" aria-labelledby="deleteServiceModalLabel" aria-modal="true" role="dialog">
  <x-delete-modal title="Delete Service" :route="route('admin.services.destroy', $serviceToDelete)" :itemName="$serviceToDeleteName" livewireCancel="cancelDelete">
    This action cannot be undone. Are you sure you want to delete <strong>{{ $serviceToDeleteName }}</strong>?
  </x-delete-modal>
</div>
@endif
</div>

