@extends('layouts.app')
@section('title','Book Appointment')

@section('content')
<div class="container py-4">
  <form method="POST" action="{{ route('appointments.store') }}">
    @csrf

    {{-- Clinic --}}
    <div class="mb-3">
      <label class="form-label">Clinic</label>
      <select id="clinic" name="clinic_id" class="form-select" required>
        <option value="">Select a clinic</option>
        @foreach($clinics as $c)
          <option value="{{ $c->id }}" {{ old('clinic_id')==$c->id?'selected':'' }}>
            {{ $c->name }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Service --}}
    <div class="mb-3">
      <label class="form-label">Service</label>
      <select id="service" name="service_id" class="form-select" required>
        <option value="">First select a clinic</option>
      </select>
    </div>

    {{-- Doctor --}}
    <div class="mb-3">
      <label class="form-label">Doctor</label>
      <select id="doctor" name="doctor_id" class="form-select" required>
        <option value="">First select a clinic</option>
      </select>
    </div>

    {{-- Date & Time --}}
    <div class="row g-3 mb-3">
      <div class="col">
        <label class="form-label">Date</label>
        <input type="date" name="appointment_date"
               class="form-control" value="{{ old('appointment_date') }}" required>
      </div>
      <div class="col">
        <label class="form-label">Time</label>
        <input type="time" name="appointment_time"
               class="form-control" value="{{ old('appointment_time') }}" required>
      </div>
    </div>

    <button class="btn btn-primary">Book Now</button>
  </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const clinicSelect  = document.getElementById('clinic');
  const serviceSelect = document.getElementById('service');
  const doctorSelect  = document.getElementById('doctor');

  // Build a map: clinicId -> { services: [...], doctors: [{id,name,services:[ids]}] }
  const clinicMap = {};
  @foreach($clinics as $c)
    clinicMap[{{ $c->id }}] = {
      services: @json($c->services->map(fn($s)=>['id'=>$s->id,'name'=>$s->name])),
      doctors:  @json($c->doctors->map(fn($d)=>[
        'id'=>$d->id,
        'name'=>$d->name,
        'services'=> $d->services->pluck('id')->values()
      ]))
    };
  @endforeach

  function populateServices(clinicId) {
    serviceSelect.innerHTML = '<option value="">Select a service</option>';
    const list = clinicMap[clinicId]?.services || [];
    list.forEach(s => serviceSelect.add(new Option(s.name, s.id)));
    // clear doctor until service chosen
    doctorSelect.innerHTML = '<option value="">First select a service</option>';
  }

  function populateDoctors(clinicId, serviceId) {
    doctorSelect.innerHTML = '<option value="">Select a doctor</option>';
    const docs = clinicMap[clinicId]?.doctors || [];
    docs
      .filter(d => d.services.includes(Number(serviceId)))
      .forEach(d => doctorSelect.add(new Option(d.name, d.id)));
  }

  clinicSelect.addEventListener('change', e => {
    const cid = e.target.value;
    if (!cid) return;
    populateServices(cid);
  });

  serviceSelect.addEventListener('change', e => {
    const cid = clinicSelect.value;
    const sid = e.target.value;
    if (!cid || !sid) return;
    populateDoctors(cid, sid);
  });

  // repopulate on validation error
  @if(old('clinic_id'))
    populateServices({{ old('clinic_id') }});
    serviceSelect.value = "{{ old('service_id') }}";
    populateDoctors({{ old('clinic_id') }}, "{{ old('service_id') }}");
    doctorSelect.value = "{{ old('doctor_id') }}";
  @endif
});
</script>
@endsection
