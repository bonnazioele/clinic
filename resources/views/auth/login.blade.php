@extends('layouts.app')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center align-items-center min-vh-100">

    {{-- Left: Welcome Text --}}
    <div class="col-md-6 mb-5 mb-md-0">
      <h1 class="fw-bold text-primary display-5">Let's sign in to CliniQ!</h1>
      <p class="text-muted fs-6 mt-3">
        Unlock healthcare and do more in just a minute.
      </p>
    </div>

    {{-- Right: Login Form --}}
    <div class="col-md-6">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
          <h5 class="fw-semibold text-primary mb-4">
            <i class="bi bi-shield-lock-fill me-2"></i>Sign In
          </h5>

          <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
              <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
              <input id="email" type="email"
                     class="form-control @error('email') is-invalid @enderror"
                     name="email" value="{{ old('email') }}" required autofocus>
              @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
              <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
              <input id="password" type="password"
                     class="form-control @error('password') is-invalid @enderror"
                     name="password" required>
              @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Remember Me + Forgot --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                       {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label small text-muted" for="remember">
                  Keep me logged in
                </label>
              </div>

              @if (Route::has('password.request'))
              <a class="small text-decoration-none text-primary" href="{{ route('password.request') }}">
                Forgot your password?
              </a>
              @endif
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
              Sign in
            </button>

            <div class="text-center mt-3">
              <small class="text-muted">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-primary text-decoration-none">Sign up</a>
              </small>
            </div>
          </form>

        </div>
      </div>
    </div>

  </div>
</div>
@endsection
