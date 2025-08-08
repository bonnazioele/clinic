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
      <button type="button" class="btn btn-primary" id="addServiceBtn">
        <i class="bi bi-plus-circle me-2"></i>
        Add Service to Clinic
      </button>
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

<!-- Add Services Modal -->
<div class="modal fade" id="addServicesModal" tabindex="-1" aria-labelledby="addServicesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addServicesModalLabel">
          <i class="bi bi-plus-circle me-2"></i>
          Add Services to Clinic
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Search Box -->
        <div class="mb-3">
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-search"></i>
            </span>
            <input type="text" class="form-control" id="serviceSearch" placeholder="Search services...">
          </div>
        </div>

        <!-- Duration Setting -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="defaultDuration" class="form-label">Default Duration (minutes)</label>
            <input type="number" class="form-control" id="defaultDuration" value="30" min="5" max="480">
            <div class="form-text">This will be applied to all selected services</div>
          </div>
        </div>

        <!-- Services List -->
        <div id="servicesContainer">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Loading available services...</div>
          </div>
        </div>

        <!-- Selected Services Summary -->
        <div id="selectedSummary" class="mt-3" style="display: none;">
          <div class="alert alert-info">
            <strong>Selected Services: </strong>
            <span id="selectedCount">0</span> service(s) selected
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="addSelectedServices" disabled>
          <i class="bi bi-check-circle me-2"></i>
          Add Selected Services
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addServiceBtn = document.getElementById('addServiceBtn');
    const addServicesModal = new bootstrap.Modal(document.getElementById('addServicesModal'));
    const servicesContainer = document.getElementById('servicesContainer');
    const serviceSearch = document.getElementById('serviceSearch');
    const addSelectedBtn = document.getElementById('addSelectedServices');
    const selectedSummary = document.getElementById('selectedSummary');
    const selectedCount = document.getElementById('selectedCount');
    
    let availableServices = [];
    let selectedServices = new Set();

    // Open modal and load services
    addServiceBtn.addEventListener('click', function() {
        loadAvailableServices();
        addServicesModal.show();
    });

    // Load available services via AJAX
    function loadAvailableServices() {
        servicesContainer.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading available services...</div>
            </div>
        `;

        fetch('{{ route("secretary.services.available") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            availableServices = data.services;
            renderServices(availableServices);
        })
        .catch(error => {
            console.error('Error:', error);
            servicesContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading services. Please try again.
                </div>
            `;
        });
    }

    // Render services list
    function renderServices(services) {
        if (services.length === 0) {
            servicesContainer.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-check-circle display-1 text-success mb-3"></i>
                    <h5 class="text-muted">All Services Added</h5>
                    <p class="text-muted">This clinic has all available services assigned.</p>
                </div>
            `;
            return;
        }

        let html = '<div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">';
        
        services.forEach(service => {
            const isSelected = selectedServices.has(service.id);
            html += `
                <div class="list-group-item service-item" data-service-id="${service.id}">
                    <div class="d-flex align-items-center">
                        <div class="form-check me-3">
                            <input class="form-check-input service-checkbox" type="checkbox" 
                                   value="${service.id}" id="service_${service.id}" 
                                   ${isSelected ? 'checked' : ''}>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${service.service_name}</h6>
                            <p class="mb-0 text-muted small">
                                ${service.description || 'No description available'}
                            </p>
                        </div>
                        <div class="ms-2">
                            <label class="btn btn-sm ${isSelected ? 'btn-success' : 'btn-outline-primary'}" 
                                   for="service_${service.id}">
                                <i class="bi ${isSelected ? 'bi-check-lg' : 'bi-plus'}"></i>
                                ${isSelected ? 'Selected' : 'Add'}
                            </label>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        servicesContainer.innerHTML = html;

        // Add event listeners for checkboxes
        document.querySelectorAll('.service-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const serviceId = parseInt(this.value);
                const label = this.closest('.service-item').querySelector('label');
                const icon = label.querySelector('i');
                
                if (this.checked) {
                    selectedServices.add(serviceId);
                    label.className = 'btn btn-sm btn-success';
                    label.innerHTML = '<i class="bi bi-check-lg"></i> Selected';
                } else {
                    selectedServices.delete(serviceId);
                    label.className = 'btn btn-sm btn-outline-primary';
                    label.innerHTML = '<i class="bi bi-plus"></i> Add';
                }
                
                updateSelectedSummary();
            });
        });
    }

    // Search functionality
    serviceSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const filteredServices = availableServices.filter(service => 
            service.service_name.toLowerCase().includes(searchTerm) ||
            (service.description && service.description.toLowerCase().includes(searchTerm))
        );
        renderServices(filteredServices);
    });

    // Update selected summary
    function updateSelectedSummary() {
        const count = selectedServices.size;
        selectedCount.textContent = count;
        
        if (count > 0) {
            selectedSummary.style.display = 'block';
            addSelectedBtn.disabled = false;
        } else {
            selectedSummary.style.display = 'none';
            addSelectedBtn.disabled = true;
        }
    }

    // Add selected services
    addSelectedBtn.addEventListener('click', function() {
        if (selectedServices.size === 0) return;

        const duration = document.getElementById('defaultDuration').value;
        
        if (!duration || duration < 5 || duration > 480) {
            alert('Please enter a valid duration between 5 and 480 minutes.');
            return;
        }

        // Show loading state
        addSelectedBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';
        addSelectedBtn.disabled = true;

        // Prepare data
        const data = {
            service_ids: Array.from(selectedServices),
            duration_minutes: parseInt(duration)
        };

        // Send AJAX request
        fetch('{{ route("secretary.services.add-to-clinic") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                addServicesModal.hide();
                
                // Show success message
                showAlert('success', data.success);
                
                // Reload page to show updated services
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else if (data.error) {
                showAlert('danger', data.error);
            }
            
            if (data.errors && data.errors.length > 0) {
                data.errors.forEach(error => {
                    showAlert('warning', error);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while adding services. Please try again.');
        })
        .finally(() => {
            // Reset button
            addSelectedBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Add Selected Services';
            addSelectedBtn.disabled = false;
        });
    });

    // Reset modal when closed
    document.getElementById('addServicesModal').addEventListener('hidden.bs.modal', function() {
        selectedServices.clear();
        serviceSearch.value = '';
        document.getElementById('defaultDuration').value = '30';
        updateSelectedSummary();
    });

    // Show alert function
    function showAlert(type, message) {
        const alertsContainer = document.querySelector('.container-fluid');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert after the alerts include
        const existingAlerts = alertsContainer.querySelector('[data-include="partials.alerts"]') || 
                              alertsContainer.querySelector('.alert') ||
                              alertsContainer.firstElementChild;
        
        if (existingAlerts) {
            existingAlerts.parentNode.insertBefore(alert, existingAlerts.nextSibling);
        } else {
            alertsContainer.insertBefore(alert, alertsContainer.firstElementChild);
        }

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
});
</script>
@endpush
@endsection
