@extends('admin.layouts.app')

@section('title', 'Add Service')

@section('content')
<div class="container-fluid px-0">
  <div class="card shadow-sm border-0 rounded-4 p-4">
    <h4 class="text-primary fw-bold mb-4">
      <i class="bi bi-heart-pulse-fill me-2"></i>Add New Service
    </h4>

    {{-- ✅ Toast Alert (Success) --}}
    <x-alerts.toast />

    <form method="POST" action="{{ route('admin.services.store') }}">
      @csrf

      {{-- Service Name --}}
      <div class="mb-3">
        <label for="service_name" class="form-label">Service Name <span class="text-danger">*</span></label>
        <input type="text"
               id="service_name"
               name="service_name"
               class="form-control @error('service_name') is-invalid @enderror"
               placeholder="e.g. General Consultation"
               value="{{ old('service_name') }}"
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
                  placeholder="e.g. A routine check-up for non-emergency cases."
                  rows="4">{{ old('description') }}</textarea>
        @error('description')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Active Status --}}
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">
          Active
        </label>
      </div>

      {{-- Action Buttons --}}
      <div class="d-flex justify-content-end">
        <a href="{{ route('admin.services.index') }}" class="btn btn-link me-2">Cancel</a>
        <button type="submit" class="btn btn-primary rounded-pill px-4">
          <i class="bi bi-save me-2"></i>Save Service
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
