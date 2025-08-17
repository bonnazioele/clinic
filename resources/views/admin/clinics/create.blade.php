@extends('admin.layouts.app')

@section('title','Add Clinic')

@push('styles')
  <style>
    .map-picker { height: 400px; width: 100%; border: 2px solid #dee2e6; border-radius: .5rem; margin-bottom: 1rem; }
    .map-picker .leaflet-container { height: 100%; width: 100%; }
  </style>
@endpush

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="card medical-card shadow-sm">
    <div class="card-header bg-primary text-white d-flex align-items-center">
      <i class="bi bi-building-add me-2"></i>
      <h5 class="mb-0">Add Clinic</h5>
    </div>
    <div class="card-body">
  <form method="POST" action="{{ route('admin.clinics.store') }}" enctype="multipart/form-data">
    @csrf

    {{-- Name --}}
    <div class="mb-3">
      <label class="form-label"><i class="bi bi-building me-1"></i>Clinic Name <span class="text-danger">*</span></label>
      <input type="text" name="name"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name') }}" required maxlength="255"
             placeholder="Enter clinic name">
      @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Branch Code --}}
    <div class="mb-3">
      <label class="form-label"><i class="bi bi-tag me-1"></i>Branch Code <span class="text-danger">*</span></label>
      <input type="text" name="branch_code"
             class="form-control @error('branch_code') is-invalid @enderror"
             value="{{ old('branch_code') }}" required maxlength="50"
             placeholder="e.g., CLINIC001, BRANCH-A1">
      @error('branch_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
      <div class="form-text">Unique identifier for this clinic branch.</div>
    </div>

    {{-- Address --}}
    <div class="mb-3">
      <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Address <span class="text-danger">*</span></label>
      <textarea name="address"
                class="form-control @error('address') is-invalid @enderror"
                rows="3" required maxlength="1000"
                placeholder="Enter complete address">{{ old('address') }}</textarea>
      @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Contact / Email --}}
    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-telephone me-1"></i>Contact Number <span class="text-danger">*</span></label>
          <input type="tel" name="contact_number"
                 class="form-control @error('contact_number') is-invalid @enderror"
                 value="{{ old('contact_number') }}" required maxlength="50"
                 placeholder="e.g., +63 917 123 4567">
          @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-envelope me-1"></i>Email Address <span class="text-danger">*</span></label>
          <input type="email" name="email"
                 class="form-control @error('email') is-invalid @enderror"
                 value="{{ old('email') }}" required maxlength="100"
                 placeholder="clinic@example.com">
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>

    {{-- Services Offered --}}
    <div class="mb-3">
      <label class="form-label"><i class="bi bi-gear me-1"></i>Services Offered</label>
      <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start"
                type="button" id="servicesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          Select Services
        </button>
        <ul class="dropdown-menu w-100" aria-labelledby="servicesDropdown" style="max-height: 200px; overflow-y: auto;">
          @foreach($services as $service)
            <li>
              <label class="dropdown-item">
                <input type="checkbox" class="form-check-input me-2 service-checkbox"
                       name="service_ids[]" value="{{ $service->id }}"
                       {{ in_array($service->id, old('service_ids', [])) ? 'checked' : '' }}>
                {{ $service->name }}
              </label>
            </li>
          @endforeach
        </ul>
      </div>
      @error('service_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

      <div id="selected-services" class="mt-2"></div>
    </div>

    {{-- Logo Upload --}}
    <div class="mb-3">
      <label class="form-label"><i class="bi bi-image me-1"></i>Clinic Logo <small class="text-muted">(optional)</small></label>
      <input type="file" name="logo" id="logoInput"
             class="form-control @error('logo') is-invalid @enderror"
             accept="image/jpeg,image/png,image/jpg,image/gif">
      @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
      <div class="form-text">Accepted formats: JPEG, PNG, JPG, GIF. Maximum size: 2MB.</div>

      {{-- Logo Preview --}}
      <div id="logoPreview" class="mt-2" style="display:none;">
        <img id="previewImage" src="" alt="Logo Preview" class="img-thumbnail" style="max-width:150px; max-height:150px;">
        <div class="mt-1">
          <button type="button" id="removeLogoBtn" class="btn btn-sm btn-outline-danger">Remove</button>
        </div>
      </div>
    </div>

    {{-- Map --}}
    <div class="mb-3">
      <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Location <span class="text-danger">*</span></label>
      <div class="form-text mb-2">Click on the map or drag the marker to set the clinic location.</div>
      <div id="mapPicker" class="map-picker"></div>
    </div>

    {{-- Lat/Lng --}}
    <div class="row">
      <div class="col">
  <label class="form-label"><i class="bi bi-compass me-1"></i>Latitude <span class="text-danger">*</span></label>
        <input type="text" id="lat" name="latitude"
               class="form-control @error('latitude') is-invalid @enderror"
               value="{{ old('latitude') }}" readonly required>
        @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col">
  <label class="form-label"><i class="bi bi-compass me-1"></i>Longitude <span class="text-danger">*</span></label>
        <input type="text" id="lng" name="longitude"
               class="form-control @error('longitude') is-invalid @enderror"
               value="{{ old('longitude') }}" readonly required>
        @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-save me-2"></i>Save Clinic
      </button>
      <a href="{{ route('admin.clinics.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-circle me-2"></i>Cancel
      </a>
    </div>
  </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script>
  document.addEventListener('DOMContentLoaded', () => {
      // ---------- Services panel ----------
      const services = @json($services);
      const selectedPanel = document.getElementById('selected-services');

      function updatePanel() {
        const checkboxes = document.querySelectorAll('.service-checkbox:checked');
        const selected = [];
        checkboxes.forEach(cb => {
          const id = parseInt(cb.value);
          const found = services.find(s => s.id === id);
          if (found) selected.push(found);
        });

        selectedPanel.innerHTML = '';
        if (!selected.length) {
          selectedPanel.innerHTML = '<small class="text-muted">No services selected</small>';
          return;
        }

        selected.forEach(service => {
          const badge = document.createElement('span');
          badge.className = 'badge bg-primary me-2 mb-2 d-inline-flex align-items-center';
          badge.innerHTML = `
            ${service.name}
            <button type="button" class="btn-close btn-close-white ms-2 remove-service"
                    aria-label="Remove" data-id="${service.id}" style="font-size:.75em;"></button>
          `;
          selectedPanel.appendChild(badge);
        });
      }

      document.addEventListener('change', (e) => {
        if (e.target.classList.contains('service-checkbox')) updatePanel();
      });

      selectedPanel.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-service')) {
          const id = e.target.getAttribute('data-id');
          const cb = document.querySelector(`.service-checkbox[value="${id}"]`);
          if (cb) { cb.checked = false; updatePanel(); }
        }
      });

      // ---------- Logo preview ----------
      const logoInput = document.getElementById('logoInput');
      const logoWrap  = document.getElementById('logoPreview');
      const imgEl     = document.getElementById('previewImage');
      const removeBtn = document.getElementById('removeLogoBtn');

      function resetLogo() {
        logoInput.value = '';
        imgEl.src = '';
        logoWrap.style.display = 'none';
      }

      logoInput.addEventListener('change', (e) => {
        const file = e.target.files?.[0];
        if (!file) return resetLogo();

        const reader = new FileReader();
        reader.onload = (ev) => {
          imgEl.src = ev.target.result;
          logoWrap.style.display = 'block';
        };
        reader.readAsDataURL(file);
      });

      removeBtn.addEventListener('click', resetLogo);

      // ---------- Map ----------
      tryInitMap();
      updatePanel();
    });

  function tryInitMap(attempt = 0) {
      const el = document.getElementById('mapPicker');
      if (!el || typeof L === 'undefined') {
        if (attempt < 20) return setTimeout(() => tryInitMap(attempt + 1), 150);
        return;
      }
      initializeMap();
    }

    function initializeMap() {
      const mapContainer = document.getElementById('mapPicker');
      if (!mapContainer) return;

      const defaultCenter = [10.3157, 123.8854];
      const map = L.map('mapPicker').setView(defaultCenter, 10);

      // Primary OSM tile layer with fallback if tiles fail to load
      let osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);
      osmLayer.on('tileerror', function () {
        // Fallback to single-host tile server
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
      });

      const marker = L.marker(map.getCenter(), { draggable: true }).addTo(map);

      function updateInputs() {
        const p = marker.getLatLng();
        document.getElementById('lat').value = p.lat.toFixed(6);
        document.getElementById('lng').value = p.lng.toFixed(6);
      }

      marker.on('dragend', updateInputs);
      map.on('click', (e) => { marker.setLatLng(e.latlng); updateInputs(); });

      // Seed initial (or old) values if present
      const oldLat = parseFloat('{{ old('latitude') }}');
      const oldLng = parseFloat('{{ old('longitude') }}');
      if (!isNaN(oldLat) && !isNaN(oldLng)) {
        const old = L.latLng(oldLat, oldLng);
        marker.setLatLng(old);
        map.setView(old, 15);
      }
      updateInputs();

      // Ensure proper rendering
      setTimeout(() => map.invalidateSize(), 300);
    }
  </script>
@endpush
