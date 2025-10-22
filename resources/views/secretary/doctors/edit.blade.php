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
    
    <div class="mb-3">
      <label for="doctor_name" class="form-label"><i class="bi bi-person me-1"></i>Name</label>
      <input id="doctor_name" type="text" name="name"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name', $doctor->name) }}" required>
      @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    
    <div class="mb-3">
      <label for="doctor_email" class="form-label"><i class="bi bi-envelope me-1"></i>Email</label>
      <input id="doctor_email" type="email" name="email"
             class="form-control @error('email') is-invalid @enderror"
             value="{{ old('email', $doctor->email) }}" required>
      @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    
    <div class="mb-3">
      <label for="doctor_phone" class="form-label"><i class="bi bi-telephone me-1"></i>Phone</label>
      <input id="doctor_phone" type="text" name="phone"
             class="form-control @error('phone') is-invalid @enderror"
             value="{{ old('phone', $doctor->phone) }}">
      @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    
    <div class="mb-3">
      <label for="doctor_address" class="form-label"><i class="bi bi-geo-alt me-1"></i>Address</label>
      <textarea id="doctor_address" name="address"
                class="form-control @error('address') is-invalid @enderror"
                rows="2">{{ old('address', $doctor->address) }}</textarea>
      @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    
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


    
    <div class="mb-3">
      <label for="doctor_services" class="form-label"><i class="bi bi-scissors me-1"></i>Services</label>
      <select id="doctor_services" name="service_ids[]"
              class="form-select enhanced-multiselect @error('service_ids') is-invalid @enderror"
              multiple data-placeholder="Select one or more services">
        @foreach($services as $s)
          <option value="{{ $s->id }}" @selected(in_array($s->id, old('service_ids', $doctor->services->pluck('id')->toArray())))>{{ $s->name }}</option>
        @endforeach
      </select>
      @error('service_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
      <div class="form-text">Select all services this doctor can perform. Start typing to filter.</div>
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
