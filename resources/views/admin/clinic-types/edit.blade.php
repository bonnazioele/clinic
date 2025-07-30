@extends('admin.layouts.app')

@section('title','Edit Clinic Type')

@section('content')
  <h3 class="mb-4">Edit Clinic Type</h3>

  <form method="POST" action="{{ route('admin.clinic-types.update', $clinicType) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label for="type_name" class="form-label">Clinic Type Name</label>
      <input type="text"
             id="type_name"
             name="type_name"
             class="form-control @error('type_name') is-invalid @enderror"
             value="{{ old('type_name', $clinicType->type_name) }}"
             required maxlength="100">
      @error('type_name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3">
      <label for="description" class="form-label">Description <small class="text-muted">(optional)</small></label>
      <textarea id="description"
                name="description"
                class="form-control @error('description') is-invalid @enderror"
                rows="4">{{ old('description', $clinicType->description) }}</textarea>
      @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3">
      <small class="text-muted">
        <i class="fas fa-info-circle me-1"></i>
        This clinic type is currently used by <strong>{{ $clinicType->clinics()->count() }}</strong> clinic(s).
      </small>
    </div>

    <button type="submit" class="btn btn-primary">Update Clinic Type</button>
    <a href="{{ route('admin.clinic-types.index') }}" class="btn btn-link">Cancel</a>
  </form>
@endsection
