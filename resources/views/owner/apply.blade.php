@extends('layouts.app')

@section('content')
@php($permitTypes = $permitTypes ?? config('clinic_permits.types', []))

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      <div class="card medical-card shadow-sm border-0 float-once">
        <div class="card-header bg-primary text-white d-flex align-items-center">
          <i class="bi bi-building-add me-2"></i>
          <h5 class="mb-0">Apply to Register Your Clinic</h5>
        </div>

        <div class="card-body">
          <form method="POST" action="{{ route('owner.apply.store') }}" enctype="multipart/form-data" id="clinicApplyForm">
            @csrf

            <h6 class="text-muted mb-3">Contact Person Details</h6>

            <div class="alert alert-info">
              <i class="bi bi-info-circle me-1"></i>
              <small>
                The contact person must be the official clinic admin or an authorized representative of the clinic.
              </small>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="contact_first_name" class="form-label">
                  <i class="bi bi-person me-1"></i>First Name <span class="text-danger">*</span>
                </label>
                <input id="contact_first_name" type="text" name="contact_first_name"
                       class="form-control @error('contact_first_name') is-invalid @enderror"
                       value="{{ old('contact_first_name') }}" maxlength="255" required placeholder="e.g., Juan">
                @error('contact_first_name')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6 mb-3">
                <label for="contact_last_name" class="form-label">
                  <i class="bi bi-person me-1"></i>Last Name <span class="text-danger">*</span>
                </label>
                <input id="contact_last_name" type="text" name="contact_last_name"
                       class="form-control @error('contact_last_name') is-invalid @enderror"
                       value="{{ old('contact_last_name') }}" maxlength="255" required placeholder="e.g., Dela Cruz">
                @error('contact_last_name')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mb-3">
              <label for="contact_person_email" class="form-label">
                <i class="bi bi-envelope me-1"></i>Contact Person Email <span class="text-danger">*</span>
              </label>
              <input id="contact_person_email" type="email" name="contact_person_email"
                     class="form-control @error('contact_person_email') is-invalid @enderror"
                     value="{{ old('contact_person_email') }}" maxlength="255" required
                     placeholder="contactperson@example.com">
              @error('contact_person_email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
              <div class="form-text">
                This email will be used to send account credentials and communication regarding the application.
              </div>
            </div>

            <h6 class="text-muted mb-3 mt-4">Clinic Details</h6>

            <div class="mb-3">
              <label for="clinic_name" class="form-label">
                <i class="bi bi-building me-1"></i>Clinic Name <span class="text-danger">*</span>
              </label>
              <input id="clinic_name" type="text" name="clinic_name"
                     class="form-control @error('clinic_name') is-invalid @enderror"
                     value="{{ old('clinic_name') }}" required maxlength="255"
                     placeholder="Enter clinic name">
              @error('clinic_name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="branch_code" class="form-label">
                <i class="bi bi-tag me-1"></i>Branch Code <span class="text-danger">*</span>
              </label>
              <input id="branch_code" type="text" name="branch_code"
                     class="form-control @error('branch_code') is-invalid @enderror"
                     value="{{ old('branch_code') }}" maxlength="50" required
                     placeholder="e.g., CLINIC001, BRANCH-A1">
              @error('branch_code')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
              <div class="form-text">Provide a unique code for this clinic branch.</div>
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
                   style="display:none; position:absolute; z-index:1050; left:0; right:0; max-height:220px; overflow-y:auto;"></div>
            </div>

            <div class="mb-3">
              <label class="form-label">
                <i class="bi bi-geo-alt me-1"></i>Location
              </label>
              <div class="form-text mb-2">
                Search above, click on the map, or drag the marker to set the clinic location.
              </div>
              <div id="mapPicker" style="height: 300px;" class="border rounded"></div>
            </div>

            <div id="location_confirmed" class="alert alert-success py-2 d-none">
              <i class="bi bi-check-circle-fill me-2"></i>
              <span id="location_confirmed_text"></span>
              <button type="button" class="btn-close float-end btn-sm" id="clearLocationBtn" aria-label="Clear"></button>
            </div>

            <div class="row g-3">
              <div class="col-md-6">

                <input type="hidden"
                       id="gps_latitude"
                       name="gps_latitude"
                       class="form-control @error('gps_latitude') is-invalid @enderror"
                       value="{{ old('gps_latitude') }}"
                       readonly>
                @error('gps_latitude')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <input type="hidden"
                       id="gps_longitude"
                       name="gps_longitude"
                       class="form-control @error('gps_longitude') is-invalid @enderror"
                       value="{{ old('gps_longitude') }}"
                       readonly>
                @error('gps_longitude')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mb-3">
              <label for="clinic_address" class="form-label">
                <i class="bi bi-geo-alt me-1"></i>Address <span class="text-danger">*</span>
              </label>
              <textarea id="clinic_address" name="clinic_address"
                        class="form-control @error('clinic_address') is-invalid @enderror"
                        rows="3" required maxlength="1000"
                        placeholder="Enter complete address">{{ old('clinic_address') }}</textarea>
              @error('clinic_address')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3 mt-3">
                  <label for="clinic_contact" class="form-label">
                    <i class="bi bi-telephone me-1"></i>Contact Number <span class="text-danger">*</span>
                  </label>
                  <input id="clinic_contact" type="tel" name="clinic_contact"
                         class="form-control @error('clinic_contact') is-invalid @enderror"
                         value="{{ old('clinic_contact') }}" required maxlength="50"
                         placeholder="e.g., +63 917 123 4567">
                  @error('clinic_contact')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="mb-3 mt-3">
                  <label for="clinic_email" class="form-label">
                    <i class="bi bi-envelope me-1"></i>Clinic Email <span class="text-danger">*</span>
                  </label>
                  <input id="clinic_email" type="email" name="clinic_email"
                         class="form-control @error('clinic_email') is-invalid @enderror"
                         value="{{ old('clinic_email') }}" maxlength="100" required
                         placeholder="clinic@example.com">
                  @error('clinic_email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            @if(!empty($permitTypes))
              <h6 class="text-muted mb-3 mt-4">Regulatory Permits</h6>

              <div class="row g-3">
                @foreach($permitTypes as $permit)
                  @php($key = $permit['key'])
                  <div class="col-md-4">
                    <div class="border rounded p-3 h-100 shadow-sm">
                      <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-file-earmark-lock text-primary me-2"></i>
                        <div>
                          <strong>{{ $permit['label'] }}</strong>
                          <div class="small text-muted">{{ $permit['description'] ?? '' }}</div>
                        </div>
                      </div>

                      <div class="mb-2">
                        <label class="form-label small fw-semibold">
                          Permit Number{{ ($permit['requires_number'] ?? false) ? ' *' : '' }}
                        </label>
                        <input type="text"
                               name="permits[{{ $key }}][permit_number]"
                               class="form-control form-control-sm @error('permits.' . $key . '.permit_number') is-invalid @enderror"
                               value="{{ old('permits.' . $key . '.permit_number') }}"
                               placeholder="Document reference">
                        @error('permits.' . $key . '.permit_number')
                          <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="row g-2 mb-2">
                        <div class="col-6">
                          <label class="form-label small fw-semibold">
                            Issued{{ ($permit['requires_issue_date'] ?? false) ? ' *' : '' }}
                          </label>
                          <input type="date"
                                 name="permits[{{ $key }}][issued_at]"
                                 class="form-control form-control-sm @error('permits.' . $key . '.issued_at') is-invalid @enderror"
                                 value="{{ old('permits.' . $key . '.issued_at') }}">
                          @error('permits.' . $key . '.issued_at')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                          @enderror
                        </div>

                        <div class="col-6">
                          <label class="form-label small fw-semibold">
                            Expires{{ ($permit['requires_expiry_date'] ?? false) ? ' *' : '' }}
                          </label>
                          <input type="date"
                                 name="permits[{{ $key }}][expires_at]"
                                 class="form-control form-control-sm @error('permits.' . $key . '.expires_at') is-invalid @enderror"
                                 value="{{ old('permits.' . $key . '.expires_at') }}">
                          @error('permits.' . $key . '.expires_at')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                          @enderror
                        </div>
                      </div>

                      <div class="mb-2">
                        <label class="form-label small fw-semibold">Attachment *</label>
                        <input type="file"
                               name="permits[{{ $key }}][file]"
                               class="form-control form-control-sm @error('permits.' . $key . '.file') is-invalid @enderror"
                               accept="application/pdf,image/*">
                        @error('permits.' . $key . '.file')
                          <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="form-text small">PDF or image, max 5MB.</div>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif

            <div class="mb-3 mt-4">
              <label class="form-label">
                <i class="bi bi-gear me-1"></i>Services Offered
              </label>

              <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start"
                        type="button"
                        id="servicesDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                  Select Services
                </button>

                <ul class="dropdown-menu w-100 p-2" aria-labelledby="servicesDropdown" style="max-height: 250px; overflow-y: auto;">
                  <li class="mb-2">
                    <input type="text" id="serviceSearch" class="form-control form-control-sm" placeholder="Search services...">
                  </li>
                  <li><hr class="dropdown-divider"></li>

                  <div id="servicesList">
                    @foreach(($services ?? []) as $service)
                      <li>
                        <label class="dropdown-item">
                          <input type="checkbox"
                                 class="form-check-input me-2 service-checkbox"
                                 name="service_ids[]"
                                 value="{{ $service->id }}"
                                 {{ in_array($service->id, old('service_ids', [])) ? 'checked' : '' }}>
                          {{ $service->name }}
                        </label>
                      </li>
                    @endforeach
                  </div>
                </ul>
              </div>

              @error('service_ids')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror

              <div id="selected-services" class="mt-2"></div>
            </div>

            <div class="mb-3">
              <label class="form-label">
                <i class="bi bi-image me-1"></i>Clinic Logo <small class="text-muted">(optional)</small>
              </label>
              <input type="file"
                     name="logo"
                     id="logoInput"
                     class="form-control @error('logo') is-invalid @enderror"
                     accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
              @error('logo')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
              <div class="form-text">Accepted formats: JPEG, PNG, JPG, GIF, WEBP. Maximum size: 2MB.</div>

              <div id="logoPreview" class="mt-2" style="display:none;">
                <img id="previewImage" src="" alt="Logo Preview" class="img-thumbnail" style="max-width:150px; max-height:150px;">
                <div class="mt-1">
                  <button type="button" id="removeLogoBtn" class="btn btn-sm btn-outline-danger">Remove</button>
                </div>
              </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
              <a href="{{ route('welcome') }}" class="btn btn-light">Cancel</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-send me-1"></i>Submit Application
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

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
document.addEventListener('DOMContentLoaded', function () {
  const services = @json($services ?? []);
  const selectedPanel = document.getElementById('selected-services');

  function updatePanel() {
    const cbs = document.querySelectorAll('.service-checkbox:checked');
    const selected = [];

    cbs.forEach(cb => {
      const id = parseInt(cb.value, 10);
      const found = services.find(s => s.id === id);
      if (found) selected.push(found);
    });

    selectedPanel.innerHTML = '';

    if (!selected.length) {
      selectedPanel.innerHTML = '<small class="text-muted">No services selected</small>';
      return;
    }

    selected.forEach(s => {
      const badge = document.createElement('span');
      badge.className = 'badge bg-primary me-2 mb-2 d-inline-flex align-items-center';
      badge.innerHTML = `${escapeHtml(s.name)} <button type="button" class="btn-close btn-close-white ms-2 remove-service" data-id="${s.id}" style="font-size:.75em;"></button>`;
      selectedPanel.appendChild(badge);
    });
  }

  document.addEventListener('change', e => {
    if (e.target.classList.contains('service-checkbox')) updatePanel();
  });

  selectedPanel?.addEventListener('click', e => {
    if (e.target.classList.contains('remove-service')) {
      const id = e.target.getAttribute('data-id');
      const cb = document.querySelector(`.service-checkbox[value="${id}"]`);
      if (cb) {
        cb.checked = false;
        updatePanel();
      }
    }
  });

  updatePanel();

  const searchInputService = document.getElementById('serviceSearch');
  const servicesList = document.getElementById('servicesList');
  let fetchTimeout;

  async function remoteSearch(term) {
    try {
      const params = new URLSearchParams();
      if (term) params.append('q', term);

      const res = await fetch(`/services/search?${params.toString()}`);
      if (!res.ok) return;

      const json = await res.json();
      const selectedIds = Array.from(document.querySelectorAll('.service-checkbox:checked')).map(cb => parseInt(cb.value, 10));

      servicesList.innerHTML = '';

      json.data.forEach(s => {
        const li = document.createElement('li');
        const label = document.createElement('label');
        label.className = 'dropdown-item';

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.name = 'service_ids[]';
        input.value = s.id;
        input.className = 'form-check-input me-2 service-checkbox';
        if (selectedIds.includes(s.id)) input.checked = true;

        label.appendChild(input);
        label.appendChild(document.createTextNode(' ' + s.name));
        li.appendChild(label);
        servicesList.appendChild(li);
      });

      updatePanel();
    } catch (e) {}
  }

  if (searchInputService && servicesList) {
    searchInputService.addEventListener('input', function () {
      clearTimeout(fetchTimeout);
      const term = this.value.trim();
      fetchTimeout = setTimeout(() => remoteSearch(term), 250);
    });
  }

  const logoInput = document.getElementById('logoInput');
  const logoWrap  = document.getElementById('logoPreview');
  const imgEl     = document.getElementById('previewImage');
  const removeBtn = document.getElementById('removeLogoBtn');

  function resetLogo() {
    if (!logoInput) return;
    logoInput.value = '';
    imgEl.src = '';
    logoWrap.style.display = 'none';
  }

  logoInput?.addEventListener('change', (e) => {
    const f = e.target.files?.[0];
    if (!f) return resetLogo();

    const r = new FileReader();
    r.onload = (ev) => {
      imgEl.src = ev.target.result;
      logoWrap.style.display = 'block';
    };
    r.readAsDataURL(f);
  });

  removeBtn?.addEventListener('click', resetLogo);

  initLocationPicker();
});

function initLocationPicker() {
  const mapEl = document.getElementById('mapPicker');
  if (!mapEl || typeof L === 'undefined') return;

  const addressInput = document.getElementById('clinic_address');
  const searchInput = document.getElementById('location_search');
  const searchBtn = document.getElementById('searchLocationBtn');
  const suggestionsEl = document.getElementById('location_suggestions');
  const latInput = document.getElementById('gps_latitude');
  const lngInput = document.getElementById('gps_longitude');
  const confirmedBox = document.getElementById('location_confirmed');
  const confirmedText = document.getElementById('location_confirmed_text');
  const clearBtn = document.getElementById('clearLocationBtn');

  let debounceTimer = null;

  const defaultLat = 10.3157;
  const defaultLng = 123.8854;

  const oldLat = parseFloat(latInput?.value || '{{ old('gps_latitude') }}');
  const oldLng = parseFloat(lngInput?.value || '{{ old('gps_longitude') }}');

  const startLat = !isNaN(oldLat) ? oldLat : defaultLat;
  const startLng = !isNaN(oldLng) ? oldLng : defaultLng;

  const map = L.map('mapPicker').setView([startLat, startLng], !isNaN(oldLat) && !isNaN(oldLng) ? 15 : 10);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);

  function updateInputs(lat, lng, message = null) {
    latInput.value = Number(lat).toFixed(7);
    lngInput.value = Number(lng).toFixed(7);
    confirmedText.textContent = message || `Location is set`;
    confirmedBox.classList.remove('d-none');
  }

  function hideSuggestions() {
    suggestionsEl.style.display = 'none';
    suggestionsEl.innerHTML = '';
  }

  function selectResult(item) {
    const lat = parseFloat(item.lat);
    const lng = parseFloat(item.lon);

    marker.setLatLng([lat, lng]);
    map.setView([lat, lng], 16);

    updateInputs(lat, lng);
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

    marker.bindPopup(item.display_name).openPopup();
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

  marker.on('dragend', function () {
    const pos = marker.getLatLng();
    updateInputs(pos.lat, pos.lng, `Pinned at ${pos.lat.toFixed(5)}, ${pos.lng.toFixed(5)} (dragged)`);
  });

  map.on('click', function (e) {
    marker.setLatLng(e.latlng);
    updateInputs(e.latlng.lat, e.latlng.lng, `Pinned at ${e.latlng.lat.toFixed(5)}, ${e.latlng.lng.toFixed(5)} (clicked)`);
  });

  if (!isNaN(oldLat) && !isNaN(oldLng)) {
    updateInputs(oldLat, oldLng);
  }

  searchInput?.addEventListener('input', () => {
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
    latInput.value = '';
    lngInput.value = '';
    confirmedBox.classList.add('d-none');
    searchInput.value = '';
    hideSuggestions();
    marker.setLatLng([defaultLat, defaultLng]);
    map.setView([defaultLat, defaultLng], 10);
  });

  document.addEventListener('click', e => {
    if (!suggestionsEl.contains(e.target) && e.target !== searchInput && e.target !== searchBtn) {
      hideSuggestions();
    }
  });

  setTimeout(() => map.invalidateSize(), 300);
}

function escapeHtml(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
</script>
@endpush
@endsection