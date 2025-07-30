@extends('admin.layouts.app')

@section('title','Add Clinic')

@section('content')
  <h3 class="mb-4">Add Clinic</h3>

  <form method="POST" action="{{ route('admin.clinics.store') }}" enctype="multipart/form-data">
    @csrf

    <!-- Name -->
    <div class="mb-3">
      <label class="form-label">Clinic Name <span class="text-danger">*</span></label>
      <input type="text"
             name="name"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name') }}"
             required
             maxlength="255"
             placeholder="Enter clinic name">
      @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Branch Code -->
    <div class="mb-3">
      <label class="form-label">Branch Code <span class="text-danger">*</span></label>
      <input type="text"
             name="branch_code"
             class="form-control @error('branch_code') is-invalid @enderror"
             value="{{ old('branch_code') }}"
             required
             maxlength="50"
             placeholder="e.g., CLINIC001, BRANCH-A1">
      @error('branch_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
      <div class="form-text">Unique identifier for this clinic branch.</div>
    </div>

    <!-- Address -->
    <div class="mb-3">
      <label class="form-label">Address <span class="text-danger">*</span></label>
      <textarea name="address"
                class="form-control @error('address') is-invalid @enderror"
                rows="3"
                required
                maxlength="1000"
                placeholder="Enter complete address">{{ old('address') }}</textarea>
      @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Contact Information Row -->
    <div class="row">
      <div class="col-md-6">
        <!-- Contact Number -->
        <div class="mb-3">
          <label class="form-label">Contact Number <span class="text-danger">*</span></label>
          <input type="tel"
                 name="contact_number"
                 class="form-control @error('contact_number') is-invalid @enderror"
                 value="{{ old('contact_number') }}"
                 required
                 maxlength="50"
                 placeholder="e.g., +63 917 123 4567">
          @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="col-md-6">
        <!-- Email -->
        <div class="mb-3">
          <label class="form-label">Email Address <span class="text-danger">*</span></label>
          <input type="email"
                 name="email"
                 class="form-control @error('email') is-invalid @enderror"
                 value="{{ old('email') }}"
                 required
                 maxlength="100"
                 placeholder="clinic@example.com">
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>

    <!-- Clinic Type -->
    <div class="mb-3">
      <label class="form-label">Clinic Type <span class="text-danger">*</span></label>
      <select name="type_id" 
              class="form-select @error('type_id') is-invalid @enderror" 
              required>
        <option value="">Select Clinic Type</option>
        @foreach($clinicTypes as $clinicType)
          <option value="{{ $clinicType->id }}" 
                  {{ old('type_id') == $clinicType->id ? 'selected' : '' }}>
            {{ $clinicType->type_name }}
          </option>
        @endforeach
      </select>
      @error('type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Logo Upload -->
    <div class="mb-3">
      <label class="form-label">Clinic Logo <small class="text-muted">(optional)</small></label>
      <input type="file"
             name="logo"
             class="form-control @error('logo') is-invalid @enderror"
             accept="image/jpeg,image/png,image/jpg,image/gif"
             id="logoInput">
      @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
      <div class="form-text">Accepted formats: JPEG, PNG, JPG, GIF. Maximum size: 2MB.</div>
      
      <!-- Logo Preview -->
      <div id="logoPreview" class="mt-2" style="display: none;">
        <img id="previewImage" src="" alt="Logo Preview" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
        <div class="mt-1">
          <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLogo()">Remove</button>
        </div>
      </div>
    </div>

    <!-- Location Section -->
    <div class="mb-3">
      <label class="form-label">Location <span class="text-danger">*</span></label>
      <div class="form-text mb-2">Click on the map or drag the marker to set the clinic location.</div>
      <div id="mapPicker" class="map-picker"></div>
    </div>

    <!-- Latitude & Longitude -->
    <div class="row">
      <div class="col">
        <label class="form-label">Latitude <span class="text-danger">*</span></label>
        <input type="text"
               id="lat"
               name="latitude"
               class="form-control @error('latitude') is-invalid @enderror"
               value="{{ old('latitude') }}"
               readonly
               required>
        @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col">
        <label class="form-label">Longitude <span class="text-danger">*</span></label>
        <input type="text"
               id="lng"
               name="longitude"
               class="form-control @error('longitude') is-invalid @enderror"
               value="{{ old('longitude') }}"
               readonly
               required>
        @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    <!-- Services Offered -->
    <div class="mb-3">
      <label class="form-label">Services Offered <small class="text-muted">(optional)</small></label>
      <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" id="servicesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          Select Services
        </button>
        <ul class="dropdown-menu w-100" aria-labelledby="servicesDropdown" style="max-height: 200px; overflow-y: auto;">
          @foreach($services as $service)
            <li>
              <label class="dropdown-item">
                <input type="checkbox" 
                       class="form-check-input me-2 service-checkbox" 
                       name="service_ids[]" 
                       value="{{ $service->id }}"
                       {{ in_array($service->id, old('service_ids', [])) ? 'checked' : '' }}>
                {{ $service->service_name }}
              </label>
            </li>
          @endforeach
        </ul>
      </div>
      @error('service_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
      
      <!-- Selected Services Display -->
      <div id="selected-services" class="mt-2"></div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> Save Clinic
      </button>
      <a href="{{ route('admin.clinics.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-times me-1"></i> Cancel
      </a>
    </div>
  </form>
@endsection

@section('scripts')
<script>
  // Wait for DOM to be fully loaded
  document.addEventListener('DOMContentLoaded', function() {
    // Services data injected from Laravel
    const services = @json($services);
    const selectedPanel = document.getElementById('selected-services');

    // Function to update the selected services panel
    function updatePanel() {
      const checkboxes = document.querySelectorAll('.service-checkbox:checked');
      const selectedServices = [];

      checkboxes.forEach(checkbox => {
        const serviceId = parseInt(checkbox.value);
        const service = services.find(s => s.id === serviceId);
        if (service) {
          selectedServices.push(service);
        }
      });

      // Clear and rebuild the panel
      selectedPanel.innerHTML = '';

      if (selectedServices.length > 0) {
        selectedServices.forEach(service => {
          const badge = document.createElement('span');
          badge.className = 'badge bg-primary me-2 mb-2 d-inline-flex align-items-center';
          badge.innerHTML = `
            ${service.service_name}
            <button type="button" 
                    class="btn-close btn-close-white ms-2 remove-service" 
                    aria-label="Remove" 
                    data-id="${service.id}"
                    style="font-size: 0.75em;"></button>
          `;
          selectedPanel.appendChild(badge);
        });
      } else {
        selectedPanel.innerHTML = '<small class="text-muted">No services selected</small>';
      }
    }

    // Event listener for checkbox changes using event delegation
    document.addEventListener('change', function(e) {
      if (e.target.classList.contains('service-checkbox')) {
        updatePanel();
      }
    });

    // Event listener for removing services
    selectedPanel.addEventListener('click', function(e) {
      if (e.target.classList.contains('remove-service')) {
        const serviceId = e.target.getAttribute('data-id');
        const checkbox = document.querySelector(`.service-checkbox[value="${serviceId}"]`);
        if (checkbox) {
          checkbox.checked = false;
          updatePanel();
        }
      }
    });

    // Logo preview functionality
    const logoInput = document.getElementById('logo');
    const logoPreview = document.getElementById('logo-preview');
    const removeLogoBtn = document.getElementById('remove-logo-btn');

    if (logoInput) {
      logoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            logoPreview.src = e.target.result;
            logoPreview.style.display = 'block';
            removeLogoBtn.style.display = 'inline-block';
          };
          reader.readAsDataURL(file);
        }
      });
    }

    if (removeLogoBtn) {
      removeLogoBtn.addEventListener('click', function() {
        logoInput.value = '';
        logoPreview.src = '';
        logoPreview.style.display = 'none';
        removeLogoBtn.style.display = 'none';
      });
    }

    // Make updatePanel globally available
    window.updatePanel = updatePanel;

    // Initialize the panel
    updatePanel();
  });

  // initialize map picker
  var map = L.map('mapPicker').setView([10.3157,123.8854],10);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    maxZoom:19, attribution:'&copy; OSM'
  }).addTo(map);
  var marker = L.marker(map.getCenter(),{draggable:true}).addTo(map);

  function updateInputs() {
    var p = marker.getLatLng();
    document.getElementById('lat').value = p.lat.toFixed(6);
    document.getElementById('lng').value = p.lng.toFixed(6);
  }
  marker.on('dragend', updateInputs);
  map.on('click', function(e){
    marker.setLatLng(e.latlng);
    updateInputs();
  });
  // seed initial
  updateInputs();
</script>
@endsection

