{{-- resources/views/secretary/doctors/create.blade.php --}}
@extends('layouts.secretary')

@section('title','Add Doctor')

@section('content')
<div class="container-fluid page-container">
    @include('partials.alerts')

    <!-- Page Header with aligned content -->
    <div class="page-header">
        <h3 class="card-title">
            <i class="bi bi-person-plus me-2"></i>Add New Doctor
        </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('secretary.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('secretary.doctors.index') }}">Doctors</a></li>
                <li class="breadcrumb-item active">Add Doctor</li>
            </ol>
        </nav>
    </div>

    <!-- Doctor Creation Form -->
    <form method="POST" action="{{ route('secretary.doctors.store') }}" id="createDoctorForm">
        @csrf

        <!-- Personal Information Section -->
        <h5 class="section-title content-spacing-md">
            <i class="bi bi-person me-2"></i>Personal Information
        </h5>

        <!-- Name Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name"
                           class="form-control @error('first_name') is-invalid @enderror"
                           value="{{ old('first_name') }}" required
                           maxlength="255" placeholder="Enter first name">
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name"
                           class="form-control @error('last_name') is-invalid @enderror"
                           value="{{ old('last_name') }}" required
                           maxlength="255" placeholder="Enter last name">
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <!-- Contact Information Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required
                           maxlength="255" placeholder="doctor@example.com">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone') }}" maxlength="20"
                           placeholder="e.g., +1-234-567-8900">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <!-- Personal Details Row -->
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="">Select Gender</option>
                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                        <option value="other" @selected(old('gender') === 'other')>Other</option>
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Age</label>
                    <input type="number" name="age" min="18" max="100"
                           class="form-control @error('age') is-invalid @enderror"
                           value="{{ old('age') }}" placeholder="Age">
                    @error('age')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Birthdate</label>
                    <input type="date" name="birthdate"
                           class="form-control @error('birthdate') is-invalid @enderror"
                           value="{{ old('birthdate') }}">
                    @error('birthdate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <!-- Address -->
        <div class="mb-4">
            <label class="form-label">Address</label>
            <textarea name="address"
                      class="form-control @error('address') is-invalid @enderror"
                      rows="3" maxlength="500"
                      placeholder="Enter complete address">{{ old('address') }}</textarea>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <!-- Professional Information Section -->
        <h5 class="mb-3 text-primary">
            <i class="bi bi-briefcase me-2"></i>Professional Information
        </h5>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Years of Experience</label>
                    <input type="number" name="years_of_experience" min="0" max="50"
                           class="form-control @error('years_of_experience') is-invalid @enderror"
                           value="{{ old('years_of_experience', 0) }}"
                           placeholder="Years of experience">
                    @error('years_of_experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Biography <small class="text-muted">(optional)</small></label>
            <textarea name="biography"
                      class="form-control @error('biography') is-invalid @enderror"
                      rows="4" maxlength="1000"
                      placeholder="Brief professional biography, qualifications, or specializations">{{ old('biography') }}</textarea>
            @error('biography')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Brief description of qualifications, specializations, or experience</div>
        </div>

        <!-- Account Credentials Section -->
        <h5 class="mb-3 text-primary">
            <i class="bi bi-key me-2"></i>Account Credentials
        </h5>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required minlength="8" placeholder="Enter password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Minimum 8 characters</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation"
                           class="form-control" required minlength="8"
                           placeholder="Confirm password">
                </div>
            </div>
        </div>

        <!-- Service Assignment Section -->
        <h5 class="mb-3 text-primary">
            <i class="bi bi-stethoscope me-2"></i>Service Assignment
            <small class="text-muted">- {{ $clinic->name }}</small>
        </h5>

        @if($services->count() > 0)
            <div class="mb-4">
                <label class="form-label">Services Offered <small class="text-muted">(optional)</small></label>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" 
                            type="button" id="servicesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Select Services
                    </button>
                    <ul class="dropdown-menu w-100" aria-labelledby="servicesDropdown" 
                        style="max-height: 200px; overflow-y: auto;">
                        @foreach($services as $service)
                            <li>
                                <label class="dropdown-item">
                                    <input type="checkbox" 
                                           class="form-check-input me-2 service-checkbox" 
                                           name="services[]" 
                                           value="{{ $service->id }}"
                                           {{ in_array($service->id, old('services', [])) ? 'checked' : '' }}>
                                    <strong>{{ $service->service_name }}</strong>
                                    @if($service->description)
                                        <br><small class="text-muted">{{ $service->description }}</small>
                                    @endif
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @error('services')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                
                <!-- Selected Services Display -->
                <div id="selected-services" class="mt-2"></div>
            </div>
        @else
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle me-2"></i>
                No services are currently available at this clinic. You can assign services later.
            </div>
        @endif

        <!-- Weekly Schedule Section -->
        <h5 class="mb-3 text-primary">
            <i class="bi bi-calendar-alt me-2"></i>Weekly Schedule
            <small class="text-muted">(optional)</small>
        </h5>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-text">Set working hours for each day. You can add schedules later if needed.</div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addScheduleBtn">
                    <i class="bi bi-plus me-1"></i>Add Day
                </button>
            </div>
            
            <div id="scheduleContainer">
                <!-- Schedule items will be added here dynamically -->
            </div>
        </div>

        <!-- Form Actions -->
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Create Doctor
            </button>
            <a href="{{ route('secretary.doctors.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-times me-1"></i>Cancel
            </a>
        </div>
    </form>
</div>

<!-- Schedule Item Template (Hidden) -->
<template id="scheduleItemTemplate">
    <div class="schedule-item border rounded p-3 mb-3 bg-light" data-index="">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 text-primary">Schedule Entry</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-schedule">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Day of Week</label>
                    <select name="schedules[][day_of_week]" class="form-select" required>
                        <option value="">Select Day</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Start Time</label>
                    <input type="time" name="schedules[][start_time]" class="form-control" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">End Time</label>
                    <input type="time" name="schedules[][end_time]" class="form-control" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Max Patients</label>
                    <input type="number" name="schedules[][max_patients]" class="form-control" 
                           min="1" max="50" value="10" placeholder="10">
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let scheduleIndex = 0;
    
    // Services functionality
    const services = @json($services);
    const selectedPanel = document.getElementById('selected-services');

    function updateServicesPanel() {
        const checkboxes = document.querySelectorAll('.service-checkbox:checked');
        const selectedServices = [];
        
        checkboxes.forEach(checkbox => {
            const serviceId = parseInt(checkbox.value);
            const service = services.find(s => s.id === serviceId);
            if (service) {
                selectedServices.push(service);
            }
        });
        
        if (selectedServices.length > 0) {
            let html = '<div class="alert alert-light border"><strong>Selected Services:</strong><ul class="mb-0 mt-2">';
            selectedServices.forEach(service => {
                html += `<li>${service.service_name}</li>`;
            });
            html += '</ul></div>';
            selectedPanel.innerHTML = html;
        } else {
            selectedPanel.innerHTML = '';
        }
    }

    // Add event listeners to service checkboxes
    document.querySelectorAll('.service-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateServicesPanel);
    });

    // Initialize services panel
    updateServicesPanel();
    
    // Add schedule functionality
    document.getElementById('addScheduleBtn').addEventListener('click', function() {
        const template = document.getElementById('scheduleItemTemplate');
        const clone = template.content.cloneNode(true);
        
        // Update the data-index
        const scheduleItem = clone.querySelector('.schedule-item');
        scheduleItem.setAttribute('data-index', scheduleIndex);
        
        // Update input names with proper indexing
        const inputs = clone.querySelectorAll('input, select');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace('[]', `[${scheduleIndex}]`));
            }
        });
        
        // Add remove functionality
        clone.querySelector('.remove-schedule').addEventListener('click', function() {
            scheduleItem.remove();
        });
        
        document.getElementById('scheduleContainer').appendChild(clone);
        scheduleIndex++;
    });
    
    // Form validation
    document.getElementById('createDoctorForm').addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]').value;
        const confirmPassword = document.querySelector('input[name="password_confirmation"]').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        // Validate schedule times
        const scheduleItems = document.querySelectorAll('.schedule-item');
        let timeError = false;
        
        scheduleItems.forEach(item => {
            const startTime = item.querySelector('input[name*="start_time"]').value;
            const endTime = item.querySelector('input[name*="end_time"]').value;
            
            if (startTime && endTime && startTime >= endTime) {
                timeError = true;
            }
        });
        
        if (timeError) {
            e.preventDefault();
            alert('End time must be after start time for all schedules!');
            return false;
        }
    });
});
</script>
@endpush
