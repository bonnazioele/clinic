{{-- resources/views/secretary/doctors/create.blade.php --}}
@extends('layouts.app')

@section('title','Add Doctor')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <h3>Add New Doctor</h3>
  <form method="POST" action="{{ route('secretary.doctors.store') }}">
    @csrf

    <!-- Name -->
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name') }}" required>
      @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Email -->
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email"
             class="form-control @error('email') is-invalid @enderror"
             value="{{ old('email') }}" required>
      @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Phone -->
    <div class="mb-3">
      <label class="form-label">Phone</label>
      <input type="text" name="phone"
             class="form-control @error('phone') is-invalid @enderror"
             value="{{ old('phone') }}">
      @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Address -->
    <div class="mb-3">
      <label class="form-label">Address</label>
      <textarea name="address"
                class="form-control @error('address') is-invalid @enderror"
                rows="2">{{ old('address') }}</textarea>
      @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Password & Confirmation -->
    <div class="row g-3 mb-3">
      <div class="col">
        <label class="form-label">Password</label>
        <div class="input-group">
          <input type="password" id="doctor_password" name="password"
                 class="form-control @error('password') is-invalid @enderror"
                 required>
          <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#doctor_password" aria-label="Show password">
            <i class="bi bi-eye"></i>
          </button>
        </div>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
          <input type="password" id="doctor_password_confirmation" name="password_confirmation"
                 class="form-control" required>
          <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#doctor_password_confirmation" aria-label="Show password">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Clinics Multi-Select -->
    <div class="mb-3">
      <label class="form-label">Assign to Clinics</label>
      <select name="clinic_ids[]"
              class="form-select @error('clinic_ids') is-invalid @enderror"
              multiple>
        @foreach($clinics as $clinic)
          <option value="{{ $clinic->id }}"
            @selected(in_array($clinic->id, old('clinic_ids', [])))>
            {{ $clinic->name }}
          </option>
        @endforeach
      </select>
      @error('clinic_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
  <div class="form-text">Tip: Hold Ctrl (Cmd on Mac) to select multiple clinics.</div>
    </div>

    <!-- Services Multi-Select -->
    <div class="mb-3">
      <label class="form-label">Assign to Services</label>
      <select name="service_ids[]"
              class="form-select @error('service_ids') is-invalid @enderror"
              multiple>
        @foreach($services as $service)
          <option value="{{ $service->id }}"
            @selected(in_array($service->id, old('service_ids', [])))>
            {{ $service->name }}
          </option>
        @endforeach
      </select>
      @error('service_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
  <div class="form-text">Tip: Hold Ctrl (Cmd on Mac) to select multiple services.</div>
    </div>

    <!-- Form Actions -->
    <button class="btn btn-primary">Add Doctor</button>
    <a href="{{ route('secretary.doctors.index') }}" class="btn btn-link">Cancel</a>
  </form>
</div>
@endsection
