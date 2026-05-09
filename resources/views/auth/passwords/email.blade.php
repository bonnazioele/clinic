@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
@include('auth._auth-panel-styles')

<div class="auth-panel-page">
  <section class="auth-panel">
    <div class="auth-panel-hero">
      <div class="auth-panel-title-wrap">
        <span class="auth-panel-icon">
          <i class="bi bi-key"></i>
        </span>

        <div>
          <h1 class="auth-panel-title h3">Reset Password</h1>
          <p class="auth-panel-subtitle">Enter your email and we will send a reset link.</p>
        </div>
      </div>
    </div>

    <div class="auth-panel-body">
      @if (session('status'))
        <div class="alert alert-success rounded-4 border-0">
          <i class="bi bi-check2-circle me-2"></i>
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
          <label for="email" class="form-label">Email Address</label>
          <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
          @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary auth-panel-submit w-100" data-loading-text="Sending...">
          <i class="bi bi-send me-2"></i>
          Send Reset Link
        </button>
      </form>
    </div>
  </section>
</div>
@endsection
