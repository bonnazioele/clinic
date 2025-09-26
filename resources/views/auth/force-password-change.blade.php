@extends('layouts.app')

@section('title', 'Set Your Password')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Action Required: Set Your Password</h5>
        </div>
        <div class="card-body">
          <p class="text-muted">For security, you must set a new password before accessing the system for the first time.</p>
          <form method="POST" action="{{ route('secretary.auth.password.force.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
              <label for="password" class="form-label">New Password</label>
              <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="password_confirmation" class="form-label">Confirm Password</label>
              <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Update Password</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
