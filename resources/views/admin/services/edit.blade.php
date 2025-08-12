@extends('admin.layouts.app')

@section('title', 'Edit Service')

@section('content')
<div class="container-fluid px-0">
  <div class="card shadow-sm border-0 rounded-4 p-4">
    <h4 class="text-primary fw-bold mb-4">
      <i class="bi bi-pencil-square me-2"></i>Edit Service
    </h4>

    {{-- ✅ Toast Alert (Success) --}}
    <x-alerts.toast />

    <form method="POST" action="{{ route('admin.services.update', $service) }}">
      @csrf
      @method('PATCH')

      {{-- Service Name --}}
      <div class="mb-3">
        <label for="service_name" class="form-label">Service Name <span class="text-danger">*</span></label>
        <input type="text"
               id="service_name"
               name="service_name"
               class="form-control @error('service_name') is-invalid @enderror"
               placeholder="e.g. General Consultation"
               value="{{ old('service_name', $service->service_name) }}"
               required maxlength="100">
        @error('service_name')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Description --}}
      <div class="mb-3">
        <label for="description" class="form-label">Description <small class="text-muted">(optional)</small></label>
        <textarea id="description"
                  name="description"
                  class="form-control @error('description') is-invalid @enderror"
                  placeholder="e.g. Routine check-up, lab test consultation, etc."
                  rows="4">{{ old('description', $service->description) }}</textarea>
        @error('description')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Active Status --}}
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">
          Active
        </label>
      </div>

      {{-- Warning if service is in use --}}
      @if ($service->clinics()->count() > 0)
        <div class="mb-3">
          <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            This service is currently used by <strong>{{ $service->clinics()->count() }}</strong> clinic(s). Changing the name will update it system-wide—ensure it remains accurate.
          </small>
        </div>
      @endif

      {{-- Action Buttons --}}
      <div class="d-flex justify-content-end">
        <a href="{{ route('admin.services.index') }}" class="btn btn-link me-2">Cancel</a>
        <button type="submit" class="btn btn-success rounded-pill px-4">
          <i class="bi bi-save me-2"></i>Update Service
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
