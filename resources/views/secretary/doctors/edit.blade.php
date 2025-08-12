@extends('layouts.secretary')
@section('title','Edit Doctor')

@section('content')
<div class="container-fluid page-container">
  @include('partials.alerts')

  <!-- Page Header with aligned content -->
  <div class="page-header">
    <div>
      <h3 class="card-title">Edit Doctor Profile</h3>
      <small class="meta-label">{{ $doctor->name }} at {{ $clinic->name }}</small>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('secretary.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('secretary.doctors.index') }}">Doctors</a></li>
        <li class="breadcrumb-item"><a href="{{ route('secretary.doctors.show', $doctor) }}">{{ $doctor->name }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
      </ol>
    </nav>
  </div>

  <form method="POST" action="{{ route('secretary.doctors.update',$doctor) }}">
    @csrf @method('PATCH')

    <!-- Clinic Information (Read-only display) -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-light">
        <h5 class="section-title">
          <i class="bi bi-building me-2"></i>Clinic Assignment
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info mb-0">
          <i class="bi bi-info-circle me-2"></i>
          This doctor is currently assigned to <strong>{{ $clinic->name }}</strong>. 
          Contact your administrator to change clinic assignments.
        </div>
      </div>
    </div>

    <!-- Personal Information -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="section-title text-white">
          <i class="bi bi-person me-2"></i>Personal Information
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="first_name" class="form-label fw-bold">First Name</label>
            <input type="text" 
                   class="form-control @error('first_name') is-invalid @enderror" 
                   id="first_name" 
                   name="first_name" 
                   value="{{ old('first_name', $doctor->first_name) }}" 
                   required>
            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label for="last_name" class="form-label fw-bold">Last Name</label>
            <input type="text" 
                   class="form-control @error('last_name') is-invalid @enderror" 
                   id="last_name" 
                   name="last_name" 
                   value="{{ old('last_name', $doctor->last_name) }}" 
                   required>
            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label for="email" class="form-label fw-bold">Email</label>
            <input type="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   id="email" 
                   name="email" 
                   value="{{ old('email', $doctor->email) }}" 
                   required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label for="phone" class="form-label fw-bold">Phone</label>
            <input type="tel" 
                   class="form-control @error('phone') is-invalid @enderror" 
                   id="phone" 
                   name="phone" 
                   value="{{ old('phone', $doctor->phone) }}">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label for="age" class="form-label fw-bold">Age</label>
            <input type="number" 
                   class="form-control @error('age') is-invalid @enderror" 
                   id="age" 
                   name="age" 
                   value="{{ old('age', $doctor->age) }}" 
                   min="18" 
                   max="100">
            @error('age')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label for="birthdate" class="form-label fw-bold">Birth Date</label>
            <input type="date" 
                   class="form-control @error('birthdate') is-invalid @enderror" 
                   id="birthdate" 
                   name="birthdate" 
                   value="{{ old('birthdate', $doctor->birthdate ? $doctor->birthdate->format('Y-m-d') : '') }}">
            @error('birthdate')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label for="gender" class="form-label fw-bold">Gender</label>
            <select class="form-select @error('gender') is-invalid @enderror" 
                    id="gender" 
                    name="gender">
              <option value="">Select Gender</option>
              <option value="male" {{ old('gender', $doctor->gender) == 'male' ? 'selected' : '' }}>Male</option>
              <option value="female" {{ old('gender', $doctor->gender) == 'female' ? 'selected' : '' }}>Female</option>
              <option value="other" {{ old('gender', $doctor->gender) == 'other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label for="address" class="form-label fw-bold">Address</label>
            <textarea class="form-control @error('address') is-invalid @enderror" 
                      id="address" 
                      name="address" 
                      rows="3">{{ old('address', $doctor->address) }}</textarea>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Professional Information -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-success text-white">
        <h5 class="section-title text-white">
          <i class="bi bi-briefcase me-2"></i>Professional Information
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="years_of_experience" class="form-label fw-bold">Years of Experience</label>
            <input type="number" 
                   class="form-control @error('years_of_experience') is-invalid @enderror" 
                   id="years_of_experience" 
                   name="years_of_experience" 
                   value="{{ old('years_of_experience', $doctor->doctor->years_of_experience ?? '') }}" 
                   min="0" 
                   max="50">
            @error('years_of_experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label for="biography" class="form-label fw-bold">Biography</label>
            <textarea class="form-control @error('biography') is-invalid @enderror" 
                      id="biography" 
                      name="biography" 
                      rows="4" 
                      placeholder="Brief professional biography, specializations, qualifications...">{{ old('biography', $doctor->doctor->biography ?? '') }}</textarea>
            @error('biography')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Password Change (Optional) -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-warning text-dark">
        <h5 class="section-title text-dark">
          <i class="bi bi-key me-2"></i>Change Password
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-warning">
          <i class="bi bi-exclamation-triangle me-2"></i>
          Leave password fields empty if you don't want to change the password.
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label for="password" class="form-label fw-bold">New Password</label>
            <input type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   id="password" 
                   name="password" 
                   minlength="8">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label for="password_confirmation" class="form-label fw-bold">Confirm Password</label>
            <input type="password" 
                   class="form-control" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   minlength="8">
          </div>
        </div>
      </div>
    </div>

    <!-- Services -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="section-title text-white">
          <i class="bi bi-stethoscope me-2"></i>Services Assignment
          <small class="text-light">- {{ $clinic->name }}</small>
        </h5>
      </div>
      <div class="card-body">
        @if($services->count() > 0)
          <label class="form-label fw-bold">Services Offered</label>
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
                           {{ in_array($service->id, old('services', $doctorServices)) ? 'checked' : '' }}>
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
        @else
          <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-2"></i>
            No services are currently available at this clinic.
          </div>
        @endif
      </div>
    </div>

    <!-- Weekly Schedule -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-info text-white">
        <h5 class="section-title text-white">
          <i class="bi bi-calendar-alt me-2"></i>Weekly Schedule
        </h5>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-text">Set working hours for each day at {{ $clinic->name }}</div>
          <button type="button" class="btn btn-sm btn-outline-primary" id="addScheduleBtn">
            <i class="bi bi-plus me-1"></i>Add Day
          </button>
        </div>
        
        <div id="scheduleContainer">
          <!-- Existing schedules will be populated here -->
          @if($doctor->doctor && $doctor->doctor->schedules->count() > 0)
            @foreach($doctor->doctor->schedules as $index => $schedule)
              <div class="schedule-item border rounded p-3 mb-3 bg-light" data-index="{{ $index }}">
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
                      <select name="schedules[{{ $index }}][day_of_week]" class="form-select" required>
                        <option value="">Select Day</option>
                        <option value="Monday" {{ $schedule->day_of_week == 'Monday' ? 'selected' : '' }}>Monday</option>
                        <option value="Tuesday" {{ $schedule->day_of_week == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                        <option value="Wednesday" {{ $schedule->day_of_week == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                        <option value="Thursday" {{ $schedule->day_of_week == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                        <option value="Friday" {{ $schedule->day_of_week == 'Friday' ? 'selected' : '' }}>Friday</option>
                        <option value="Saturday" {{ $schedule->day_of_week == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                        <option value="Sunday" {{ $schedule->day_of_week == 'Sunday' ? 'selected' : '' }}>Sunday</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="mb-3">
                      <label class="form-label">Start Time</label>
                      <input type="time" name="schedules[{{ $index }}][start_time]" class="form-control" 
                             value="{{ $schedule->start_time ? $schedule->start_time->format('H:i') : '' }}" required>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="mb-3">
                      <label class="form-label">End Time</label>
                      <input type="time" name="schedules[{{ $index }}][end_time]" class="form-control" 
                             value="{{ $schedule->end_time ? $schedule->end_time->format('H:i') : '' }}" required>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="mb-3">
                      <label class="form-label">Max Patients</label>
                      <input type="number" name="schedules[{{ $index }}][max_patients]" class="form-control" 
                             min="1" max="50" value="{{ $schedule->max_patients }}" placeholder="10">
                      <input type="hidden" name="schedules[{{ $index }}][id]" value="{{ $schedule->id }}">
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          @endif
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i>Save Changes
      </button>
      <a href="{{ route('secretary.doctors.show', $doctor) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Profile
      </a>
      <a href="{{ route('secretary.doctors.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-list me-1"></i>All Doctors
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
    // Get existing schedule count to continue indexing
    let scheduleIndex = {{ $doctor->doctor && $doctor->doctor->schedules ? $doctor->doctor->schedules->count() : 0 }};
    
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
    
    // Add remove functionality to existing schedules
    document.querySelectorAll('.remove-schedule').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.schedule-item').remove();
        });
    });
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]').value;
        const confirmPassword = document.querySelector('input[name="password_confirmation"]').value;
        
        if (password && confirmPassword && password !== confirmPassword) {
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
