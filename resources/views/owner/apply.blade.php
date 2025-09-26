@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex align-items-center">
          <i class="bi bi-building-add me-2"></i>
          <h5 class="mb-0">Apply to Register Your Clinic</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('owner.apply.store') }}" enctype="multipart/form-data">
            @csrf

            <h6 class="text-muted mb-3">Clinic Details</h6>
            <div class="mb-3">
              <label class="form-label"><i class="bi bi-building me-1"></i>Clinic Name <span class="text-danger">*</span></label>
              <input type="text" name="clinic_name" class="form-control @error('clinic_name') is-invalid @enderror" value="{{ old('clinic_name') }}" required maxlength="255" placeholder="Enter clinic name">
              @error('clinic_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
              <label class="form-label"><i class="bi bi-tag me-1"></i>Branch Code <span class="text-danger">*</span></label>
              <input type="text" name="branch_code" class="form-control @error('branch_code') is-invalid @enderror" value="{{ old('branch_code') }}" maxlength="50" required placeholder="e.g., CLINIC001, BRANCH-A1">
              @error('branch_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
              <div class="form-text">Provide a unique code for this clinic branch.</div>
            </div>

            <div class="mb-3">
              <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Address <span class="text-danger">*</span></label>
              <textarea name="clinic_address" class="form-control @error('clinic_address') is-invalid @enderror" rows="3" required maxlength="1000" placeholder="Enter complete address">{{ old('clinic_address') }}</textarea>
              @error('clinic_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label"><i class="bi bi-telephone me-1"></i>Contact Number <span class="text-danger">*</span></label>
                  <input type="tel" name="clinic_contact" class="form-control @error('clinic_contact') is-invalid @enderror" value="{{ old('clinic_contact') }}" required maxlength="50" placeholder="e.g., +63 917 123 4567">
                  @error('clinic_contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label"><i class="bi bi-envelope me-1"></i>Clinic Email <span class="text-danger">*</span></label>
                  <input type="email" name="clinic_email" class="form-control @error('clinic_email') is-invalid @enderror" value="{{ old('clinic_email') }}" maxlength="100" required placeholder="clinic@example.com">
                  @error('clinic_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
            </div>

            {{-- Services Offered --}}
            <div class="mb-3">
              <label class="form-label"><i class="bi bi-gear me-1"></i>Services Offered</label>
              <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" id="servicesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  Select Services
                </button>
                <ul class="dropdown-menu w-100 p-2" aria-labelledby="servicesDropdown" style="max-height: 250px; overflow-y: auto;">
                  {{-- Search box --}}
                  <li class="mb-2">
                    <input type="text" id="serviceSearch" class="form-control form-control-sm" placeholder="Search services...">
                  </li>
                  <li><hr class="dropdown-divider"></li>

                  {{-- Service list --}}
                  <div id="servicesList">
                    @foreach(($services ?? []) as $service)
                      <li>
                        <label class="dropdown-item">
                          <input type="checkbox" class="form-check-input me-2 service-checkbox" name="service_ids[]" value="{{ $service->id }}" {{ in_array($service->id, old('service_ids', [])) ? 'checked' : '' }}>
                          {{ $service->name }}
                        </label>
                      </li>
                    @endforeach
                  </div>
                </ul>
              </div>
              @error('service_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              <div id="selected-services" class="mt-2"></div>
            </div>

            {{-- Logo Upload --}}
            <div class="mb-3">
              <label class="form-label"><i class="bi bi-image me-1"></i>Clinic Logo <small class="text-muted">(optional)</small></label>
              <input type="file" name="logo" id="logoInput" class="form-control @error('logo') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/gif">
              @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <div class="form-text">Accepted formats: JPEG, PNG, JPG, GIF. Maximum size: 2MB.</div>
              <div id="logoPreview" class="mt-2" style="display:none;">
                <img id="previewImage" src="" alt="Logo Preview" class="img-thumbnail" style="max-width:150px; max-height:150px;">
                <div class="mt-1">
                  <button type="button" id="removeLogoBtn" class="btn btn-sm btn-outline-danger">Remove</button>
                </div>
              </div>
            </div>

            {{-- Map Picker --}}
            <div class="mb-3">
              <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Location</label>
              <div class="form-text mb-2">Click on the map or drag the marker to set the clinic location.</div>
              <div id="mapPicker" style="height: 300px;" class="border rounded"></div>
            </div>

            {{-- Lat/Lng --}}
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label"><i class="bi bi-compass me-1"></i>Latitude</label>
                <input type="text" id="lat" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude') }}" readonly>
                @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="bi bi-compass me-1"></i>Longitude</label>
                <input type="text" id="lng" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude') }}" readonly>
                @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>

            <hr class="my-4">
            <h6 class="text-muted mb-3">Clinic Account</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Services selection panel
  const services = @json($services ?? []);
  const selectedPanel = document.getElementById('selected-services');
  function updatePanel() {
    const cbs = document.querySelectorAll('.service-checkbox:checked');
    const selected = [];
    cbs.forEach(cb => {
      const id = parseInt(cb.value);
      const found = services.find(s => s.id === id);
      if (found) selected.push(found);
    });
    selectedPanel.innerHTML = '';
    if (!selected.length) { selectedPanel.innerHTML = '<small class="text-muted">No services selected</small>'; return; }
    selected.forEach(s => {
      const badge = document.createElement('span');
      badge.className = 'badge bg-primary me-2 mb-2 d-inline-flex align-items-center';
      badge.innerHTML = `${s.name} <button type="button" class="btn-close btn-close-white ms-2 remove-service" data-id="${s.id}" style="font-size:.75em;"></button>`;
      selectedPanel.appendChild(badge);
    });
  }
  document.addEventListener('change', e => { if (e.target.classList.contains('service-checkbox')) updatePanel(); });
  selectedPanel?.addEventListener('click', e => {
    if (e.target.classList.contains('remove-service')) {
      const id = e.target.getAttribute('data-id');
      const cb = document.querySelector(`.service-checkbox[value="${id}"]`);
      if (cb) { cb.checked = false; updatePanel(); }
    }
  });
  updatePanel();

  // Service search filter
  const searchInput = document.getElementById('serviceSearch');
  const servicesList = document.getElementById('servicesList');
  if (searchInput && servicesList) {
    searchInput.addEventListener('keyup', function () {
      const term = this.value.toLowerCase();
      const items = servicesList.querySelectorAll('li label');
      items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.parentElement.style.display = text.includes(term) ? '' : 'none';
      });
    });
  }

  // Logo preview
  const logoInput = document.getElementById('logoInput');
  const logoWrap  = document.getElementById('logoPreview');
  const imgEl     = document.getElementById('previewImage');
  const removeBtn = document.getElementById('removeLogoBtn');
  function resetLogo(){ if(!logoInput) return; logoInput.value=''; imgEl.src=''; logoWrap.style.display='none'; }
  logoInput?.addEventListener('change', (e)=>{ const f=e.target.files?.[0]; if(!f) return resetLogo(); const r=new FileReader(); r.onload=(ev)=>{ imgEl.src=ev.target.result; logoWrap.style.display='block'; }; r.readAsDataURL(f); });
  removeBtn?.addEventListener('click', resetLogo);

  // Map picker (Leaflet)
  tryInitMap();
});

function tryInitMap(attempt=0){
  const el = document.getElementById('mapPicker');
  if (!el || typeof L === 'undefined') { if (attempt<20) return setTimeout(()=>tryInitMap(attempt+1),150); return; }
  const map = L.map('mapPicker').setView([10.3157, 123.8854], 10);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap contributors' }).addTo(map);
  const marker = L.marker(map.getCenter(), {draggable:true}).addTo(map);
  function updateInputs(){ const p=marker.getLatLng(); const latEl=document.getElementById('lat'); const lngEl=document.getElementById('lng'); if(latEl) latEl.value=p.lat.toFixed(6); if(lngEl) lngEl.value=p.lng.toFixed(6); }
  marker.on('dragend', updateInputs);
  map.on('click', (e)=>{ marker.setLatLng(e.latlng); updateInputs(); });
  const oldLat = parseFloat('{{ old('latitude') }}');
  const oldLng = parseFloat('{{ old('longitude') }}');
  if(!isNaN(oldLat) && !isNaN(oldLng)) { const old=L.latLng(oldLat, oldLng); marker.setLatLng(old); map.setView(old, 15); }
  updateInputs();
  setTimeout(()=>map.invalidateSize(), 300);
}
</script>
@endpush
@endsection
