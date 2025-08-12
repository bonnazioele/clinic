@extends('layouts.app')

@section('title', 'Edit Doctor')

@section('content')
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom-0">
          <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-pencil-square me-2"></i>Edit Doctor
          </h4>
        </div>
        <div class="card-body p-4">

          @include('partials.alerts')

          <form method="POST" action="{{ route('secretary.doctors.update', $doctor) }}">
            @csrf
            @method('PUT')

            {{-- Basic Information --}}
            <div class="mb-4">
              <h6 class="fw-semibold text-muted mb-3">Basic Information</h6>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                         id="first_name" name="first_name" value="{{ old('first_name', $doctor->first_name) }}" required>
                  @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                         id="last_name" name="last_name" value="{{ old('last_name', $doctor->last_name) }}" required>
                  @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror"
                       id="email" name="email" value="{{ old('email', $doctor->email) }}" required>
                @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="password" class="form-label">New Password</label>
                  <input type="password" class="form-control @error('password') is-invalid @enderror"
                         id="password" name="password">
                  <small class="form-text text-muted">Leave blank to keep current password</small>
                  @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label for="password_confirmation" class="form-label">Confirm Password</label>
                  <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="phone" class="form-label">Phone Number</label>
                  <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                         id="phone" name="phone" value="{{ old('phone', $doctor->phone) }}">
                  @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label for="address" class="form-label">Address</label>
                  <input type="text" class="form-control @error('address') is-invalid @enderror"
                         id="address" name="address" value="{{ old('address', $doctor->address) }}">
                  @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            {{-- Assignments --}}
            <div class="mb-4">
              <h6 class="fw-semibold text-muted mb-3">Assignments</h6>

              <div class="mb-3">
                <label for="clinic_ids" class="form-label">Assigned Clinics</label>
                <select class="form-select @error('clinic_ids') is-invalid @enderror"
                        id="clinic_ids" name="clinic_ids[]" multiple>
                  @foreach($clinics as $clinic)
                    <option value="{{ $clinic->id }}"
                            @selected(in_array($clinic->id, old('clinic_ids', $doctor->clinics->pluck('id')->toArray())))>
                      {{ $clinic->name }}
                    </option>
                  @endforeach
                </select>
                <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple clinics</small>
                @error('clinic_ids')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3">
                <label for="service_ids" class="form-label">Specialties/Services</label>
                <select class="form-select @error('service_ids') is-invalid @enderror"
                        id="service_ids" name="service_ids[]" multiple>
                  @foreach($services as $service)
                    <option value="{{ $service->id }}"
                            @selected(in_array($service->id, old('service_ids', $doctor->services->pluck('id')->toArray())))>
                      {{ $service->service_name }}
                    </option>
                  @endforeach
                </select>
                <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple services</small>
                @error('service_ids')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary rounded-pill">
                <i class="bi bi-check-circle me-2"></i>Update Doctor
              </button>
              <a href="{{ route('secretary.doctors.index') }}" class="btn btn-outline-secondary rounded-pill">
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
