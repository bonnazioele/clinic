@extends('admin.layouts.app')
@section('title','Add Clinic')

@section('content')
  <h3>Add Clinic</h3>
  <form method="POST" action="{{ route('admin.clinics.store') }}">
    @csrf
    <div class="mb-3">
      <label>Name</label>
      <input name="name" class="form-control" value="{{ old('name') }}" required>
    </div>
    <div class="mb-3">
      <label>Address</label>
      <textarea name="address" class="form-control" required>{{ old('address') }}</textarea>
    </div>
    <div id="mapPicker" class="map-picker"></div>
    <div class="row">
      <div class="col">
        <label>Latitude</label>
        <input name="latitude" id="lat" class="form-control" value="{{ old('latitude') }}" required>
      </div>
      <div class="col">
        <label>Longitude</label>
        <input name="longitude" id="lng" class="form-control" value="{{ old('longitude') }}" required>
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
