@extends('layouts.app')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center">
          <div class="display-6 mb-3 text-success">
            <i class="bi bi-check2-circle"></i>
          </div>
          <h3 class="fw-bold mb-2">Application Submitted</h3>
          <p class="text-muted mb-2">We’ve received your clinic registration. Our admins will review it shortly. You’ll get an email once it’s approved.</p>
          <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('login') }}" class="btn btn-primary">
              <i class="bi bi-box-arrow-in-right me-1"></i> Go to Login
            </a>
            <a href="{{ route('welcome') }}" class="btn btn-outline-secondary">Back to Home</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
