@extends('layouts.app')

@section('title', 'Book Appointment')

@section('content')
<div class="container py-5">
  <div class="card shadow-sm rounded-4 p-4">
    <h2 class="mb-4 text-primary fw-bold">
      <i class="bi bi-calendar-plus me-2"></i>Book Appointment
    </h2>

    {{-- Optional Toast --}}
    <x-alerts.toast />

    <form method="POST" action="{{ route('appointments.store') }}">
      @csrf

      {{-- Clinic --}}
      <div class="mb-3">
        <label class="form-label fw-semibold">Clinic <span class="text-danger">*</span></label>
        <select id="clinic" name="clinic_id" class="form-select @error('clinic_id') is-invalid @enderror" required>
          <option value="">Select a clinic</option>
          @foreach($clinics as $c)
            <option value="{{ $c->id }}" {{ old('clinic_id') == $c->id ? 'selected' : '' }}>
              {{ $c->name }}
            </option>
          @endforeach
        </select>
        @error('clinic_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- Service --}}
      <div class="mb-3">
        <label class="form-label fw-semibold">Service <span class="text-danger">*</span></label>
        <select id="service" name="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
          <option value="">First select a clinic</option>
        </select>
        @error('service_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- Doctor --}}
      <div class="mb-3">
        <label class="form-label fw-semibold">Doctor <span class="text-danger">*</span></label>
        <select id="doctor" name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
          <option value="">First select a service</option>
        </select>
        @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      {{-- Date & Time --}}
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
          <input type="date"
                 name="appointment_date"
                 class="form-control @error('appointment_date') is-invalid @enderror"
                 value="{{ old('appointment_date') }}"
                 required>
          @error('appointment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
          <input type="time"
                 name="appointment_time"
                 class="form-control @error('appointment_time') is-invalid @enderror"
                 value="{{ old('appointment_time') }}"
                 required>
          @error('appointment_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="text-end">
        <button class="btn btn-primary rounded-pill px-4">
          <i class="bi bi-calendar-check-fill me-2"></i>Book Now
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const clinicSelect  = document.getElementById('clinic');
  const serviceSelect = document.getElementById('service');
  const doctorSelect  = document.getElementById('doctor');

  const clinicMap = {};
  @foreach($clinics as $c)
    clinicMap[{{ $c->id }}] = {
      services: @json($c->services->map(fn($s)=>['id'=>$s->id,'name'=>$s->service_name])),
      doctors:  @json($c->doctors->map(fn($d)=>[
        'id'=>$d->id,
        'name'=>$d->first_name . ' ' . $d->last_name,
        'services'=> $d->services->pluck('id')->values()
      ]))
    };
  @endforeach

  function populateServices(clinicId) {
    serviceSelect.innerHTML = '<option value="">Select a service</option>';
    const list = clinicMap[clinicId]?.services || [];
    list.forEach(s => serviceSelect.add(new Option(s.name, s.id)));
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

  @if(old('clinic_id'))
    populateServices({{ old('clinic_id') }});
    serviceSelect.value = "{{ old('service_id') }}";
    populateDoctors({{ old('clinic_id') }}, "{{ old('service_id') }}");
    doctorSelect.value = "{{ old('doctor_id') }}";
  @endif
});
</script>
@endsection
