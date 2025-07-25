@extends('layouts.app')

@section('title','Book Appointment')

@section('content')
<div class="card shadow-sm border-0 mx-auto" style="max-width:540px">
  <div class="card-header bg-white"><h4 class="mb-0">Schedule a Visit</h4></div>
  <div class="card-body">
    <form method="POST" action="{{ route('appointments.store') }}">
      @csrf

      <div class="mb-3">
        <label class="form-label">Clinic</label>
        <select id="clinic" class="form-select @error('clinic_id') is-invalid @enderror"
                name="clinic_id" required>
          <option value="">Select a clinic</option>
          @foreach($clinics as $c)
            <option value="{{ $c->id }}" @selected(old('clinic_id')==$c->id)>
              {{ $c->name }}
            </option>
          @endforeach
        </select>
        @error('clinic_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label">Service</label>
        <select id="service" class="form-select @error('service_id') is-invalid @enderror"
                name="service_id" required>
          <option value="">First choose a clinic</option>
        </select>
        @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="row gx-2 mb-3">
        <div class="col">
          <label class="form-label">Date</label>
          <input type="date" name="appointment_date"
                 class="form-control @error('appointment_date') is-invalid @enderror"
                 value="{{ old('appointment_date') }}" required>
          @error('appointment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col">
          <label class="form-label">Time</label>
          <input type="time" name="appointment_time"
                 class="form-control @error('appointment_time') is-invalid @enderror"
                 value="{{ old('appointment_time') }}" required>
          @error('appointment_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <button class="btn btn-primary w-100">Book Now</button>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script>
  const clinicServices = @json($clinics->pluck('services','id'));
  document.getElementById('clinic').addEventListener('change', e => {
    const opts = clinicServices[e.target.value] || [];
    const sel = document.getElementById('service');
    sel.innerHTML = '<option value="">Select a service</option>';
    opts.forEach(s => sel.append(new Option(s.name, s.id)));
  });
</script>
@endsection
