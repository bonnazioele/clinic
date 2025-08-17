@extends('admin.layouts.app')
@section('title','Clinics')

@push('styles')
@endpush

@section('content')
<div class="container py-4">
  <!-- Page Header -->
  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h2 fw-bold text-primary mb-2">
          <i class="bi bi-building-gear medical-icon me-3" style="font-size: 2.5rem;"></i>Clinics Management
        </h1>
        <p class="text-muted mb-0 fs-5">Manage and monitor all registered healthcare facilities</p>
      </div>
      <div class="d-flex gap-3">
        <a href="{{ route('admin.clinics.create') }}" class="btn btn-primary px-4">
          <i class="bi bi-plus-circle me-2"></i>New Clinic
        </a>
      </div>
    </div>

    <!-- Statistics Row -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="p-4 border rounded bg-primary text-white">
          <h4 class="fw-semibold">{{ $clinics->count() }}</h4>
          <small>Total Clinics</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4 border rounded bg-success text-white">
          <h4 class="fw-semibold">{{ \App\Models\Service::count() }}</h4>
          <small>Total Services</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4 border rounded bg-warning text-dark">
          @php $totalWaiting = \App\Models\QueueEntry::where('status', 'waiting')->count(); @endphp
          <h4 class="fw-semibold">{{ $totalWaiting }}</h4>
          <small>People Waiting</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4 border rounded bg-info text-white">
          @php
            $totalServed = \App\Models\QueueEntry::where('status', 'served')
              ->whereDate('served_at', today())->count();
          @endphp
          <h4 class="fw-semibold">{{ $totalServed }}</h4>
          <small>Served Today</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Search and Filter -->
  <div class="medical-card p-4 mb-4">
    <h4 class="mb-4">
      <i class="bi bi-search medical-icon me-2"></i>Search & Filter
    </h4>
    <form class="row g-4" method="GET" action="{{ route('admin.clinics.index') }}">
      <div class="col-lg-4">
        <label class="form-label fw-semibold"><i class="bi bi-building me-1"></i>Clinic Name</label>
        <input type="text" name="name" class="form-control form-control-lg"
               placeholder="Search by clinic name..."
               value="{{ request('name') }}">
      </div>
      <div class="col-lg-3">
        <label class="form-label fw-semibold"><i class="bi bi-flag me-1"></i>Status</label>
        <select name="status" class="form-select form-select-lg">
          <option value="">All Status</option>
          <option value="active" @selected(request('status') == 'active')>Active</option>
          <option value="inactive" @selected(request('status') == 'inactive')>Inactive</option>
        </select>
      </div>
      <div class="col-lg-2 d-flex align-items-end">
        <div class="d-grid w-100">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-funnel me-2"></i>Filter
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- Clinics Table -->
  <div class="medical-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">
        <i class="bi bi-building medical-icon me-2"></i>Registered Clinics
      </h4>
      <span class="badge bg-primary fs-6">{{ $clinics->total() }} Clinics</span>
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
            <th class="border-0 px-4 py-3 fw-semibold" width="180">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($clinics as $clinic)
          <tr>
            <td class="px-4 py-4">
              <div class="d-flex align-items-center">
                @if($clinic->logo)
                  <img src="{{ asset('storage/' . $clinic->logo) }}" alt="Logo"
                       class="rounded-3 me-3" style="width:50px;height:50px;object-fit:cover;">
                @else
                  <div class="bg-primary rounded-3 d-flex align-items-center justify-content-center me-3"
                       style="width:50px;height:50px;">
                    <i class="bi bi-building text-white"></i>
                  </div>
                @endif
                <div>
                  <h6 class="fw-semibold text-dark mb-2">{{ $clinic->name }}</h6>
                  <small class="text-muted d-block mb-1">
                    <i class="bi bi-tag me-1"></i>{{ $clinic->branch_code }}
                  </small>
                  <small class="text-muted">
                    <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($clinic->address, 40) }}
                  </small>
                </div>
              </div>
            </td>
            <td class="px-4 py-4">
              @if($clinic->services->count())
                <div class="d-flex flex-wrap gap-2 mb-2">
                  @foreach($clinic->services->take(3) as $service)
                    <span class="badge bg-info text-dark px-3 py-2">{{ $service->name }}</span>
                  @endforeach
                  @if($clinic->services->count() > 3)
                    <span class="badge bg-secondary px-3 py-2">+{{ $clinic->services->count() - 3 }}</span>
                  @endif
                </div>
                <small class="text-muted d-block">{{ $clinic->services->count() }} service(s) available</small>
              @else
                <span class="badge bg-warning text-dark px-3 py-2">No services</span>
              @endif
            </td>
            <td class="px-4 py-4">
              @php
                $doctors = \App\Models\User::where('is_doctor', true)
                    ->whereHas('clinics', fn($q) => $q->where('clinic_id', $clinic->id))
                    ->count();
                $secretaries = $clinic->secretaries()->count();
              @endphp
              <div class="small">
                <div class="d-flex align-items-center mb-2">
                  <i class="bi bi-person-badge text-primary me-2"></i>
                  <span class="fw-semibold">{{ $doctors }} Doctor(s)</span>
                </div>
                <div class="d-flex align-items-center">
                  <i class="bi bi-person-gear text-secondary me-2"></i>
                  <span class="fw-semibold">{{ $secretaries }} Secretary(ies)</span>
                </div>
              </div>
            </td>
            <td class="px-4 py-4">
              @php
                $todayAppointments = \App\Models\Appointment::where('clinic_id', $clinic->id)
                    ->whereDate('appointment_date', \Carbon\Carbon::today())->count();
                $totalAppointments = \App\Models\Appointment::where('clinic_id', $clinic->id)->count();
              @endphp
              <div class="small">
                <div class="d-flex align-items-center mb-2">
                  <i class="bi bi-calendar-day text-success me-2"></i>
                  <span class="fw-semibold">{{ $todayAppointments }} today</span>
                </div>
                <div class="d-flex align-items-center">
                  <i class="bi bi-calendar-check text-info me-2"></i>
                  <span class="fw-semibold">{{ $totalAppointments }} total</span>
                </div>
              </div>
            </td>
            <td class="px-4 py-4">
              <div class="small">
                <div class="d-flex align-items-center mb-2">
                  <i class="bi bi-geo-alt text-primary me-2"></i>
                  <span class="fw-semibold">{{ $clinic->gps_latitude ?? $clinic->latitude }}</span>
                </div>
                <div class="d-flex align-items-center">
                  <i class="bi bi-geo-alt text-primary me-2"></i>
                  <span class="fw-semibold">{{ $clinic->gps_longitude ?? $clinic->longitude }}</span>
                </div>
              </div>
            </td>
            <td class="px-4 py-4">
              <div class="d-flex gap-2">
                <a href="{{ route('admin.clinics.edit', $clinic) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                  <i class="bi bi-pencil me-1"></i>Edit
                </a>

                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill w-100"
                        onclick="deleteClinic({{ $clinic->id }})">
                  <i class="bi bi-trash me-1"></i>Delete
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

  <!-- Interactive Map -->
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

  document.addEventListener('DOMContentLoaded', function () {
    tryInitMap();
  });

  function tryInitMap(attempt = 0) {
    const el = document.getElementById('map');
    if (!el || typeof L === 'undefined') {
      if (attempt < 20) return setTimeout(() => tryInitMap(attempt + 1), 150);
      return; // stop after ~3s
    }
    initializeMap();
  }

  function initializeMap() {
    const el = document.getElementById('map');
    if (!el) return;

    // Default center: Cebu City
    map = L.map(el).setView([10.3157, 123.8854], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const bounds = L.latLngBounds();

    @php $source = isset($clinicsWithCoords) ? $clinicsWithCoords : $clinics; @endphp
    @foreach($source as $clinic)
      @php
        $lat = $clinic->gps_latitude ?? $clinic->latitude;
        $lng = $clinic->gps_longitude ?? $clinic->longitude;
      @endphp
      @if(!is_null($lat) && !is_null($lng))
        (function() {
          const lat = parseFloat('{{ $lat }}');
          const lng = parseFloat('{{ $lng }}');
          const marker = L.marker([lat, lng]).addTo(map).bindPopup(`
            <div class="text-center">
              <h6 class="fw-bold text-primary">{{ addslashes($clinic->name) }}</h6>
              <p class="mb-2">{{ addslashes($clinic->address) }}</p>
              <div class="d-grid gap-1">
                <a href="{{ route('admin.clinics.edit', $clinic) }}" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-pencil me-1"></i>Edit Clinic
                </a>

              </div>
            </div>
          `);
          markers.push({ id: {{ $clinic->id }}, marker, lat, lng });
          bounds.extend([lat, lng]);
        })();
      @endif
    @endforeach

    if (bounds.isValid()) {
      map.fitBounds(bounds.pad(0.1));
    }

    setTimeout(() => map.invalidateSize(), 300);
  }

  function focusOnMap(lat, lng) {
    if (!map) return;
    map.setView([lat, lng], 15);
    markers.forEach(m => {
      if (m.lat === lat && m.lng === lng) m.marker.openPopup();
    });
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
