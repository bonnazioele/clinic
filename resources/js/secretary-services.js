/**
 * Secretary Services Management
 * Handles the add service to clinic functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Only run if we're on the services page
    if (!document.getElementById('addServiceBtn')) {
        return;
    }

    const addServiceBtn = document.getElementById('addServiceBtn');
    const addServicesModal = new bootstrap.Modal(document.getElementById('addServicesModal'));
    const servicesContainer = document.getElementById('servicesContainer');
    const serviceSearch = document.getElementById('serviceSearch');
    const addSelectedBtn = document.getElementById('addSelectedServices');
    const selectedSummary = document.getElementById('selectedSummary');
    const selectedCount = document.getElementById('selectedCount');
    
    let availableServices = [];
    let selectedServices = new Set();

    // Get routes from global window object (set from blade template)
    const routes = window.secretaryServiceRoutes || {};

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

        axios.get(routes.available)
            .then(response => {
                availableServices = response.data.services;
                renderServices(availableServices);
            })
            .catch(error => {
                console.error('Error loading services:', error);
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
                                   value="${service.id}" ${isSelected ? 'checked' : ''}>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${service.service_name}</h6>
                            <p class="mb-0 text-muted small">
                                ${service.description || 'No description available'}
                            </p>
                        </div>
                        <div class="ms-2">
                            <label class="btn btn-sm ${isSelected ? 'btn-success' : 'btn-outline-primary'}" 
                                   for="service-${service.id}">
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
                    icon.className = 'bi bi-check-lg';
                    label.innerHTML = '<i class="bi bi-check-lg"></i> Selected';
                } else {
                    selectedServices.delete(serviceId);
                    label.className = 'btn btn-sm btn-outline-primary';
                    icon.className = 'bi bi-plus';
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
            showAlert('warning', 'Please enter a valid duration between 5 and 480 minutes.');
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

        // Send AJAX request using Axios
        axios.post(routes.addToClinic, data)
            .then(response => {
                if (response.data.success) {
                    // Close modal
                    addServicesModal.hide();
                    
                    // Show success message
                    showAlert('success', response.data.success);
                    
                    // Reload page to show updated services
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else if (response.data.error) {
                    showAlert('danger', response.data.error);
                }
                
                if (response.data.errors && response.data.errors.length > 0) {
                    response.data.errors.forEach(error => {
                        showAlert('warning', error);
                    });
                }
            })
            .catch(error => {
                console.error('Error adding services:', error);
                
                // Handle different types of errors
                if (error.response) {
                    // Server responded with error status
                    const errorData = error.response.data;
                    if (errorData.error) {
                        showAlert('danger', errorData.error);
                    } else if (errorData.errors) {
                        // Validation errors
                        Object.values(errorData.errors).forEach(errorArray => {
                            errorArray.forEach(errorMsg => {
                                showAlert('warning', errorMsg);
                            });
                        });
                    } else {
                        showAlert('danger', 'Server error occurred. Please try again.');
                    }
                } else if (error.request) {
                    // Network error
                    showAlert('danger', 'Network error. Please check your connection and try again.');
                } else {
                    // Other error
                    showAlert('danger', 'An unexpected error occurred. Please try again.');
                }
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
