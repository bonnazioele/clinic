@extends('layouts.app')

@section('title', 'Confirm Password')

@section('content')
@include('auth._auth-panel-styles')

<div class="auth-panel-page">
  <section class="auth-panel">
    <div class="auth-panel-hero">
      <div class="auth-panel-title-wrap">
        <span class="auth-panel-icon">
          <i class="bi bi-shield-lock"></i>
        </span>

        <div>
          <h1 class="auth-panel-title h3">Confirm Password</h1>
          <p class="auth-panel-subtitle">Verify your identity before continuing.</p>
        </div>
      </div>
    </div>

    <div class="auth-panel-body">
      <div class="auth-panel-note mb-3">
        Please confirm your password before continuing.
      </div>

      <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#password" aria-label="Show password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary auth-panel-submit w-100" data-loading-text="Confirming...">
          <i class="bi bi-check2-circle me-2"></i>
          Confirm Password
        </button>

        @if (Route::has('password.request'))
          <div class="text-center mt-3">
            <a class="text-primary fw-bold" href="{{ route('password.request') }}">Forgot password?</a>
          </div>
        @endif
      </form>
    </div>
  </section>
</div>
@endsection
