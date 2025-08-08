@extends('layouts.secretary')

@section('title', 'Secretary Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="clinic-name">
                {{ session('current_clinic_name', 'Clinic Name') }}
</h2>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <!-- Quick Actions Dropdown -->
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="quickActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-lightning-fill me-2"></i>
                    Quick Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="quickActionsDropdown">
                    <li>
                        <a class="dropdown-item" href="{{ route('secretary.appointments.index') }}">
                            <i class="bi bi-calendar-plus me-2"></i>
                            Add Appointments
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('secretary.doctors.index') }}">
                            <i class="bi bi-people me-2"></i>
                            Add Walk-in Patients
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('secretary.doctors.create') }}">
                            <i class="bi bi-person-plus me-2"></i>
                            Create Patient Account
                        </a>
                    </li>
                    <hr class="dropdown-divider">
                </ul>
            </div>
        </div>
    </div>

    <!-- Dashboard Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="text-primary mb-2">
                        <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="card-title mb-1">Today's Appointments</h6>
                    <h4 class="text-primary mb-0">{{ $todayAppointments ?? '0' }}</h4>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="text-info mb-2">
                        <i class="bi bi-people" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="card-title mb-1">Queue Status</h6>
                    <h4 class="text-info mb-0">{{ $queueCount ?? '0' }}</h4>
                    <small class="text-muted">Waiting</small>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="text-success mb-2">
                        <i class="bi bi-person-badge" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="card-title mb-1">Active Doctors</h6>
                    <h4 class="text-success mb-0">{{ $activeDoctors ?? '0' }}</h4>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="text-warning mb-2">
                        <i class="bi bi-gear" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="card-title mb-1">Services</h6>
                    <h4 class="text-warning mb-0">{{ $servicesCount ?? '0' }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Dashboard Content -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        Dashboard Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="bi bi-speedometer2 display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Dashboard Analytics Coming Soon</h5>
                        <p class="text-muted">Advanced analytics and reporting features will be available here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

