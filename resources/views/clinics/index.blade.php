{{-- resources/views/clinics/index.blade.php --}}
@extends('layouts.app')

@section('title','Find Clinics')

@section('content')
  <form class="row g-2 mb-4" method="GET" action="{{ route('clinics.index') }}">
    <div class="col-md-6">
      <div class="input-group">
        <input
          type="text"
          name="name"
          class="form-control"
          placeholder="Clinic name or location"
          value="{{ request('name') }}"
        >
        <button class="btn btn-secondary">Search</button>
      </div>
    </div>
    <div class="col-md-4">
      <select name="service_id" class="form-select">
        <option value="">All Services</option>
        @foreach($services as $service)
          <option value="{{ $service->id }}"
            @selected(request('service_id')==$service->id)>{{ $service->service_name }}</option>
        @endforeach
      </select>
    </div>
  </form>

  {{-- Map container --}}
  <div id="map" class="map-container mb-4"></div>

  {{-- Clinic cards --}}
  <div class="row row-cols-1 row-cols-md-3 g-4">
    @foreach($clinics as $clinic)
      <div class="col">
        <div class="clinic-card">
          <div class="card-body d-flex flex-column">
            <h5>{{ $clinic->name }}</h5>
            <p class="text-muted small mb-2">{{ $clinic->address }}</p>
            <div class="mb-3">
              @foreach($clinic->services as $svc)
                <span class="badge bg-info text-dark">{{ $svc->service_name }}</span>
              @endforeach
            </div>
            <a href="{{ route('appointments.create',['clinic_id'=>$clinic->id]) }}"
               class="mt-auto btn btn-primary btn-sm">
              Book
            </a>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-4 d-flex justify-content-center">
    {{ $clinics->withQueryString()->links() }}
  </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize the map
  var map = L.map('map').setView([10.3157, 123.8854], 10);

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // Add markers and build bounds
  var bounds = L.latLngBounds();
  @foreach($clinics as $c)
    @if($c->gps_latitude && $c->gps_longitude)
      var marker = L.marker([{{ $c->gps_latitude }}, {{ $c->gps_longitude }}])
        .addTo(map)
        .bindPopup(
          `<strong>{{ addslashes($c->name) }}</strong><br>{{ addslashes($c->address) }}`
        );
      bounds.extend(marker.getLatLng());
    @endif
  @endforeach

  // Fit the map to the markers (or fallback center)
  if (bounds.isValid()) {
    map.fitBounds(bounds.pad(0.1));
  } else {
    map.setView([10.3157, 123.8854], 10);
  }
});
</script>
@endsection
