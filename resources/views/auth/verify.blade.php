@extends('layouts.app')

@section('title', 'Verify Email')

@section('content')
@include('auth._auth-panel-styles')

<div class="auth-panel-page">
  <section class="auth-panel">
    <div class="auth-panel-hero">
      <div class="auth-panel-title-wrap">
        <span class="auth-panel-icon">
          <i class="bi bi-envelope-check"></i>
        </span>

        <div>
          <h1 class="auth-panel-title h3">Verify Your Email</h1>
          <p class="auth-panel-subtitle">Check your inbox to activate your CliniQ account.</p>
        </div>
      </div>
    </div>

    <div class="auth-panel-body">
      @if (session('resent'))
        <div class="alert alert-success rounded-4 border-0">
          <i class="bi bi-check2-circle me-2"></i>
          A fresh verification link has been sent to your email address.
        </div>
      @endif

      <div class="auth-panel-note mb-3">
        Before proceeding, please check your email for a verification link.
      </div>

      <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="btn btn-primary auth-panel-submit w-100" data-loading-text="Sending...">
          <i class="bi bi-send me-2"></i>
          Send Another Verification Link
        </button>
      </form>
    </div>
  </section>
</div>
@endsection
