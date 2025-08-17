@extends('admin.layouts.app')

@section('title','Edit Service')

@section('content')
  <div class="container py-4">
    @include('partials.alerts')

    <div class="card medical-card shadow-sm">
      <div class="card-header bg-primary text-white d-flex align-items-center">
        <i class="bi bi-pencil-square me-2"></i>
        <h5 class="mb-0">Edit Service</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.services.update', $service) }}">
          @csrf
          @method('PATCH')

          <div class="mb-3">
            <label for="name" class="form-label"><i class="bi bi-gear me-1"></i>Service Name</label>
            <input type="text"
                   id="name"
                   name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $service->name) }}"
                   required>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="description" class="form-label"><i class="bi bi-card-text me-1"></i>Description <small class="text-muted">(optional)</small></label>
            <textarea id="description"
                      name="description"
                      class="form-control @error('description') is-invalid @enderror"
                      rows="4">{{ old('description', $service->description) }}</textarea>
            @error('description')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="clinic_ids" class="form-label"><i class="bi bi-building me-1"></i>Available at Clinics <span class="text-danger">*</span></label>
            <div class="form-text mb-2">Select which clinics will offer this service</div>
            <select name="clinic_ids[]" id="clinic_ids" class="form-select enhanced-multiselect @error('clinic_ids') is-invalid @enderror" multiple required>
              @foreach(\App\Models\Clinic::all() as $clinic)
                <option value="{{ $clinic->id }}" {{ in_array($clinic->id, old('clinic_ids', $service->clinics->pluck('id')->toArray())) ? 'selected' : '' }}>
                  {{ $clinic->name }} - {{ $clinic->branch_code }}
                </option>
              @endforeach
            </select>
            @error('clinic_ids')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-2"></i>Update Service
            </button>
            <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-x-circle me-2"></i>Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
