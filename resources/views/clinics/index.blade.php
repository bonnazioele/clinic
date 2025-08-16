@extends('layouts.app')

@section('title', 'Find Clinics')

@push('styles')
  {{-- Leaflet CSS --}}
  <link rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="medical-card p-4 text-center">
        <div class="d-flex align-items-center justify-content-center mb-3">
          <i class="bi bi-building medical-icon me-3" style="font-size: 3rem;"></i>
          <div>
            <h1 class="mb-1 fw-bold text-primary">Find Your Healthcare Provider</h1>
            <p class="text-muted mb-0">
              <i class="bi bi-geo-alt me-2"></i>Discover clinics and medical services near you
            </p>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-3">
            <div class="text-center">
              <div class="dashboard-stat">{{ $clinics->total() }}</div>
              <small class="text-muted">Available Clinics</small>
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-center">
              <div class="dashboard-stat">{{ \App\Models\Service::count() }}</div>
              <small class="text-muted">Medical Services</small>
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-center">
              <div class="dashboard-stat">{{ \App\Models\User::where('is_doctor', true)->count() }}</div>
              <small class="text-muted">Healthcare Professionals</small>
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-center">
              <div class="dashboard-stat">{{ $bookedAppointmentsCount ?? 0 }}</div>
              <small class="text-muted">Appointments Booked</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Enhanced Search & Filter --}}
  <div class="medical-card p-4 mb-4">
    <h5 class="mb-3">
      <i class="bi bi-search medical-icon me-2"></i>Search & Filter Clinics
    </h5>
    <form class="row g-3" method="GET" action="{{ route('clinics.index') }}">
      <div class="col-lg-5">
        <label class="form-label fw-semibold">
          <i class="bi bi-building me-1"></i>Clinic Name or Location
        </label>
        <div class="input-group">
          <span class="input-group-text bg-light">
            <i class="bi bi-search text-muted"></i>
          </span>
          <input type="text" name="name" class="form-control"
                 placeholder="e.g. San Nicolas Health Center, Cebu City"
                 value="{{ request('name') }}">
        </div>
      </div>

      <div class="col-lg-4">
        <label class="form-label fw-semibold">
          <i class="bi bi-gear me-1"></i>Medical Service
        </label>
        <select name="service_id" class="form-select">
          <option value="">All Medical Services</option>
          @foreach(\App\Models\Service::all() as $service)
            <option value="{{ $service->id }}" @selected(request('service_id') == $service->id)>
              {{ $service->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-lg-3 d-flex align-items-end">
        <div class="d-grid w-100">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-funnel me-2"></i>Search Clinics
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- Interactive Map --}}
  <div class="medical-card p-4 mb-4">
    <h5 class="mb-3">
      <i class="bi bi-geo-alt medical-icon me-2"></i>Clinic Locations
    </h5>
    <div id="map" class="rounded-3 shadow-sm" style="height: 400px;"></div>
    <div class="text-center mt-2">
      <small class="text-muted">
        <i class="bi bi-info-circle me-1"></i>
        Click on markers to see clinic details
      </small>
    </div>
  </div>

  {{-- Enhanced Clinics Grid --}}
  <div class="medical-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h5 class="mb-0">
        <i class="bi bi-building medical-icon me-2"></i>Available Clinics
      </h5>
      <div class="d-flex align-items-center">
        <span class="badge bg-primary me-2">{{ $clinics->total() }} clinics found</span>
        <div class="btn-group btn-group-sm" role="group">
          <button type="button" class="btn btn-outline-primary" id="gridView">
            <i class="bi bi-grid-3x3-gap"></i>
          </button>
          <button type="button" class="btn btn-outline-primary" id="listView">
            <i class="bi bi-list-ul"></i>
          </button>
        </div>
      </div>
    </div>

    <div id="clinicsGrid" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
      @forelse($clinics as $clinic)
        <div class="col">
          <div class="clinic-card h-100 p-4">
            <div class="d-flex align-items-start justify-content-between mb-3">
              <div class="d-flex align-items-center">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                     style="width: 50px; height: 50px;">
                  <i class="bi bi-building text-white fs-4"></i>
                </div>
                <div>
                  <h6 class="fw-bold text-primary mb-1">{{ $clinic->name }}</h6>
                  <small class="text-muted">
                    <i class="bi bi-geo-alt me-1"></i>{{ $clinic->address }}
                  </small>
                </div>
              </div>
              <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li>
                    <a class="dropdown-item" href="{{ route('appointments.create', ['clinic_id' => $clinic->id]) }}">
                      <i class="bi bi-calendar-plus me-2"></i>Book Appointment
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="#" onclick="showClinicDetails({{ $clinic->id }})">
                      <i class="bi bi-info-circle me-2"></i>View Details
                    </a>
                  </li>
                  @php
                    $lat = $clinic->gps_latitude ?? $clinic->latitude;
                    $lng = $clinic->gps_longitude ?? $clinic->longitude;
                  @endphp
                  @if($lat && $lng)
                    <li>
                      <a class="dropdown-item" href="#" onclick="focusOnMap({{ $lat }}, {{ $lng }})">
                        <i class="bi bi-geo-alt me-2"></i>Show on Map
                      </a>
                    </li>
                  @endif
                </ul>
              </div>
            </div>

            <div class="mb-3">
              <h6 class="fw-semibold mb-2">
                <i class="bi bi-gear me-1"></i>Available Services
              </h6>
              <div class="d-flex flex-wrap gap-1">
                @foreach($clinic->services->take(3) as $service)
                  <span class="badge bg-info text-dark">{{ $service->name }}</span>
                @endforeach
                @if($clinic->services->count() > 3)
                  <span class="badge bg-secondary">+{{ $clinic->services->count() - 3 }} more</span>
                @endif
              </div>
            </div>

            <!-- Queue Status -->
            <div class="mb-3">
              <div class="d-flex align-items-center justify-content-between">
                <h6 class="fw-semibold mb-2">
                  <i class="bi bi-people me-1"></i>Queue Status
                </h6>
                @php
                  $waitingCount = \App\Models\QueueEntry::where('clinic_id', $clinic->id)
                    ->where('status', 'waiting')->count();
                @endphp
                <span class="badge {{ $waitingCount > 0 ? 'bg-warning' : 'bg-success' }} text-dark" title="People currently waiting in line">
                  {{ $waitingCount }} {{ $waitingCount == 1 ? 'person' : 'people' }} waiting
                </span>
              </div>
              @if($waitingCount > 0)
                <small class="text-muted">
                  <i class="bi bi-clock me-1"></i>Estimated wait time: {{ $waitingCount * 15 }} minutes
                </small>
              @else
                <small class="text-success">
                  <i class="bi bi-check-circle me-1"></i>No wait time
                </small>
              @endif
            </div>

            <div class="d-grid gap-2">
              <a href="{{ route('appointments.create', ['clinic_id' => $clinic->id]) }}" class="btn btn-primary">
                <i class="bi bi-calendar-plus me-2"></i>Book Appointment
              </a>
              <div class="text-center">
                <small class="text-muted">
                  <i class="bi bi-info-circle me-1"></i>You'll be automatically added to the queue
                </small>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="text-center py-5">
            <i class="bi bi-building-x text-muted" style="font-size: 4rem;"></i>
            <h5 class="text-muted mt-3">No clinics found</h5>
            <p class="text-muted mb-3">Try adjusting your search criteria or filters.</p>
            <a href="{{ route('clinics.index') }}" class="btn btn-outline-primary">
              <i class="bi bi-arrow-clockwise me-2"></i>Clear Filters
            </a>
          </div>
        </div>
      @endforelse
    </div>

    {{-- Pagination --}}
    @if($clinics->hasPages())
      <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Clinics pagination">
          {{ $clinics->withQueryString()->links() }}
        </nav>
      </div>
    @endif
  </div>
