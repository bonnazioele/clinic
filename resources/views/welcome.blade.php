@extends('layouts.app')

@section('title','Welcome to CliniQ')

@section('content')
<!-- Hero Section -->
<div class="medical-gradient text-white py-5 mb-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <h1 class="display-4 fw-bold mb-4">
          <i class="bi bi-heart-pulse-fill me-3"></i>Welcome to CliniQ
        </h1>
        <p class="lead mb-4">
          Your trusted partner in healthcare management. Connect with clinics, book appointments, and manage your health journey with ease.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <a href="{{ route('clinics.index') }}" class="btn btn-light btn-lg">
            <i class="bi bi-building me-2"></i>Find a Clinic
          </a>
          @guest
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">
              <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </a>
            <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">
              <i class="bi bi-person-plus me-2"></i>Register
            </a>
          @endguest
        </div>
      </div>
      <div class="col-lg-6 text-center">
        <i class="bi bi-heart-pulse-fill" style="font-size: 15rem; opacity: 0.3;"></i>
      </div>
    </div>
  </div>
</div>

<!-- Features Section -->
<div class="container mb-5">
  <div class="row text-center mb-5">
    <div class="col-12">
      <h2 class="fw-bold text-primary mb-3">
        <i class="bi bi-star medical-icon me-2"></i>Why Choose CliniQ?
      </h2>
      <p class="lead text-muted">Experience healthcare management like never before</p>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="medical-card h-100 p-4 text-center">
        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
             style="width: 80px; height: 80px;">
          <i class="bi bi-building text-white" style="font-size: 2rem;"></i>
        </div>
        <h5 class="fw-bold mb-3">Find Clinics Easily</h5>
        <p class="text-muted mb-0">
          Discover healthcare facilities near you with our interactive map and comprehensive search features.
        </p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="medical-card h-100 p-4 text-center">
        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
             style="width: 80px; height: 80px;">
          <i class="bi bi-calendar-check text-white" style="font-size: 2rem;"></i>
        </div>
        <h5 class="fw-bold mb-3">Book Appointments</h5>
        <p class="text-muted mb-0">
          Schedule appointments with just a few clicks. Manage your healthcare schedule efficiently.
        </p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="medical-card h-100 p-4 text-center">
        <div class="bg-info rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
             style="width: 80px; height: 80px;">
          <i class="bi bi-people text-white" style="font-size: 2rem;"></i>
        </div>
        <h5 class="fw-bold mb-3">Queue Management</h5>
        <p class="text-muted mb-0">
          Skip the wait with our smart queue system. Get real-time updates on your appointment status.
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Statistics Section -->
<div class="bg-light py-5 mb-5">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-3 mb-4">
        <div class="dashboard-stat text-primary">{{ \App\Models\Clinic::count() }}</div>
        <h6 class="text-muted">Healthcare Facilities</h6>
      </div>
      <div class="col-md-3 mb-4">
        <div class="dashboard-stat text-success">{{ \App\Models\Service::count() }}</div>
        <h6 class="text-muted">Medical Services</h6>
      </div>
      <div class="col-md-3 mb-4">
        <div class="dashboard-stat text-info">{{ \App\Models\User::where('is_doctor', true)->count() }}</div>
        <h6 class="text-muted">Healthcare Professionals</h6>
      </div>
      <div class="col-md-3 mb-4">
        <div class="dashboard-stat text-warning">{{ \App\Models\Appointment::count() }}</div>
        <h6 class="text-muted">Appointments Booked</h6>
      </div>
    </div>
  </div>
</div>

