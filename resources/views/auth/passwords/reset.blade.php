@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
@include('auth._auth-panel-styles')

<div class="auth-panel-page">
  <section class="auth-panel">
    <div class="auth-panel-hero">
      <div class="auth-panel-title-wrap">
        <span class="auth-panel-icon">
          <i class="bi bi-lock"></i>
        </span>

        <div>
          <h1 class="auth-panel-title h3">Create New Password</h1>
          <p class="auth-panel-subtitle">Choose a fresh password for your account.</p>
        </div>
      </div>
    </div>

    <div class="auth-panel-body">
      <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
          @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#password" aria-label="Show password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
          <label for="password-confirm" class="form-label">Confirm Password</label>
          <div class="input-group">
            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#password-confirm" aria-label="Show password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary auth-panel-submit w-100" data-loading-text="Resetting...">
          <i class="bi bi-check2-circle me-2"></i>
          Reset Password
        </button>
      </form>
    </div>
  </section>
</div>
@endsection
