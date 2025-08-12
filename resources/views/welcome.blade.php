@extends('layouts.app')

@section('title', 'Welcome to CliniQ')

@section('content')
<div class="container py-5">
  <div class="row align-items-center">
    <div class="col-lg-6 mb-5 mb-lg-0">
      <h1 class="display-4 fw-bold text-primary mb-4">
        Your Health, Our Priority
      </h1>
      <p class="lead text-muted mb-4">
        CliniQ connects you with trusted healthcare providers, making it easy to book appointments,
        manage your health records, and access quality medical care when you need it most.
      </p>
      <div class="d-flex gap-3 flex-wrap">
        <a href="{{ route('clinics.index') }}" class="btn btn-primary btn-lg rounded-pill">
          <i class="bi bi-search me-2"></i>Find Clinics
        </a>
        @guest
          <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg rounded-pill">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
          </a>
        @else
          <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-lg rounded-pill">
            <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
          </a>
        @endguest
      </div>
    </div>
    <div class="col-lg-6 text-center">
      <img src="{{ asset('images/healthcare-illustration.svg') }}" alt="Healthcare" class="img-fluid" style="max-height: 400px;">
    </div>
  </div>

  {{-- Features Section --}}
  <div class="row mt-5 pt-5">
    <div class="col-12 text-center mb-5">
      <h2 class="fw-bold text-primary">Why Choose CliniQ?</h2>
      <p class="text-muted">Discover the benefits of our comprehensive healthcare platform</p>
    </div>

    <div class="col-md-4 mb-4">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center p-4">
          <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
            <i class="bi bi-calendar-check fs-2"></i>
          </div>
          <h5 class="fw-semibold">Easy Appointment Booking</h5>
          <p class="text-muted">Book appointments with your preferred healthcare providers in just a few clicks.</p>
        </div>
      </div>
    </div>

    <div class="col-md-4 mb-4">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center p-4">
          <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
            <i class="bi bi-hospital fs-2"></i>
          </div>
          <h5 class="fw-semibold">Verified Healthcare Providers</h5>
          <p class="text-muted">Access a network of licensed and verified healthcare professionals and facilities.</p>
        </div>
      </div>
    </div>

    <div class="col-md-4 mb-4">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center p-4">
          <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
            <i class="bi bi-shield-check fs-2"></i>
          </div>
          <h5 class="fw-semibold">Secure & Private</h5>
          <p class="text-muted">Your health information is protected with industry-standard security measures.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- CTA Section --}}
  <div class="row mt-5 pt-5">
    <div class="col-12">
      <div class="card border-0 bg-primary text-white rounded-4">
        <div class="card-body text-center p-5">
          <h3 class="fw-bold mb-3">Ready to Get Started?</h3>
          <p class="mb-4">Join thousands of users who trust CliniQ for their healthcare needs.</p>
          @guest
            <a href="{{ route('register') }}" class="btn btn-light btn-lg rounded-pill">
              <i class="bi bi-person-plus me-2"></i>Create Account
            </a>
          @else
            <a href="{{ route('appointments.create') }}" class="btn btn-light btn-lg rounded-pill">
              <i class="bi bi-calendar-plus me-2"></i>Book Appointment
            </a>
          @endguest
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
