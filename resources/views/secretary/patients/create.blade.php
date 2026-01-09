@extends('layouts.app')

@section('title','Register Patient Account')

@section('content')
<div class="container py-4">
  @include('partials.alerts')

  @if(session('generated_password'))
    <div class="alert alert-warning d-flex align-items-center" role="alert">
      <i class="bi bi-key me-2"></i>
      <div>
        <strong>Temporary password:</strong>
        <span class="fw-semibold">{{ session('generated_password') }}</span>
        <div class="small text-muted">Share this with the patient securely since no email was sent.</div>
      </div>
    </div>
  @endif

  <div class="card medical-card shadow-sm">
    <div class="card-header bg-primary text-white d-flex align-items-center">
      <i class="bi bi-person-plus me-2"></i>
      <h5 class="mb-0">Manual Patient Registration</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('secretary.patients.store') }}">
        @csrf

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-person me-1"></i>First Name</label>
            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-person me-1"></i>Last Name</label>
            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-envelope me-1"></i>Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-telephone me-1"></i>Phone</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="Optional">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Address</label>
          <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" placeholder="Optional">{{ old('address') }}</textarea>
          @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-key me-1"></i>Password (leave blank to auto-generate)</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label"><i class="bi bi-key-fill me-1"></i>Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <button class="btn btn-primary"><i class="bi bi-save me-2"></i>Register Patient</button>
          <a href="{{ route('secretary.patients.index') }}" class="btn btn-outline-primary"><i class="bi bi-people me-2"></i>Patients List</a>
          <a href="{{ route('secretary.dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back to Dashboard</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
