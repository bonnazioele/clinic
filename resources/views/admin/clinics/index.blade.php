@extends('admin.layouts.app')
@section('title','Clinics')

@push('styles')
@endpush

@section('content')
<div class="container py-4">
  
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold text-primary mb-1">
          <i class="bi bi-building-fill-gear me-2"></i>Clinics Management
        </h2>
        <p class="text-muted mb-0">Manage and monitor all registered healthcare facilities</p>
      </div>
      <div class="d-none d-md-block">
        <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary">
          <i class="bi bi-plus-circle me-2"></i>New Clinic
        </a>
      </div>
    </div>
    <div class="mt-3 d-md-none">
      <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary w-100">
        <i class="bi bi-plus-circle me-2"></i>New Clinic
      </a>
    </div>
  </div>

  
  <div class="medical-card p-4 mb-4">
    <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.clinics.index') }}">
      <div class="col-md-5 col-lg-4">
        <label class="form-label fw-semibold"><i class="bi bi-search me-1"></i>Search</label>
        <input type="text" name="name" class="form-control form-control-lg" placeholder="Search clinic name..." value="{{ request('name') }}">
      </div>
      <div class="col-md-4 col-lg-3">
        <label class="form-label fw-semibold"><i class="bi bi-filter me-1"></i>Status</label>
        <select name="status" class="form-select form-select-lg">
          <option value="">All Status</option>
          <option value="active" @selected(request('status') == 'active')>Active</option>
          <option value="inactive" @selected(request('status') == 'inactive')>Inactive</option>
        </select>
      </div>
      <div class="col-md-2 col-lg-2">
        <button type="submit" class="btn btn-primary btn-lg w-100">
          <i class="bi bi-funnel me-1"></i>Apply
        </button>
      </div>
      <div class="col-md-2 col-lg-2">
        @if(request()->hasAny(['name','status']) && (request('name') || request('status')))
          <a href="{{ route('admin.clinics.index') }}" class="btn btn-outline-secondary btn-lg w-100">
            <i class="bi bi-x-circle me-1"></i>Clear
          </a>
        @endif
      </div>
    </form>
  </div>

  
  <div class="medical-card p-4">
    <div class="mb-4">
      <h4 class="mb-0 d-flex align-items-center gap-2">
        Registered Clinics
        <span class="badge bg-primary" style="font-size:.75rem;">{{ $clinics->total() }}</span>
      </h4>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr class="bg-light">
            <th class="border-0 px-4 py-3 fw-semibold"><i class="bi bi-building me-1"></i>Clinic</th>
            <th class="border-0 px-4 py-3 fw-semibold"><i class="bi bi-gear me-1"></i>Services</th>
            <th class="border-0 px-4 py-3 fw-semibold"><i class="bi bi-people me-1"></i>Staff</th>
            <th class="border-0 px-4 py-3 fw-semibold"><i class="bi bi-calendar-check me-1"></i>Appointments</th>
            <th class="border-0 px-4 py-3 fw-semibold"><i class="bi bi-geo-alt me-1"></i>Location</th>
            <th class="border-0 px-4 py-3 fw-semibold" width="160">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($clinics as $clinic)
          <tr class="clinic-row" onclick="window.location='{{ route('admin.clinics.show', $clinic) }}'" style="cursor:pointer;">
            <td class="px-4 py-4 fs-6">
              <div class="d-flex align-items-center">
                @if($clinic->logo)
                  <img src="{{ asset('storage/' . $clinic->logo) }}" alt="Logo"
                       class="rounded-circle me-3" style="width:50px;height:50px;object-fit:cover;">
                @else
                  <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                       style="width:50px;height:50px;">
                    <i class="bi bi-hospital text-white"></i>
                  </div>
                @endif
                <div>
                  <h6 class="fw-semibold text-dark mb-2">
                    <a href="{{ route('admin.clinics.show', $clinic) }}" class="text-decoration-none" onclick="event.stopPropagation();">{{ $clinic->name }}</a>
                  </h6>
                  <small class="text-muted d-block mb-1">{{ $clinic->branch_code }}</small>
                </div>
              </div>
            </td>
            <td class="px-4 py-4 fs-6">
              <span class="badge bg-info text-dark px-3 py-2">{{ $clinic->services->count() }}</span>
            </td>
            <td class="px-4 py-4 fs-6">
              @php
                $doctors = \App\Models\User::where('is_doctor', true)
                    ->whereHas('clinics', fn($q) => $q->where('clinic_id', $clinic->id))
                    ->count();
                $secretaries = $clinic->secretaries()->count();
              @endphp
              <div class="small">
                <div class="mb-2">{{ $doctors }} Doctor(s)</div>
                <div>{{ $secretaries }} Secretary(ies)</div>
              </div>
            </td>
            <td class="px-4 py-4 fs-6">
              @php
                $todayAppointments = \App\Models\Appointment::where('clinic_id', $clinic->id)
                    ->whereDate('appointment_date', \Carbon\Carbon::today())->count();
                $totalAppointments = \App\Models\Appointment::where('clinic_id', $clinic->id)->count();
              @endphp
              <div class="small">
                <div class="mb-2">{{ $todayAppointments }} today</div>
                <div>{{ $totalAppointments }} total</div>
              </div>
            </td>
            <td class="px-4 py-4 fs-6">
              <div class="small">{{ Str::limit($clinic->address, 70) }}</div>
            </td>
            <td class="px-4 py-4">
              <div class="d-flex gap-2">
                <a href="{{ route('admin.clinics.edit', $clinic) }}" class="btn btn-primary d-flex align-items-center justify-content-center" style="width:36px;height:36px;" title="Edit" onclick="event.stopPropagation();">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <button type="button" class="btn btn-danger d-flex align-items-center justify-content-center" style="width:36px;height:36px;" title="Delete" onclick="event.stopPropagation(); deleteClinic({{ $clinic->id }})">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="text-center py-5">
              <div class="medical-card p-4 mt-4">
                <i class="bi bi-building-x display-4 text-muted mb-3"></i>
                <h5 class="text-muted mb-3">No Clinics Found</h5>
                <p class="text-muted mb-4">Get started by creating your first clinic.</p>
                <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary">
                  <i class="bi bi-plus-circle me-2"></i>Create First Clinic
                </a>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($clinics->hasPages())
      <div class="d-flex justify-content-center p-4">
        {{ $clinics->links() }}
      </div>
    @endif
  </div>

  
  <div class="medical-card p-4 mt-4">
    <h5 class="mb-3">
      <i class="bi bi-geo-alt medical-icon me-2"></i>Clinic Locations Overview
    </h5>
    <div id="map" class="rounded-3 shadow-sm" style="height:400px;"></div>
    <div class="text-center mt-2">
      <small class="text-muted">
        <i class="bi bi-info-circle me-1"></i>
        Click markers for details; use the action buttons above to focus specific locations.
      </small>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script>
  let map, markers = [];

  // Build mapping data in PHP first to avoid complex inline collection/closure syntax that produced a parse error.
  @php
    $sourceClinics = isset($clinicsWithCoords) ? $clinicsWithCoords : $clinics;
    $mapClinics = $sourceClinics->map(function($c){
        return [
          'id' => $c->id,
          'name' => $c->name,
          'address' => $c->address,
          'lat' => (float) ($c->gps_latitude ?? $c->latitude),
          'lng' => (float) ($c->gps_longitude ?? $c->longitude),
          'showUrl' => route('admin.clinics.show', $c),
          'editUrl' => route('admin.clinics.edit', $c),
        ];
    })->filter(function($c){
        return !is_null($c['lat']) && !is_null($c['lng']);
    })->values();
  @endphp
  const clinicsData = @json($mapClinics);

  document.addEventListener('DOMContentLoaded', () => tryInitMap());

  function tryInitMap(attempt = 0) {
    const el = document.getElementById('map');
    if (!el || typeof L === 'undefined') {
      if (attempt < 30) return setTimeout(() => tryInitMap(attempt + 1), 100);
      return;
    }
    initializeMap();
  }

  function initializeMap() {
    const el = document.getElementById('map');
    if (!el) return;

    map = L.map(el).setView([10.3157, 123.8854], 10); // Cebu default
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const bounds = L.latLngBounds();
    clinicsData.forEach(c => {
      if (isFinite(c.lat) && isFinite(c.lng)) {
        const marker = L.marker([c.lat, c.lng]).addTo(map).bindPopup(
          `<div class="text-center">
             <h6 class="fw-bold text-primary">${escapeHtml(c.name)}</h6>
             <p class="mb-2">${escapeHtml(c.address ?? '')}</p>
             <div class="d-grid gap-1">
               <a href="${c.showUrl}" class="btn btn-sm btn-outline-secondary">
                 <i class="bi bi-eye me-1"></i>View Details
               </a>
               <a href="${c.editUrl}" class="btn btn-sm btn-outline-primary">
                 <i class="bi bi-pencil me-1"></i>Edit Clinic
               </a>
             </div>
           </div>`
        );
        markers.push({ id: c.id, marker, lat: c.lat, lng: c.lng });
        bounds.extend([c.lat, c.lng]);
      }
    });

    if (bounds.isValid()) {
      map.fitBounds(bounds.pad(0.1));
    }
    setTimeout(() => map.invalidateSize(), 200);
  }

  function escapeHtml(str){
    return String(str ?? '').replace(/[&<>"'`]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;','`':'&#96;'}[s]));
  }

  function focusOnMap(lat, lng) {
    if (!map) return;
    map.setView([lat, lng], 15);
    markers.forEach(m => { if (m.lat === lat && m.lng === lng) m.marker.openPopup(); });
  }

  function deleteClinic(clinicId) {
    if (!confirm('Are you sure you want to delete this clinic? This action cannot be undone.')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/clinics/${clinicId}`;
    form.innerHTML = `
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="_method" value="DELETE">
    `;
    document.body.appendChild(form);
    form.submit();
  }
  </script>
@endpush
