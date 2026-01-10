@extends('layouts.app')

@section('title', 'Edit Patient')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  <div class="medical-card p-4 mb-4">
    <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center">
      <div>
        <h1 class="h4 fw-bold text-primary mb-1">
          <i class="bi bi-person-gear me-2"></i>Edit Patient
        </h1>
        <div class="text-muted">{{ $patient->name }} &middot; Account ID #{{ $patient->id }}</div>
      </div>
      <a href="{{ route('secretary.patients.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Patients
      </a>
    </div>
  </div>

  <div class="medical-card p-4">
    <form method="POST" action="{{ route('secretary.patients.update', $patient) }}" class="row g-3">
      @csrf
      @method('PUT')
      <div class="col-md-4">
        <label class="form-label fw-semibold">First Name<span class="text-danger">*</span></label>
        <input type="text" name="first_name" value="{{ old('first_name', $patient->first_name) }}" class="form-control" required>
        @error('first_name')
          <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Last Name<span class="text-danger">*</span></label>
        <input type="text" name="last_name" value="{{ old('last_name', $patient->last_name) }}" class="form-control" required>
        @error('last_name')
          <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Email Address<span class="text-danger">*</span></label>
        <input type="email" name="email" value="{{ old('email', $patient->email) }}" class="form-control" required>
        @error('email')
          <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Phone Number</label>
        <input type="text" name="phone" value="{{ old('phone', $patient->phone) }}" class="form-control" placeholder="09xxxxxxxxx">
        @error('phone')
          <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold">Address</label>
        <textarea name="address" rows="3" class="form-control" placeholder="Street, barangay, city">{{ old('address', $patient->address) }}</textarea>
        @error('address')
          <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
      </div>
      <div class="col-12 d-flex justify-content-end gap-2">
        <a href="{{ route('secretary.patients.index') }}" class="btn btn-light">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check2-circle me-1"></i>Save Changes
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