<!-- How It Works Section -->
<div class="container mb-5">
  <div class="row text-center mb-5">
    <div class="col-12">
      <h2 class="fw-bold text-primary mb-3">
        <i class="bi bi-gear medical-icon me-2"></i>How It Works
      </h2>
      <p class="lead text-muted">Simple steps to better healthcare</p>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-3">
      <div class="text-center">
        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
             style="width: 60px; height: 60px;">
          <span class="text-white fw-bold fs-4">1</span>
        </div>
        <h6 class="fw-bold">Search</h6>
        <p class="text-muted small">Find clinics and services in your area</p>
      </div>
    </div>

    <div class="col-md-3">
      <div class="text-center">
        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
             style="width: 60px; height: 60px;">
          <span class="text-white fw-bold fs-4">2</span>
        </div>
        <h6 class="fw-bold">Book</h6>
        <p class="text-muted small">Schedule your appointment online</p>
      </div>
    </div>

    <div class="col-md-3">
      <div class="text-center">
        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
             style="width: 60px; height: 60px;">
          <span class="text-white fw-bold fs-4">3</span>
        </div>
        <h6 class="fw-bold">Manage</h6>
        <p class="text-muted small">Track and manage your appointments</p>
      </div>
    </div>

    <div class="col-md-3">
      <div class="text-center">
        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
             style="width: 60px; height: 60px;">
          <span class="text-white fw-bold fs-4">4</span>
        </div>
        <h6 class="fw-bold">Visit</h6>
        <p class="text-muted small">Get the care you need</p>
      </div>
    </div>
  </div>
</div>

<!-- User Types Section -->
<div class="bg-light py-5 mb-5">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-12">
        <h2 class="fw-bold text-primary mb-3">
          <i class="bi bi-people medical-icon me-2"></i>For Everyone
        </h2>
        <p class="lead text-muted">Tailored experiences for different user types</p>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="medical-card h-100 p-4 text-center">
          <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
               style="width: 80px; height: 80px;">
            <i class="bi bi-person-heart text-white" style="font-size: 2rem;"></i>
          </div>
          <h5 class="fw-bold mb-3">Patients</h5>
          <ul class="list-unstyled text-start text-muted">
            <li><i class="bi bi-check-circle text-success me-2"></i>Find clinics and services</li>
            <li><i class="bi bi-check-circle text-success me-2"></i>Book appointments online</li>
            <li><i class="bi bi-check-circle text-success me-2"></i>Track appointment status</li>
            <li><i class="bi bi-check-circle text-success me-2"></i>Manage health records</li>
          </ul>
        </div>
      </div>

      <div class="col-md-4">
        <div class="medical-card h-100 p-4 text-center">
          <div class="bg-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
               style="width: 80px; height: 80px;">
            <i class="bi bi-person-badge text-white" style="font-size: 2rem;"></i>
          </div>
          <h5 class="fw-bold mb-3">Healthcare Staff</h5>
          <ul class="list-unstyled text-start text-muted">
            <li><i class="bi bi-check-circle text-success me-2"></i>Manage appointments</li>
            <li><i class="bi bi-check-circle text-success me-2"></i>Handle patient queues</li>
            <li><i class="bi bi-check-circle text-success me-2"></i>Coordinate with doctors</li>
            <li><i class="bi bi-check-circle text-success me-2"></i>Track patient flow</li>
          </ul>
        </div>
      </div>

      <div class="col-md-4">
        <div class="medical-card h-100 p-4 text-center">
          <div class="bg-info rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
               style="width: 80px; height: 80px;">
            <i class="bi bi-shield-check text-white" style="font-size: 2rem;"></i>
          </div>
          <h5 class="fw-bold mb-3">Administrators</h5>
          <ul class="list-unstyled text-start text-muted">
            <li><i class="bi bi-check-circle text-success me-2"></i>Manage clinics and services</li>
            <li><i class="bi bi-check-circle text-success me-2"></i>Monitor system performance</li>
            <li><i class="bi bi-check-circle text-success me-2"></i>Generate reports</li>
            <li><i class="bi bi-check-circle text-success me-2"></i>System configuration</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Call to Action -->
<div class="container text-center mb-5">
  <div class="medical-card p-5">
    <h2 class="fw-bold text-primary mb-3">Ready to Get Started?</h2>
    <p class="lead text-muted mb-4">
      Join thousands of users who trust CliniQ for their healthcare management needs.
    </p>
    <div class="d-flex flex-wrap justify-content-center gap-3">
      <a href="{{ route('clinics.index') }}" class="btn btn-primary btn-lg">
        <i class="bi bi-building me-2"></i>Explore Clinics
      </a>
      @guest
        <a href="{{ route('register') }}" class="btn btn-success btn-lg">
          <i class="bi bi-person-plus me-2"></i>Create Account
        </a>
        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">
          <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
        </a>
      @endguest
    </div>
  </div>
</div>
@endsection
