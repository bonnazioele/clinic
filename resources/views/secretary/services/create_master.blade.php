@extends('layouts.app')
@section('title','Create Service')
@section('content')
<div class="container py-4">
  @include('partials.alerts')
  <div class="medical-card p-4">
    <h2 class="fw-bold text-primary mb-3"><i class="bi bi-plus-circle me-2"></i>New Service</h2>
    <form method="POST" action="{{ route('secretary.services.store') }}">
      @csrf
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Name</label>
          <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
          @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Default Duration (minutes)</label>
          <input type="number" name="duration_minutes" value="{{ old('duration_minutes',30) }}" min="5" max="480" class="form-control @error('duration_minutes') is-invalid @enderror">
          @error('duration_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
          @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Attach to Clinics</label>
          <select name="clinic_ids[]" multiple class="form-select @error('clinic_ids') is-invalid @enderror" required size="5">
            @foreach($assignedClinics as $c)
              <option value="{{ $c->id }}" @if(collect(old('clinic_ids',[$defaultClinicId]))->contains($c->id)) selected @endif>{{ $c->name }}</option>
            @endforeach
          </select>
          <div class="form-text">Hold CTRL (Windows) to multi-select.</div>
          @error('clinic_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Create</button>
        <a href="{{ route('secretary.services.index') }}" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
