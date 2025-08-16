@extends('admin.layouts.app')

@section('title','Add Service')

@section('content')
  <h3 class="mb-4">Add Service</h3>

  <form method="POST" action="{{ route('admin.services.store') }}">
    @csrf

    <div class="mb-3">
      <label for="name" class="form-label">Service Name</label>
      <input type="text"
             id="name"
             name="name"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name') }}"
             required>
      @error('name')
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

    <div class="mb-3">
      <label for="clinic_ids" class="form-label">Available at Clinics <span class="text-danger">*</span></label>
      <div class="form-text mb-2">Select which clinics will offer this service</div>
      <select name="clinic_ids[]" id="clinic_ids" class="form-select @error('clinic_ids') is-invalid @enderror" multiple required>
        @foreach(\App\Models\Clinic::all() as $clinic)
          <option value="{{ $clinic->id }}" {{ in_array($clinic->id, old('clinic_ids', [])) ? 'selected' : '' }}>
            {{ $clinic->name }} - {{ $clinic->branch_code }}
          </option>
        @endforeach
      </select>
      @error('clinic_ids')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <button type="submit" class="btn btn-primary">Save Service</button>
    <a href="{{ route('admin.services.index') }}" class="btn btn-link">Cancel</a>
  </form>
@endsection
