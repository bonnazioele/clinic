@extends('admin.layouts.app')

@section('title','Add Clinic')

@section('content')
  <h3 class="mb-4">Add Clinic</h3>

  <form method="POST" action="{{ route('admin.clinics.store') }}">
    @csrf

    <!-- Name -->
    <div class="mb-3">
      <label class="form-label">Clinic Name</label>
      <input type="text"
             name="name"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name') }}"
             required>
      @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Address -->
    <div class="mb-3">
      <label class="form-label">Address</label>
      <textarea name="address"
                class="form-control @error('address') is-invalid @enderror"
                rows="3"
                required>{{ old('address') }}</textarea>
      @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Services Multi-Select -->
    <div class="mb-3">
      <label class="form-label">Services Offered</label>
      <select name="service_ids[]"
              id="services"
              class="form-select @error('service_ids') is-invalid @enderror"
              multiple>
        @foreach($services as $svc)
          <option value="{{ $svc->id }}"
            @selected(in_array($svc->id, old('service_ids', [])))>
            {{ $svc->name }}
          </option>
        @endforeach
      </select>
      @error('service_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Map Picker -->
    <div id="mapPicker" class="map-picker mb-3"></div>

    <!-- Latitude & Longitude -->
    <div class="row">
      <div class="col">
        <label class="form-label">Latitude</label>
        <input type="text"
               id="lat"
               name="latitude"
               class="form-control @error('latitude') is-invalid @enderror"
               value="{{ old('latitude') }}"
               required>
        @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col">
        <label class="form-label">Longitude</label>
        <input type="text"
               id="lng"
               name="longitude"
               class="form-control @error('longitude') is-invalid @enderror"
               value="{{ old('longitude') }}"
               required>
        @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    <button class="btn btn-primary mt-3">Save Clinic</button>
  </form>
@endsection

@section('scripts')
<script>
  // initialize map picker
  var map = L.map('mapPicker').setView([10.3157,123.8854],10);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    maxZoom:19, attribution:'&copy; OSM'
  }).addTo(map);
  var marker = L.marker(map.getCenter(),{draggable:true}).addTo(map);

  function updateInputs() {
    var p = marker.getLatLng();
    document.getElementById('lat').value = p.lat.toFixed(6);
    document.getElementById('lng').value = p.lng.toFixed(6);
  }
  marker.on('dragend', updateInputs);
  map.on('click', function(e){
    marker.setLatLng(e.latlng);
    updateInputs();
  });
  // seed initial
  updateInputs();
</script>
@endsection