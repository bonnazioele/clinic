@extends('admin.layouts.app')

@section('title','Edit Clinic')

@section('content')
  <h3 class="mb-4">Edit Clinic</h3>

  <form method="POST" action="{{ route('admin.clinics.update', $clinic) }}">
    @csrf
    @method('PATCH')

    <!-- Name -->
    <div class="mb-3">
      <label class="form-label">Clinic Name</label>
      <input type="text"
             name="name"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name', $clinic->name) }}"
             required>
      @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Address -->
    <div class="mb-3">
      <label class="form-label">Address</label>
      <textarea name="address"
                class="form-control @error('address') is-invalid @enderror"
                rows="3"
                required>{{ old('address', $clinic->address) }}</textarea>
      @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Clinic Type -->
    <div class="mb-3">
      <label class="form-label">Clinic Type</label>
      <select name="type_id" 
              class="form-select @error('type_id') is-invalid @enderror" 
              required>
        <option value="">Select Clinic Type</option>
        @foreach($clinicTypes as $clinicType)
          <option value="{{ $clinicType->id }}" 
                  {{ old('type_id', $clinic->type_id) == $clinicType->id ? 'selected' : '' }}>
            {{ $clinicType->type_name }}
          </option>
        @endforeach
      </select>
      @error('type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
            @selected(in_array(
              $svc->id,
              old('service_ids', $clinic->services->pluck('id')->toArray())
            ))>
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
               value="{{ old('latitude', $clinic->latitude) }}"
               required>
        @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col">
        <label class="form-label">Longitude</label>
        <input type="text"
               id="lng"
               name="longitude"
               class="form-control @error('longitude') is-invalid @enderror"
               value="{{ old('longitude', $clinic->longitude) }}"
               required>
        @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    <button class="btn btn-primary mt-3">Update Clinic</button>
  </form>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('mapPicker')
               .setView([{{ $clinic->latitude }},{{ $clinic->longitude }}],12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom:19, attribution:'&copy; OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker(
      [{{ $clinic->latitude }},{{ $clinic->longitude }}],
      { draggable:true }
    ).addTo(map);

    function updateInputs() {
      var p = marker.getLatLng();
      document.getElementById('lat').value = p.lat.toFixed(6);
      document.getElementById('lng').value = p.lng.toFixed(6);
    }

    marker.on('dragend', updateInputs);
    map.on('click', function(e) {
      marker.setLatLng(e.latlng);
      updateInputs();
    });

    updateInputs();
  });
</script>
@endsection
