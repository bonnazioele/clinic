@extends('layouts.app')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center align-items-center min-vh-100">

    <div class="col-md-8">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-5">

          <h4 class="fw-bold text-primary mb-3">
            <i class="bi bi-lock-fill me-2"></i>Forgot Your Password?
          </h4>

          @if (session('status'))
            <div class="alert alert-success rounded-3">
              {{ session('status') }}
            </div>
          @endif

          <p class="text-muted mb-4">
            Enter your email address below and we'll send you a link to reset your password.
          </p>

          <form method="POST" action="{{ route('password.email') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-4">
              <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
              <input id="email" type="email"
                     class="form-control @error('email') is-invalid @enderror"
                     name="email" value="{{ old('email') }}" required autofocus>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
              Send Reset Link
            </button>
          </form>

        </div>
      </div>
    </div>

  </div>
</div>
@endsection
