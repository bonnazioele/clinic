@extends('layouts.app')
@section('title','Edit Appointment')

@section('content')
<div class="container py-4">
  @include('partials.alerts')
  <div class="medical-card p-4">
    <h5 class="mb-4"><i class="bi bi-calendar-event me-2"></i>Edit Appointment</h5>
    <form method="POST" action="{{ route('appointments.update',$appointment) }}">
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label class="form-label fw-semibold">Clinic</label>
        <div class="form-control bg-light">{{ $clinic->name }}</div>
      </div>

      <div class="mb-3">
        <label for="service_id" class="form-label fw-semibold">Service</label>
        <select id="service_id" name="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
          @foreach($clinic->services as $service)
            <option value="{{ $service->id }}" @selected(old('service_id',$appointment->service_id)==$service->id)>{{ $service->name }}</option>
          @endforeach
        </select>
        @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label for="doctor_id" class="form-label fw-semibold">Doctor</label>
        <select id="doctor_id" name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
          @foreach($clinic->doctors as $doc)
            <option value="{{ $doc->id }}" @selected(old('doctor_id',$appointment->doctor_id)==$doc->id)>{{ $doc->name }}</option>
          @endforeach
        </select>
        @error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label for="appointment_date" class="form-label fw-semibold">Date</label>
          <input type="date" id="appointment_date" name="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror" value="{{ old('appointment_date',$appointment->appointment_date?->format('Y-m-d')) }}" required>
          @error('appointment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label for="appointment_time" class="form-label fw-semibold">Time (HH:MM)</label>
          <input type="time" id="appointment_time" name="appointment_time" class="form-control @error('appointment_time') is-invalid @enderror" value="{{ old('appointment_time', substr($appointment->getRawOriginal('appointment_time'),0,5)) }}" required>
          @error('appointment_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
        <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-2"></i>Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