</div>

<!-- Clinic Details Modal -->
<div class="modal fade" id="clinicDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-building medical-icon me-2"></i>Clinic Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="clinicDetailsContent">
        <!-- Content will be loaded here -->
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  {{-- Leaflet JS --}}
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
          integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

  <script>
  let map, markers = [];

  document.addEventListener('DOMContentLoaded', function () {
    initializeMap();
    initializeViewToggle();
    initializeQueueUpdates();
  });

  function initializeMap() {
    const el = document.getElementById('map');
    if (!el || typeof L === 'undefined') return;

    // Default: Cebu City
    map = L.map(el).setView([10.3157, 123.8854], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const bounds = L.latLngBounds();

    @foreach($clinics as $clinic)
      @php
        $lat = $clinic->gps_latitude ?? $clinic->latitude;
        $lng = $clinic->gps_longitude ?? $clinic->longitude;
      @endphp
      @if($lat && $lng)
        (function() {
          const lat = parseFloat('{{ $lat }}');
          const lng = parseFloat('{{ $lng }}');
          const marker = L.marker([lat, lng])
            .addTo(map)
            .bindPopup(`
              <div class="text-center">
                <h6 class="fw-bold text-primary">{{ addslashes($clinic->name) }}</h6>
                <p class="mb-2">{{ addslashes($clinic->address) }}</p>
                <a href="{{ route('appointments.create', ['clinic_id' => $clinic->id]) }}"
                   class="btn btn-sm btn-primary">
                  <i class="bi bi-calendar-plus me-1"></i>Book Now
                </a>
              </div>
            `);

          markers.push({ id: {{ $clinic->id }}, marker, lat, lng });
          bounds.extend([lat, lng]);
        })();
      @endif
    @endforeach

    if (bounds.isValid()) map.fitBounds(bounds.pad(0.1));
    // In case container was hidden/relayouted
    setTimeout(() => map.invalidateSize(), 300);
  }

  function initializeViewToggle() {
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const clinicsGrid = document.getElementById('clinicsGrid');
    const clinicsList = document.getElementById('clinicsList');

    if (gridView && listView) {
      gridView.addEventListener('click', () => {
        if (clinicsGrid) clinicsGrid.style.display = 'flex';
        if (clinicsList) clinicsList.style.display = 'none';
        gridView.classList.add('active');
        listView.classList.remove('active');
        if (map) setTimeout(() => map.invalidateSize(), 150);
      });

      listView.addEventListener('click', () => {
        if (clinicsGrid) clinicsGrid.style.display = 'none';
        if (clinicsList) clinicsList.style.display = 'block';
        listView.classList.add('active');
        gridView.classList.remove('active');
        if (map) setTimeout(() => map.invalidateSize(), 150);
      });
    }
  }

  function initializeQueueUpdates() { /* reserved for future live updates */ }

  function focusOnMap(lat, lng) {
    if (!map) return;
    map.setView([lat, lng], 15);
    markers.forEach(m => { if (m.lat === lat && m.lng === lng) m.marker.openPopup(); });
  }

  function showClinicDetails(clinicId) { /* reserved for future modal content load */ }
  </script>
@endpush
