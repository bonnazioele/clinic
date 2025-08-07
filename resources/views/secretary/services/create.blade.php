@extends('layouts.secretary')
@section('title','Add New Service')

@section('content')
<div class="container-fluid">
  @include('partials.alerts')

  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="mb-1">Add New Service</h2>
      <p class="text-muted mb-0">Create a new service for your clinic</p>
    </div>
    <div>
      <a href="{{ route('secretary.services.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>
        Back to Services
      </a>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
          <h5 class="card-title mb-0">
            <i class="bi bi-plus-circle me-2"></i>
            Service Information
          </h5>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('secretary.services.store') }}">
            @csrf

            <!-- Service Name -->
            <div class="mb-3">
              <label for="service_name" class="form-label">Service Name <span class="text-danger">*</span></label>
              <input type="text" 
                     class="form-control @error('service_name') is-invalid @enderror" 
                     id="service_name" 
                     name="service_name" 
                     value="{{ old('service_name') }}" 
                     placeholder="e.g., General Consultation, X-Ray, Blood Test"
                     required>
              @error('service_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Description -->
            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control @error('description') is-invalid @enderror" 
                        id="description" 
                        name="description" 
                        rows="3" 
                        placeholder="Describe what this service includes...">{{ old('description') }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">Optional: Provide details about what this service includes</div>
            </div>

            <!-- Duration -->
            <div class="mb-3">
              <label for="duration_minutes" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
              <div class="row">
                <div class="col-md-6">
                  <input type="number" 
                         class="form-control @error('duration_minutes') is-invalid @enderror" 
                         id="duration_minutes" 
                         name="duration_minutes" 
                         value="{{ old('duration_minutes', 30) }}" 
                         min="5" 
                         max="480" 
                         required>
                  @error('duration_minutes')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6">
                  <div class="form-text mt-2">
                    <small class="text-muted">Typical durations:</small><br>
                    <small class="text-muted">• Quick check-up: 15-20 min</small><br>
                    <small class="text-muted">• Consultation: 30-45 min</small><br>
                    <small class="text-muted">• Procedure: 60+ min</small>
                  </div>
                </div>
              </div>
            </div>

            <!-- Status -->
            <div class="mb-4">
              <div class="form-check">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="is_active" 
                       name="is_active" 
                       value="1" 
                       {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                  <strong>Active Service</strong>
                  <div class="form-text">When checked, this service will be available for appointments</div>
                </label>
              </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i>
                Create Service
              </button>
              <a href="{{ route('secretary.services.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-2"></i>
                Cancel
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <!-- Help Card -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-bottom">
          <h6 class="card-title mb-0">
            <i class="bi bi-info-circle me-2"></i>
            Service Guidelines
          </h6>
        </div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
            <li class="mb-2">
              <i class="bi bi-check-circle text-success me-2"></i>
              <small>Use clear, descriptive names</small>
            </li>
            <li class="mb-2">
              <i class="bi bi-check-circle text-success me-2"></i>
              <small>Set realistic duration times</small>
            </li>
            <li class="mb-2">
              <i class="bi bi-check-circle text-success me-2"></i>
              <small>Include relevant service details</small>
            </li>
            <li class="mb-2">
              <i class="bi bi-check-circle text-success me-2"></i>
              <small>Start with active status enabled</small>
            </li>
          </ul>
        </div>
      </div>

      <!-- Example Card -->
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-light border-bottom">
          <h6 class="card-title mb-0">
            <i class="bi bi-lightbulb me-2"></i>
            Service Examples
          </h6>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <strong>General Consultation</strong>
            <p class="text-muted small mb-1">Basic medical consultation and examination</p>
            <span class="badge bg-info">30 minutes</span>
          </div>
          <div class="mb-3">
            <strong>Blood Pressure Check</strong>
            <p class="text-muted small mb-1">Quick vital signs monitoring</p>
            <span class="badge bg-info">15 minutes</span>
          </div>
          <div>
            <strong>Physical Therapy</strong>
            <p class="text-muted small mb-1">Therapeutic exercise and rehabilitation</p>
            <span class="badge bg-info">60 minutes</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
