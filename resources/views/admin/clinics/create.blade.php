@extends('admin.layouts.app')

@section('title','Add Clinic')

@section('content')
  <h3 class="mb-4">Add Clinic</h3>

  <form method="POST" action="{{ route('admin.clinics.store') }}">
    @csrf

    <!-- Name -->
    <div class="mb-3">
      <label class="form-label">Clinic Name</label>
      <input type="text"
             name="name"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name') }}"
             required>
      @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Address -->
    <div class="mb-3">
      <label class="form-label">Address</label>
      <textarea name="address"
                class="form-control @error('address') is-invalid @enderror"
                rows="3"
                required>{{ old('address') }}</textarea>
      @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>


    <!-- Map Picker -->
    <div id="mapPicker" class="map-picker mb-3"></div>

    <!-- Latitude & Longitude -->
    <div class="row">
      <div class="col">
        <label class="form-label">Latitude</label>
        <input type="text"
               id="lat"
               name="latitude"
               class="form-control @error('latitude') is-invalid @enderror"
               value="{{ old('latitude') }}"
               required>
        @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col">
        <label class="form-label">Longitude</label>
        <input type="text"
               id="lng"
               name="longitude"
               class="form-control @error('longitude') is-invalid @enderror"
               value="{{ old('longitude') }}"
               required>
        @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    <!-- Services Offered -->
    <div class="mb-3">
      <label class="form-label">Services Offered</label>
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

    <button class="btn btn-primary mt-3">Save Clinic</button>
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

