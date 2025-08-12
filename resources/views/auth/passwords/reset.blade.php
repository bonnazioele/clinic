@extends('layouts.app')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center align-items-center min-vh-100">

    <div class="col-md-8">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-5">

          <h4 class="fw-bold text-primary mb-4">
            <i class="bi bi-key-fill me-2"></i>Reset Your Password
          </h4>

          <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            {{-- Email --}}
            <div class="mb-3">
              <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
              <input id="email" type="email"
                     class="form-control @error('email') is-invalid @enderror"
                     name="email" value="{{ $email ?? old('email') }}" required autofocus>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- New Password --}}
            <div class="mb-3">
              <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
              <input id="password" type="password"
                     class="form-control @error('password') is-invalid @enderror"
                     name="password" required>
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-4">
              <label for="password-confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
              <input id="password-confirm" type="password"
                     class="form-control" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
              Reset Password
            </button>

          </form>

        </div>
      </div>
    </div>

  </div>
</div>
@endsection
