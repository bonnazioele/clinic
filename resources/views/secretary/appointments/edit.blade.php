@extends('layouts.app')

@section('title','Manage Appointment #'.$appointment->id)

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="card medical-card shadow-sm">
    <div class="card-header bg-primary text-white d-flex align-items-center">
      <i class="bi bi-calendar-check me-2"></i>
      <h5 class="mb-0">Manage Appointment #{{ $appointment->id }}</h5>
    </div>
    <div class="card-body">
  <form method="POST"
        action="{{ route('secretary.appointments.update',$appointment) }}">
    @csrf @method('PATCH')

    {{-- Clinic --}}
    <div class="mb-3">
      <label class="form-label">Clinic</label>
      <select name="clinic_id"
              class="form-select @error('clinic_id') is-invalid @enderror">
        @foreach($clinics as $c)
          <option value="{{ $c->id }}"
            @selected(old('clinic_id',$appointment->clinic_id)==$c->id)>
            {{ $c->name }}
          </option>
        @endforeach
      </select>
      @error('clinic_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Service --}}
    <div class="mb-3">
      <label class="form-label">Service</label>
      <select name="service_id"
              class="form-select @error('service_id') is-invalid @enderror">
        @foreach(\App\Models\Service::all() as $s)
          <option value="{{ $s->id }}"
            @selected(old('service_id',$appointment->service_id)==$s->id)>
            {{ $s->name }}
          </option>
        @endforeach
      </select>
      @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Doctor --}}
    <div class="mb-3">
      <label class="form-label">Assign Doctor</label>
      <select name="doctor_id"
              class="form-select @error('doctor_id') is-invalid @enderror">
        <option value="">— Unassigned —</option>
        @foreach($doctors as $d)
          <option value="{{ $d->id }}"
            @selected(old('doctor_id',$appointment->doctor_id)==$d->id)>
            {{ $d->name }}
          </option>
        @endforeach
      </select>
      @error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Date/Time --}}
    <div class="row g-3 mb-3">
      <div class="col">
        <label class="form-label">Date</label>
        <input type="date" name="appointment_date"
               class="form-control @error('appointment_date') is-invalid @enderror"
               value="{{ old('appointment_date',$appointment->appointment_date) }}">
        @error('appointment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col">
        <label class="form-label">Time</label>
        <input type="time" name="appointment_time"
               class="form-control @error('appointment_time') is-invalid @enderror"
               value="{{ old('appointment_time',$appointment->appointment_time) }}">
        @error('appointment_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    {{-- Status --}}
    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status"
              class="form-select @error('status') is-invalid @enderror">
        @foreach(['scheduled','completed','cancelled'] as $st)
          <option value="{{ $st }}"
            @selected(old('status',$appointment->status)==$st)>
            {{ ucfirst($st) }}
          </option>
        @endforeach
      </select>
      @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
      <a href="{{ route('secretary.appointments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
    </div>
  </form>
    </div>
  </div>
</div>
@endsection
