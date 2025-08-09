{{-- resources/views/secretary/doctors/show.blade.php --}}
@extends('layouts.secretary')

@section('title', 'Doctor Profile - ' . $doctor->name)

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">{{ $doctor->name }}</h3>
            <small class="text-muted">Doctor Profile at {{ $clinic->name }}</small>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('secretary.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('secretary.doctors.index') }}">Doctors</a></li>
                <li class="breadcrumb-item active">{{ $doctor->name }}</li>
            </ol>
        </nav>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <x-info-card 
                title="Today's Appointments"
                :value="$todayAppointments"
                icon="bi-calendar-check"
                color="success"
                subtitle="Scheduled for today" />
        </div>
        <div class="col-md-3">
            <x-info-card 
                title="Services Offered"
                :value="$doctorServices->count()"
                icon="bi-stethoscope"
                color="primary"
                subtitle="Available services" />
        </div>
        <div class="col-md-3">
            <x-info-card 
                title="Experience"
                :value="($doctorProfile->years_of_experience ?? 0) . ' years'"
                icon="bi-briefcase"
                color="warning"
                subtitle="Professional experience" />
        </div>
        <div class="col-md-3">
            <x-info-card 
                title="Working Days"
                :value="$doctorSchedules->count()"
                icon="bi-clock"
                color="info"
                subtitle="Days per week" />
        </div>
    </div>

    <div class="row g-4">
        <!-- Personal Information -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-bold">First Name</label>
                            <p class="mb-0">{{ $doctor->first_name }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-bold">Last Name</label>
                            <p class="mb-0">{{ $doctor->last_name }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-bold">Email</label>
                            <p class="mb-0">
                                <a href="mailto:{{ $doctor->email }}" class="text-decoration-none">
                                    {{ $doctor->email }}
                                </a>
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-bold">Phone</label>
                            <p class="mb-0">
                                @if($doctor->phone)
                                    <a href="tel:{{ $doctor->phone }}" class="text-decoration-none">
                                        {{ $doctor->phone }}
                                    </a>
                                @else
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </p>
                        </div>
                        @if($doctor->age)
                            <div class="col-sm-6">
                                <label class="form-label fw-bold">Age</label>
                                <p class="mb-0">{{ $doctor->age }} years old</p>
                            </div>
                        @endif
                        @if($doctor->gender)
                            <div class="col-sm-6">
                                <label class="form-label fw-bold">Gender</label>
                                <p class="mb-0">{{ ucfirst($doctor->gender) }}</p>
                            </div>
                        @endif
                        @if($doctor->address)
                            <div class="col-12">
                                <label class="form-label fw-bold">Address</label>
                                <p class="mb-0">{{ $doctor->address }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Information -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-briefcase me-2"></i>Professional Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Years of Experience</label>
                            <p class="mb-0">{{ $doctorProfile->years_of_experience ?? 0 }} years</p>
                        </div>
                        @if($doctorProfile && $doctorProfile->biography)
                            <div class="col-12">
                                <label class="form-label fw-bold">Biography</label>
                                <p class="mb-0">{{ $doctorProfile->biography }}</p>
                            </div>
                        @endif
                        <div class="col-12">
                            <label class="form-label fw-bold">Status</label>
                            <span class="badge bg-{{ $doctor->is_active ? 'success' : 'danger' }}">
                                {{ $doctor->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services -->
        @if($doctorServices->count() > 0)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-stethoscope me-2"></i>Services Offered
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($doctorServices as $service)
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-2 bg-light rounded">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <div class="flex-grow-1">
                                            <strong>{{ $service->service_name }}</strong>
                                            @if($service->description)
                                                <br><small class="text-muted">{{ $service->description }}</small>
                                            @endif
                                            @if($service->pivot && $service->pivot->duration)
                                                <br><small class="text-info">Duration: {{ $service->pivot->duration }} minutes</small>
                                            @endif
                                        </div>
                                        <span class="badge bg-{{ $service->pivot && $service->pivot->is_active ? 'success' : 'secondary' }}">
                                            {{ $service->pivot && $service->pivot->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Schedule -->
        @if($doctorSchedules->count() > 0)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-alt me-2"></i>Weekly Schedule
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($doctorSchedules as $schedule)
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                        <div>
                                            <strong>{{ $schedule->day_of_week }}</strong>
                                            <br><small class="text-muted">
                                                {{ date('g:i A', strtotime($schedule->start_time)) }} - 
                                                {{ date('g:i A', strtotime($schedule->end_time)) }}
                                            </small>
                                        </div>
                                        <span class="badge bg-primary">
                                            Max {{ $schedule->max_patients }} patients
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Empty States for Services and Schedule -->
        @if($doctorServices->count() === 0)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-stethoscope me-2"></i>Services Offered
                        </h5>
                    </div>
                    <div class="card-body text-center py-4">
                        <i class="bi bi-stethoscope display-6 text-muted mb-3"></i>
                        <p class="text-muted">No services assigned yet</p>
                        <a href="{{ route('secretary.doctors.edit', $doctor) }}" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-plus me-1"></i>Assign Services
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if($doctorSchedules->count() === 0)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-alt me-2"></i>Weekly Schedule
                        </h5>
                    </div>
                    <div class="card-body text-center py-4">
                        <i class="bi bi-calendar-alt display-6 text-muted mb-3"></i>
                        <p class="text-muted">No schedule configured yet</p>
                        <a href="{{ route('secretary.doctors.edit', $doctor) }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-plus me-1"></i>Set Schedule
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2 mt-4">
        <a href="{{ route('secretary.doctors.edit', $doctor) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i>Edit Profile
        </a>
        <a href="{{ route('secretary.doctors.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Doctors
        </a>
    </div>
</div>
@endsection
