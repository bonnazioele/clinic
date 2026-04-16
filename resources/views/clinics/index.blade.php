@extends('layouts.app')

@section('title', 'Register a Clinic')

@section('content')
<div class="container py-4">

  @include('partials.alerts')

  <div class="row mb-4">
    <div class="col-12">
      <div class="medical-card p-4 text-center">
        <i class="bi bi-building-add medical-icon d-block mx-auto mb-2" style="font-size: 2.25rem;"></i>
        <h1 class="h3 mb-1 fw-bold text-primary">Register Your Clinic</h1>
        <p class="text-muted mb-0">Fill in the details below. Your clinic will be reviewed before going live.</p>
      </div>
    </div>
  </div>

  <form method="POST"
        action="{{ route('clinics.store') }}"
        enctype="multipart/form-data"
        id="clinicRegisterForm"
        novalidate>
    @csrf

    <div class="row g-4">

      <div class="col-lg-8">

        <div class="medical-card p-4 mb-4">
          <h5 class="mb-3">
            <i class="bi bi-info-circle medical-icon me-2"></i>Basic Information
          </h5>

          <div class="row g-3">
            <div class="col-md-8">
              <label for="name" class="form-label fw-semibold">
                Clinic Name <span class="text-danger">*</span>
              </label>
              <input type="text"
                     id="name"
                     name="name"
                     class="form-control @error('name') is-invalid @enderror"
                     value="{{ old('name') }}"
                     placeholder="e.g. San Nicolas Health Center"
                     required>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-4">
              <label for="branch_code" class="form-label fw-semibold">Branch Code</label>
              <input type="text"
                     id="branch_code"
                     name="branch_code"
                     class="form-control @error('branch_code') is-invalid @enderror"
                     value="{{ old('branch_code') }}"
                     placeholder="e.g. BR-001">
              @error('branch_code')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12">
              <label for="description" class="form-label fw-semibold">Description</label>
              <textarea id="description"
                        name="description"
                        class="form-control @error('description') is-invalid @enderror"
                        rows="3"
                        placeholder="Briefly describe your clinic's specialties and services…">{{ old('description') }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="medical-card p-4 mb-4">
          <h5 class="mb-3">
            <i class="bi bi-geo-alt medical-icon me-2"></i>Location
          </h5>

          <div class="mb-3">
            <label for="address" class="form-label fw-semibold">
              Street Address <span class="text-danger">*</span>
            </label>
            <input type="text"
                   id="address"
                   name="address"
                   class="form-control @error('address') is-invalid @enderror"
                   value="{{ old('address') }}"
                   placeholder="e.g. 123 Osmena Blvd, Cebu City"
                   required>
            @error('address')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3 location-search-wrapper">
            <label for="location_search" class="form-label fw-semibold">
              <i class="bi bi-search me-1"></i>Search Location on Map
            </label>
            <div class="input-group">
              <span class="input-group-text bg-light">
                <i class="bi bi-geo-alt text-muted"></i>
              </span>
              <input type="text"
                     id="location_search"
                     class="form-control"
                     placeholder="Type an address or landmark to pin on the map…"
                     autocomplete="off">
              <button type="button" class="btn btn-outline-primary" id="searchLocationBtn">
                <i class="bi bi-search me-1"></i>Find
              </button>
            </div>
            <small class="text-muted">
              <i class="bi bi-info-circle me-1"></i>
              Search for your clinic's location to pin it on the map. The coordinates will be saved automatically.
            </small>

            <div id="location_suggestions"
                 class="list-group shadow mt-1"
                 style="display:none; position:absolute; z-index:1050; left:0; right:0; max-height:220px; overflow-y:auto;">
            </div>
          </div>

          <div id="location_confirmed" class="alert alert-success py-2 d-none">
            <i class="bi bi-check-circle-fill me-2"></i>
            <span id="location_confirmed_text"></span>
            <button type="button" class="btn-close float-end btn-sm" id="clearLocationBtn" aria-label="Clear"></button>
          </div>

          @error('gps_latitude')
            <div class="alert alert-danger py-2 mt-1">{{ $message }}</div>
          @enderror
          @error('gps_longitude')
            <div class="alert alert-danger py-2 mt-1">{{ $message }}</div>
          @enderror

          <input type="hidden" name="gps_latitude" id="gps_latitude" value="{{ old('gps_latitude') }}">
          <input type="hidden" name="gps_longitude" id="gps_longitude" value="{{ old('gps_longitude') }}">

          <div id="location_map_preview"
               class="rounded-3 mt-3 shadow-sm {{ old('gps_latitude') ? '' : 'd-none' }}"
               style="height: 260px;">
          </div>
        </div>

        <div class="medical-card p-4 mb-4">
          <h5 class="mb-3">
            <i class="bi bi-telephone medical-icon me-2"></i>Contact Information
          </h5>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="contact_number" class="form-label fw-semibold">Phone Number</label>
              <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-telephone text-muted"></i></span>
                <input type="text"
                       id="contact_number"
                       name="contact_number"
                       class="form-control @error('contact_number') is-invalid @enderror"
                       value="{{ old('contact_number') }}"
                       placeholder="+63 912 345 6789">
              </div>
              @error('contact_number')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="email" class="form-label fw-semibold">Clinic Email</label>
              <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="clinic@example.com">
              </div>
              @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="contact_first_name" class="form-label fw-semibold">Contact Person — First Name</label>
              <input type="text"
                     id="contact_first_name"
                     name="contact_first_name"
                     class="form-control @error('contact_first_name') is-invalid @enderror"
                     value="{{ old('contact_first_name') }}"
                     placeholder="Juan">
              @error('contact_first_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="contact_last_name" class="form-label fw-semibold">Contact Person — Last Name</label>
              <input type="text"
                     id="contact_last_name"
                     name="contact_last_name"
                     class="form-control @error('contact_last_name') is-invalid @enderror"
                     value="{{ old('contact_last_name') }}"
                     placeholder="dela Cruz">
              @error('contact_last_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12">
              <label for="contact_person_email" class="form-label fw-semibold">Contact Person Email</label>
              <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-person-lines-fill text-muted"></i></span>
                <input type="email"
                       id="contact_person_email"
                       name="contact_person_email"
                       class="form-control @error('contact_person_email') is-invalid @enderror"
                       value="{{ old('contact_person_email') }}"
                       placeholder="contact.person@example.com">
              </div>
              @error('contact_person_email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

      </div>

      <div class="col-lg-4">

        <div class="medical-card p-4 mb-4">
          <h5 class="mb-3">
            <i class="bi bi-people medical-icon me-2"></i>Queue Mode
          </h5>
          <select name="queue_mode" id="queue_mode"
                  class="form-select @error('queue_mode') is-invalid @enderror">
            <option value="fcfs" @selected(old('queue_mode','fcfs') === 'fcfs')>First Come, First Served</option>
            <option value="appointment" @selected(old('queue_mode') === 'appointment')>By Appointment</option>
          </select>
          @error('queue_mode')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="medical-card p-4 mb-4">
          <h5 class="mb-3">
            <i class="bi bi-gear medical-icon me-2"></i>Medical Services
          </h5>
          <div class="d-flex flex-column gap-2" style="max-height: 260px; overflow-y: auto;">
            @foreach($services as $service)
              <div class="form-check">
                <input class="form-check-input"
                       type="checkbox"
                       name="services[]"
                       id="service_{{ $service->id }}"
                       value="{{ $service->id }}"
                       @checked(in_array($service->id, old('services', [])))>
                <label class="form-check-label" for="service_{{ $service->id }}">
                  {{ $service->name }}
                </label>
              </div>
            @endforeach
          </div>
          @error('services')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="medical-card p-4 mb-4">
          <h5 class="mb-3">
            <i class="bi bi-image medical-icon me-2"></i>Logo
          </h5>
          <input type="file"
                 name="logo"
                 id="logo"
                 class="form-control @error('logo') is-invalid @enderror"
                 accept="image/jpg,image/jpeg,image/png,image/webp">
          <small class="text-muted d-block mt-1">JPG / PNG / WebP, max 2 MB.</small>
          @error('logo')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div id="logo_preview" class="mt-2 d-none">
            <img id="logo_preview_img" src="" alt="Logo preview"
                 class="rounded" style="max-height:80px; object-fit:cover;">
          </div>
        </div>

        <div class="medical-card p-4 mb-4">
          <h5 class="mb-3">
            <i class="bi bi-card-image medical-icon me-2"></i>Cover Image
          </h5>
          <input type="file"
                 name="cover_image"
                 id="cover_image"
                 class="form-control @error('cover_image') is-invalid @enderror"
                 accept="image/jpg,image/jpeg,image/png,image/webp">
          <small class="text-muted d-block mt-1">JPG / PNG / WebP, max 4 MB.</small>
          @error('cover_image')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div id="cover_preview" class="mt-2 d-none">
            <img id="cover_preview_img" src="" alt="Cover preview"
                 class="rounded img-fluid" style="max-height:120px; width:100%; object-fit:cover;">
          </div>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
            <i class="bi bi-building-add me-2"></i>Register Clinic
          </button>
          <a href="{{ route('clinics.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Clinics
          </a>
        </div>

      </div>
    </div>
  </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<style>
  .location-search-wrapper { position: relative; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script>
(function () {
  'use strict';

  const addressInput  = document.getElementById('address');
  const searchInput   = document.getElementById('location_search');
  const searchBtn     = document.getElementById('searchLocationBtn');
  const suggestionsEl = document.getElementById('location_suggestions');
  const latInput      = document.getElementById('gps_latitude');
  const lngInput      = document.getElementById('gps_longitude');
  const confirmedBox  = document.getElementById('location_confirmed');
  const confirmedText = document.getElementById('location_confirmed_text');
  const clearBtn      = document.getElementById('clearLocationBtn');
  const mapPreviewEl  = document.getElementById('location_map_preview');

  let debounceTimer = null;
  let previewMap = null;
  let previewMarker = null;

  function clearCoords() {
    latInput.value = '';
    lngInput.value = '';
    confirmedBox.classList.add('d-none');

    if (previewMarker) {
      previewMarker.remove();
      previewMarker = null;
    }
  }

  function hideSuggestions() {
    suggestionsEl.style.display = 'none';
    suggestionsEl.innerHTML = '';
  }

  function setPreviewPin(lat, lng, label) {
    mapPreviewEl.classList.remove('d-none');

    if (!previewMap) {
      setTimeout(() => {
        previewMap = L.map(mapPreviewEl).setView([lat, lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; OpenStreetMap contributors'
        }).addTo(previewMap);

        previewMarker = L.marker([lat, lng], { draggable: true })
          .addTo(previewMap)
          .bindPopup(label)
          .openPopup();

        previewMarker.on('dragend', function (e) {
          const pos = e.target.getLatLng();
          latInput.value = pos.lat.toFixed(7);
          lngInput.value = pos.lng.toFixed(7);
          confirmedText.textContent = `Pinned at ${pos.lat.toFixed(5)}, ${pos.lng.toFixed(5)} (dragged)`;
          confirmedBox.classList.remove('d-none');
        });
      }, 50);
    } else {
      previewMap.setView([lat, lng], 16);

      if (!previewMarker) {
        previewMarker = L.marker([lat, lng], { draggable: true }).addTo(previewMap);

        previewMarker.on('dragend', function (e) {
          const pos = e.target.getLatLng();
          latInput.value = pos.lat.toFixed(7);
          lngInput.value = pos.lng.toFixed(7);
          confirmedText.textContent = `Pinned at ${pos.lat.toFixed(5)}, ${pos.lng.toFixed(5)} (dragged)`;
          confirmedBox.classList.remove('d-none');
        });
      } else {
        previewMarker.setLatLng([lat, lng]);
      }

      previewMarker.bindPopup(label).openPopup();
      previewMap.invalidateSize();
    }
  }

  function selectResult(item) {
    const lat = parseFloat(item.lat);
    const lng = parseFloat(item.lon);

    latInput.value = lat.toFixed(7);
    lngInput.value = lng.toFixed(7);
    searchInput.value = item.display_name;

    if (addressInput) {
      const a = item.address || {};
      const parts = [
        a.house_number,
        a.road || a.pedestrian || a.footway,
        a.suburb || a.neighbourhood,
        a.city || a.town || a.municipality || a.county,
        a.state,
        a.postcode,
      ].filter(Boolean);

      addressInput.value = parts.length ? parts.join(', ') : item.display_name;
    }

    confirmedText.textContent = `Location set: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
    confirmedBox.classList.remove('d-none');

    setPreviewPin(lat, lng, item.display_name);
    hideSuggestions();
  }

  function renderSuggestions(results) {
    suggestionsEl.innerHTML = '';

    if (!results.length) {
      suggestionsEl.innerHTML =
        '<div class="list-group-item text-muted py-2">' +
        '<i class="bi bi-exclamation-circle me-2"></i>No results found. Try a different search.' +
        '</div>';
      suggestionsEl.style.display = 'block';
      return;
    }

    results.forEach(item => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'list-group-item list-group-item-action py-2 px-3';
      btn.innerHTML =
        `<i class="bi bi-geo-alt text-primary me-2"></i>` +
        `<span class="small">${escapeHtml(item.display_name)}</span>`;
      btn.addEventListener('click', () => selectResult(item));
      suggestionsEl.appendChild(btn);
    });

    suggestionsEl.style.display = 'block';
  }

  function doSearch(query) {
    query = query.trim();
    if (!query) {
      hideSuggestions();
      return;
    }

    suggestionsEl.innerHTML =
      '<div class="list-group-item text-muted py-2">' +
      '<span class="spinner-border spinner-border-sm me-2"></span>Searching…</div>';
    suggestionsEl.style.display = 'block';

    fetch(
      `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=6&countrycodes=ph&q=${encodeURIComponent(query)}`,
      { headers: { 'Accept-Language': 'en' } }
    )
      .then(r => {
        if (!r.ok) throw new Error('Network error');
        return r.json();
      })
      .then(renderSuggestions)
      .catch(() => {
        suggestionsEl.innerHTML =
          '<div class="list-group-item text-danger py-2">' +
          '<i class="bi bi-wifi-off me-2"></i>Search failed. Please try again.</div>';
        suggestionsEl.style.display = 'block';
      });
  }

  searchInput?.addEventListener('input', () => {
    clearCoords();
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => doSearch(searchInput.value), 400);
  });

  searchBtn?.addEventListener('click', () => {
    doSearch(searchInput.value || addressInput.value || '');
  });

  searchInput?.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      e.preventDefault();
      doSearch(searchInput.value);
    }
  });

  addressInput?.addEventListener('blur', () => {
    const address = addressInput.value.trim();
    if (address && (!latInput.value || !lngInput.value)) {
      searchInput.value = address;
      doSearch(address);
    }
  });

  clearBtn?.addEventListener('click', () => {
    clearCoords();
    searchInput.value = '';
    hideSuggestions();
    mapPreviewEl.classList.add('d-none');
  });

  document.addEventListener('click', e => {
    if (suggestionsEl &&
        !suggestionsEl.contains(e.target) &&
        e.target !== searchInput &&
        e.target !== searchBtn) {
      hideSuggestions();
    }
  });

  window.addEventListener('DOMContentLoaded', () => {
    const oldLat = latInput.value;
    const oldLng = lngInput.value;

    if (oldLat && oldLng) {
      const lat = parseFloat(oldLat);
      const lng = parseFloat(oldLng);

      confirmedText.textContent = `Location set: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
      confirmedBox.classList.remove('d-none');
      setPreviewPin(lat, lng, 'Selected location');
    }
  });

  function wirePreview(inputId, previewDivId, previewImgId) {
    const input = document.getElementById(inputId);
    const div = document.getElementById(previewDivId);
    const img = document.getElementById(previewImgId);

    if (!input) return;

    input.addEventListener('change', () => {
      const file = input.files[0];
      if (file) {
        img.src = URL.createObjectURL(file);
        div.classList.remove('d-none');
      } else {
        div.classList.add('d-none');
      }
    });
  }

  wirePreview('logo', 'logo_preview', 'logo_preview_img');
  wirePreview('cover_image', 'cover_preview', 'cover_preview_img');

  document.getElementById('clinicRegisterForm')?.addEventListener('submit', function (e) {
    if (!latInput.value || !lngInput.value) {
      const proceed = confirm(
        'You haven\\'t pinned a location on the map.\n' +
        'Your clinic won\\'t appear on the map without coordinates.\n\n' +
        'Click OK to submit anyway, or Cancel to go back and add a location.'
      );

      if (!proceed) e.preventDefault();
    }
  });

  function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
})();
</script>
@endpush