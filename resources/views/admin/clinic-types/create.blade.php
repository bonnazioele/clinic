@extends('admin.layouts.app')

@section('title','Add Clinic type')

@section('content')
  <h3 class="mb-4">Add type</h3>

  <form method="POST" action="{{ route('admin.clinic-types.store') }}">
    @csrf

    <div class="mb-3">
      <label for="type_name" class="form-label">Clinic Type Name</label>
      <input type="text"
             id="type_name"
             name="type_name"
             class="form-control @error('type_name') is-invalid @enderror"
             value="{{ old('type_name') }}">
      @error('type_name')
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

    <button type="submit" class="btn btn-primary">Save Clinic Type</button>
    <a href="{{ route('admin.clinic-types.index') }}" class="btn btn-link">Cancel</a>
  </form>
@endsection

