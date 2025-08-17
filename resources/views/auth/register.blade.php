@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="container py-5">
  @include('partials.alerts', ['toastOffsetTop' => '6rem', 'toastOffsetRight' => '1.25rem'])
  <div class="row justify-content-center align-items-center min-vh-100">

    {{-- Left: Welcome Text --}}
    <div class="col-md-6 mb-5 mb-md-0">
      <h1 class="fw-bold text-primary display-5">Join CliniQ Today!</h1>
      <p class="text-muted fs-6 mt-3">
        Create your account and start managing your healthcare journey.
      </p>
    </div>

    {{-- Right: Registration Form --}}
    <div class="col-md-6">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
          <h5 class="fw-semibold text-primary mb-4">
            <i class="bi bi-person-plus-fill me-2"></i>Create Account
          </h5>

          <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- First Name --}}
            <div class="mb-3">
              <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
              <input id="first_name" type="text"
                     class="form-control @error('first_name') is-invalid @enderror"
                     name="first_name" value="{{ old('first_name') }}" required autofocus>
              @error('first_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Last Name --}}
            <div class="mb-3">
              <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
              <input id="last_name" type="text"
                     class="form-control @error('last_name') is-invalid @enderror"
                     name="last_name" value="{{ old('last_name') }}" required>
              @error('last_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
              <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
              <input id="email" type="email"
                     class="form-control @error('email') is-invalid @enderror"
                     name="email" value="{{ old('email') }}" required>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Phone --}}
            <div class="mb-3">
              <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
              <input id="phone" type="tel"
                     class="form-control @error('phone') is-invalid @enderror"
                     name="phone" value="{{ old('phone') }}" required
                     placeholder="e.g. 09123456789">
              @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Birthdate --}}
            <div class="mb-3">
              <label for="birthdate" class="form-label">Birth Date <span class="text-danger">*</span></label>
              <input id="birthdate" type="date"
                     class="form-control @error('birthdate') is-invalid @enderror"
                     name="birthdate" value="{{ old('birthdate') }}" required>
              @error('birthdate')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Address --}}
            <div class="mb-3">
              <label for="address" class="form-label">Address <small class="text-muted">(optional)</small></label>
              <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror"
                        rows="2" placeholder="e.g. 123 Main St, City">{{ old('address') }}</textarea>
              @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
              <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <input id="password" type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       name="password" required>
                <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#password" aria-label="Show password">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-4">
              <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <input id="password_confirmation" type="password"
                       class="form-control" name="password_confirmation" required>
                <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#password_confirmation" aria-label="Show password">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 mb-3">
              Create Account
            </button>

            <div class="text-center">
              <small class="text-muted">
                Already have an account?
                <a href="{{ route('login') }}" class="text-primary text-decoration-none">Sign in</a>
              </small>
            </div>
          </form>

        </div>
      </div>
    </div>

  </div>
</div>
@endsection
