@extends('layouts.app')
@section('title','Edit Doctor')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="card medical-card shadow-sm">
    <div class="card-header bg-primary text-white d-flex align-items-center">
      <i class="bi bi-person-gear me-2"></i>
      <h5 class="mb-0">Edit Doctor</h5>
    </div>
    <div class="card-body">
  <form method="POST" action="{{ route('secretary.doctors.update',$doctor) }}">
    @csrf @method('PATCH')
    <!-- Name -->
    <div class="mb-3">
      <label class="form-label"><i class="bi bi-person me-1"></i>Name</label>
      <input type="text" name="name"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name', $doctor->name) }}" required>
      @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Email -->
    <div class="mb-3">
      <label class="form-label"><i class="bi bi-envelope me-1"></i>Email</label>
      <input type="email" name="email"
             class="form-control @error('email') is-invalid @enderror"
             value="{{ old('email', $doctor->email) }}" required>
      @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Phone -->
    <div class="mb-3">
      <label class="form-label"><i class="bi bi-telephone me-1"></i>Phone</label>
      <input type="text" name="phone"
             class="form-control @error('phone') is-invalid @enderror"
             value="{{ old('phone', $doctor->phone) }}">
      @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Address -->
    <div class="mb-3">
      <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Address</label>
      <textarea name="address"
                class="form-control @error('address') is-invalid @enderror"
                rows="2">{{ old('address', $doctor->address) }}</textarea>
      @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Optional Password Update -->
    <div class="row g-3 mb-3">
      <div class="col">
  <label class="form-label"><i class="bi bi-key me-1"></i>New Password (optional)</label>
        <div class="input-group">
          <input type="password" id="doctor_password" name="password"
                 class="form-control @error('password') is-invalid @enderror">
          <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#doctor_password" aria-label="Show password">
            <i class="bi bi-eye"></i>
          </button>
        </div>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col">
  <label class="form-label"><i class="bi bi-key-fill me-1"></i>Confirm New Password</label>
        <div class="input-group">
          <input type="password" id="doctor_password_confirmation" name="password_confirmation"
                 class="form-control">
          <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#doctor_password_confirmation" aria-label="Show password">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Clinics -->
    <div class="mb-3">
  <label class="form-label"><i class="bi bi-building me-1"></i>Clinics</label>
  <select name="clinic_ids[]"
      class="form-select enhanced-multiselect @error('clinic_ids') is-invalid @enderror"
              multiple>
        @foreach($clinics as $c)
          <option value="{{ $c->id }}"
            @selected(
              in_array(
                $c->id,
                old('clinic_ids',
                    $doctor->clinics->pluck('id')->toArray()
                )
              )
            )>
            {{ $c->name }}
          </option>
        @endforeach
      </select>
      @error('clinic_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
  <div class="form-text">Tip: Hold Ctrl (Cmd on Mac) to select multiple clinics.</div>
    </div>

    <!-- Services -->
    <div class="mb-3">
  <label class="form-label"><i class="bi bi-scissors me-1"></i>Services</label>
  <select name="service_ids[]"
      class="form-select enhanced-multiselect @error('service_ids') is-invalid @enderror"
              multiple>
        @foreach($services as $s)
          <option value="{{ $s->id }}"
            @selected(
              in_array(
                $s->id,
                old('service_ids',
                    $doctor->services->pluck('id')->toArray()
                )
              )
            )>
            {{ $s->name }}
          </option>
        @endforeach
      </select>
      @error('service_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
  <div class="form-text">Tip: Hold Ctrl (Cmd on Mac) to select multiple services.</div>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
      <a href="{{ route('secretary.doctors.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-2"></i>Cancel</a>
    </div>
  </form>
    </div>
  </div>
</div>
@endsection
