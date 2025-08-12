@extends('layouts.app')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center align-items-center min-vh-100">

    <div class="col-md-8">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-5">

          <h4 class="fw-bold text-primary mb-3">
            <i class="bi bi-envelope-check-fill me-2"></i>Verify Your Email Address
          </h4>

          @if (session('resent'))
            <div class="alert alert-success rounded-3">
              A new verification link has been sent to your email.
            </div>
          @endif

          <p class="mb-2 text-muted">
            Before proceeding, please check your inbox for the verification link we sent.
          </p>

          <p class="text-muted mb-4">
            Didn't receive the email?
            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
              @csrf
              <button type="submit" class="btn btn-link p-0 m-0 align-baseline text-primary fw-semibold text-decoration-none">
                Click here to request another.
              </button>
            </form>
          </p>

          <a href="{{ route('logout') }}"
             onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
             class="btn btn-outline-secondary rounded-pill">
            Back to Login
          </a>

          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
          </form>

        </div>
      </div>
    </div>

  </div>
</div>
@endsection
