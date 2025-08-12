@extends('layouts.app')

@section('title', 'Find Clinics')

@section('content')
<div class="container py-4">

  {{-- 🔍 Search & Filter --}}
  <form class="row g-2 mb-4 align-items-end" method="GET" action="{{ route('clinics.index') }}">
    <div class="col-md-6">
      <label class="form-label fw-semibold">Search by Name or Location</label>
      <div class="input-group shadow-sm">
        <input type="text" name="name" class="form-control"
               placeholder="e.g. San Nicolas, Health Center"
               value="{{ request('name') }}">
        <button class="btn btn-outline-primary px-4" type="submit">
          <i class="bi bi-search me-1"></i>Search
        </button>
      </div>
    </div>

    <div class="col-md-4">
      <label class="form-label fw-semibold">Filter by Service</label>
      <select name="service_id" class="form-select shadow-sm">
        <option value="">All Services</option>
        @foreach(\App\Models\Service::all() as $s)
          <option value="{{ $s->id }}" @selected(request('service_id') == $s->id)>
            {{ $s->service_name }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i>Filter
      </button>
    </div>
  </form>

  {{-- 🗺️ Map (shows right below search/filter) --}}
  <div id="map" class="rounded-4 shadow-sm mb-5" style="height: 300px;"></div>

  {{-- 🏥 Clinics Grid --}}
  <div class="row row-cols-1 row-cols-md-3 g-4">
    @forelse($clinics as $clinic)
      <div class="col">
        <div class="card h-100 shadow-sm rounded-4 border-0 clinic-card">
          <div class="card-body d-flex flex-column">
            <h5 class="fw-semibold text-primary">
              <i class="bi bi-hospital-fill me-1"></i>{{ $clinic->name }}
            </h5>
            <p class="text-muted small mb-2">
              <i class="bi bi-geo-alt-fill me-1 text-secondary"></i>{{ $clinic->address }}
            </p>

            <div class="mb-2">
              @foreach($clinic->services as $svc)
                <span class="badge bg-info text-dark me-1 mb-1">{{ $svc->service_name }}</span>
              @endforeach
            </div>

            <a href="{{ route('appointments.create', ['clinic_id' => $clinic->id]) }}"
               class="btn btn-outline-primary btn-sm mt-auto rounded-pill">
              <i class="bi bi-calendar-plus me-1"></i>Book Appointment
            </a>
          </div>
        </div>
      </div>
    @empty
      <div class="col">
        <div class="alert alert-info text-center rounded-3">
          <i class="bi bi-info-circle-fill me-1"></i>No clinics found. Try adjusting your filters.
        </div>
      </div>
    @endforelse
  </div>

  {{-- 📄 Pagination --}}
  <div class="mt-4 d-flex justify-content-center">
    {{ $clinics->withQueryString()->links() }}
  </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const el = document.getElementById('map');
  // Leaflet not loaded or element missing—bail safely
  if (!el || typeof L === 'undefined') return;

  // Default: Cebu City
  const map = L.map(el).setView([10.3157, 123.8854], 10);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  const bounds = L.latLngBounds();

  @foreach($clinics as $clinic)
    @if($clinic->gps_latitude && $clinic->gps_longitude)
      L.marker([{{ $clinic->gps_latitude }}, {{ $clinic->gps_longitude }}])
        .addTo(map)
        .bindPopup(`<strong>{{ addslashes($clinic->name) }}</strong><br>{{ addslashes($clinic->address) }}`);
      bounds.extend([{{ $clinic->gps_latitude }}, {{ $clinic->gps_longitude }}]);
    @endif
  @endforeach

  if (bounds.isValid()) {
    map.fitBounds(bounds.pad(0.1));
  }
});
</script>
@endpush
