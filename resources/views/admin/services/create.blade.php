@extends('admin.layouts.app')

@section('title','Add Service')

@section('content')
  <h3 class="mb-4">Add Service</h3>

  <form method="POST" action="{{ route('admin.services.store') }}">
    @csrf

    <div class="mb-3">
      <label for="service_name" class="form-label">Service Name</label>
      <input type="text"
             id="service_name"
             name="service_name"
             class="form-control @error('service_name') is-invalid @enderror"
             value="{{ old('service_name') }}"
             required maxlength="100">
      @error('service_name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3">
      <label for="description" class="form-label">Description <small class="text-muted">(optional)</small></label>
      <textarea id="description"
                name="description"
                class="form-control @error('description') is-invalid @enderror"
                rows="4">{{ old('description') }}</textarea>
      @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">
        Active
      </label>
    </div>

    <button type="submit" class="btn btn-primary">Save Service</button>
    <a href="{{ route('admin.services.index') }}" class="btn btn-link">Cancel</a>
  </form>
@endsection
