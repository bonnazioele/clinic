@extends('layouts.app')

@section('title', 'Set Your Password')

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
          <h1 class="auth-panel-title h3">Set Your Password</h1>
          <p class="auth-panel-subtitle">Secure your new clinic account before continuing.</p>
        </div>
      </div>
    </div>

    <div class="auth-panel-body">
      <div class="auth-panel-note mb-3">
        For security, create a new password before accessing the system for the first time.
      </div>

      <form method="POST" action="{{ route('secretary.auth.password.force.update') }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
          <label for="password" class="form-label">New Password</label>
          <div class="input-group">
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#password" aria-label="Show password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
          <label for="password_confirmation" class="form-label">Confirm Password</label>
          <div class="input-group">
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#password_confirmation" aria-label="Show password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary auth-panel-submit w-100" data-loading-text="Updating...">
          <i class="bi bi-check2-circle me-2"></i>
          Update Password
        </button>
      </form>
    </div>
  </section>
</div>
@endsection
