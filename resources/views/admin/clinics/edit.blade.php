@extends('admin.layouts.app')
@section('title','Edit Clinic')

@section('content')
  <h3>Edit Clinic</h3>
  <form method="POST" action="{{ route('admin.clinics.update',$clinic) }}">
    @csrf @method('PATCH')
    <!-- same inputs… but values from $clinic -->
    <input name="name"      value="{{ old('name',$clinic->name) }}"…>
    <textarea name="address">{{ old('address',$clinic->address) }}</textarea>
    <!-- map div & lat/lng inputs -->

    @section('scripts')
<script>
  var map = L.map('mapPicker').setView([{{ $clinic->latitude }},{{ $clinic->longitude }}],12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ maxZoom:19 }).addTo(map);
  var marker = L.marker([{{ $clinic->latitude }},{{ $clinic->longitude }}],{draggable:true}).addTo(map);
  // …same updateInputs logic…
</script>
@endsection
