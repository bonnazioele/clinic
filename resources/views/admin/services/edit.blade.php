@extends('admin.layouts.app')

@section('title','Edit Service')

@section('content')
  <h3 class="mb-4">Edit Service</h3>

  <form method="POST" action="{{ route('admin.services.update', $service) }}">
    @csrf
    @method('PATCH')

    <div class="mb-3">
      <label for="service_name" class="form-label">Service Name</label>
      <input type="text"
             id="service_name"
             name="service_name"
             class="form-control @error('service_name') is-invalid @enderror"
             value="{{ old('service_name', $service->service_name) }}"
             required>
      @error('service_name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3">
      <label for="description" class="form-label">Description <small class="text-muted">(optional)</small></label>
      <textarea id="description"
                name="description"
                class="form-control @error('description') is-invalid @enderror"
                rows="4">{{ old('description', $service->description) }}</textarea>
      @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <button type="submit" class="btn btn-primary">Update Service</button>
    <a href="{{ route('admin.services.index') }}" class="btn btn-link">Cancel</a>
  </form>
@endsection
