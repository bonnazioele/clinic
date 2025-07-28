@extends('layouts.app')

@section('title','Book Appointment')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="card shadow-sm">
    <div class="card-header">Schedule an Appointment</div>
    <div class="card-body">
      <form method="POST" action="{{ route('appointments.store') }}">
        @csrf

        {{-- Clinic selector --}}
        <div class="mb-3">
          <label for="clinic" class="form-label">Clinic</label>
          <select id="clinic"
                  name="clinic_id"
                  class="form-select @error('clinic_id') is-invalid @enderror"
                  required>
            <option value="">Select a clinic</option>
            @foreach($clinics as $c)
              <option value="{{ $c->id }}"
                {{ old('clinic_id')==$c->id ? 'selected':'' }}>
                {{ $c->name }}
              </option>
            @endforeach
          </select>
          @error('clinic_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Service selector (populated by JS) --}}
        <div class="mb-3">
          <label for="service" class="form-label">Service</label>
          <select id="service"
                  name="service_id"
                  class="form-select @error('service_id') is-invalid @enderror"
                  required>
            <option value="">First select a clinic</option>
          </select>
          @error('service_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Date & Time --}}
        <div class="row g-3 mb-3">
          <div class="col">
            <label for="appointment_date" class="form-label">Date</label>
            <input type="date"
                   id="appointment_date"
                   name="appointment_date"
                   class="form-control @error('appointment_date') is-invalid @enderror"
                   value="{{ old('appointment_date') }}"
                   required>
            @error('appointment_date')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col">
            <label for="appointment_time" class="form-label">Time</label>
            <input type="time"
                   id="appointment_time"
                   name="appointment_time"
                   class="form-control @error('appointment_time') is-invalid @enderror"
                   value="{{ old('appointment_time') }}"
                   required>
            @error('appointment_time')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <button class="btn btn-primary">Book Now</button>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const clinicSelect  = document.getElementById('clinic');
  const serviceSelect = document.getElementById('service');

  // Build a JS map: clinic_id => [ {id,name},… ]
  const clinicServices = @json(
    $clinics->mapWithKeys(function($c){
      return [ $c->id => $c->services->map(fn($s)=>['id'=>$s->id,'name'=>$s->name]) ];
    })
  );

  function populateServices(clinicId) {
    const list = clinicServices[clinicId] || [];
    serviceSelect.innerHTML = '<option value="">Select a service</option>';
    list.forEach(s => {
      serviceSelect.add(new Option(s.name, s.id));
    });
  }

  // When clinic changes:
  clinicSelect.addEventListener('change', e => {
    populateServices(e.target.value);
  });

  // On load: if old input exists, populate & re-select
  @if(old('clinic_id'))
    populateServices({{ old('clinic_id') }});
    serviceSelect.value = "{{ old('service_id') }}";
  @endif
});
</script>
@endsection
