@extends('layouts.app')

@section('content')
<div class="container py-4">
  @include('partials.alerts')
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold text-primary mb-1">
          <i class="bi bi-building medical-icon me-2"></i>Edit Clinic Profile
        </h2>
        <p class="text-muted mb-0">{{ $clinic->name }}</p>
      </div>
      <a href="{{ route('secretary.appointments.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i>Back
      </a>
    </div>
  </div>

  <form method="POST" action="{{ route('secretary.clinic.update', $clinic) }}" enctype="multipart/form-data" class="card shadow-sm">
    @csrf
    @method('PUT')
    <div class="card-body">
      <div class="row g-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Clinic Name</label>
          <input type="text" name="name" class="form-control" value="{{ old('name', $clinic->name) }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Address</label>
          <input type="text" id="clinic_address" name="address" class="form-control" value="{{ old('address', $clinic->address) }}" required>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold"><i class="bi bi-geo-alt me-1"></i>Location</label>
          <div class="small text-muted mb-2">Search for your clinic location, then pick a result to auto-set coordinates.</div>
          <label for="location_search" class="form-label small">Search Location</label>
          <input
            type="text"
            id="location_search"
            class="form-control"
            placeholder="Search barangay, street, city, or landmark"
            autocomplete="off"
          >
          <div id="locationSearchResults" class="list-group mt-2 d-none"></div>
          <div class="small text-muted mt-2">You can also click the map or drag the marker for precise pinning.</div>
          <div id="mapPicker" style="height: 300px;" class="border rounded mt-2"></div>

          <input type="hidden" id="lat" name="latitude" value="{{ old('latitude', $clinic->gps_latitude) }}">
          <input type="hidden" id="lng" name="longitude" value="{{ old('longitude', $clinic->gps_longitude) }}">
          @error('latitude')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          @error('longitude')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" rows="4" class="form-control" placeholder="Describe your clinic...">{{ old('description', $clinic->description) }}</textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Queue Strategy <span class="text-danger">*</span></label>
          <select name="queue_mode" class="form-select" required>
            <option value="fcfs" {{ old('queue_mode', $clinic->queue_mode ?? 'fcfs') === 'fcfs' ? 'selected' : '' }}>First-Come, First-Served (FCFS)</option>
            <option value="priority" {{ old('queue_mode', $clinic->queue_mode ?? 'fcfs') === 'priority' ? 'selected' : '' }}>Priority-Aware Queue</option>
          </select>
          <div class="small mt-2">
            <strong>1. First-Come, First-Served (FCFS):</strong> Patients are served strictly in order of arrival, regardless of any priority flags. This ignores priority patients.<br>
            <strong>2. Priority-Aware Queue (PAQ):</strong><br>
            The system adjusts the order dynamically, allowing patients with designated priority flags (senior, PWD, pregnant, emergency) to <em>override the queue</em> and be served earlier.
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Logo</label>
          <input type="file" name="logo" class="form-control" accept="image/*">
          @if($clinic->logo)
            <div class="mt-2">
              <img src="{{ asset('storage/'.$clinic->logo) }}" alt="Logo" style="height:60px;" class="rounded">
            </div>
          @endif
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Cover Image</label>
          <input type="file" name="cover_image" class="form-control" accept="image/*">
          @if($clinic->cover_image)
            <div class="mt-2">
              <img src="{{ asset('storage/'.$clinic->cover_image) }}" alt="Cover" style="height:60px;" class="rounded">
            </div>
          @endif
        </div>
      </div>
    </div>
    <div class="card-footer bg-light d-flex justify-content-between">
      <div></div>
      <button class="btn btn-primary">
        <i class="bi bi-save me-1"></i>Save Changes
      </button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof L === 'undefined') {
    return;
  }

  const locationSearchInput = document.getElementById('location_search');
  const locationResults = document.getElementById('locationSearchResults');
  const addressInput = document.getElementById('clinic_address');
  const latInput = document.getElementById('lat');
  const lngInput = document.getElementById('lng');

  const initialLat = parseFloat(latInput?.value || '{{ $clinic->gps_latitude ?: '10.3157' }}');
  const initialLng = parseFloat(lngInput?.value || '{{ $clinic->gps_longitude ?: '123.8854' }}');

  const map = L.map('mapPicker').setView([initialLat, initialLng], 15);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  const marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

  function updateInputs() {
    const point = marker.getLatLng();
    if (latInput) latInput.value = point.lat.toFixed(6);
    if (lngInput) lngInput.value = point.lng.toFixed(6);
  }

  function clearLocationResults() {
    if (!locationResults) return;
    locationResults.innerHTML = '';
    locationResults.classList.add('d-none');
  }

  function renderLocationResults(items) {
    if (!locationResults) return;
    if (!items.length) {
      clearLocationResults();
      return;
    }

    locationResults.innerHTML = '';
    items.forEach(item => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'list-group-item list-group-item-action';
      button.textContent = item.display_name;
      button.addEventListener('click', () => {
        const lat = Number(item.lat);
        const lng = Number(item.lon);
        if (Number.isNaN(lat) || Number.isNaN(lng)) {
          return;
        }
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 16);
        updateInputs();
        if (locationSearchInput) {
          locationSearchInput.value = item.display_name;
        }
        if (addressInput && !addressInput.value.trim()) {
          addressInput.value = item.display_name;
        }
        clearLocationResults();
      });
      locationResults.appendChild(button);
    });
    locationResults.classList.remove('d-none');
  }

  async function searchLocation(term) {
    if (!term || term.length < 3) {
      clearLocationResults();
      return;
    }

    try {
      const params = new URLSearchParams({
        q: term,
        format: 'jsonv2',
        limit: '6',
        addressdetails: '1',
        countrycodes: 'ph',
      });
      const response = await fetch(`https://nominatim.openstreetmap.org/search?${params.toString()}`);
      if (!response.ok) {
        clearLocationResults();
        return;
      }
      const data = await response.json();
      renderLocationResults(Array.isArray(data) ? data : []);
    } catch (error) {
      clearLocationResults();
    }
  }

  let locationSearchTimer;
  locationSearchInput?.addEventListener('input', () => {
    clearTimeout(locationSearchTimer);
    const term = locationSearchInput.value.trim();
    locationSearchTimer = setTimeout(() => searchLocation(term), 280);
  });

  locationSearchInput?.addEventListener('blur', () => {
    setTimeout(clearLocationResults, 180);
  });

  document.addEventListener('click', event => {
    if (!locationResults || !locationSearchInput) return;
    if (locationResults.contains(event.target) || locationSearchInput.contains(event.target)) return;
    clearLocationResults();
  });

  marker.on('dragend', updateInputs);
  map.on('click', event => {
    marker.setLatLng(event.latlng);
    updateInputs();
    clearLocationResults();
  });

  updateInputs();
  setTimeout(() => map.invalidateSize(), 250);
});
</script>
@endpush
