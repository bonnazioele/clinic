@extends('layouts.app')

@section('title', 'Edit Appointment')

@section('content')
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom-0">
          <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-pencil-square me-2"></i>Edit Appointment
          </h4>
        </div>
        <div class="card-body p-4">

          @include('partials.alerts')

          <form method="POST" action="{{ route('secretary.appointments.update', $appointment) }}">
            @csrf
            @method('PUT')

            {{-- Patient Info --}}
            <div class="mb-4">
              <h6 class="fw-semibold text-muted mb-3">Patient Information</h6>
              <div class="row">
                <div class="col-md-6">
                  <label class="form-label">Patient Name</label>
                  <input type="text" class="form-control" value="{{ $appointment->user->first_name }} {{ $appointment->user->last_name }}" readonly>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" value="{{ $appointment->user->email }}" readonly>
                </div>
              </div>
            </div>

            {{-- Appointment Details --}}
            <div class="mb-4">
              <h6 class="fw-semibold text-muted mb-3">Appointment Details</h6>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="clinic_id" class="form-label">Clinic <span class="text-danger">*</span></label>
                  <select class="form-select @error('clinic_id') is-invalid @enderror" id="clinic_id" name="clinic_id" required>
                    <option value="">Select Clinic</option>
                    @foreach($clinics as $clinic)
                      <option value="{{ $clinic->id }}" @selected(old('clinic_id', $appointment->clinic_id) == $clinic->id)>
                        {{ $clinic->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('clinic_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label for="service_id" class="form-label">Service <span class="text-danger">*</span></label>
                  <select class="form-select @error('service_id') is-invalid @enderror" id="service_id" name="service_id" required>
                    <option value="">Select Service</option>
                    @foreach(\App\Models\Service::all() as $service)
                      <option value="{{ $service->id }}" @selected(old('service_id', $appointment->service_id) == $service->id)>
                        {{ $service->service_name }}
                      </option>
                    @endforeach
                  </select>
                  @error('service_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="doctor_id" class="form-label">Doctor</label>
                  <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id">
                    <option value="">Select Doctor</option>
                    @foreach($doctors as $doctor)
                      <option value="{{ $doctor->id }}" @selected(old('doctor_id', $appointment->doctor_id) == $doctor->id)>
                        {{ $doctor->first_name }} {{ $doctor->last_name }}
                      </option>
                    @endforeach
                  </select>
                  @error('doctor_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                  <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="scheduled" @selected(old('status', $appointment->status) == 'scheduled')>Scheduled</option>
                    <option value="completed" @selected(old('status', $appointment->status) == 'completed')>Completed</option>
                    <option value="cancelled" @selected(old('status', $appointment->status) == 'cancelled')>Cancelled</option>
                  </select>
                  @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="appointment_date" class="form-label">Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control @error('appointment_date') is-invalid @enderror"
                         id="appointment_date" name="appointment_date"
                         value="{{ old('appointment_date', $appointment->appointment_date) }}" required>
                  @error('appointment_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label for="appointment_time" class="form-label">Time <span class="text-danger">*</span></label>
                  <input type="time" class="form-control @error('appointment_time') is-invalid @enderror"
                         id="appointment_time" name="appointment_time"
                         value="{{ old('appointment_time', $appointment->appointment_time) }}" required>
                  @error('appointment_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary rounded-pill">
                <i class="bi bi-check-circle me-2"></i>Update Appointment
              </button>
              <a href="{{ route('secretary.appointments.index') }}" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-2"></i>Back to List
              </a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
