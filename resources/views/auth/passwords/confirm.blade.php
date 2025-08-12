@extends('layouts.app')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center align-items-center min-vh-100">

    <div class="col-md-8">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-5">

          <h4 class="fw-bold text-primary mb-3">
            <i class="bi bi-shield-lock-fill me-2"></i>Confirm Your Password
          </h4>

          <p class="text-muted mb-4">
            For your security, please confirm your password before continuing.
          </p>

          <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            {{-- Password --}}
            <div class="mb-4">
              <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
              <input id="password" type="password"
                     class="form-control @error('password') is-invalid @enderror"
                     name="password" required autocomplete="current-password">
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 mb-3">
              Confirm Password
            </button>

            @if (Route::has('password.request'))
              <div class="text-center">
                <a class="text-decoration-none text-primary small" href="{{ route('password.request') }}">
                  Forgot your password?
                </a>
              </div>
            @endif

          </form>

        </div>
      </div>
    </div>

  </div>
</div>
@endsection
